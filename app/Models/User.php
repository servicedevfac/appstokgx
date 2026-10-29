<?php

namespace App\Models;

use App\Services\EntrepriseContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles {
        hasRole as spatieHasRole;
        hasAnyRole as spatieHasAnyRole;
        hasPermissionTo as spatieHasPermissionTo;
    }

    public const ROLE_LABELS = [
        'super admin'    => 'Superadministrateur',
        'Propriétaire'   => 'Propriétaire',
        'Administrateur' => 'Administrateur',
        'Gestionnaire'   => 'Gestionnaire de stock',
        'Commerciale'    => 'Vendeur / Caissier',
    ];

    public const ROLE_ALIASES = [
        'superadministrateur'   => 'super admin',
        'super_admin'           => 'super admin',
        'super admin'           => 'super admin',
        'proprietaire'          => 'Propriétaire',
        'propriétaire'          => 'Propriétaire',
        'administrateur'        => 'Administrateur',
        'admin'                 => 'Administrateur',
        'gestionnaire de stock' => 'Gestionnaire',
        'gestionnaire'          => 'Gestionnaire',
        'vendeur'               => 'Commerciale',
        'caissier'              => 'Commerciale',
        'vendeur / caissier'    => 'Commerciale',
        'commercial'            => 'Commerciale',
        'commerciale'           => 'Commerciale',
    ];

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'actif',
        'is_proprietaire',
        'current_entreprise_id',
        'is_demo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'actif'             => 'boolean',
            'is_proprietaire'   => 'boolean',
            'is_demo'           => 'boolean',
        ];
    }

    public static function normalizeRoleName(string $role): string
    {
        $lower = mb_strtolower(trim($role));
        return self::ROLE_ALIASES[$lower] ?? $role;
    }

    public static function roleLabel(string $role): string
    {
        $normalized = self::normalizeRoleName($role);
        return self::ROLE_LABELS[$normalized] ?? $role;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nom'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nom'] = $value;
    }

    public function getRoleLabelAttribute(): string
    {
        $role = $this->roleInEntreprise() ?? $this->roles->first()?->name ?? 'Utilisateur';
        return self::roleLabel($role);
    }

    public function getRoleLibelleAttribute(): string
    {
        return $this->getRoleLabelAttribute();
    }

    public function getEntrepriseIdAttribute(): ?int
    {
        return EntrepriseContext::currentId($this) ?? $this->current_entreprise_id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->spatieHasRole('super admin');
    }

    public function isProprietaire(): bool
    {
        if ($this->is_proprietaire || $this->spatieHasRole('Propriétaire')) {
            return true;
        }
        $entId = EntrepriseContext::currentId($this);
        return $this->roleInEntreprise($entId) === 'Propriétaire';
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'user_id');
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class, 'user_id');
    }

    public function entreprises()
    {
        return $this->belongsToMany(Entreprise::class, 'entreprise_user')
            ->withPivot(['role', 'permissions', 'actif'])
            ->withTimestamps();
    }

    public function entreprisesPossedees()
    {
        return $this->hasMany(Entreprise::class, 'proprietaire_id');
    }

    public function currentEntreprise()
    {
        return $this->belongsTo(Entreprise::class, 'current_entreprise_id');
    }

    /**
     * Retourne l'identifiant de l'unique entreprise à laquelle l'utilisateur est rattaché.
     * Un utilisateur métier ne peut être lié qu'à une seule entreprise.
     */
    public function singleEntrepriseId(): ?int
    {
        if (! $this->exists) {
            return null;
        }

        if ($this->spatieHasRole('super admin') && ! $this->is_proprietaire) {
            return null;
        }

        if ($this->current_entreprise_id) {
            $ent = Entreprise::where('id', $this->current_entreprise_id)->where('actif', true)->first();
            if ($ent) {
                return (int) $ent->id;
            }
        }

        $ownedId = Entreprise::where('proprietaire_id', $this->id)
            ->where('actif', true)
            ->orderBy('id')
            ->value('id');
        if ($ownedId) {
            return (int) $ownedId;
        }

        $pivotId = DB::table('entreprise_user')
            ->join('entreprises', 'entreprises.id', '=', 'entreprise_user.entreprise_id')
            ->where('entreprise_user.user_id', $this->id)
            ->where('entreprise_user.actif', true)
            ->where('entreprises.actif', true)
            ->orderBy('entreprises.id')
            ->value('entreprises.id');

        return $pivotId ? (int) $pivotId : null;
    }

    /**
     * Retourne la liste des IDs d'entreprises actives auxquelles l'utilisateur a réellement accès
     * (Strictement 1 seule entreprise par utilisateur)
     */
    public function accessibleEntrepriseIds(): array
    {
        $singleId = $this->singleEntrepriseId();
        return $singleId ? [$singleId] : [];
    }

    public function accessibleEntreprises()
    {
        $ids = $this->accessibleEntrepriseIds();
        if (empty($ids)) {
            return collect();
        }
        return Entreprise::whereIn('id', $ids)->get();
    }

    public function canAccessEntreprise(?int $entrepriseId): bool
    {
        if (! $entrepriseId) {
            return false;
        }

        $entreprise = Entreprise::find($entrepriseId);
        if (! $entreprise || ! $entreprise->actif) {
            return false;
        }

        // Isolation stricte du mode démo
        if ($this->is_demo && ! $entreprise->is_demo) {
            return false;
        }

        if ($this->spatieHasRole('super admin') && ! $this->is_proprietaire) {
            return false;
        }

        $singleId = $this->singleEntrepriseId();
        return $singleId !== null && (int) $singleId === (int) $entrepriseId;
    }

    /**
     * Rôle effectif de l'utilisateur dans l'entreprise donnée (ou l'entreprise active)
     */
    public function roleInEntreprise(?int $entrepriseId = null): ?string
    {
        if ($this->spatieHasRole('super admin')) {
            return 'super admin';
        }

        $entrepriseId = $entrepriseId ?? EntrepriseContext::currentId($this);

        if ($entrepriseId) {
            $entreprise = Entreprise::find($entrepriseId);
            if ($entreprise && (int) $entreprise->proprietaire_id === (int) $this->id) {
                return 'Propriétaire';
            }

            $pivotRole = DB::table('entreprise_user')
                ->where('entreprise_id', $entrepriseId)
                ->where('user_id', $this->id)
                ->where('actif', true)
                ->value('role');

            if ($pivotRole) {
                return self::normalizeRoleName($pivotRole);
            }

            // Si l'utilisateur tente d'agir dans une entreprise où il n'a aucun rôle
            return null;
        }

        if ($this->is_proprietaire) {
            return 'Propriétaire';
        }

        return $this->roles->first()?->name;
    }

    public function permissionsInEntreprise(?int $entrepriseId = null): array
    {
        if ($this->spatieHasRole('super admin')) {
            return ['*'];
        }

        $entrepriseId = $entrepriseId ?? EntrepriseContext::currentId($this);
        $roleName = $this->roleInEntreprise($entrepriseId);

        if (! $roleName) {
            return [];
        }

        if (in_array($roleName, ['Propriétaire', 'Administrateur'], true)) {
            return SpatieRole::where('name', $roleName)->first()?->permissions->pluck('name')->all() ?? [];
        }

        $rolePermissions = SpatieRole::where('name', $roleName)->first()?->permissions->pluck('name')->all() ?? [];

        $customPermissions = [];
        if ($entrepriseId) {
            $pivotPermsJson = DB::table('entreprise_user')
                ->where('entreprise_id', $entrepriseId)
                ->where('user_id', $this->id)
                ->where('actif', true)
                ->value('permissions');
            if ($pivotPermsJson) {
                $decoded = json_decode($pivotPermsJson, true);
                if (is_array($decoded)) {
                    $customPermissions = $decoded;
                }
            }
        }

        return array_values(array_unique(array_merge($rolePermissions, $customPermissions)));
    }

    /**
     * Extrait et normalise une liste de noms de rôles depuis tout type d'entrée
     * (string, pipe-string, array, Collection, Traversable, Role model, BackedEnum, int)
     */
    private static function extractRoleNames(mixed $roles): array
    {
        if ($roles instanceof \Illuminate\Support\Collection) {
            $roles = $roles->all();
        } elseif ($roles instanceof \Traversable) {
            $roles = iterator_to_array($roles);
        } elseif (is_string($roles) && str_contains($roles, '|')) {
            $roles = explode('|', $roles);
        } elseif (! is_array($roles)) {
            $roles = [$roles];
        }

        $names = [];
        foreach ($roles as $r) {
            if (is_array($r) || $r instanceof \Illuminate\Support\Collection || $r instanceof \Traversable) {
                $names = array_merge($names, self::extractRoleNames($r));
            } elseif (is_string($r)) {
                if (str_contains($r, '|')) {
                    $names = array_merge($names, self::extractRoleNames(explode('|', $r)));
                } else {
                    $names[] = self::normalizeRoleName($r);
                }
            } elseif ($r instanceof \BackedEnum) {
                $names[] = self::normalizeRoleName((string) $r->value);
            } elseif (is_int($r)) {
                $roleModel = SpatieRole::find($r);
                if ($roleModel) {
                    $names[] = self::normalizeRoleName($roleModel->name);
                }
            } elseif (is_object($r) && isset($r->name)) {
                $names[] = self::normalizeRoleName((string) $r->name);
            }
        }

        return array_values(array_unique(array_filter($names, fn ($n) => $n !== '')));
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        $normalizedCheck = self::extractRoleNames($roles);

        if (empty($normalizedCheck)) {
            return false;
        }

        // Si on vérifie Administrateur, un Propriétaire possède aussi les prérogatives d'administration de ses entreprises
        if (in_array('Administrateur', $normalizedCheck, true) && ! in_array('Propriétaire', $normalizedCheck, true)) {
            $normalizedCheck[] = 'Propriétaire';
        }

        if ($this->spatieHasRole('super admin', $guard)) {
            return in_array('super admin', $normalizedCheck, true);
        }

        $entrepriseId = EntrepriseContext::currentId($this);
        $hasAnyPivot = DB::table('entreprise_user')->where('user_id', $this->id)->exists()
            || Entreprise::where('proprietaire_id', $this->id)->exists();

        if ($entrepriseId || $hasAnyPivot) {
            $effectiveRole = $this->roleInEntreprise($entrepriseId);
            if ($effectiveRole !== null) {
                return in_array(self::normalizeRoleName($effectiveRole), $normalizedCheck, true);
            }
            return false;
        }

        if ($this->is_proprietaire && in_array('Propriétaire', $normalizedCheck, true)) {
            return true;
        }

        foreach ($normalizedCheck as $singleRole) {
            if ($singleRole !== '' && $this->spatieHasRole((string) $singleRole, $guard)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole(self::extractRoleNames($roles));
    }

    public function checkPermissionTo($permission, $guardName = null): bool
    {
        return $this->hasPermissionTo($permission, $guardName);
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission)) {
            return $this->hasPermissionInEntreprise($permission);
        }
        if ($permission instanceof \BackedEnum) {
            return $this->hasPermissionInEntreprise((string) $permission->value);
        }
        if (is_object($permission) && isset($permission->name)) {
            return $this->hasPermissionInEntreprise((string) $permission->name);
        }

        try {
            return $this->spatieHasPermissionTo($permission, $guardName);
        } catch (\Throwable) {
            return false;
        }
    }

    public const PERMISSION_ALIASES = [
        'afficher fournisseurs'     => 'view fournisseur',
        'créer fournisseur'         => 'create fournisseur',
        'modifier fournisseur'      => 'edit fournisseur',
        'supprimer fournisseur'     => 'delete fournisseur',
        'afficher achats'           => 'view achat',
        'créer achat'               => 'create achat',
        'modifier achat'            => 'edit achat',
        'réceptionner achat'        => 'receptionner achat',
        'payer fournisseur'         => 'payer achat',
        'afficher employés'         => 'view employe',
        'créer employé'             => 'create employe',
        'modifier employé'          => 'edit employe',
        'supprimer employé'         => 'delete employe',
        'afficher avances salaires' => 'view avance_salaire',
        'créer avance salaire'      => 'create avance_salaire',
        'valider avance salaire'    => 'valider avance_salaire',
        'rembourser avance salaire' => 'rembourser avance_salaire',
        'importer données'          => 'importer donnees',
        'afficher journal audit'    => 'view audit',
        'gérer entreprises'         => 'view entreprise',
        'afficher produits'         => 'view produit',
        'afficher ventes'           => 'view vente',
        'afficher mouvements'       => 'view stock',
        'afficher clients'          => 'view client',
    ];

    public function hasPermissionInEntreprise(string $permission, ?int $entrepriseId = null): bool
    {
        $canonicalPermission = self::PERMISSION_ALIASES[$permission] ?? $permission;

        if ($this->spatieHasRole('super admin') && ! $this->is_proprietaire) {
            return in_array($canonicalPermission, [
                'view role', 'create role', 'edit role', 'delete role',
                'view permission', 'create permission', 'edit permission', 'delete permission',
                'create entreprise', 'delete entreprise',
                'view audit',
            ], true);
        }

        $entrepriseId = $entrepriseId ?? EntrepriseContext::currentId($this);
        $hasAnyPivot = DB::table('entreprise_user')->where('user_id', $this->id)->exists()
            || Entreprise::where('proprietaire_id', $this->id)->exists();

        if ($entrepriseId || $hasAnyPivot) {
            $role = $this->roleInEntreprise($entrepriseId);
            if (! $role) {
                return false;
            }
            if (in_array($role, ['Propriétaire', 'Administrateur'], true)) {
                // Propriétaire et Administrateur ont accès aux permissions métier de leur entreprise (mais ne peuvent pas créer/supprimer une entreprise)
                if (! in_array($canonicalPermission, [
                    'view role', 'create role', 'edit role', 'delete role',
                    'view permission', 'create permission', 'edit permission', 'delete permission',
                    'create entreprise', 'delete entreprise',
                ], true)) {
                    return true;
                }
            }
            $perms = $this->permissionsInEntreprise($entrepriseId);
            return in_array('*', $perms, true)
                || in_array($permission, $perms, true)
                || in_array($canonicalPermission, $perms, true);
        }

        try {
            return $this->spatieHasPermissionTo($canonicalPermission) || $this->spatieHasPermissionTo($permission);
        } catch (\Throwable) {
            return false;
        }
    }
}
