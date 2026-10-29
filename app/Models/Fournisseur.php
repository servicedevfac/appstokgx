<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'code_fournisseur',
        'nom',
        'contact',
        'telephone',
        'email',
        'adresse',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->code_fournisseur)) {
                $prefix = 'FRN-' . date('my') . '-';
                $count = self::withoutGlobalScopes()
                    ->where('entreprise_id', $model->entreprise_id)
                    ->count() + 1;

                do {
                    $code = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
                    $count++;
                } while (self::withoutGlobalScopes()->where('entreprise_id', $model->entreprise_id)->where('code_fournisseur', $code)->exists());

                $model->code_fournisseur = $code;
            }
        });
    }

    public function achats()
    {
        return $this->hasMany(Achat::class, 'fournisseur_id');
    }

    public function paiements()
    {
        return $this->hasMany(PaiementFournisseur::class, 'fournisseur_id');
    }

    public function retours()
    {
        return $this->hasMany(RetourFournisseur::class, 'fournisseur_id');
    }

    public function getSoldeDetteAttribute(): float
    {
        return (float) $this->achats()
            ->where('statut_reception', '!=', 'annule')
            ->sum('reste_a_payer');
    }
}
