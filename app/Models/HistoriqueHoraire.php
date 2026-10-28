<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueHoraire extends Model
{


    protected $fillable = [
        'jour_semaine',
        'ancienne_ouverture',
        'ancienne_fermeture',
        'nouvelle_ouverture',
        'nouvelle_fermeture',
        'id_utilisateur',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }
}
