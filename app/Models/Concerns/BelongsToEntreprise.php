<?php

namespace App\Models\Concerns;

use App\Models\Entreprise;
use App\Services\EntrepriseContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToEntreprise
{
    public static function bootBelongsToEntreprise(): void
    {
        static::addGlobalScope('entreprise', function (Builder $builder) {
            if (EntrepriseContext::isBypassed()) {
                return;
            }

            $user = Auth::user();
            if (! $user) {
                return;
            }

            $entrepriseId = EntrepriseContext::currentId($user);
            $table = $builder->getModel()->getTable();

            if ($entrepriseId !== null) {
                $builder->where("{$table}.entreprise_id", $entrepriseId);
                return;
            }

            // Si des entreprises existent mais que l'utilisateur n'a accès à aucune (et n'est pas super admin)
            if (! $user->hasRole('super admin') && Entreprise::query()->exists()) {
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->entreprise_id)) {
                $entrepriseId = EntrepriseContext::currentId();
                if ($entrepriseId !== null) {
                    $model->entreprise_id = $entrepriseId;
                }
            }
        });
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function scopeForEntreprise(Builder $query, int $entrepriseId): Builder
    {
        return $query->withoutGlobalScope('entreprise')
            ->where($this->getTable() . '.entreprise_id', $entrepriseId);
    }

    public function scopeForEntreprises(Builder $query, array $entrepriseIds): Builder
    {
        return $query->withoutGlobalScope('entreprise')
            ->whereIn($this->getTable() . '.entreprise_id', $entrepriseIds);
    }
}

