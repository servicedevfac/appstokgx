<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Liste des permissions
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')->paginate(15);
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Enregistrement d'une nouvelle permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ], [
            'name.required' => 'Le nom de la permission est obligatoire.',
            'name.unique'   => 'Cette permission existe déjà.',
            'name.max'      => 'Le nom de la permission ne peut pas dépasser 255 caractères.',
        ]);

        Permission::create([
            'name'       => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission créée avec succès.');
    }

    /**
     * Affichage d'une permission
     */
    public function show(string $id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Mise à jour d'une permission
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ], [
            'name.required' => 'Le nom de la permission est obligatoire.',
            'name.unique'   => 'Cette permission existe déjà.',
            'name.max'      => 'Le nom de la permission ne peut pas dépasser 255 caractères.',
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->route('permissions.index')->with('success', 'Permission mise à jour avec succès.');
    }

    /**
     * Suppression d'une permission
     */
    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);

        if ($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')->with('error', 'Impossible de supprimer cette permission : elle est attribuée à des rôles.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission supprimée avec succès.');
    }
}
