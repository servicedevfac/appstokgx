<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DepensePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view depense',
            'create depense',
            'edit depense',
            'delete depense',
            'exporter depense',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Propriétaire et Administrateur d'entreprise ont tous les droits sur les dépenses de leur entreprise
        foreach (['Propriétaire', 'Administrateur'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Le Gestionnaire peut consulter, enregistrer et exporter les dépenses (sans modifier ni supprimer)
        $gestionnaire = Role::where('name', 'Gestionnaire')->first();
        if ($gestionnaire) {
            $gestionnaire->revokePermissionTo(['edit depense', 'delete depense']);
            $gestionnaire->givePermissionTo(['view depense', 'create depense', 'exporter depense']);
        }

        // Vendeur / Caissier
        foreach (['Commercial', 'Commerciale'] as $cRole) {
            $role = Role::where('name', $cRole)->first();
            if ($role) {
                $role->revokePermissionTo(['edit depense', 'delete depense']);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
