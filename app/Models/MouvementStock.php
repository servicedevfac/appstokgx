<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
     protected $table = 'mouvement_stocks';

    // Supprime ceci si tu l’avais défini :
    // protected $primaryKey = 'id_mouvement_stock';

    protected $fillable = [
        'produit_id',
        'user_id',
        'vente_id',
        'quantite',
        'type_mouvement',
        'date_mouvement',
        'motif',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}










