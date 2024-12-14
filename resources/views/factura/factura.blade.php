<style>
    /* Estilo general para tablas personalizadas */
    .custom-table {
        width: 100%;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        border-collapse: collapse;
        table-layout: fixed;
        /* Ajusta el ancho de las celdas automáticamente */
        margin-bottom: 20px;
        /* Espaciado entre tablas */
    }

    /* Encabezados */
    .custom-table th {
        font-weight: bold;
        text-align: left;
        /* Alineación del texto */
        background-color: #f3f3f3;
        /* Color de fondo para distinguir encabezados */
        padding: 8px;
        /* Espaciado interno */
        border: 1px solid #ccc;
        /* Bordes */
    }

    /* Celdas */
    .custom-table td {
        text-align: left;
        /* Alineación del texto */
        vertical-align: middle;
        /* Alineación vertical */
        word-wrap: break-word;
        /* Divide palabras largas */
        white-space: normal;
        /* Ajusta el texto en varias líneas */
        padding: 8px;
        /* Espaciado interno */
        border: 1px solid #ccc;
        /* Bordes */
    }

    /* Asegura que las celdas con datos extensos se ajusten */
    .custom-table td span {
        display: block;
        word-wrap: break-word;
        /* Divide palabras largas */
    }

    /* Resalta filas alternas para mejor legibilidad */
    .custom-table tr:nth-child(even) {
        background-color: #f9f9f9;
        /* Color alterno */
    }

    /* Alinear contenido numérico o de texto extenso */
    .custom-table .text-right {
        text-align: right;
    }

    .custom-table .text-center {
        text-align: center;
    }
</style>


<table style="font-size: 12px;width: 100%;font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <th
            style="font-size: 14px;font-weight: bold;font-family: Arial, Helvetica, sans-serif;vertical-align: middle;text-align: center;width: 100%;">
            {{ $empresa->nombre }}</th>
    </tr>
    <tr>
        <th
            style="font-size: 12px;font-weight: bold;font-family: Arial, Helvetica, sans-serif;vertical-align: middle;text-align: center;width: 100%;">
            Nit: <span style="font-weight: normal;">{{ $empresa->nit_empresa }} - {{ $empresa->dv }}</span>
        </th>
    </tr>
</table>

<br>

<hr style="border: .1px solid black;">

<table style="font-size: 10px; width: 100%; font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <th style="font-weight: bold; vertical-align: middle; width: 20%; text-align: left;">Ticket No.:</th>
        <td style="vertical-align: middle; width: 30%; text-align: right; font-weight: normal;">
            {{ sprintf('FAC-%06d', $factura->id) }}
        </td>
        <th style="font-weight: bold; vertical-align: middle; width: 10%; text-align: left;">Fecha:</th>
        <td style="vertical-align: middle; width: 40%; text-align: left; font-weight: normal;">
            {{ $factura->created_at->format('Y-m-d h:i A') }}
        </td>
    </tr>
    <tr>
        <th style="font-weight: bold; vertical-align: middle; width: 20%; text-align: left;">
            {{ $factura->cliente->tipodocumento->sigla }}:</th>
        <td style="vertical-align: middle; width: 30%; text-align: right; font-weight: normal;">
            {{ number_format($factura->cliente->identificacion, 0) }}
            {{ $factura->cliente->tipo_identificacion === 6 ? ' - ' . $factura->cliente->dv : '' }}
        </td>
        <th style="font-weight: bold; vertical-align: middle; width: 10%; text-align: left;">Cliente:</th>
        <td
            style="vertical-align: middle; width: 40%; text-align: left; text-transform: uppercase; font-weight: normal;">
            {{ $factura->cliente->nombres }}
            {{ $factura->cliente->tipo_identificacion === 1 ? $factura->cliente->apellidos : '' }}
        </td>
    </tr>
</table>

<table style="font-size: 10px;width: 100%;font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <th style="font-weight: bold;vertical-align: middle;width: 30%;text-align: left;">Forma de pago:</th>
        <th style="vertical-align: middle;width: 70%;text-align: left;font-weight: normal; text-transform: capitalize;"
            colspan="3">
            {{ isset($factura->factura_pago) &&
            $factura->factura_pago->first() &&
            $factura->factura_pago->first()->metodo_pago
                ? $factura->factura_pago->first()->metodo_pago->nombre
                : 'Sin método de pago' }}
            
            {{ $factura->factura_pago->first()?->banco_id != 9999999
                ? ' - ' .$factura->factura_pago->first()?->banco->nombre
                : '' }}

        </th>
    </tr>
</table>

<hr style="border: .1px solid black;">

<table style="font-size: 9px;width: 100%;font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;"
    class="">

    <thead>
        <tr>
            <th style="width: 5%;border: .1px solid #000;text-align: center;">#</th>
            <th style="width: 10%;text-align: left;border: .1px solid #000;overflow: hidden; text-overflow: ellipsis;">
                Codigo</th>
            <th style="text-align: left;border: .1px solid #000;overflow: hidden; text-overflow: ellipsis;">Descripción
            </th>
            <th style="width: 5%;text-align: center;border: .1px solid #000;">Cantidad</th>
            <th style="width: 20%;text-align: right;border: .1px solid #000;white-space: nowrap;">Valor Unidad</th>
            <th style="width: 20%;text-align: right;border: .1px solid #000;white-space: nowrap;">Valor Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($factura->detalles_facturas as $key => $item)
            <tr>
                <td style='vertical-align: middle;text-align: center;'>
                    {{-- {{ $loop->iteration }} --}}
                    {{ $key + 1 }}
                </td>
                <td style='vertical-align: middle;overflow: hidden; text-overflow: ellipsis;'>
                    {{ $item->articulo->sku }}
                </td>
                <td
                    style='vertical-align: middle;text-transform: capitalize;overflow: hidden; text-overflow: ellipsis;'>
                    {{ $item->articulo->nombre }}
                </td>
                <td style='vertical-align: middle;text-align: center;'>
                    {{ $item->cantidad_item }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->precio_item, 2) }}
                </td>
                <td style='vertical-align: middle;text-align: right;'>
                    $ {{ number_format($item->sub_total - $item->total_descuento + $item->total_iva, 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6">
                <hr style="border: .1px solid black;">
            </th>
        </tr>
        <tr>
            <td colspan="5" style="text-align: right; font-weight: bold;">Subtotal:</td>
            <td colspan="1" style="text-align: right;">$ {{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: right; font-weight: bold;">IVA:</td>
            <td colspan="1" style="text-align: right;">$ {{ number_format($iva, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: right; font-weight: bold;">Descuento:</td>
            <td colspan="1" style="text-align: right;">$ {{ number_format($descuento, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: right; font-weight: bold;">Total:</td>
            <td colspan="1" style="text-align: right;">$ {{ number_format($total, 2) }}</td>
        </tr>
    </tfoot>

</table>

<br><br>

<span
    style="font-weight: 500;font-family: Arial, Helvetica, sans-serif;font-size: 12px;text-align: center;width: 100%; ">Elaborado
    por: <span style="font-weight: bold;">
        {{ $usuario->name }}
    </span> el
    <span style="font-weight: bold;">{{ date('Y-m-d h:i A') }} </span>
</span>
<br>
<span
    style="font-weight: 500;font-family: Arial, Helvetica, sans-serif;font-size: 12px;text-align: center;width: 100%; ">Impreso
    en el software <span style="font-weight: bold;">K-TUS</span> </span>

<script>
    window.print();
</script>
