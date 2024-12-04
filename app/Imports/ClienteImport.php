<?php

namespace App\Imports;

use App\Models\Clientes\CLiente;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Departamento;
use App\Models\Genero;
use App\Models\Municipio;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class ClienteImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;
    use SkipsErrors;

    // Declaración de propiedades
    private $filasOmitidas = 0;
    private $filasProcesadas = 0;
    private $totalProcesadas = 0;

    public function __construct()
    {
        $this->filasOmitidas = 0;
        $this->filasProcesadas = 0;
        $this->totalProcesadas = 0;
    }

    public function __destruct()
    {
        Log::info("Total de filas procesadas: {$this->filasProcesadas}");
        Log::info("Total de filas omitidas: {$this->filasOmitidas}");
        Log::info("Total de filas procesadas: {$this->totalProcesadas}");
    }

    public function sheet(): string
    {
        return 'Datos'; // Nombre exacto de la hoja que quieres procesar
    }

    // public function limit(): int
    // {
    //     return 2; // Procesa solo las primeras dos filas
    // }

    public function model(array $row)
    {

        // Ignorar filas vacías
        if (empty(array_filter($row, function ($value) {
            return !is_null($value) && trim($value) !== '';
        }))) {
            Log::info('Fila vacía o irrelevante omitida:', $row);
            $this->filasOmitidas++;
            return Cliente::first();
            return null;
        }

        // Validar que las claves necesarias estén presentes y no sean vacías
        if (empty($row['identificacion']) || empty($row['nombres'])) {
            Log::warning('Fila omitida por falta de datos requeridos (identificación o nombres):', $row);
            $this->filasOmitidas++;
            return Cliente::first();
            return null;
        }

        // Limpiar los valores de la fila (eliminar espacios innecesarios)
        $row = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        try {

            $user = auth('api')->user();
            if (!$user) {
                return Cliente::first();
            }

            // Log::info('Valores de la fila:', $row);


            // Buscar dependencias
            $tipo_identificacion = $this->findOrNull(TipoDocumento::class, 'nombre', $row["tipo_identificacion"], null);
            $departamento_id = $this->findOrNull(Departamento::class, 'nombre', $row["departamento_id"], null);
            $municipio_id = $this->findOrNull(Municipio::class, 'nombre', $row["municipio_id"], null);
            $sede_id = $this->findOrNull(Sede::class, 'nombre', $row["sede_id"], $user->empresa_id);
            $segmento_cliente_id = $this->findOrNull(SegmentoCliente::class, 'nombre', $row["tipo"], $user->empresa_id);
            $genero_id = $this->findOrNull(Genero::class, 'nombre', $row["genero"], null);


            // Calcular el DV usando el NIT del registro
            $dv = $this->calculateDV($row["identificacion"]);

            try {
                if (empty(trim($row['fecha_nacimiento']))) {
                    // Si el valor está vacío o nulo, asigna null
                    $fechaNacimiento = null;
                } elseif (is_numeric($row['fecha_nacimiento'])) {
                    // Si es un número de serie de Excel, conviértelo a una fecha
                    $fechaNacimiento = Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($row['fecha_nacimiento'] - 25569) * 86400))->format('Y-m-d');
                } elseif (preg_match('/\d{4}-\d{2}-\d{2}/', $row['fecha_nacimiento'])) {
                    // Si ya está en formato Y-m-d, úsala directamente
                    $fechaNacimiento = $row['fecha_nacimiento'];
                } else {
                    // Si no coincide con ningún formato esperado, lanza una excepción
                    throw new \Exception("Formato desconocido");
                }
            } catch (\Exception $e) {
                Log::error("Formato de fecha inválido para fecha_nacimiento: " . ($row['fecha_nacimiento'] ?? 'valor vacío'));
                $fechaNacimiento = null; // Permitir valores nulos si la fecha es inválida
            }

            if (!$tipo_identificacion || !$departamento_id || !$municipio_id || !$sede_id || !$segmento_cliente_id) {
                Log::error('Error: Dependencias faltantes para crear cliente.', [
                    'tipo_identificacion' => $tipo_identificacion ? $tipo_identificacion->toArray() : null,
                    'departamento_id' => $departamento_id ? $departamento_id->toArray() : null,
                    'municipio_id' => $municipio_id ? $municipio_id->toArray() : null,
                    'sede_id' => $sede_id ? $sede_id->toArray() : null,
                    'segmento_cliente_id' => $segmento_cliente_id ? $segmento_cliente_id->toArray() : null,
                ]);
                return null;
            }


            // Crear Artículo
            $cliente = Cliente::updateOrCreate(
                [
                    'identificacion' => $row["identificacion"], // Condiciones para verificar si el registro existe
                ],
                [
                    'nombres' => $row["nombres"],
                    'tipo_identificacion' => $tipo_identificacion->id,
                    'dv' => $dv, // Usar el DV calculado
                    'apellidos' => $row["apellidos"] ?? '',
                    'email' => $row["email"] ?? '',
                    'direccion' => $row["direccion"] ?? '',
                    'celular' => $row["celular"] ?? '',
                    'departamento_id' => $departamento_id->id,
                    'municipio_id' => $municipio_id->id,
                    'empresa_id' => $user->empresa_id,
                    'sede_id' => $sede_id->id,
                    'estado' => 1,
                    'fecha_nacimiento' => $fechaNacimiento ?? null,
                    'user_id' => $user->id,
                    'is_discount' => strtolower($row["adelanto"]) == "no" ? 1 : 2,
                    'segmento_cliente_id' => $segmento_cliente_id->id,
                    'genero_id' => $genero_id->id,
                ]
            );

            // Log::info('Cliente creado o actualizado:', ['cliente' => $cliente->toArray()]);
            // Log::info('Fila procesada correctamente:', ['row' => $row]);
            $this->filasProcesadas++;
            $this->totalProcesadas++;
            return $cliente;
        } catch (\Exception $e) {
            Log::error('Error en el método model:', [
                'message' => $e->getMessage(),
                'row' => $row,
            ]);
            $this->filasOmitidas++;
            return Cliente::first();
        }
    }

    /**
     * Calcula el Dígito de Verificación (DV) para un NIT dado.
     *
     * @param string $nit
     * @return string
     */
    protected function calculateDV(string $nit): string
    {
        if (empty($nit)) {
            return ''; // O devuelve un valor predeterminado, como '0'
        }

        $primeNumbers = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $nitArray = array_reverse(str_split($nit));
        $total = 0;

        foreach ($nitArray as $i => $digit) {
            $total += (int)$digit * $primeNumbers[$i];
        }

        $remainder = $total % 11;

        return $remainder > 1 ? (string)(11 - $remainder) : (string)$remainder;
    }

    /**
     * Buscar registro por nombre o columna.
     */
    protected function findOrNull($model, $column, $value, $empresaId = null)
    {
        $value = strtolower(trim($value));
        $query = $model::whereRaw("LOWER({$column}) = ?", [$value]);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        return $query->first();
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
            // 'tipo_identificacion' => ['required', 'string'], // Permitir nulo
            // 'identificacion' => ['required', 'numeric'], // Obligatorio
            // 'nombres' => ['required', 'string'], // Obligatorio
            // 'departamento_id' => ['required', 'string'], // Permitir nulo
            // 'municipio_id' => ['required', 'string'], // Obligatorio
            // 'adelanto' => ['required', 'in:SI,NO'], // Permitir nulo
            // 'tipo' => ['required', 'string'], // Permitir nulo
            // 'genero' => ['required', 'string'], // Permitir nulo
        ];
    }
}
