<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'categorie_id',
        'nom',
        'reference',
        'prix_achat',
        'prix',
        'seuil_alerte',
        'alerte_envoyee',
        'last_alerted_at',
    ];

    protected $casts = [
        'prix_achat'      => 'decimal:2',
        'alerte_envoyee'  => 'boolean',
        'last_alerted_at' => 'datetime',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function details()
    {
        return $this->hasMany(Detail_vente::class, 'produit_id');
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class, 'produit_id');
    }

    public function getStockActuelAttribute()
    {
        $entree = $this->mouvements()->withoutGlobalScope('entreprise')->where('type_mouvement', 'entree')->sum('quantite');
        $sortie = $this->mouvements()->withoutGlobalScope('entreprise')->where('type_mouvement', 'sortie')->sum('quantite');
        return (int) ($entree - $sortie);
    }

    public function getStockAttribute(): int
    {
        return $this->getStockActuelAttribute();
    }
}
