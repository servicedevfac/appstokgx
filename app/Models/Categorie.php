<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'description',
    ];

    public function produits()
    {
        return $this->hasMany(Produit::class, 'categorie_id');
    }
}

