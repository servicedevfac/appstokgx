<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class RemboursementAvance extends Model
{
    use BelongsToEntreprise;

    protected $table = 'remboursement_avances';

    protected $fillable = [
        'entreprise_id',
        'avance_salaire_id',
        'employe_id',
        'user_id',
        'reference',
        'montant',
        'date_remboursement',
        'mode_remboursement',
        'notes',
    ];

    public function avance()
    {
        return $this->belongsTo(AvanceSalaire::class, 'avance_salaire_id');
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

