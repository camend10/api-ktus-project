<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $buscar = $request->get('buscar');
        $roles = $this->roleService->getRolesByFilter($buscar);

        return response()->json([
            'total' => $roles->total(),
            'roles' => $roles->map(function ($rol) {
                $rol->permission_pluck = $rol->permissions->pluck('name');
                $rol->created_format_at = $rol->created_at->format("Y-m-d h:i A");
                return $rol;
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $this->authorize('create', Role::class);

        $validated = $request->validated();

        $role = $this->roleService->storeRoles($validated["name"], $validated["permissions"]);

        return response()->json([
            'message' => 200,
            'message_text' => 'El rol se registró de manera exitosa',
            'role' => [
                "permission_pluck" => $role->permissions->pluck('name'),
                "created_format_at" => $role->created_at->format("Y-m-d h:i A"),
                "id" => $role->id,
                "name" => $role->name,
                "permissions" => $role->permissions
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, string $id)
    {
        $this->authorize('update', Role::class);
        $validated = $request->validated();
        // Log::info('Datos recibidos para sincronización', [
        //     'permissions' => $validated["permissions"],
        //     'role_id' => $id,
        // ]);
        
        $role = $this->roleService->updateRoles($validated, $id, $validated["permissions"]);

        return response()->json([
            'message' => 200,
            'message_text' => 'El rol se editó de manera exitosa',
            'role' => [
                "permission_pluck" => $role->permissions->pluck('name'),
                "created_format_at" => $role->created_at->format("Y-m-d h:i A"),
                "id" => $role->id,
                "name" => $role->name,
                "permissions" => $role->permissions
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', Role::class);
        $respuesta = $this->roleService->eliminarRoles($id);

        return response()->json([
            'message' => 200,
            'message_text' => 'Rol eliminado de manera exitosa',
        ]);
    }
}
