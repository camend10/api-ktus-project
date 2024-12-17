<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Articulos\Articulo;
use App\Models\Clientes\Cliente;
use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\Iva;
use App\Models\Configuracion\MetodoPago;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SedeDeliverie;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Configuracion\Unidad;
use App\Models\Facturas\Factura;
use App\Models\Movimientos\Movimiento;
use App\Models\Movimientos\Solicitud;
use App\Models\User;
use App\Policies\ArticuloPolicy;
use App\Policies\BodegaPolicy;
use App\Policies\CategoriaPolicy;
use App\Policies\ClientePolicy;
use App\Policies\FacturaPolicy;
use App\Policies\IvaPolicy;
use App\Policies\MetodoPagoPolicy;
use App\Policies\ProveedorPolicy;
use App\Policies\RolePolicy;
use App\Policies\SedeDeliveriePolicy;
use App\Policies\SedePolicy;
use App\Policies\SegmentoClientePolicy;
use App\Policies\SolicitudPolicy;
use App\Policies\UnidadPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        Bodega::class => BodegaPolicy::class,
        MetodoPago::class => MetodoPagoPolicy::class,
        SedeDeliverie::class => SedeDeliveriePolicy::class,
        Sede::class => SedePolicy::class,
        User::class => UserPolicy::class,
        SegmentoCliente::class => SegmentoClientePolicy::class,
        Categoria::class => CategoriaPolicy::class,
        Proveedor::class => ProveedorPolicy::class,
        Unidad::class => UnidadPolicy::class,
        Iva::class => IvaPolicy::class,
        Articulo::class => ArticuloPolicy::class,
        Cliente::class => ClientePolicy::class,
        Factura::class => FacturaPolicy::class,
        Solicitud::class => SolicitudPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super-Admin') ? true : null;
        });
    }
}
