<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
// use Validator;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Exception\RuntimeException;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verificar']]);
    }


    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User();
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        return response()->json($user, 201);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $empresa_id = request()->empresa_id;
        $sede_id = request()->sede_id;

        $credentials = request(['email', 'password']);
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, $empresa_id, $sede_id);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Cerró sesión exitosamente']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {

        $guard = auth('api');
        if (! $guard instanceof JWTGuard) {
            throw new RuntimeException('Wrong guard returned.');
        }

        try {
            $token = $guard->refresh();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al renovar el token.'], 500);
        }

        return $this->respondWithToken($token);

        // $guard = auth('api');
        // if (! $guard instanceof JWTGuard) {
        //     throw new RuntimeException('Wrong guard returned.');
        // }
        // return $this->respondWithToken($guard->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $empresa_id = null, $sede_id = null)
    {
        $guard = auth('api');
        $user = $guard->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user instanceof \App\Models\User) {
            $permissions = $user->getAllPermissions()->map(function ($perm) {
                return $perm->name;
            });
        }

        if (! $guard instanceof JWTGuard) {
            throw new RuntimeException('Wrong guard returned.');
        }

        /** @var \App\Models\User $user */
        if (!is_null($sede_id)) {
            if ($user->role_id == 1) {
                $user->empresa_id = $empresa_id;
            }
            $user->sede_id = $sede_id;
            $user->save();
        }

        $sedes = $user->sedes->map(function ($sede) {
            return [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion,
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
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL(),
            // 'expiresIn' => JWTAuth::factory()->getTTL(),
            'user' => [
                'permissions' => $permissions,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'identificacion' => $user->identificacion,
                'usuario' => $user->usuario,
                'direccion' => $user->direccion,
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
            // 'user' => $guard->user()
        ]);
    }

    public function verificar()
    {
        $email = request()->email;

        $user = User::with([
            'empresa',
            'sedes'
        ])
            ->where('email', $email)
            ->where('estado', 1)
            ->first();

        $empresas = Empresa::with([
            'sedes'
        ])->where('estado', 1)->get();

        if ($user) {
            return response()->json([
                'sedes' => $user->sedes ?? [],
                'empresas' => $empresas,
                'role_id' => $user->role_id,
            ], 200);
        } else {
            return response()->json([
                'message' => 403,
                'error' => "El usuario no existe ",
            ], 200);
        }
    }
}
