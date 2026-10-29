<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'matricule',
        'nom',
        'prenom',
        'telephone',
        'email',
        'poste',
        'salaire_base',
        'plafond_avance',
        'date_embauche',
        'actif',
    ];

    protected $casts = [
        'salaire_base'   => 'decimal:2',
        'plafond_avance' => 'decimal:2',
        'date_embauche'  => 'date',
        'actif'          => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->matricule)) {
                $prefix = 'EMP-';
                $count = self::withoutGlobalScopes()
                    ->where('entreprise_id', $model->entreprise_id)
                    ->count() + 1;

                do {
                    $matricule = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
                    $count++;
                } while (self::withoutGlobalScopes()->where('entreprise_id', $model->entreprise_id)->where('matricule', $matricule)->exists());

                $model->matricule = $matricule;
            }
        });
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function avances()
    {
        return $this->hasMany(AvanceSalaire::class, 'employe_id');
    }

    public function remboursements()
    {
        return $this->hasMany(RemboursementAvance::class, 'employe_id');
    }

    public function getPlafondEffectifAttribute(): float
    {
        if ($this->plafond_avance !== null && (float) $this->plafond_avance > 0) {
            return (float) $this->plafond_avance;
        }
        return (float) ($this->entreprise?->plafond_avance_salaire ?? 0);
    }

    public function getSoldeAvanceAttribute(): float
    {
        return (float) $this->avances()
            ->whereIn('statut', ['approuvee', 'en_cours', 'partiel'])
            ->sum('reste_a_rembourser');
    }

    public function getSoldeAvanceRestantAttribute(): float
    {
        return $this->getSoldeAvanceAttribute();
    }
}
