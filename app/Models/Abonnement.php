<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;

    protected $table = 'abonnements';

    public const PLANS = [
        'essai' => [
            'nom'              => 'Période d\'Essai SaaS',
            'prix_mensuel'     => 0,
            'prix_annuel'      => 0,
            'max_utilisateurs' => 3,
            'max_produits'     => 100,
            'description'      => 'Découverte complète pendant 14 jours.',
        ],
        'starter' => [
            'nom'              => 'Pack Essentiel (Starter)',
            'prix_mensuel'     => 15000,
            'prix_annuel'      => 150000,
            'max_utilisateurs' => 3,
            'max_produits'     => 500,
            'description'      => 'Idéal pour une boutique : ventes, stocks, clients et tickets PDF.',
        ],
        'pro' => [
            'nom'              => 'Pack Business Pro',
            'prix_mensuel'     => 35000,
            'prix_annuel'      => 350000,
            'max_utilisateurs' => 10,
            'max_produits'     => null, // Illimité
            'description'      => 'Pour PME : achats fournisseurs, ventes à crédit, trésorerie, employés et import/export Excel.',
        ],
        'enterprise' => [
            'nom'              => 'Pack Enterprise Multi-Sites',
            'prix_mensuel'     => 75000,
            'prix_annuel'      => 750000,
            'max_utilisateurs' => null, // Illimité
            'max_produits'     => null, // Illimité
            'description'      => 'Multi-entreprises illimité, vue consolidée Propriétaire, audit complet et support prioritaire.',
        ],
    ];

    protected $fillable = [
        'entreprise_id',
        'plan',
        'nom_plan',
        'periodicite',
        'montant',
        'devise',
        'date_debut',
        'date_fin',
        'statut',
        'max_utilisateurs',
        'max_produits',
        'mode_paiement',
        'reference_paiement',
        'notes',
        'cree_par',
    ];

    protected $casts = [
        'montant'          => 'decimal:2',
        'date_debut'       => 'date',
        'date_fin'         => 'date',
        'max_utilisateurs' => 'integer',
        'max_produits'     => 'integer',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function isExpired(): bool
    {
        if ($this->statut === 'expire') {
            return true;
        }
        if ($this->date_fin && Carbon::parse($this->date_fin)->endOfDay()->isPast()) {
            return true;
        }
        return false;
    }

    public function isActive(): bool
    {
        if (in_array($this->statut, ['suspendu', 'expire'], true)) {
            return false;
        }
        return ! $this->isExpired();
    }

    public function joursRestants(): int
    {
        if (! $this->date_fin) {
            return 365;
        }
        $diff = Carbon::today()->diffInDays(Carbon::parse($this->date_fin)->startOfDay(), false);
        return (int) $diff;
    }

    public function statutEffectif(): string
    {
        if ($this->statut === 'suspendu') {
            return 'suspendu';
        }
        if ($this->isExpired()) {
            return 'expire';
        }
        return $this->statut ?: 'actif';
    }

    public function statutLabel(): string
    {
        return match ($this->statutEffectif()) {
            'actif'    => 'Actif',
            'essai'    => 'En période d\'essai',
            'expire'   => 'Expiré',
            'suspendu' => 'Suspendu',
            default    => ucfirst($this->statutEffectif()),
        };
    }

    public function badgeClass(): string
    {
        return match ($this->statutEffectif()) {
            'actif'    => 'bg-success',
            'essai'    => 'bg-info text-dark',
            'expire'   => 'bg-danger',
            'suspendu' => 'bg-warning text-dark',
            default    => 'bg-secondary',
        };
    }
}

