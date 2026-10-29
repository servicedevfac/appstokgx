<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'vente_id',
        'user_id',
        'reference',
        'montant',
        'reste_a_payer',
        'mode_paiement',
        'type_operation',
        'date_paiement',
    ];

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
