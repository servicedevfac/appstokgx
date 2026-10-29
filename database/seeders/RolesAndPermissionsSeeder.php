<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Initialise les rôles et permissions au format SaaS Multi-Entreprises.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions globales de la plateforme SaaS (réservées au Super Admin)
        $platformPermissions = [
            'view permission',
            'create permission',
            'edit permission',
            'delete permission',
            'view role',
            'create role',
            'edit role',
            'delete role',
            'create entreprise',
            'delete entreprise',
        ];

        // Permissions métier d'entreprise
        $entreprisePermissions = [
            'view utilisateur',
            'create utilisateur',
            'edit utilisateur',
            'delete utilisateur',

            'view entreprise',
            'edit entreprise',

            'view categorie',
            'create categorie',
            'edit categorie',
            'delete categorie',

            'view produit',
            'create produit',
            'edit produit',
            'delete produit',
            'exporter produit',

            'view client',
            'create client',
            'edit client',
            'delete client',
            'exporter client',

            'view fournisseur',
            'create fournisseur',
            'edit fournisseur',
            'delete fournisseur',

            'view achat',
            'create achat',
            'edit achat',
            'receptionner achat',
            'payer achat',
            'retourner achat',

            'view vente',
            'create vente',
            'edit vente',
            'delete vente',
            'exporter vente',
            'annuler vente',
            'retourner vente',

            'view stock',
            'create stock',
            'edit stock',
            'delete stock',
            'exporter stock',

            'view depense',
            'create depense',
            'edit depense',
            'delete depense',
            'exporter depense',

            'view employe',
            'create employe',
            'edit employe',
            'delete employe',

            'view avance_salaire',
            'create avance_salaire',
            'valider avance_salaire',
            'rembourser avance_salaire',

            'importer donnees',
            'view audit',
            'view rapport_financier',
        ];

        $allPermissions = array_merge($platformPermissions, $entreprisePermissions);

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 1. Super Administrateur SaaS Global : uniquement gestion de la plateforme et audit
        $superAdmin = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(array_merge($platformPermissions, ['view audit']));

        // 2. Propriétaire d'entreprise(s) : toutes les permissions métier sur ses entreprises
        $proprietaire = Role::firstOrCreate(['name' => 'Propriétaire', 'guard_name' => 'web']);
        $proprietaire->syncPermissions($entreprisePermissions);

        // 3. Administrateur d'entreprise : gestion complète d'une entreprise
        $admin = Role::firstOrCreate(['name' => 'Administrateur', 'guard_name' => 'web']);
        $admin->syncPermissions($entreprisePermissions);

        // 4. Gestionnaire de stock : produits, catégories, stocks, fournisseurs, achats, clients, ventes, dépenses (consultation/création)
        $gestionnaire = Role::firstOrCreate(['name' => 'Gestionnaire', 'guard_name' => 'web']);
        $gestionnaire->syncPermissions([
            'view client',
            'create client',
            'edit client',
            'view categorie',
            'create categorie',
            'edit categorie',
            'view produit',
            'create produit',
            'edit produit',
            'exporter produit',
            'view fournisseur',
            'create fournisseur',
            'edit fournisseur',
            'view achat',
            'create achat',
            'receptionner achat',
            'retourner achat',
            'view vente',
            'create vente',
            'view stock',
            'create stock',
            'exporter stock',
            'view depense',
            'create depense',
            'exporter depense',
        ]);

        // 5. Vendeur / Caissier (Commerciale) : ventes, clients, consultation produits & stocks
        $commerciale = Role::firstOrCreate(['name' => 'Commerciale', 'guard_name' => 'web']);
        $commerciale->syncPermissions([
            'view client',
            'create client',
            'edit client',
            'view produit',
            'view vente',
            'create vente',
            'view stock',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
