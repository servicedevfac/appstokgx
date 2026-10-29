<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class PaiementFournisseur extends Model
{
    use BelongsToEntreprise;

    protected $table = 'paiement_fournisseurs';

    protected $fillable = [
        'entreprise_id',
        'achat_id',
        'fournisseur_id',
        'user_id',
        'reference',
        'montant',
        'reste_a_payer',
        'mode_paiement',
        'date_paiement',
        'type_operation',
        'notes',
    ];

    public function achat()
    {
        return $this->belongsTo(Achat::class, 'achat_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

