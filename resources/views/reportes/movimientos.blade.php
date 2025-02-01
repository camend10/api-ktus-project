<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            /* Tres columnas proporcionales */
            align-items: center;
            /* Alineación vertical */
            margin-bottom: 20px;
        }

        .header .logo {
            text-align: left;
        }

        .header .logo img {
            width: 50px;
            /* Tamaño reducido del logo */
            height: auto;
        }

        .header .software-name {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .header .software-name span {
            font-size: 12px;
            /* Subtítulo más pequeño */
            font-weight: normal;
        }

        .header .company-details {
            text-align: right;
            font-size: 10px;
            /* Reducir tamaño de fuente */
            color: #555;
            line-height: 1.2;
            /* Reducir espacio entre líneas */
        }

        h1 {
            text-align: center;
            font-size: 16px;
            /* Ajustar tamaño del título principal */
            color: #333;
            margin-bottom: 10px;
        }

        tr {
            page-break-inside: avoid;
            /* Evitar que una fila se divida en dos páginas */
            page-break-after: auto;
            /* Asegurar que el salto ocurra después de cada fila si es necesario */
        }

        thead {
            display: table-header-group;
            /* Repetir el encabezado en cada página */
        }

        tfoot {
            display: table-footer-group;
            /* Repetir el pie de tabla (si tienes uno) */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            /* Reducir espacio superior */
            page-break-inside: auto;
            /* Permitir que las tablas se dividan entre páginas */
        }

        table th,
        table td {
            /* border: 1px solid #ddd; */
            padding: 5px;
            /* Reducir relleno */
            text-align: left;
            font-size: 10px;
            /* Reducir tamaño de texto */
        }


        /* table th {
            background-color: #f4f4f4;
            color: #333;
        } */

        /* table tr:nth-child(even) {
            background-color: #f9f9f9;
        } */
        /*
        .low-stock {
            background-color: #ffcccc;
        } */

        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 8px;
            /* Reducir tamaño de texto */
            color: #555;
        }

        .page-break {
            page-break-before: always;
        }

        .d-flex {
            display: flex !important;
        }

        .justify-content-start {
            justify-content: flex-start !important;
        }

        .flex-column {
            flex-direction: column !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .text-muted {
            color: #99A1B7 !important;
        }

        .text-muted {
            --bs-text-opacity: 1;
            color: rgba(7, 20, 55, 0.75) !important;
        }

        .fw-semibold {
            font-weight: 500 !important;
        }

        .d-block {
            display: block !important;
        }

        .text-gray-600 {
            color: #78829D !important;
        }

        .text-gray-900 {
            color: #071437 !important;
        }

        .text-hover-primary {
            transition: color 0.2s ease;
        }

        .g-2,
        .gy-2 {
            --bs-gutter-y: 0.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
        }

        .badge-status {
            display: inline-block;
            width: 100px;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        .badge-warning {
            color: #000000;
            background-color: #F6C000;
        }

        .badge-danger {
            color: #ffffff;
            background-color: #D81A48;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

        .fs-6 {
            font-size: 0.70rem !important;
        }

        .fs-7 {
            font-size: 0.95rem !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        a {
            transition: color 0.2s ease;
        }

        a {
            color: rgba(27, 132, 255, 0.1, 1);
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="header" style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
        <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
            <tr>
                @php
                    // Verifica si la imagen existe y genera la ruta
                    $rutaLogo =
                        $empresa->imagen !== 'SIN-IMAGEN'
                            ? storage_path('app/public/' . $empresa->imagen)
                            : storage_path('app/public/empresas/blank.png');

                    // Verifica si el archivo realmente existe en el sistema
                    if (file_exists($rutaLogo)) {
                        $type = pathinfo($rutaLogo, PATHINFO_EXTENSION); // Obtiene el tipo de archivo (png, jpg, etc.)
                        $data = file_get_contents($rutaLogo); // Obtiene el contenido del archivo
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); // Convierte a Base64
                    } else {
                        // Si no existe el archivo, usa un marcador como respaldo
                        $base64 =
                            'data:image/png;base64,' .
                            base64_encode(file_get_contents(storage_path('app/public/empresas/blank.png')));
                    }
                @endphp
                <!-- Logo -->
                <td style="width: 20%; text-align: left; vertical-align: middle; border: none;">
                    <img src="{{ $base64 }}" alt="Logo Empresa"
                        style="width: 100px; height: auto; max-height: 100px;">
                </td>

                <!-- Nombre del Software -->
                <td style="width: 30%; text-align: right; vertical-align: middle; border: none;">
                    <span style="font-size: 14px; font-weight: bold;">{{ $software }}</span><br>
                    {{-- <span style="font-size: 12px; font-weight: normal;">Reporte de Baja Existencia</span> --}}
                </td>
                <!-- Datos de la Empresa -->
                <td
                    style="width: 50%; text-align: right; vertical-align: middle; font-size: 10px; line-height: 1.2; border: none;">
                    <strong>{{ $empresa->nombre }}</strong><br>
                    Dirección: {{ $empresa->direccion }}, {{ $empresa->municipio->nombre }},
                    {{ $empresa->departamento->nombre }}<br>
                    Correo electrónico: {{ $empresa->email }}<br>
                    Celular: {{ $empresa->celular }}
                </td>

            </tr>
        </table>
    </div>


    <!-- Título del reporte -->
    <h1 style="text-align: center; font-size: 16px; margin-bottom: 10px;">{{ $titulo }}</h1>

    <div style="margin: 0; border: 1px solid #004b70; border-radius: 8px; overflow: hidden;margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse; border-spacing: 0; margin: 0;">
            <thead>
                <tr style="background-color: #004b70; color: white;">
                    <th colspan="3" style="padding: 5px; font-size: 12px; text-align: left;">Datos de la busqueda
                    </th>
                </tr>
            </thead>
            <tbody>
                @if (!is_null($sede))
                    <tr>
                        <td style="padding: 5px; font-size: 10px;width: 15%;" class="fw-bold">Sede:</td>
                        <td style="padding: 5px; font-size: 10px; text-transform: capitalize;">{{ $sede->nombre }}</td>
                        <td style="padding: 5px; font-size: 10px; text-transform: capitalize;font-weight: bold;">Total
                            Entradas
                            :{{ $total_entradas }} {{ $unidad }}</td>
                    </tr>
                @endif

                @if (!is_null($bodega))
                    <tr>
                        <td style="padding: 5px; font-size: 10px;width: 15%;" class="fw-bold">Bodega:</td>
                        <td style="padding: 5px; font-size: 10px;">{{ $bodega->nombre }}</td>
                        <td style="padding: 5px; font-size: 10px; text-transform: capitalize;font-weight: bold;">Total
                            Salidas
                            :{{ $total_salidas }} {{ $unidad }}</td>
                    </tr>
                @endif

                @if (!is_null($nombreArticulo))
                    <tr>
                        <td style="padding: 5px; font-size: 10px;width: 15%;" class="fw-bold">Articulo:</td>
                        <td style="padding: 5px; font-size: 10px;">{{ $nombreArticulo }}</td>
                        <td>
                            <hr>
                        </td>
                    </tr>
                @endif

                @if (!is_null($fecha_inicio))
                    <tr>
                        <td style="padding: 5px; font-size: 10px;width: 15%;" class="fw-bold">Fecha:</td>
                        <td style="padding: 5px; font-size: 10px;text-transform: capitalize;"><strong>Desde: </strong>
                            {{ $fecha_inicio }}
                            <strong>Hasta: </strong>: {{ $fecha_final }}
                        </td>
                        <td style="padding: 5px; font-size: 10px; text-transform: capitalize;font-weight: bold;">Total
                            Existencia
                            :{{ $existencia_final }} {{ $unidad }}</td>
                    </tr>
                @endif

            </tbody>
        </table>
    </div>

    <p style="text-align: left; font-size: 12px; margin-bottom: 5px;">
        Total de movimientos: <strong>{{ $movimientos->count() }}</strong>
    </p>

    <div style="margin: 0; border: 1px solid #F8285A; border-radius: 8px; overflow: hidden;margin-bottom: 30px;">
        <table
            style="width: 100%; border-collapse: collapse; margin-top: 20px; border: none;border-spacing: 0; margin: 0;">
            <thead>
                <tr style="background-color: #F8285A; color: white; border: none;text-align: center;">
                    <th style="border: none; padding: 5px;">#</th>
                    <th style="border: none; padding: 5px;">Nº</th>
                    <th style="border: none; padding: 5px;">FECHA</th>
                    <th style="border: none; padding: 10px;">MOVIMIENTO</th>
                    <th style="border: none; padding: 10px;">DETALLE</th>
                    <th style="border: none; padding: 10px;">CANTIDAD</th>
                    <th style="border: none; padding: 10px;">ORIGEN</th>
                    <th style="border: none; padding: 10px;">USUARIO</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background-color: #f0f0f0;">
                    <td class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;"
                        colspan="2">

                    </td>
                    <td class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;"
                        colspan="2">

                    </td>

                    <td
                        class="border: none;font-weight: bold;text-wrap: nowrap;vertical-align: middle; padding: 5px;font-size: 10px;text-align: right;">
                        <strong> Existencia Inicial</strong>
                    </td>

                    <td
                        class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;">
                        <strong>{{ $existencia_inicial }} <span
                                style="text-transform: capitalize;">{{ $unidad }}</span></strong>
                    </td>

                    <td class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;"
                        colspan="2">

                    </td>
                </tr>
                @forelse ($movimientos as $item)
                    <tr>
                        <td
                            class="border: none; padding: 5px;font-size: 10px;text-center;text-wrap: nowrap;
                            vertical-align: middle;">
                            {{ $loop->iteration }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-transform: capitalize;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ $item['id'] }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-transform: capitalize;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ \Carbon\Carbon::parse($item['fecha'])->format('Y-m-d') }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-align: center;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ $item['tipo'] }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-align: center;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ $item['observacion'] }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-align: right;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ $item['cantidad'] }} {{ $unidad }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-align: center;text-transform: capitalize;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ $item['origen'] }}
                        </td>

                        <td style="border: none; padding: 5px;font-size: 10px;text-wrap: nowrap;
                        vertical-align: middle;text-align: center;"
                            class="text-gray-900 text-hover-primary mb-1 fs-6">
                            {{ $item['usuario'] }}
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8"
                            style="border: none; padding: 5px; text-align: center;font-size: 13px;font-weight: bold">No
                            existen movimientos con estos filtros</td>
                    </tr>
                @endforelse
                <tr style="background-color: #f0f0f0;">
                    <td class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;"
                        colspan="2">

                    </td>
                    <td class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;"
                        colspan="2">

                    </td>
                    <td
                        class="border: none;font-weight: bold;text-wrap: nowrap;vertical-align: middle; padding: 5px;font-size: 10px;text-align: right;">
                        <strong> Existencia Final</strong>
                    </td>

                    <td
                        class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;">
                        <strong>{{ $existencia_final }} <span
                                style="text-transform: capitalize;">{{ $unidad }}</span></strong>
                    </td>


                    <td class="border: none; padding: 5px;font-size: 10px;text-align: center;text-wrap: nowrap;
                    vertical-align: middle;"
                        colspan="2">

                    </td>
                </tr>
            </tbody>

        </table>
    </div>
    {{-- <div class="page-break"></div> --}}

    <div class="footer">
        Generado el: {{ now()->format('d/m/Y') }}
    </div>

    <script type="text/php">
        if(isset($pdf)){
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(270, 800, "Página " . $PAGE_NUM . " de ". $PAGE_COUNT, $font, 10);
            '
            );
        }
    </script>
</body>

</html>
