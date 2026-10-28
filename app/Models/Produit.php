<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'nom',
        'prix',
        'seuil_alerte',
        'alerte_envoyee',
        'last_alerted_at',
    ];

 public function details()
    {
        return $this->hasMany(Detail_Vente::class, 'produit_id');
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class, 'produit_id');
    }

    public function getStockActuelAttribute()
    {
        $entree = $this->mouvements()->where('type_mouvement', 'entree')->sum('quantite');
        $sortie = $this->mouvements()->where('type_mouvement', 'sortie')->sum('quantite');
        return $entree - $sortie;
    }
}
