<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horaire extends Model
{
     protected $fillable = [

        'jour_semaine',
        'heure_ouverture',
        'heure_fermeture',
        'id_utilisateur', // si tu le gères aussi
    ];
}
