<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserContoller extends Controller
{
    private function checkAdminPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && ! $user->can("{$action} utilisateur")) {
            abort(403, 'Accès refusé. Seul un propriétaire ou administrateur peut gérer les utilisateurs.');
        }
    }

    private function ensureCanManageUser(User $targetUser): void
    {
        $currentUser = Auth::user();
        if ($currentUser->hasRole('super admin')) {
            return;
        }

        if ($targetUser->hasRole('super admin')) {
            abort(403, 'Vous ne pouvez pas modifier un compte super administrateur.');
        }

        $myEntrepriseIds = $currentUser->accessibleEntrepriseIds();
        $targetEntrepriseIds = $targetUser->accessibleEntrepriseIds();

        if (! empty($myEntrepriseIds) && empty(array_intersect($myEntrepriseIds, $targetEntrepriseIds))) {
            abort(403, 'Cet utilisateur n\'appartient à aucune de vos entreprises.');
        }
    }

    public function index()
    {
        $this->checkAdminPermission('view');
        $currentUser = Auth::user();

        if ($currentUser->hasRole('super admin')) {
            $roles = Role::all();
            $users = User::orderByDesc('created_at')->paginate(10);
        } else {
            $roles = Role::where('name', '!=', 'super admin')->get();
            $entrepriseId = EntrepriseContext::currentId($currentUser);

            $query = User::withoutRole('super admin');
            if ($entrepriseId) {
                $userIdsInEntreprise = DB::table('entreprise_user')
                    ->where('entreprise_id', $entrepriseId)
                    ->pluck('user_id')
                    ->all();
                $query->where(function ($q) use ($userIdsInEntreprise, $entrepriseId) {
                    $q->whereIn('id', $userIdsInEntreprise)
                      ->orWhere('current_entreprise_id', $entrepriseId);
                });
            }
            $users = $query->orderByDesc('created_at')->paginate(10);
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->checkAdminPermission('create');
        $currentUser = Auth::user();

        $roles = $currentUser->hasRole('super admin')
            ? Role::all()
            : Role::where('name', '!=', 'super admin')->get();

        $entreprises = $currentUser->accessibleEntreprises();
        $currentEntrepriseId = EntrepriseContext::currentId($currentUser);

        return view('admin.users.create', compact('roles', 'entreprises', 'currentEntrepriseId'));
    }

    public function store(Request $request)
    {
        $this->checkAdminPermission('create');

        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'prenom'        => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:6',
            'telephone'     => 'required|string|max:255',
            'role'          => 'required|string',
            'entreprise_id' => 'nullable|integer|exists:entreprises,id',
            'permissions'   => 'nullable|array',
        ]);

        $normalizedRole = User::normalizeRoleName($validated['role']);
        Role::firstOrCreate(['name' => $normalizedRole, 'guard_name' => 'web']);

        if ($normalizedRole === 'super admin' && ! Auth::user()->hasRole('super admin')) {
            abort(403, 'Vous n\'êtes pas autorisé à attribuer le rôle super admin.');
        }

        $currentUser = Auth::user();
        $entrepriseId = $validated['entreprise_id'] ?? EntrepriseContext::currentId($currentUser);

        if ($normalizedRole !== 'super admin' && ! $entrepriseId) {
            return back()->withInput()->with('error', 'Tout utilisateur doit obligatoirement être rattaché à une entreprise.');
        }

        if ($entrepriseId && ! $currentUser->canAccessEntreprise((int) $entrepriseId) && ! $currentUser->hasRole('super admin')) {
            abort(403, 'Vous n\'avez pas accès à l\'entreprise sélectionnée.');
        }

        if ($entrepriseId) {
            $entreprise = \App\Models\Entreprise::find((int) $entrepriseId);
            if ($entreprise && ! $entreprise->canAddUser()) {
                return back()->withInput()->with(
                    'error',
                    'Limite maximale d\'utilisateurs atteinte pour votre abonnement (' . ($entreprise->abonnementActuel()?->nom_plan ?? 'Standard') . '). Veuillez mettre à niveau votre abonnement.'
                );
            }
        }

        $user = DB::transaction(function () use ($validated, $normalizedRole, $entrepriseId) {
            $user = User::create([
                'nom'                   => $validated['nom'],
                'prenom'                => $validated['prenom'],
                'email'                 => $validated['email'],
                'password'              => Hash::make($validated['password']),
                'telephone'             => $validated['telephone'],
                'actif'                 => true,
                'is_proprietaire'       => ($normalizedRole === 'Propriétaire'),
                'current_entreprise_id' => $entrepriseId,
            ]);

            $user->assignRole($normalizedRole);

            if ($entrepriseId) {
                // Un utilisateur ne peut être lié qu'à une seule entreprise
                DB::table('entreprise_user')->where('user_id', $user->id)->delete();
                DB::table('entreprise_user')->insert([
                    'entreprise_id' => $entrepriseId,
                    'user_id'       => $user->id,
                    'role'          => $normalizedRole,
                    'permissions'   => ! empty($validated['permissions']) ? json_encode($validated['permissions']) : null,
                    'actif'         => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            AuditService::log(
                'compte.creation',
                $user,
                $user->id,
                ['email' => $user->email, 'role' => $normalizedRole, 'entreprise_id' => $entrepriseId],
                'Création de compte utilisateur',
                $entrepriseId ? (int) $entrepriseId : null
            );

            return $user;
        });

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $this->checkAdminPermission('view');
        $this->ensureCanManageUser($user);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->checkAdminPermission('edit');
        $this->ensureCanManageUser($user);
        $currentUser = Auth::user();

        $roles = $currentUser->hasRole('super admin')
            ? Role::all()
            : Role::where('name', '!=', 'super admin')->get();

        $entreprises = $currentUser->accessibleEntreprises();
        $currentEntrepriseId = $user->current_entreprise_id ?? EntrepriseContext::currentId($currentUser);

        return view('admin.users.edite', compact('user', 'roles', 'entreprises', 'currentEntrepriseId'));
    }

    public function update(Request $request, User $user)
    {
        $this->checkAdminPermission('edit');
        $this->ensureCanManageUser($user);

        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'prenom'        => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone'     => 'required|string|max:255',
            'role'          => 'required|string',
            'entreprise_id' => 'nullable|integer|exists:entreprises,id',
            'permissions'   => 'nullable|array',
        ]);

        $normalizedRole = User::normalizeRoleName($validated['role']);
        Role::firstOrCreate(['name' => $normalizedRole, 'guard_name' => 'web']);

        if ($normalizedRole === 'super admin' && ! Auth::user()->hasRole('super admin')) {
            abort(403, 'Vous n\'êtes pas autorisé à attribuer le rôle super admin.');
        }

        $currentUser = Auth::user();
        $entrepriseId = $validated['entreprise_id'] ?? $user->current_entreprise_id ?? EntrepriseContext::currentId($currentUser);

        if ($normalizedRole !== 'super admin' && ! $entrepriseId) {
            return back()->withInput()->with('error', 'Tout utilisateur doit obligatoirement être rattaché à une entreprise.');
        }

        if ($entrepriseId && ! $currentUser->canAccessEntreprise((int) $entrepriseId) && ! $currentUser->hasRole('super admin')) {
            abort(403, 'Vous n\'avez pas accès à l\'entreprise sélectionnée.');
        }

        $userData = Arr::only($validated, ['nom', 'prenom', 'email', 'telephone']);
        $userData['current_entreprise_id'] = $normalizedRole === 'super admin' ? null : $entrepriseId;
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        DB::transaction(function () use ($user, $userData, $normalizedRole, $entrepriseId, $validated) {
            $oldRole = $user->roleInEntreprise($entrepriseId ? (int) $entrepriseId : null);

            $user->update($userData);
            $user->syncRoles($normalizedRole);

            if ($entrepriseId) {
                // Un utilisateur ne peut être lié qu'à une seule entreprise
                DB::table('entreprise_user')
                    ->where('user_id', $user->id)
                    ->where('entreprise_id', '!=', $entrepriseId)
                    ->delete();

                DB::table('entreprise_user')->updateOrInsert(
                    ['entreprise_id' => $entrepriseId, 'user_id' => $user->id],
                    [
                        'role'        => $normalizedRole,
                        'permissions' => isset($validated['permissions']) ? json_encode($validated['permissions']) : null,
                        'actif'       => (bool) $user->actif,
                        'updated_at'  => now(),
                    ]
                );
            }

            if ($oldRole !== $normalizedRole || isset($validated['permissions'])) {
                AuditService::log(
                    'permission.changement',
                    $user,
                    $user->id,
                    [
                        'ancien_role'   => $oldRole,
                        'nouveau_role'  => $normalizedRole,
                        'entreprise_id' => $entrepriseId,
                        'permissions'   => $validated['permissions'] ?? null,
                    ],
                    'Modification de rôle / permissions utilisateur',
                    $entrepriseId ? (int) $entrepriseId : null
                );
            }
        });

        return redirect()->route('users.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy(User $user)
    {
        $this->checkAdminPermission('delete');
        $this->ensureCanManageUser($user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->hasRole('super admin')) {
            return back()->with('error', 'Impossible de supprimer un compte super administrateur.');
        }

        if ($user->ventes()->withoutGlobalScope('entreprise')->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur : il a déjà enregistré des ventes.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggle(User $user)
    {
        $this->checkAdminPermission('edit');
        $this->ensureCanManageUser($user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas bloquer votre propre compte.');
        }

        if ($user->hasRole('super admin')) {
            return back()->with('error', 'Impossible de bloquer un super administrateur.');
        }

        $user->actif = ! $user->actif;
        $user->save();

        AuditService::log(
            $user->actif ? 'compte.activation' : 'compte.desactivation',
            $user,
            $user->id,
            ['actif' => $user->actif],
            $user->actif ? 'Activation du compte utilisateur' : 'Désactivation du compte utilisateur'
        );

        return back()->with('success', $user->actif ? 'Utilisateur activé avec succès.' : 'Utilisateur bloqué avec succès.');
    }
}
