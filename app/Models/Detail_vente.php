<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail_vente extends Model
{
    protected $table = 'detail_ventes';

    protected $fillable = [
        'vente_id',
        'produit_id',
        'quantite',
        'prix',
        'cout_achat_unitaire',
        'total',
    ];

    public function vente()
    {
        return $this->belongsTo(Vente::class, 'vente_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
}
