<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class AvanceSalaire extends Model
{
    use BelongsToEntreprise;

    protected $table = 'avance_salaires';

    protected $fillable = [
        'entreprise_id',
        'employe_id',
        'valide_par',
        'date_avance',
        'montant',
        'montant_rembourse',
        'reste_a_rembourser',
        'statut',
        'motif',
    ];

    protected $casts = [
        'date_avance'        => 'date',
        'montant'            => 'decimal:2',
        'montant_rembourse'  => 'decimal:2',
        'reste_a_rembourser' => 'decimal:2',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function remboursements()
    {
        return $this->hasMany(RemboursementAvance::class, 'avance_salaire_id');
    }
}
