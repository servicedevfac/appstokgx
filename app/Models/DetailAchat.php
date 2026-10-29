<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailAchat extends Model
{
    protected $table = 'detail_achats';

    protected $fillable = [
        'achat_id',
        'produit_id',
        'quantite',
        'quantite_recue',
        'prix_unitaire',
        'total',
    ];

    public function achat()
    {
        return $this->belongsTo(Achat::class, 'achat_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function getPrixAchatUnitaireAttribute(): float
    {
        return (float) $this->prix_unitaire;
    }

    public function getTotalLigneAttribute(): float
    {
        return (float) $this->total;
    }
}
