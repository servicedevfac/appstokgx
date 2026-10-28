<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['vente_id', 'montant',"reste_a_payer", 'mode_paiement', 'date_paiement'];
    public function vente()
{
    return $this->belongsTo(Vente::class);
}


}
