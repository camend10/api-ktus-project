<table>

    <thead>
        <tr>
            <th style="font-weight: bold;text-transform: uppercase;text-align: center;">#</th>
            <th width="20" style="font-weight: bold;">Código</th>
            <th width="60" style="font-weight: bold;text-transform: uppercase;">Articulo</th>
            <th width="80" style="font-weight: bold;">Descripción</th>
            <th width="30" style="font-weight: bold;text-transform: uppercase;">Categoria</th>
            <th width="10" style="font-weight: bold;">Precio</th>
            <th width="10" style="font-weight: bold;">Punto de pedido</th>
            <th width="10" style="font-weight: bold;text-align: center;">Unidad punto de pedido</th>
            <th width="10" style="font-weight: bold;text-align: center;">Tiempo de abastecimiento (dias)</th>
            <th width="30" style="font-weight: bold;text-transform: uppercase;">Proveedor</th>
            <th width="40" style="font-weight: bold;">Disponibilidad</th>
            <th width="30" style="font-weight: bold;">Tipo de impuesto</th>
            <th width="10" style="font-weight: bold;text-align: center;">Iva</th>
            <th width="20" style="font-weight: bold;text-align: center;">Descuento mínimo</th>
            <th width="20" style="font-weight: bold;text-align: center;">Descuento máximo</th>
            <th width="10" style="font-weight: bold;text-align: center;">Peso</th>
            <th width="10" style="font-weight: bold;text-align: center;">Ancho</th>
            <th width="10" style="font-weight: bold;text-align: center;">Alto</th>
            <th width="10" style="font-weight: bold;text-align: center;">Largo</th>
            <th width="10" style="font-weight: bold;text-transform: uppercase;text-align: center;">Estado</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($articulos as $key => $item)
            <tr>
                <td style='vertical-align: middle;text-align: center;'>
                    {{-- {{ $loop->iteration }} --}}
                    {{ $key + 1 }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->sku }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->nombre }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->descripcion ?? '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->categoria->nombre }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->precio_general }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->punto_pedido }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->unidad_punto_pedido->nombre }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->tiempo_de_abastecimiento }}
                </td>
                <td style="vertical-align: middle; text-transform: capitalize;">
                    {{ $item->proveedor->nombres }}
                    @if ($item->proveedor->tipo_identificacion == 1)
                        {{ $item->proveedor->apellidos }}
                    @endif
                </td>
                <td style='vertical-align: middle;'>
                    @php
                        $disponibilidad = '';
                        switch ((int) $item->disponibilidad) {
                            case 1:
                                $disponibilidad = 'Vender los articulos sin stock';
                                break;
                            case 2:
                                $disponibilidad = 'No vender los articulos sin stock';
                                break;
                            case 3:
                                $disponibilidad = 'Proyectar con los contratos que se tenga';
                                break;

                            default:
                                $disponibilidad = 'Seleccione Disponibilidad';
                                break;
                        }
                    @endphp
                    {{ $disponibilidad }}
                </td>
                <td style='vertical-align: middle;'>
                    @php
                        $impuesto = '';
                        switch ((int) $item->impuesto) {
                            case 1:
                                $impuesto = 'Libre de impuestos';
                                break;
                            case 2:
                                $impuesto = 'Bienes sujetos a impuestos';
                                break;
                            case 3:
                                $impuesto = 'Producto descargable';
                                break;

                            default:
                                $impuesto = 'Seleccione Impuesto';
                                break;
                        }
                    @endphp

                    {{ $impuesto }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->iva->porcentaje }} %
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->descuento_minimo }} %
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->descuento_maximo }} %
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->peso ?? 0 }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->ancho ?? 0 }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->alto ?? 0 }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->largo ?? 0 }}
                </td>
                <td style="vertical-align: middle; text-transform: capitalize;text-align: center;">
                    {{ $item->estado == 1 ? 'Activo' : 'Inactivo' }}
                </td>                
            </tr>
        @endforeach
    </tbody>

</table>
