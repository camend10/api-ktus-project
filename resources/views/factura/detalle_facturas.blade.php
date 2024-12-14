<table>

    <thead>
        <tr>
            <th width="15" style="font-weight: bold;text-align: center;">#</th>
            <th width="15" style="font-weight: bold;text-align: center;">Nº Factura</th>
            <th width="45" style="font-weight: bold;">Articulo</th>
            <th width="25" style="font-weight: bold;">Categoria</th>
            <th width="25" style="font-weight: bold;">Unidad</th>
            <th width="20" style="font-weight: bold;text-align: right;">Precio</th>
            <th width="20" style="font-weight: bold;text-align: right;">Cantidad</th>
            {{-- <th width="20" style="font-weight: bold;text-align: left;">Descuento</th>
            <th width="20" style="font-weight: bold;text-align: left;">Iva</th> --}}
            <th width="20" style="font-weight: bold;text-align: right;">Total descuento</th>
            <th width="20" style="font-weight: bold;text-align: right;">Total iva</th>
            <th width="20" style="font-weight: bold;text-align: right;">Subtotal</th>
            <th width="20" style="font-weight: bold;text-align: right;">Total</th>
            <th width="10" style="font-weight: bold;">Fecha de registro</th>
            <th width="10" style="font-weight: bold;">Empresa</th>
            <th width="10" style="font-weight: bold;">Sede</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($detalles as $key => $item)
            <tr>
                <td style='vertical-align: middle;text-align: center;'>
                    {{-- {{ $loop->iteration }} --}}
                    {{ $key + 1 }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ sprintf('FAC-%06d', $item->factura->id) }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->articulo->nombre }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->categoria->nombre }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->unidad->nombre }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->precio_item, 2) }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    {{ $item->cantidad_item }}
                </td>
                {{-- <td style='vertical-align: middle;text-align: left;'>
                    $ {{ number_format($item->descuento, 2) }}
                </td>
                <td style='vertical-align: middle;text-align: left;'>
                    $ {{ number_format($item->articulo->iva->porcentaje * $item->precio_item * 0.01, 2) }}
                </td> --}}
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
                    {{ $item->created_at->format('Y-m-d h:i A') }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->empresa ? $item->empresa->nombre : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->sede ? $item->sede->nombre : '' }}
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
