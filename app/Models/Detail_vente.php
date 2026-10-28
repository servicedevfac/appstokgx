<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail_vente extends Model
{
    
     protected $fillable = [
        'vente_id', 'produit_id', 'quantite', 'prix', 'total'
    ];

    // 🔁 Une vente appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    // 🔁 Une vente est faite par un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    // 🔁 Une vente a plusieurs lignes de détail
    public function details()
    {
        return $this->hasMany(Detail_Vente::class, 'id_vente');
    }
    // 🔁 Une vente a plusieurs produits
    public function produits()
    {
        return $this->hasMany(Produit::class, 'id_vente');
    }
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
}
