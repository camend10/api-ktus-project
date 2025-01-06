@php
function getNombreUnidad($kardex_articulo, $unidad_id)
{
    $unidad_enc = null;
    foreach ($kardex_articulo['unidades'] as $key => $unidad) {
        if ($unidad->id == $unidad_id) {
            $unidad_enc = $unidad;
            break;
        }
    }
    return $unidad_enc ? $unidad_enc->nombre : '';
}
@endphp

@foreach ($kardex_articulos as $item)
    <table>
        <thead>
            <th colspan="5"><b>ARTICULO: {{ $item['nombre'] }}</b></th>
            <th colspan="3"><b>CODIGO: {{ $item['sku'] }}</b></th>
            <th colspan="3"><b>CATEGORIA: {{ $item['categoria'] }}</b></th>
        </thead>
    </table>
    <table>

        <thead>
            <tr>
                <th rowspan="1" colspan="2"></th>
                <th colspan="3" class="entrada">Entrada</th>
                <th colspan="3" class="salida">Salida</th>
                <th colspan="3" class="existencias">Existencias</th>
            </tr>
            <tr>
                <th rowspan="2">Fecha</th>
                <th rowspan="2">Detalle</th>
                <th colspan="9" class="subheader">{{ $item['unidad_first']->nombre }}</th>
                <!-- <th colspan="3" class="subheader">UNIDAD</th>
                <th colspan="3" class="subheader">UNIDAD</th> -->
            </tr>
            <tr>
                <th>Cantidad</th>
                <th>V/Unitario</th>
                <th>V/Total</th>
                <th>Cantidad</th>
                <th>V/Unitario</th>
                <th>V/Total</th>
                <th>Cantidad</th>
                <th>V/Unitario</th>
                <th>V/Total</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($item['movimiento_unidades'] as $mov_uni)
                @if ($mov_uni['unidad_id'] == $item['unidad_first']->id)
                    @foreach ($mov_uni['movimientos'] as $movimiento)
                        <tr>
                            <td>{{ $movimiento['fecha'] }}</td>
                            <td>{{ $movimiento['detalle'] }}</td>

                            @if ($movimiento['ingreso'])
                                <td>{{ $movimiento['ingreso']['cantidad'] }}</td>
                                <td>$ {{ number_format($movimiento['ingreso']['precio'], 2) }}</td>
                                <td>$ {{ number_format($movimiento['ingreso']['total'], 2) }}</td>
                            @else
                                <td></td>
                                <td></td>
                                <td></td>
                            @endif

                            @if ($movimiento['salida'])
                                <td>{{ $movimiento['salida']['cantidad'] }}</td>
                                <td>$ {{ number_format($movimiento['salida']['precio'], 2) }}</td>
                                <td>$ {{ number_format($movimiento['salida']['total'], 2) }}</td>
                            @else
                                <td></td>
                                <td></td>
                                <td></td>
                            @endif

                            <td>{{ $movimiento['existencia']['cantidad'] }}</td>
                            <td>$ {{ number_format($movimiento['existencia']['precio'], 2) }}</td>
                            <td>$ {{ number_format($movimiento['existencia']['total'], 2) }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach

            @foreach ($item['movimiento_unidades'] as $mov_uni)
                @if ($mov_uni['unidad_id'] != $item['unidad_first']->id)
                    <tr class="new-row">
                        <td colspan="2"></td>
                        <td colspan="9"><b>{{ getNombreUnidad($item, $mov_uni['unidad_id']) }}</b>
                        </td>
                        <!-- <td colspan="3"><b>CAJA</b></td>
                        <td colspan="3"><b>CAJA</b></td> -->
                    </tr>
                    <tr class="new-row">
                        <td><b>Fecha</b></td>
                        <td><b>Detalle</b></td>
                        <td><b>Cantidad</b></td>
                        <td><b>V/Unitario</b></td>
                        <td><b>V/Total</b></td>
                        <td><b>Cantidad</b></td>
                        <td><b>V/Unitario</b></td>
                        <td><b>V/Total</b></td>
                        <td><b>Cantidad</b></td>
                        <td><b>V/Unitario</b></td>
                        <td><b>V/Total</b></td>
                    </tr>

                    @foreach ($mov_uni['movimientos'] as $movimiento)
                        <tr>
                            <td>{{ $movimiento['fecha'] }}</td>
                            <td>{{ $movimiento['detalle'] }}</td>

                            @if ($movimiento['ingreso'])
                                <td>{{ $movimiento['ingreso']['cantidad'] }}</td>
                                <td>$ {{ number_format($movimiento['ingreso']['precio'], 2) }}</td>
                                <td>$ {{ number_format($movimiento['ingreso']['total'], 2) }}</td>
                            @else
                                <td></td>
                                <td></td>
                                <td></td>
                            @endif

                            @if ($movimiento['salida'])
                                <td>{{ $movimiento['salida']['cantidad'] }}</td>
                                <td>$ {{ number_format($movimiento['salida']['precio'], 2) }}</td>
                                <td>$ {{ number_format($movimiento['salida']['total'], 2) }}</td>
                            @else
                                <td></td>
                                <td></td>
                                <td></td>
                            @endif

                            <td>{{ $movimiento['existencia']['cantidad'] }}</td>
                            <td>$ {{ number_format($movimiento['existencia']['precio'], 2) }}</td>
                            <td>$ {{ number_format($movimiento['existencia']['total'], 2) }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach

        </tbody>
    </table>
@endforeach
