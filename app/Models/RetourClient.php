<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class RetourClient extends Model
{
    use BelongsToEntreprise;

    protected $table = 'retour_clients';

    protected $fillable = [
        'entreprise_id',
        'vente_id',
        'produit_id',
        'user_id',
        'quantite',
        'prix_unitaire',
        'montant_retour',
        'reintegrer_stock',
        'etat_marchandise',
        'montant_remboursable',
        'montant_rembourse',
        'motif',
        'date_retour',
        'statut',
    ];

    protected $casts = [
        'reintegrer_stock' => 'boolean',
    ];

    public function vente()
    {
        return $this->belongsTo(Vente::class, 'vente_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

