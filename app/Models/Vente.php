<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable=[
        'client_id',
        'user_id', // Changement de 'utilisateur_id' à 'user_id'
        // 'utilisateur_id', // Commenté pour éviter la confusion
        'montant_total',
        'remise',
        'date_vente',
        'mode_paiement',
        'pdf_recu',
        'code_recu',
        'statut',// Ajouté pour permettre l'enregistrement du code reçu
        'est_paye',
        'reste_a_payer',
        'montant_paye',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function paiements()
{
    return $this->hasMany(Paiement::class);
}



    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function details()
    {
        return $this->hasMany(Detail_vente::class);
    }


}
