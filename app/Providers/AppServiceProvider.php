<?php

namespace App\Providers;

use App\Models\Entreprise;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::before(function ($user, string $ability, array $arguments = []) {
            if (! $user->actif) {
                return false;
            }

            if ($user->hasRole('super admin') && ! $user->is_proprietaire) {
                return $user->hasPermissionInEntreprise($ability);
            }

            if (empty($arguments)) {
                if ($user->hasPermissionInEntreprise($ability)) {
                    return true;
                }

                $hasMultiEntrepriseRole = DB::table('entreprise_user')->where('user_id', $user->id)->exists()
                    || Entreprise::where('proprietaire_id', $user->id)->exists();

                if ($hasMultiEntrepriseRole) {
                    return false;
                }
            }

            return null;
        });
    }
}
