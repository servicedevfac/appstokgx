<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'client_id',
        'user_id',
        'montant_total',
        'montant_initial',
        'remise',
        'date_vente',
        'date_echeance',
        'mode_paiement',
        'pdf_recu',
        'code_recu',
        'statut',
        'statut_paiement',
        'est_paye',
        'reste_a_payer',
        'montant_paye',
        'motif_annulation',
        'annule_par',
        'annule_at',
        'marchandise_recuperee',
        'montant_rembourse',
        'idempotency_key',
    ];

    protected $casts = [
        'est_paye'              => 'boolean',
        'marchandise_recuperee' => 'boolean',
        'annule_at'             => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function retours()
    {
        return $this->hasMany(RetourClient::class, 'vente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    public function details()
    {
        return $this->hasMany(Detail_vente::class, 'vente_id');
    }
}
