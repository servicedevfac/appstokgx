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

    /**
     * Résout le logo de l'entreprise sous forme de base64 data URI pour les tickets et reçus PDF.
     * Cette méthode est immunisée contre les pertes lors des déploiements (git pull, conteneurs, releases).
     */
    public function getLogoBase64(): ?string
    {
        // 1. Priorité absolue : base64 persisté en base de données (résiste à tout redéploiement)
        if (! empty($this->parametres['logo_base64'])) {
            return $this->parametres['logo_base64'];
        }

        if (empty($this->logo)) {
            return $this->getFallbackLogoBase64();
        }

        // Si la colonne logo contient déjà du base64 data:image
        if (str_starts_with($this->logo, 'data:image')) {
            return $this->logo;
        }

        // 2. Recherche sur tous les chemins de stockage possibles sur le disque
        $possiblePaths = [
            public_path($this->logo),
            public_path(ltrim($this->logo, '/\\')),
            storage_path('app/public/' . ltrim($this->logo, '/\\')),
            storage_path('app/public/' . str_replace(['storage/', 'public/'], '', ltrim($this->logo, '/\\'))),
            public_path('uploads/logos_entreprises/' . basename($this->logo)),
            storage_path('app/public/logos_entreprises/' . basename($this->logo)),
        ];

        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path) && ! is_dir($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png'         => 'image/png',
                    'webp'        => 'image/webp',
                    'gif'         => 'image/gif',
                    'svg'         => 'image/svg+xml',
                    default       => 'image/png',
                };
                $content = @file_get_contents($path);
                if ($content !== false && strlen($content) > 0) {
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode($content);
                    // Sauvegarde automatique en base pour immuniser les prochains déploiements
                    try {
                        $params = $this->parametres ?? [];
                        if (empty($params['logo_base64'])) {
                            $params['logo_base64'] = $base64;
                            $this->updateQuietly(['parametres' => $params]);
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    return $base64;
                }
            }
        }

        // 3. Si logo est une URL distante
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 3]]);
                $content = @file_get_contents($this->logo, false, $ctx);
                if ($content !== false && strlen($content) > 0) {
                    $ext = strtolower(pathinfo(parse_url($this->logo, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png'         => 'image/png',
                        'webp'        => 'image/webp',
                        default       => 'image/png',
                    };
                    return 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $this->getFallbackLogoBase64();
    }

    /**
     * Logo par défaut (fallback)
     */
    public function getFallbackLogoBase64(): ?string
    {
        $fallbacks = [
            public_path('assets/images/logo-dark.png'),
            public_path('assets/images/logo-light.png'),
            public_path('assets/images/logo-sm.png'),
        ];

        foreach ($fallbacks as $path) {
            if (file_exists($path) && ! is_dir($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $content = @file_get_contents($path);
                if ($content !== false) {
                    return 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            }
        }

        return null;
    }

    /**
     * URL du logo pour l'affichage HTML dans les vues
     */
    public function getLogoUrlAttribute(): string
    {
        if (! empty($this->parametres['logo_base64'])) {
            return $this->parametres['logo_base64'];
        }

        if (! empty($this->logo)) {
            if (file_exists(public_path($this->logo))) {
                return asset($this->logo);
            }
            if (file_exists(storage_path('app/public/' . $this->logo))) {
                return asset('storage/' . $this->logo);
            }
        }

        return asset('assets/images/logo-dark.png');
    }
}

