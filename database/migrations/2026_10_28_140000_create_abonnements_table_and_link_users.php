<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('abonnements')) {
            Schema::create('abonnements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('entreprise_id')->index();
                $table->string('plan', 40)->default('pro'); // essai, starter, pro, enterprise
                $table->string('nom_plan')->default('Pack Business Pro');
                $table->string('periodicite', 20)->default('mensuel'); // mensuel, annuel
                $table->decimal('montant', 15, 2)->default(35000);
                $table->string('devise', 15)->default('FCFA');
                $table->date('date_debut');
                $table->date('date_fin');
                $table->string('statut', 30)->default('actif')->index(); // actif, essai, expire, suspendu
                $table->integer('max_utilisateurs')->nullable();
                $table->integer('max_produits')->nullable();
                $table->string('mode_paiement')->nullable();
                $table->string('reference_paiement')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('cree_par')->nullable();
                $table->timestamps();

                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            });
        }

        // 1. S'assurer qu'une entreprise principale non-démo existe pour rattacher les utilisateurs métier et données orphelines
        if (Schema::hasTable('entreprises') && Schema::hasTable('users')) {
            $defaultEntreprise = DB::table('entreprises')->where('is_demo', false)->orderBy('id')->first();

            if (! $defaultEntreprise) {
                $firstAdmin = DB::table('users')
                    ->where('is_demo', false)
                    ->orderBy('id')
                    ->first();

                $defaultEntrepriseId = DB::table('entreprises')->insertGetId([
                    'nom'                    => 'STOKGX Entreprise Principale',
                    'code'                   => 'ENT-MAIN01',
                    'devise'                 => 'FCFA',
                    'plafond_avance_salaire' => 100000,
                    'proprietaire_id'        => $firstAdmin?->id,
                    'actif'                  => true,
                    'is_demo'                => false,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
            } else {
                $defaultEntrepriseId = $defaultEntreprise->id;
            }

            // 2. Rattacher les données métier orphelines (entreprise_id IS NULL) à l'entreprise principale
            $businessTables = [
                'categories',
                'produits',
                'clients',
                'fournisseurs',
                'achats',
                'ventes',
                'paiements',
                'mouvement_stocks',
                'depenses',
                'employes',
                'avances_salaires',
                'horaires',
            ];

            foreach ($businessTables as $tableName) {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'entreprise_id')) {
                    DB::table($tableName)
                        ->whereNull('entreprise_id')
                        ->update(['entreprise_id' => $defaultEntrepriseId]);
                }
            }

            // 3. Isoler les comptes super admin (SaaS global uniquement) et lier obligatoirement tous les autres utilisateurs à une entreprise
            $superAdminRoleId = Schema::hasTable('roles')
                ? DB::table('roles')->where('name', 'super admin')->value('id')
                : null;

            $superAdminUserIds = [];
            if ($superAdminRoleId && Schema::hasTable('model_has_roles')) {
                $superAdminUserIds = DB::table('model_has_roles')
                    ->where('role_id', $superAdminRoleId)
                    ->pluck('model_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            if (! empty($superAdminUserIds)) {
                DB::table('users')
                    ->whereIn('id', $superAdminUserIds)
                    ->where('is_proprietaire', false)
                    ->update(['current_entreprise_id' => null]);

                if (Schema::hasTable('entreprise_user')) {
                    DB::table('entreprise_user')
                        ->whereIn('user_id', $superAdminUserIds)
                        ->delete();
                }
            }

            // Rattacher tous les utilisateurs non-super-admin qui n'ont pas encore d'entreprise
            $allUsers = DB::table('users')->get();
            foreach ($allUsers as $u) {
                if (in_array((int) $u->id, $superAdminUserIds, true) && ! $u->is_proprietaire) {
                    continue;
                }

                $targetEntId = $u->current_entreprise_id;
                if (! $targetEntId) {
                    $ownedEntId = DB::table('entreprises')->where('proprietaire_id', $u->id)->value('id');
                    $pivotEntId = Schema::hasTable('entreprise_user')
                        ? DB::table('entreprise_user')->where('user_id', $u->id)->value('entreprise_id')
                        : null;
                    $targetEntId = $ownedEntId ?? $pivotEntId ?? $defaultEntrepriseId;

                    DB::table('users')->where('id', $u->id)->update([
                        'current_entreprise_id' => $targetEntId,
                    ]);
                }

                if ($targetEntId && Schema::hasTable('entreprise_user')) {
                    $hasPivot = DB::table('entreprise_user')
                        ->where('entreprise_id', $targetEntId)
                        ->where('user_id', $u->id)
                        ->exists();

                    if (! $hasPivot) {
                        $roleName = $u->is_proprietaire ? 'Propriétaire' : 'Administrateur';
                        if (Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
                            $assignedRole = DB::table('model_has_roles')
                                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                ->where('model_has_roles.model_id', $u->id)
                                ->where('roles.name', '!=', 'super admin')
                                ->value('roles.name');
                            if ($assignedRole) {
                                $roleName = $assignedRole;
                            }
                        }

                        DB::table('entreprise_user')->insert([
                            'entreprise_id' => $targetEntId,
                            'user_id'       => $u->id,
                            'role'          => $roleName,
                            'actif'         => true,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }

            // 4. Créer un abonnement actif pour chaque entreprise existante qui n'en possède pas encore
            $entreprises = DB::table('entreprises')->get();
            foreach ($entreprises as $ent) {
                $hasSub = DB::table('abonnements')->where('entreprise_id', $ent->id)->exists();
                if (! $hasSub) {
                    DB::table('abonnements')->insert([
                        'entreprise_id'    => $ent->id,
                        'plan'             => $ent->is_demo ? 'essai' : 'pro',
                        'nom_plan'         => $ent->is_demo ? 'Environnement Démo SaaS' : 'Pack Business Pro',
                        'periodicite'      => 'annuel',
                        'montant'          => $ent->is_demo ? 0 : 350000,
                        'devise'           => $ent->devise ?: 'FCFA',
                        'date_debut'       => now()->toDateString(),
                        'date_fin'         => now()->addYear()->toDateString(),
                        'statut'           => 'actif',
                        'max_utilisateurs' => 10,
                        'max_produits'     => null,
                        'mode_paiement'    => 'Activation Initiale',
                        'notes'            => 'Abonnement initialisé lors de la mise à niveau SaaS.',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};

