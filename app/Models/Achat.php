<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'fournisseur_id',
        'user_id',
        'numero_achat',
        'date_achat',
        'date_echeance',
        'date_reception',
        'montant_total',
        'montant_paye',
        'reste_a_payer',
        'statut_reception',
        'statut_paiement',
        'notes',
        'idempotency_key',
    ];

    protected $casts = [
        'montant_total' => 'decimal:2',
        'montant_paye'  => 'decimal:2',
        'reste_a_payer' => 'decimal:2',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(DetailAchat::class, 'achat_id');
    }

    public function paiements()
    {
        return $this->hasMany(PaiementFournisseur::class, 'achat_id');
    }

    public function retours()
    {
        return $this->hasMany(RetourFournisseur::class, 'achat_id');
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class, 'achat_id');
    }
}

