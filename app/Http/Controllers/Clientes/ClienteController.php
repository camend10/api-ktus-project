<?php

namespace App\Http\Controllers\Clientes;

use App\Exports\Cliente\ExportCliente;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\ClienteRequest;
use App\Http\Requests\Clientes\ImportClienteRequest;
use App\Http\Resources\Clientes\ClienteCollection;
use App\Http\Resources\Clientes\ClienteResource;
use App\Imports\ClienteImport;
use App\Models\Clientes\Cliente;
use App\Services\Clientes\ClienteService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cliente::class);

        $data = $request->all();
        $clientes = $this->clienteService->getByFilter($data);

        if (!$clientes) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $clientes->total(),
            'clientes' => ClienteCollection::make($clientes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClienteRequest $request)
    {
        $this->authorize('create', Cliente::class);

        $validated = $request->validated();

        $cliente = $this->clienteService->store($validated);

        if (!$cliente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El cliente se registró de manera exitosa',
            'cliente' => ClienteResource::make($cliente)
        ]);
    }

    public function update(ClienteRequest $request, string $id)
    {
        $this->authorize('update', Cliente::class);

        $validated = $request->validated();

        $cliente = $this->clienteService->update($validated, $id);

        if (!$cliente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El cliente se editó de manera exitosa',
            'cliente' => ClienteResource::make($cliente)
        ]);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Cliente::class);

        $cliente = $this->clienteService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Cliente activado de manera exitosa';
        } else {
            $texto = 'Cliente eliminado de manera exitosa';
        }

        if ($cliente == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Cliente no encontrado',
                'cliente' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'cliente' => ClienteResource::make($cliente)
        ]);
    }

    public function export_clientes(Request $request)
    {
        $data = $request->all();

        $clientes = $this->clienteService->getAllClientes($data);

        return Excel::download(new ExportCliente($clientes), 'Clientes_descargados.xlsx');
    }

    public function import_clientes(ImportClienteRequest $request)
    {
        $validated = $request->validated();

        $path = $request->file('import_file');

        $data = Excel::import(new ClienteImport(), $path);

        return response()->json([
            'message' => 200,
            'message_text' => 'Los clientes han sido importados exitosamente',
        ]);
    }

    public function buscarClientes(Request $request)
    {
        $data = $request->all();

        $clientes = $this->clienteService->getAllClientes($data);

        if (!$clientes) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'clientes' => ClienteCollection::make($clientes),
        ]);
    }
}
