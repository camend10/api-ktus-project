<table>

    <thead>
        <tr>
            <th width="15" style="font-weight: bold;text-align: center;">#</th>
            <th width="15" style="font-weight: bold;text-align: center;">Nº Factura</th>
            <th width="45" style="font-weight: bold;">Cliente</th>
            <th width="25" style="font-weight: bold;">Tipo de cliente</th>
            <th width="20" style="font-weight: bold;text-align: left;">Total descuento</th>
            <th width="20" style="font-weight: bold;text-align: left;">Total iva</th>
            <th width="20" style="font-weight: bold;text-align: left;">Subtotal</th>
            <th width="20" style="font-weight: bold;text-align: left;">Total venta</th>
            <th width="80" style="font-weight: bold;">Fecha de registro</th>
            <th width="30" style="font-weight: bold;">Estado factura</th>
            <th width="10" style="font-weight: bold;">Vendedor</th>
            <th width="10" style="font-weight: bold;">Forma de pago</th>
            <th width="10" style="font-weight: bold;">Lugar de entrega</th>
            <th width="10" style="font-weight: bold;">Descripción</th>
            <th width="10" style="font-weight: bold;">Empresa</th>
            <th width="10" style="font-weight: bold;">Sede</th>
            <th width="10" style="font-weight: bold;text-transform: uppercase;text-align: center;">Estado</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($facturas as $key => $item)
            <tr>
                <td style='vertical-align: middle;text-align: center;'>
                    {{-- {{ $loop->iteration }} --}}
                    {{ $key + 1 }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ sprintf('FAC-%06d', $item->id) }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->cliente->nombres }}
                    {{ $item->cliente->tipo_identificacion === 1 ? $item->cliente->apellidos : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ isset($item->cliente->segmento) ? $item->cliente->segmento->nombre : '' }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->total_descuento, 2) }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->total_iva, 2) }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->sub_total, 2) }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->sub_total - $item->total_descuento + $item->total_iva, 2) }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->created_at->format('Y-m-d') }}
                </td>
                <td style='vertical-align: middle;'>
                    @switch($item->estado_pago)
                        @case(1)
                            PENDIENTE
                        @break

                        @case(2)
                            PARCIAL
                        @break

                        @case(3)
                            PAGADA
                        @break

                        @default
                            DESCONOCIDO
                    @endswitch
                </td>

                <td style='vertical-align: middle;'>
                    {{ $item->usuario->name ?? '' }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ isset($item->factura_pago) && $item->factura_pago->first() && $item->factura_pago->first()->metodo_pago
                        ? $item->factura_pago->first()->metodo_pago->nombre
                        : 'Sin método de pago' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ isset($item->factura_deliverie) &&
                    isset($item->factura_deliverie->sede_deliverie) &&
                    $item->factura_deliverie->sede_deliverie->nombre
                        ? $item->factura_deliverie->sede_deliverie->nombre
                        : 'En Caja' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->descripcion ?? '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->empresa ? $item->empresa->nombre : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->sede ? $item->sede->nombre : '' }}
                </td>
                <td style="vertical-align: middle; text-transform: capitalize;text-align: center;">
                    {{ $item->estado == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
