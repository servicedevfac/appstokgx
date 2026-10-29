<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class Horaire extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'jour_semaine',
        'heure_ouverture',
        'heure_fermeture',
        'id_utilisateur',
    ];
}
