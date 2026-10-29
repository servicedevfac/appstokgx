<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'logo',
        'email',
        'telephone',
        'adresse',
        'identifiant_fiscal',
        'devise',
        'plafond_avance_salaire',
        'proprietaire_id',
        'actif',
        'is_demo',
        'parametres',
    ];

    protected $casts = [
        'plafond_avance_salaire' => 'decimal:2',
        'actif'                  => 'boolean',
        'is_demo'                => 'boolean',
        'parametres'             => 'array',
    ];

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class, 'entreprise_user')
            ->withPivot(['role', 'permissions', 'actif'])
            ->withTimestamps();
    }

    public function produits()
    {
        return $this->hasMany(Produit::class, 'entreprise_id');
    }

    public function categories()
    {
        return $this->hasMany(Categorie::class, 'entreprise_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'entreprise_id');
    }

    public function fournisseurs()
    {
        return $this->hasMany(Fournisseur::class, 'entreprise_id');
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'entreprise_id');
    }

    public function achats()
    {
        return $this->hasMany(Achat::class, 'entreprise_id');
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class, 'entreprise_id');
    }

    public function employes()
    {
        return $this->hasMany(Employe::class, 'entreprise_id');
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'entreprise_id')
            ->orderByDesc('date_fin')
            ->orderByDesc('id');
    }

    public function abonnementActuel(): ?Abonnement
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('abonnements')) {
            return null;
        }

        return $this->abonnements()->first();
    }

    public function ensureDefaultSubscription(string $plan = 'pro', ?int $creePar = null): ?Abonnement
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('abonnements')) {
            return null;
        }

        $existing = $this->abonnementActuel();
        if ($existing) {
            return $existing;
        }

        $planConfig = Abonnement::PLANS[$plan] ?? Abonnement::PLANS['pro'];

        return Abonnement::create([
            'entreprise_id'    => $this->id,
            'plan'             => $plan,
            'nom_plan'         => $planConfig['nom'],
            'periodicite'      => 'mensuel',
            'montant'          => $this->is_demo ? 0 : $planConfig['prix_mensuel'],
            'devise'           => $this->devise ?: 'FCFA',
            'date_debut'       => now()->toDateString(),
            'date_fin'         => now()->addYear()->toDateString(),
            'statut'           => $plan === 'essai' ? 'essai' : 'actif',
            'max_utilisateurs' => $planConfig['max_utilisateurs'],
            'max_produits'     => $planConfig['max_produits'],
            'mode_paiement'    => 'Initialisation SaaS',
            'notes'            => 'Abonnement initialisé automatiquement.',
            'cree_par'         => $creePar,
        ]);
    }

    public function hasActiveSubscription(): bool
    {
        if (! $this->actif) {
            return false;
        }
        if ($this->is_demo) {
            return true;
        }
        $sub = $this->abonnementActuel();
        if (! $sub) {
            return true;
        }
        return $sub->isActive();
    }

    public function canAddUser(): bool
    {
        $sub = $this->abonnementActuel();
        if (! $sub || $sub->max_utilisateurs === null || $sub->max_utilisateurs <= 0) {
            return true;
        }
        $currentCount = \Illuminate\Support\Facades\DB::table('entreprise_user')
            ->where('entreprise_id', $this->id)
            ->where('actif', true)
            ->count();
        return $currentCount < $sub->max_utilisateurs;
    }

    public function canAddProduit(): bool
    {
        $sub = $this->abonnementActuel();
        if (! $sub || $sub->max_produits === null || $sub->max_produits <= 0) {
            return true;
        }
        $currentCount = Produit::withoutGlobalScope('entreprise')
            ->where('entreprise_id', $this->id)
            ->count();
        return $currentCount < $sub->max_produits;
    }
}

