<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Rôles protégés du système
     */
    private const PROTECTED_ROLES = ['super admin', 'administrateur'];

    /**
     * Liste des rôles avec leurs permissions
     */
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Formulaire de création d'un rôle
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Enregistrement d'un rôle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name',
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.required'        => 'Le nom du rôle est obligatoire.',
            'name.unique'          => 'Ce nom de rôle existe déjà.',
            'permissions.required' => 'Veuillez sélectionner au moins une permission pour ce rôle.',
            'permissions.array'    => 'Le format des permissions est invalide.',
            'permissions.min'      => 'Veuillez sélectionner au moins une permission.',
        ]);

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Détail d'un rôle
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Formulaire d'édition d'un rôle
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles.edite', compact('role', 'permissions'));
    }

    /**
     * Mise à jour d'un rôle
     */
    public function update(Request $request, Role $role)
    {
        $isProtected = in_array(strtolower($role->name), self::PROTECTED_ROLES);

        $rules = [
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ];

        if (!$isProtected) {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . $role->id;
        }

        $validated = $request->validate($rules, [
            'name.required'        => 'Le nom du rôle est obligatoire.',
            'name.unique'          => 'Ce nom de rôle existe déjà.',
            'permissions.required' => 'Veuillez sélectionner au moins une permission pour ce rôle.',
            'permissions.array'    => 'Le format des permissions est invalide.',
            'permissions.min'      => 'Veuillez sélectionner au moins une permission.',
        ]);

        if (!$isProtected && isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Suppression d'un rôle
     */
    public function destroy(Role $role)
    {
        if (in_array(strtolower($role->name), self::PROTECTED_ROLES)) {
            return redirect()->route('roles.index')->with('error', 'Le rôle système principal ne peut pas être supprimé.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Impossible de supprimer ce rôle car il est attribué à des utilisateurs actifs.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rôle supprimé avec succès.');
    }
}
