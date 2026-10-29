<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'code_client',
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
    ];

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'client_id');
    }

    public function getSoldeCreanceAttribute(): float
    {
        return (float) $this->ventes()
            ->where('statut', 'valide')
            ->sum('reste_a_payer');
    }
}
