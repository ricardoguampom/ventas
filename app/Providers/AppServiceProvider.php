<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

use App\Models\User;
use App\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Blade directive personalizada
        Blade::if('perm', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // Registra dinámicamente todos los permisos en Laravel Gate
        if (app()->runningInConsole() === false) {
            try {
                foreach (Permission::pluck('name') as $permission) {
                    Gate::define($permission, function (User $user) use ($permission) {
                        return $user->hasPermission($permission);
                    });
                }
            } catch (\Throwable $e) {
                // Silencia errores si la tabla no existe en migraciones tempranas
            }
        }
    }
}
