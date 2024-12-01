<?php

namespace App\Imports;

use App\Models\Articulos\Articulo;
use App\Models\Articulos\ArticuloWallet;
use App\Models\Articulos\BodegaArticulo;
use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\Iva;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\Unidad;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\File;

class ArticuloImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;
    use SkipsErrors;

    // Propiedad estática para almacenar las imágenes procesadas
    protected static $processedImages = [];

    public function model(array $row)
    {

        try {
            $user = auth('api')->user();
            if (!$user) {
                return Articulo::first();
            }

            // Buscar dependencias
            $categoria = $this->findOrNull(Categoria::class, 'nombre', $row["categoria"], $user->empresa_id);
            $iva = $this->findNumericOrNull(Iva::class, 'porcentaje', $row["iva"], $user->empresa_id);
            $unidadPedido = $this->findOrNull(Unidad::class, 'nombre', $row["punto_pedido_unidad"], $user->empresa_id);
            $proveedor = $this->findOrNull(Proveedor::class, 'identificacion', $row["proveedor"], $user->empresa_id);


            if (!$categoria || !$iva || !$unidadPedido) {
                return Articulo::first();
            }

            // Llamar a la función para manejar la imagen
            $storedImagePath = $this->handleImageUpload($row['imagen'], $wasImageProcessed);

            // Mapas para valores específicos
            $disponibilidadKey = strtolower(trim($row["disponibilidad"]));
            $disponibilidad = $this->mapValue([
                'vender los articulos sin stock' => 1,
                'no vender los articulos sin stock' => 2,
                'proyectar con los contratos que se tenga' => 3,
            ], $disponibilidadKey, 2);

            $impuestoKey = strtolower(trim($row["impuesto"]));
            $impuesto = $this->mapValue([
                'libre de impuestos' => 1,
                'bienes sujetos a impuestos' => 2,
                'producto descargable' => 3,
            ], $impuestoKey, 1);

            // Crear Artículo
            $articulo = Articulo::updateOrCreate(
                [
                    'sku' => $row["sku"], // Condiciones para verificar si el registro existe
                    'nombre' => $row["nombre"],
                ],
                [
                'descripcion' => $row["descripcion"] ?: $row["nombre"],
                'precio_general' => $row["precio_general"],
                'punto_pedido' => $row["punto_pedido"],
                'tipo' => $row["tipo"],
                'imagen' => $storedImagePath, // Ruta de la imagen
                'iva_id' => $iva->id,
                'empresa_id' => $user->empresa_id,
                'estado' => 1,
                'especificaciones' => $row["especificaciones"] ?? null,
                'categoria_id' => $categoria->id,
                'is_gift' => strtolower($row["gratuito"]) == "no" ? 1 : 2,
                'descuento_maximo' => strtolower($row["descuento"]) == "no" ? 0 : $row["descuento_maximo"],
                'descuento_minimo' => strtolower($row["descuento"]) == "no" ? 0 : $row["descuento_minimo"],
                'tiempo_de_abastecimiento' => $row["tiempo_de_abastecimiento"],
                'disponibilidad' => $disponibilidad,
                'peso' => $row["peso"],
                'ancho' => $row["ancho"],
                'alto' => $row["alto"],
                'largo' => $row["largo"],
                'user_id' => $user->id,
                'punto_pedido_unidad_id' => $unidadPedido->id,
                'is_discount' => strtolower($row["descuento"]) == "no" ? 1 : 2,
                'impuesto' => $impuesto,
                'proveedor_id' => $proveedor ? $proveedor->id : NULL,
            ]);

            if (!$articulo) {
                return Articulo::first();
            }

            // Crear Existencia en Bodega
            $unidadBodega = $this->findOrNull(Unidad::class, 'nombre', $row["unidad_id_bod"], $user->empresa_id);
            $bodega = $this->findOrNull(Bodega::class, 'nombre', $row["bodega_id_bod"], $user->empresa_id);

            if ($unidadBodega && $bodega) {
                BodegaArticulo::create([
                    'articulo_id' => $articulo->id,
                    'bodega_id' => $bodega->id,
                    'cantidad' => $row["cantidad_bod"],
                    'estado' => 1,
                    'unidad_id' => $unidadBodega->id,
                    'empresa_id' => $user->empresa_id,
                ]);
            }

            // Crear Precio Múltiple del Artículo
            $unidadPrecio = $this->findOrNull(Unidad::class, 'nombre', $row["unidad_id_pre"], $user->empresa_id);
            $sede = $this->findOrNull(Sede::class, 'nombre', $row["sede_id_pre"], $user->empresa_id);

            if ($unidadPrecio) {
                ArticuloWallet::create([
                    'articulo_id' => $articulo->id,
                    'unidad_id' => $unidadPrecio->id,
                    'precio' => $row["precio_pre"],
                    'estado' => 1,
                    'empresa_id' => $user->empresa_id,
                    'sede_id' => $sede ? $sede->id : null,
                ]);
            }

            if ($articulo) {
                // Eliminar la imagen temporal solo si el artículo fue creado exitosamente
                if ($wasImageProcessed) {
                    $this->deleteTemporaryImage($row['imagen']);
                }
            }
            return $articulo;
        } catch (\Exception $e) {
            Log::error('Error en el método model:', [
                'message' => $e->getMessage(),
                'sku' => $row['sku'] ?? null,
            ]);
            return Articulo::first();
        }
    }

    /**
     * Guardar imagen desde el Excel.
     */
    protected function saveImageFromExcel($imageName)
    {
        $path = "articulos/{$imageName}";

        // Verificar si la imagen existe en el almacenamiento
        if (Storage::disk('public')->exists($path)) {
            return $path; // Retornar la ruta relativa si la imagen existe
        }

        // Retornar una imagen predeterminada si no existe
        return 'SIN-IMAGEN';
    }

    /**
     * Maneja la subida de imágenes desde la carpeta temporal al almacenamiento público.
     *
     * @param string $imageName Nombre de la imagen (extraído del Excel).
     * @param string $tempPath Carpeta temporal donde están las imágenes.
     * @param string $storagePath Carpeta en el almacenamiento público donde se guardarán las imágenes.
     * @param string $default Valor predeterminado si la imagen no existe.
     * @return string Ruta de la imagen en el almacenamiento o valor predeterminado.
     */

    protected function handleImageUpload($imageName, &$wasImageProcessed, $tempPath = 'uploads/excel-images', $storagePath = 'articulos', $default = 'SIN-IMAGEN')
    {
        $originalImagePath = storage_path("app/{$tempPath}/{$imageName}");

        if (file_exists($originalImagePath)) {
            $wasImageProcessed = true; // Indicar que la imagen fue procesada

            // Mover la imagen al almacenamiento público
            return Storage::putFileAs(
                $storagePath,
                new \Illuminate\Http\File($originalImagePath),
                $imageName
            );
        } else {
            $wasImageProcessed = false; // La imagen no fue procesada
            Log::warning("La imagen {$imageName} no existe en la carpeta temporal.");
        }

        return $default;
    }

    protected function deleteTemporaryImage($imageName, $tempPath = 'uploads/excel-images')
    {
        $filePath = storage_path("app/{$tempPath}/{$imageName}");
        if (File::exists($filePath)) {
            File::delete($filePath);
            Log::info("Imagen eliminada del directorio temporal: {$imageName}");
        } else {
            Log::warning("Intento de eliminar imagen no existente: {$imageName}");
        }
    }

    /**
     * Buscar registro por nombre o columna.
     */
    protected function findOrNull($model, $column, $value, $empresaId)
    {
        $value = strtolower(trim($value));
        return $value
            ? $model::whereRaw("LOWER({$column}) = ?", [$value])
            ->where('empresa_id', $empresaId)
            ->first()
            : null;
    }

    /**
     * Buscar registro numérico.
     */
    protected function findNumericOrNull($model, $column, $value, $empresaId)
    {
        $value = is_numeric($value) ? (float)$value : null;
        return $value !== null
            ? $model::where($column, $value)
            ->where('empresa_id', $empresaId)
            ->first()
            : null;
    }

    /**
     * Mapear valores o retornar predeterminado.
     */
    protected function mapValue(array $map, $key, $default)
    {
        return $map[$key] ?? $default;
    }

    public function rules(): array
    {
        return [
            '*.sku' => ['required'],
            '*.nombre' => ['required'],
            '*.precio_general' => ['required'],
            '*.punto_pedido' => ['required'],
            '*.iva' => ['required'],
            '*.categoria' => ['required'],
            '*.punto_pedido_unidad' => ['required'],
            '*.descuento' => ['required'],
            '*.impuesto' => ['required'],
            '*.disponibilidad' => ['required'],
            '*.descuento_maximo' => ['required'],
            '*.descuento_minimo' => ['required'],
            '*.tiempo_de_abastecimiento' => ['required'],
            '*.unidad_id_bod' => ['required'],
            '*.bodega_id_bod' => ['required'],
            '*.cantidad_bod' => ['required'],
            '*.unidad_id_pre' => ['required'],
            '*.sede_id_pre' => ['required'],
            '*.precio_pre' => ['required'],
        ];
    }

    protected function ensureTemporaryDirectory1()
    {
        $tempPath = storage_path('app/uploads/excel-images');
        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }
    }

    protected function ensureTemporaryDirectory($processedImages = [])
    {
        $tempPath = storage_path('app/uploads/excel-images');

        // Crear el directorio si no existe
        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }

        // Limpiar imágenes procesadas
        foreach ($processedImages as $imageName) {
            $filePath = "{$tempPath}/{$imageName}";
            if (File::exists($filePath)) {
                File::delete($filePath);
                Log::info("Imagen eliminada del directorio temporal: {$imageName}");
            }
        }
    }
}
