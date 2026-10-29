<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class HistoriqueHoraire extends Model
{
    use BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
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
