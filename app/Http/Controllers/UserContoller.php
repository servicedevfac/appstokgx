<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserContoller extends Controller
{
    /**
     * Vérification de sécurité globale pour la gestion des utilisateurs
     */
    private function checkAdminPermission(): void
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin'])) {
            abort(403, 'Accès refusé. Seul un administrateur peut gérer les utilisateurs.');
        }
    }

    /**
     * Liste des utilisateurs
     */
    public function index()
    {
        $this->checkAdminPermission();

        $currentUser = Auth::user();

        if ($currentUser->hasRole('super admin')) {
            $roles = Role::all();
            $users = User::orderByDesc('created_at')->paginate(10);
        } else {
            $roles = Role::where('name', '!=', 'super admin')->get();
            $users = User::withoutRole('super admin')
                ->orderByDesc('created_at')
                ->paginate(10);
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Formulaire de création d'un utilisateur
     */
    public function create()
    {
        $this->checkAdminPermission();

        $roles = Auth::user()->hasRole('super admin')
            ? Role::all()
            : Role::where('name', '!=', 'super admin')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Enregistrement d'un utilisateur
     */
    public function store(Request $request)
    {
        $this->checkAdminPermission();

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'telephone' => 'required|string|max:255',
            'role'      => 'required|string|exists:roles,name',
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'prenom.required'    => 'Le prénom est obligatoire.',
            'email.required'     => 'L\'adresse email est obligatoire.',
            'email.email'        => 'L\'adresse email doit être valide.',
            'email.unique'       => 'Cette adresse email est déjà utilisée par un autre compte.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit comporter au moins 6 caractères.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'role.required'      => 'Le rôle est obligatoire.',
            'role.exists'        => 'Le rôle sélectionné n\'existe pas.',
        ]);

        // Sécurité : interdire l'attribution du rôle super admin par un non super admin
        if ($validated['role'] === 'super admin' && !Auth::user()->hasRole('super admin')) {
            abort(403, 'Vous n\'êtes pas autorisé à attribuer le rôle super admin.');
        }

        $user = User::create([
            'nom'       => $validated['nom'],
            'prenom'    => $validated['prenom'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'telephone' => $validated['telephone'],
            'actif'     => true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Affichage du profil d'un utilisateur
     */
    public function show(User $user)
    {
        $this->checkAdminPermission();

        return view('admin.users.show', compact('user'));
    }

    /**
     * Formulaire d'édition d'un utilisateur
     */
    public function edit(User $user)
    {
        $this->checkAdminPermission();

        // Empêcher un admin standard de modifier un super admin
        if ($user->hasRole('super admin') && !Auth::user()->hasRole('super admin')) {
            abort(403, 'Vous ne pouvez pas modifier un compte super administrateur.');
        }

        $roles = Auth::user()->hasRole('super admin')
            ? Role::all()
            : Role::where('name', '!=', 'super admin')->get();

        return view('admin.users.edite', compact('user', 'roles'));
    }

    /**
     * Mise à jour d'un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $this->checkAdminPermission();

        if ($user->hasRole('super admin') && !Auth::user()->hasRole('super admin')) {
            abort(403, 'Vous ne pouvez pas modifier un compte super administrateur.');
        }

        $rules = [
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'required|string|max:255',
            'role'      => 'required|string|exists:roles,name',
        ];

        $messages = [
            'nom.required'          => 'Le nom est obligatoire.',
            'prenom.required'       => 'Le prénom est obligatoire.',
            'email.required'        => 'L\'adresse email est obligatoire.',
            'email.email'           => 'L\'adresse email doit être valide.',
            'email.unique'          => 'Cette adresse email est déjà utilisée par un autre compte.',
            'password.min'          => 'Le mot de passe doit comporter au moins 6 caractères.',
            'password.confirmed'    => 'La confirmation du mot de passe ne correspond pas.',
            'telephone.required'    => 'Le numéro de téléphone est obligatoire.',
            'role.required'         => 'Le rôle est obligatoire.',
            'role.exists'           => 'Le rôle sélectionné n\'existe pas.',
        ];

        $validated = $request->validate($rules, $messages);

        // Sécurité : interdire l'élévation de privilèges vers super admin
        if ($validated['role'] === 'super admin' && !Auth::user()->hasRole('super admin')) {
            abort(403, 'Vous n\'êtes pas autorisé à attribuer le rôle super admin.');
        }

        $userData = Arr::except($validated, ['role', 'password']);
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);
        $user->syncRoles($validated['role']);

        return redirect()->route('users.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    /**
     * Suppression d'un utilisateur
     */
    public function destroy(User $user)
    {
        $this->checkAdminPermission();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->hasRole('super admin')) {
            return back()->with('error', 'Impossible de supprimer un compte super administrateur.');
        }

        if ($user->ventes()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur : il a déjà enregistré des ventes.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Activation / blocage d'un utilisateur
     */
    public function toggle(User $user)
    {
        $this->checkAdminPermission();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas bloquer votre propre compte.');
        }

        if ($user->hasRole('super admin')) {
            return back()->with('error', 'Impossible de bloquer un super administrateur.');
        }

        $user->actif = !$user->actif;
        $user->save();

        return back()->with('success', $user->actif ? 'Utilisateur activé avec succès.' : 'Utilisateur bloqué avec succès.');
    }
}
