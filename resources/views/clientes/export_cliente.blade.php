<table>

    <thead>
        <tr>
            <th style="font-weight: bold;text-transform: uppercase;text-align: center;">#</th>
            <th width="30" style="font-weight: bold;">Nombre / Razón social</th>
            <th width="60" style="font-weight: bold;">Tipo de documento</th>
            <th width="80" style="font-weight: bold;">Identificación</th>
            <th width="80" style="font-weight: bold;">Genero</th>
            <th width="30" style="font-weight: bold;">Email</th>
            <th width="10" style="font-weight: bold;">Dirección</th>
            <th width="10" style="font-weight: bold;">Celular</th>
            <th width="10" style="font-weight: bold;">Departamento</th>
            <th width="10" style="font-weight: bold;">Municipio</th>
            <th width="10" style="font-weight: bold;">Sede</th>
            <th width="10" style="font-weight: bold;">Fecha de nacimiento</th>
            <th width="10" style="font-weight: bold;">Adelanto</th>
            <th width="10" style="font-weight: bold;">Tipo de cliente</th>
            <th width="10" style="font-weight: bold;text-transform: uppercase;text-align: center;">Estado</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($clientes as $key => $item)
            <tr>
                <td style='vertical-align: middle;text-align: center;'>
                    {{-- {{ $loop->iteration }} --}}
                    {{ $key + 1 }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->nombres }} {{ $item->tipo_identificacion === 1 ? $item->apellidos : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->tipodocumento->nombre }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->identificacion }} {{ $item->tipo_identificacion === 1 ? ' - ' . $item->dv : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize'>
                    {{ $item->genero ? $item->genero->nombre : '' }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->email ?? '' }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->direccion ?? '' }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->celular }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->departamento ? $item->departamento->nombre : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->municipio ? $item->municipio->nombre : '' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->sede ? $item->sede->nombre : '' }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->fecha_nacimiento ?? '' }}
                </td>
                <td style='vertical-align: middle;'>
                    {{ $item->is_parcial === 1 ? 'NO' : 'SI' }}
                </td>
                <td style='vertical-align: middle;text-transform: capitalize;'>
                    {{ $item->segmento ? $item->segmento->nombre : '' }}
                </td>
                <td style="vertical-align: middle; text-transform: capitalize;text-align: center;">
                    {{ $item->estado == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
