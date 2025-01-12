<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\UsuarioRequest;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    protected $userService;

    public function __construct(UsuarioService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $buscar = $request->get('buscar');
        $users = $this->userService->getUsersByFilter($buscar);

        return response()->json([
            'total' => $users->total(),
            'users' => $users->map(function ($user) {

                $sedes = $user->sedes->map(function ($sede) {
                    return [
                        'id' => $sede->id,
                        'codigo' => $sede->codigo,
                        'nombre' => $sede->nombre,
                        'direccion' => $sede->direccion ?? '',
                        'telefono' => $sede->telefono,
                        'celular' => $sede->celular,
                        'identificacion_responsable' => $sede->identificacion_responsable,
                        'responsable' => $sede->responsable,
                        'telefono_responsable' => $sede->telefono_responsable,
                        'empresa_id' => $sede->empresa_id,
                        'estado' => $sede->estado,
                        "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
                    ];
                })->toArray();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'identificacion' => $user->identificacion,
                    'usuario' => $user->usuario,
                    'direccion' => is_null($user->direccion) ? '' : $user->direccion,
                    'celular' => $user->celular,
                    'estado' => $user->estado,
                    'empresa_id' => $user->empresa_id,
                    'role_id' => $user->role_id,
                    'role' => $user->role,
                    'roles' => $user->roles,
                    'avatar' => $user->avatar != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $user->avatar : env("APP_URL") . "storage/users/blank.png",
                    // "storage/users/blank.png",
                    'genero_id' => $user->genero_id,
                    'departamento_id' => $user->departamento_id,
                    'municipio_id' => $user->municipio_id,
                    'tipo_doc_id' => $user->tipo_doc_id,
                    'fecha_nacimiento' => $user->fecha_nacimiento,
                    'sede_id' => $user->sede_id,
                    'nombre_sede' => $user->sede->nombre,
                    'sedes' => $sedes,
                    'sigla' => $user->tipodocumento->sigla,
                    'empresa' => $user->empresa->nombre,
                    "created_format_at" => $user->created_at ? $user->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UsuarioRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        if ($request->password) {
            $request->request->add(["password" => bcrypt($request->password)]);
        }

        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("users", $request->file("imagen"));
            // $request->request->add(["avatar" => $path]);
            $validated['avatar'] = $path;
        } else {
            // $request->request->add(["avatar" => "SIN-IMAGEN"]);
            $validated['avatar'] = "SIN-IMAGEN";
        }

        // if ($request->has('sedes')) {
        //     $sede_id = $request->input('sedes')[0] ?? null;
        //     $request->merge(['sede_id' => $sede_id]);
        // }        

        // Agregar la primera sede como 'sede_id'
        // $request->merge(['sede_id' => $request->input('sedes')[0]]);

        $validated['sede_id'] = $request->input('sedes')[0] ?? null;
        $validated['sedes'] = $request->sedes;

        $user = $this->userService->storeUser($validated, $request->role_id);

        if ($user == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'No se pudo realizar la acción',
                'user' => []
            ], 403);
        }

        $sedes = $user->sedes->map(function ($sede) {
            return [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion ?? '',
                'telefono' => $sede->telefono,
                'celular' => $sede->celular,
                'identificacion_responsable' => $sede->identificacion_responsable,
                'responsable' => $sede->responsable,
                'telefono_responsable' => $sede->telefono_responsable,
                'empresa_id' => $sede->empresa_id,
                'estado' => $sede->estado,
                "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
            ];
        })->toArray();

        return response()->json([
            'message' => 200,
            'message_text' => 'El usuario se registró de manera exitosa',

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'identificacion' => $user->identificacion,
                'usuario' => $user->usuario,
                'direccion' => is_null($user->direccion) ? '' : $user->direccion,
                'celular' => $user->celular,
                'estado' => $user->estado,
                'empresa_id' => $user->empresa_id,
                'role_id' => $user->role_id,
                'role' => $user->role,
                'roles' => $user->roles,
                // 'avatar' => $user->avatar != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $user->avatar : env("APP_URL") . "storage/users/blank.png",
                'avatar' => $user->avatar !== 'SIN-IMAGEN'
                    ? url('storage/' . $user->avatar)
                    : url('storage/users/blank.png'),
                'genero_id' => $user->genero_id,
                'departamento_id' => $user->departamento_id,
                'municipio_id' => $user->municipio_id,
                'tipo_doc_id' => $user->tipo_doc_id,
                'fecha_nacimiento' => $user->fecha_nacimiento,
                'sede_id' => $user->sede_id,
                'nombre_sede' => $user->sede->nombre,
                'sedes' => $sedes,
                'sigla' => $user->tipodocumento->sigla,
                'empresa' => $user->empresa->nombre,
                "created_format_at" => $user->created_at ? $user->created_at->format("Y-m-d h:i A") : ''
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
    public function update(UsuarioRequest $request, string $id)
    {
        $this->authorize('update', User::class);

        $validated = $request->validated();

        // if ($request->password) {
        //     $request->request->add(["password" => bcrypt($request->password)]);
        // }

        $user = $this->userService->getUserById($request->id);

        if ($request->hasFile("imagen")) {
            if ($user->avatar && $user->avatar !== 'SIN-IMAGEN') {
                Storage::delete($user->avatar);
            }

            $path = Storage::putFile("users", $request->file("imagen"));
            $validated['avatar'] = $path;
            // $request->request->add(["avatar" => $path]);
            // $request->merge(['avatar' => $path]);
        } else {
            // $request->request->add(["avatar" => "SIN-IMAGEN"]);
            // $request->merge(['avatar' => $user->avatar ?? 'SIN-IMAGEN']);
            $validated['avatar'] = $user->avatar ?? 'SIN-IMAGEN';
        }

        // if ($request->has('sedes')) {
        //     $sede_id = $request->input('sedes')[0] ?? null;
        //     $request->merge(['sede_id' => $sede_id]);
        // }        

        // Agregar la primera sede como 'sede_id'
        // $request->merge(['sede_id' => $request->input('sedes')[0]]);

        $validated['sede_id'] = $request->input('sedes')[0] ?? null;
        $validated['sedes'] = $request->sedes;

        $user = $this->userService->updateUser($validated, $request->role_id, $request->id);

        $sedes = $user->sedes->map(function ($sede) {
            return [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion ?? '',
                'telefono' => $sede->telefono,
                'celular' => $sede->celular,
                'identificacion_responsable' => $sede->identificacion_responsable,
                'responsable' => $sede->responsable,
                'telefono_responsable' => $sede->telefono_responsable,
                'empresa_id' => $sede->empresa_id,
                'estado' => $sede->estado,
                "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
            ];
        })->toArray();

        return response()->json([
            'message' => 200,
            'message_text' => 'El usuario se editó de manera exitosa',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'identificacion' => $user->identificacion,
                'usuario' => $user->usuario,
                'direccion' => is_null($user->direccion) ? '' : $user->direccion,
                'celular' => $user->celular,
                'estado' => $user->estado,
                'empresa_id' => $user->empresa_id,
                'role_id' => $user->role_id,
                'role' => $user->role,
                'roles' => $user->roles,
                'avatar' => $user->avatar != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $user->avatar : env("APP_URL") . "storage/users/blank.png",
                'genero_id' => $user->genero_id,
                'departamento_id' => $user->departamento_id,
                'municipio_id' => $user->municipio_id,
                'tipo_doc_id' => $user->tipo_doc_id,
                'fecha_nacimiento' => $user->fecha_nacimiento,
                'sede_id' => $user->sede_id,
                'nombre_sede' => $user->sede->nombre,
                'sedes' => $sedes,
                'sigla' => $user->tipodocumento->sigla,
                'empresa' => $user->empresa->nombre,
                "created_format_at" => $user->created_at ? $user->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstadoUser(Request $request, $id)
    {
        $this->authorize('delete', User::class);

        $user = $this->userService->cambiarEstadoUser($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Usuario activado de manera exitosa';
        } else {
            $texto = 'Usuario eliminado de manera exitosa';
        }

        if ($user == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Usuario no encontrado',
                'user' => []
            ], 403);
        }

        $sedes = $user->sedes->map(function ($sede) {
            return [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion ?? '',
                'telefono' => $sede->telefono,
                'celular' => $sede->celular,
                'identificacion_responsable' => $sede->identificacion_responsable,
                'responsable' => $sede->responsable,
                'telefono_responsable' => $sede->telefono_responsable,
                'empresa_id' => $sede->empresa_id,
                'estado' => $sede->estado,
                "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
            ];
        })->toArray();

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'identificacion' => $user->identificacion,
                'usuario' => $user->usuario,
                'direccion' => is_null($user->direccion) ? '' : $user->direccion,
                'celular' => $user->celular,
                'estado' => $user->estado,
                'empresa_id' => $user->empresa_id,
                'role_id' => $user->role_id,
                'role' => $user->role,
                'roles' => $user->roles,
                'avatar' => $user->avatar != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $user->avatar : env("APP_URL") . "storage/users/blank.png",
                'genero_id' => $user->genero_id,
                'departamento_id' => $user->departamento_id,
                'municipio_id' => $user->municipio_id,
                'tipo_doc_id' => $user->tipo_doc_id,
                'fecha_nacimiento' => $user->fecha_nacimiento,
                'sede_id' => $user->sede_id,
                'nombre_sede' => $user->sede->nombre,
                'sedes' => $sedes,
                'sigla' => $user->tipodocumento->sigla,
                'empresa' => $user->empresa->nombre,
                "created_format_at" => $user->created_at ? $user->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
