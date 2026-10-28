<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'code_client',
        'nom',
        'prenom',
        'telephone',
        'adresse',
    ];

    // Méthode boot pour gérer la génération automatique du code client
    protected static function boot()
    {
        parent::boot();

        // static::created(function ($client) {
        //     $client->code_client = 'CLI-' . str_pad($client->id, 6, '0', STR_PAD_LEFT);
        //     $client->save();
        // });
    }
    // 🔁 Un client peut avoir plusieurs ventes
    public function ventes()
    {
        return $this->hasMany(Vente::class, 'client_id');
    }
}




