<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    use BelongsToEntreprise;

    protected $table = 'mouvement_stocks';

    protected $fillable = [
        'entreprise_id',
        'produit_id',
        'user_id',
        'vente_id',
        'achat_id',
        'quantite',
        'type_mouvement',
        'date_mouvement',
        'motif',
        'reference_externe',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function achat()
    {
        return $this->belongsTo(Achat::class);
    }

    public function getTypeAttribute(): ?string
    {
        return $this->type_mouvement;
    }
}
