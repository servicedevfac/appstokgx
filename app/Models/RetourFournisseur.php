<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class RetourFournisseur extends Model
{
    use BelongsToEntreprise;

    protected $table = 'retour_fournisseurs';

    protected $fillable = [
        'entreprise_id',
        'achat_id',
        'fournisseur_id',
        'produit_id',
        'user_id',
        'quantite',
        'prix_unitaire',
        'montant_retour',
        'motif',
        'date_retour',
        'statut',
    ];

    public function achat()
    {
        return $this->belongsTo(Achat::class, 'achat_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
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

