<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table entreprises
        if (! Schema::hasTable('entreprises')) {
            Schema::create('entreprises', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('code')->unique();
                $table->string('logo')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->string('adresse')->nullable();
                $table->string('identifiant_fiscal')->nullable();
                $table->string('devise')->default('FCFA');
                $table->decimal('plafond_avance_salaire', 15, 2)->default(100000);
                $table->foreignId('proprietaire_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('actif')->default(true);
                $table->boolean('is_demo')->default(false);
                $table->json('parametres')->nullable();
                $table->timestamps();
            });
        }

        // 2. Table pivot entreprise_user (rôles et permissions par entreprise)
        if (! Schema::hasTable('entreprise_user')) {
            Schema::create('entreprise_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('Commerciale');
                $table->json('permissions')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();

                $table->unique(['entreprise_id', 'user_id']);
            });
        }

        // 3. Colonnes additionnelles sur users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_proprietaire')) {
                $table->boolean('is_proprietaire')->default(false)->after('actif');
            }
            if (! Schema::hasColumn('users', 'current_entreprise_id')) {
                $table->foreignId('current_entreprise_id')->nullable()->after('is_proprietaire')->constrained('entreprises')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'is_demo')) {
                $table->boolean('is_demo')->default(false)->after('current_entreprise_id');
            }
        });

        // 4. Table categories
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['entreprise_id', 'nom']);
            });
        }

        // 5. Mise à jour de clients (entreprise_id + unicité par entreprise)
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_code_client_unique');
            if (! Schema::hasColumn('clients', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('clients', 'email')) {
                $table->string('email')->nullable()->after('telephone');
            }
            $table->unique(['entreprise_id', 'code_client']);
        });

        // 6. Mise à jour de produits (entreprise_id, categorie_id, reference, prix_achat + unicité par entreprise)
        Schema::table('produits', function (Blueprint $table) {
            $table->dropUnique('produits_nom_unique');
            if (! Schema::hasColumn('produits', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('produits', 'categorie_id')) {
                $table->foreignId('categorie_id')->nullable()->after('entreprise_id')->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('produits', 'reference')) {
                $table->string('reference')->nullable()->after('nom');
            }
            if (! Schema::hasColumn('produits', 'prix_achat')) {
                $table->decimal('prix_achat', 15, 2)->default(0)->after('reference');
            }
            $table->unique(['entreprise_id', 'nom']);
            $table->unique(['entreprise_id', 'reference']);
        });

        // 7. Table fournisseurs
        if (! Schema::hasTable('fournisseurs')) {
            Schema::create('fournisseurs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->string('code_fournisseur');
                $table->string('nom');
                $table->string('contact')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('adresse')->nullable();
                $table->timestamps();

                $table->unique(['entreprise_id', 'code_fournisseur']);
            });
        }

        // 8. Table achats
        if (! Schema::hasTable('achats')) {
            Schema::create('achats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('fournisseur_id')->constrained('fournisseurs')->restrictOnDelete();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->string('numero_achat');
                $table->date('date_achat');
                $table->date('date_echeance')->nullable();
                $table->date('date_reception')->nullable();
                $table->decimal('montant_total', 15, 2)->default(0);
                $table->decimal('montant_paye', 15, 2)->default(0);
                $table->decimal('reste_a_payer', 15, 2)->default(0);
                $table->string('statut_reception')->default('en_attente'); // en_attente, partiel, recu, annule
                $table->string('statut_paiement')->default('impaye'); // impaye, partiel, paye
                $table->text('notes')->nullable();
                $table->string('idempotency_key')->nullable()->unique();
                $table->timestamps();

                $table->unique(['entreprise_id', 'numero_achat']);
            });
        }

        // 9. Table detail_achats
        if (! Schema::hasTable('detail_achats')) {
            Schema::create('detail_achats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('achat_id')->constrained('achats')->cascadeOnDelete();
                $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
                $table->integer('quantite');
                $table->integer('quantite_recue')->default(0);
                $table->decimal('prix_unitaire', 15, 2);
                $table->decimal('total', 15, 2);
                $table->timestamps();
            });
        }

        // 10. Table paiement_fournisseurs
        if (! Schema::hasTable('paiement_fournisseurs')) {
            Schema::create('paiement_fournisseurs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('achat_id')->constrained('achats')->cascadeOnDelete();
                $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reference')->unique();
                $table->decimal('montant', 15, 2);
                $table->decimal('reste_a_payer', 15, 2)->default(0);
                $table->string('mode_paiement')->default('Espèces');
                $table->date('date_paiement');
                $table->string('type_operation')->default('paiement'); // paiement, remboursement_fournisseur
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        // 11. Table retour_fournisseurs
        if (! Schema::hasTable('retour_fournisseurs')) {
            Schema::create('retour_fournisseurs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('achat_id')->constrained('achats')->cascadeOnDelete();
                $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
                $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->integer('quantite');
                $table->decimal('prix_unitaire', 15, 2)->default(0);
                $table->decimal('montant_retour', 15, 2)->default(0);
                $table->string('motif');
                $table->date('date_retour');
                $table->string('statut')->default('valide');
                $table->timestamps();
            });
        }

        // 12. Mise à jour de ventes
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropUnique('ventes_code_recu_unique');
            if (! Schema::hasColumn('ventes', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('ventes', 'montant_initial')) {
                $table->decimal('montant_initial', 15, 2)->default(0)->after('montant_total');
            }
            if (! Schema::hasColumn('ventes', 'statut_paiement')) {
                $table->string('statut_paiement')->default('paye')->after('statut'); // paye, partiel, impaye
            }
            if (! Schema::hasColumn('ventes', 'date_echeance')) {
                $table->date('date_echeance')->nullable()->after('date_vente');
            }
            if (! Schema::hasColumn('ventes', 'motif_annulation')) {
                $table->text('motif_annulation')->nullable()->after('pdf_recu');
            }
            if (! Schema::hasColumn('ventes', 'annule_par')) {
                $table->foreignId('annule_par')->nullable()->after('motif_annulation')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('ventes', 'annule_at')) {
                $table->timestamp('annule_at')->nullable()->after('annule_par');
            }
            if (! Schema::hasColumn('ventes', 'marchandise_recuperee')) {
                $table->boolean('marchandise_recuperee')->default(true)->after('annule_at');
            }
            if (! Schema::hasColumn('ventes', 'montant_rembourse')) {
                $table->decimal('montant_rembourse', 15, 2)->default(0)->after('marchandise_recuperee');
            }
            if (! Schema::hasColumn('ventes', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->unique()->after('montant_rembourse');
            }
            $table->unique(['entreprise_id', 'code_recu']);
        });

        // 13. Mise à jour de detail_ventes (coût d'achat historique pour marge brute)
        Schema::table('detail_ventes', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_ventes', 'cout_achat_unitaire')) {
                $table->decimal('cout_achat_unitaire', 15, 2)->default(0)->after('prix');
            }
        });

        // 14. Mise à jour de paiements (entreprise_id, reference unique, type_operation)
        Schema::table('paiements', function (Blueprint $table) {
            if (! Schema::hasColumn('paiements', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('paiements', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('vente_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('paiements', 'reference')) {
                $table->string('reference')->nullable()->unique()->after('user_id');
            }
            if (! Schema::hasColumn('paiements', 'type_operation')) {
                $table->string('type_operation')->default('encaissement')->after('mode_paiement'); // encaissement, remboursement_client
            }
        });

        // 15. Table retour_clients
        if (! Schema::hasTable('retour_clients')) {
            Schema::create('retour_clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('vente_id')->constrained('ventes')->cascadeOnDelete();
                $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->integer('quantite');
                $table->decimal('prix_unitaire', 15, 2);
                $table->decimal('montant_retour', 15, 2);
                $table->boolean('reintegrer_stock')->default(false);
                $table->string('etat_marchandise')->default('bon_etat'); // bon_etat, endommage, non_reintegrable
                $table->decimal('montant_remboursable', 15, 2)->default(0);
                $table->decimal('montant_rembourse', 15, 2)->default(0);
                $table->string('motif');
                $table->date('date_retour');
                $table->string('statut')->default('valide');
                $table->timestamps();
            });
        }

        // 16. Mise à jour de mouvement_stocks
        Schema::table('mouvement_stocks', function (Blueprint $table) {
            if (! Schema::hasColumn('mouvement_stocks', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('mouvement_stocks', 'achat_id')) {
                $table->foreignId('achat_id')->nullable()->after('vente_id')->constrained('achats')->nullOnDelete();
            }
            if (! Schema::hasColumn('mouvement_stocks', 'reference_externe')) {
                $table->string('reference_externe')->nullable()->unique()->after('motif');
            }
        });

        // 17. Mise à jour de depenses
        Schema::table('depenses', function (Blueprint $table) {
            if (! Schema::hasColumn('depenses', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('depenses', 'statut')) {
                $table->string('statut')->default('validee')->after('description'); // validee, en_attente, annulee
            }
            if (! Schema::hasColumn('depenses', 'reference_unique')) {
                $table->string('reference_unique')->nullable()->unique()->after('statut');
            }
        });

        // 18. Table employes (RM-055 à RM-060)
        if (! Schema::hasTable('employes')) {
            Schema::create('employes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->string('matricule');
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('poste')->nullable();
                $table->decimal('salaire_base', 15, 2)->default(0);
                $table->decimal('plafond_avance', 15, 2)->nullable();
                $table->date('date_embauche')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();

                $table->unique(['entreprise_id', 'matricule']);
            });
        }

        // 19. Table avance_salaires
        if (! Schema::hasTable('avance_salaires')) {
            Schema::create('avance_salaires', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
                $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
                $table->date('date_avance');
                $table->decimal('montant', 15, 2);
                $table->decimal('montant_rembourse', 15, 2)->default(0);
                $table->decimal('reste_a_rembourser', 15, 2)->default(0);
                $table->string('statut')->default('approuvee'); // en_attente, approuvee, remboursee, rejetee
                $table->string('motif')->nullable();
                $table->timestamps();
            });
        }

        // 20. Table remboursement_avances
        if (! Schema::hasTable('remboursement_avances')) {
            Schema::create('remboursement_avances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('avance_salaire_id')->constrained('avance_salaires')->cascadeOnDelete();
                $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reference')->unique();
                $table->decimal('montant', 15, 2);
                $table->date('date_remboursement');
                $table->string('mode_remboursement')->default('retenue_salaire');
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        // 21. Table import_batches (RM-061 à RM-066)
        if (! Schema::hasTable('import_batches')) {
            Schema::create('import_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type_import'); // produits, categories, clients, fournisseurs, stocks_initiaux
                $table->string('nom_fichier');
                $table->string('statut')->default('apercu'); // apercu, confirme, echoue
                $table->json('lignes_valides')->nullable();
                $table->json('lignes_rejetees')->nullable();
                $table->json('lignes_ignorees')->nullable();
                $table->json('resume')->nullable();
                $table->timestamps();
            });
        }

        // 22. Table audit_logs (RM-069 à RM-072)
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action');
                $table->string('objet_type');
                $table->string('objet_id')->nullable();
                $table->text('motif')->nullable();
                $table->json('contexte')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['entreprise_id', 'action']);
                $table->index(['objet_type', 'objet_id']);
            });
        }

        // 23. Table notifications (Laravel Database Notifications)
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 24. Table sync_operations (Préparation Mode Hors Ligne Futur - Phase 11)
        if (! Schema::hasTable('sync_operations')) {
            Schema::create('sync_operations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('client_operation_uuid')->unique();
                $table->string('operation_type');
                $table->json('payload');
                $table->string('status')->default('pending'); // pending, applied, conflict, rejected
                $table->json('conflict_details')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
            });
        }

        // 25. Mise à jour de horaires, historique_horaires et user_logins
        Schema::table('horaires', function (Blueprint $table) {
            if (! Schema::hasColumn('horaires', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
        });

        Schema::table('historique_horaires', function (Blueprint $table) {
            if (! Schema::hasColumn('historique_horaires', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->cascadeOnDelete();
            }
        });

        Schema::table('user_logins', function (Blueprint $table) {
            if (! Schema::hasColumn('user_logins', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_logins', 'created_at')) {
                $table->timestamps();
            }
        });

        // 26. Rattachement automatique et sans perte des données historiques existantes
        $hasHistoricalData = DB::table('users')->exists()
            || DB::table('clients')->exists()
            || DB::table('produits')->exists()
            || DB::table('ventes')->exists()
            || DB::table('depenses')->exists();

        if ($hasHistoricalData) {
            $firstAdmin = DB::table('users')->orderBy('id')->first();

            $defaultEntrepriseId = DB::table('entreprises')->insertGetId([
                'nom'                    => 'STOKGX Entreprise Principale',
                'code'                   => 'ENT-0001',
                'devise'                 => 'FCFA',
                'plafond_avance_salaire' => 150000,
                'proprietaire_id'        => $firstAdmin?->id,
                'actif'                  => true,
                'is_demo'                => false,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            foreach (['clients', 'produits', 'ventes', 'paiements', 'mouvement_stocks', 'depenses', 'horaires', 'historique_horaires', 'user_logins'] as $tableName) {
                DB::table($tableName)->whereNull('entreprise_id')->update(['entreprise_id' => $defaultEntrepriseId]);
            }

            // Synchroniser montant_initial et statut_paiement sur les ventes existantes
            $ventes = DB::table('ventes')->get();
            foreach ($ventes as $v) {
                $statutPaiement = 'paye';
                if ((float) $v->reste_a_payer > 0) {
                    $statutPaiement = ((float) $v->montant_paye > 0) ? 'partiel' : 'impaye';
                }
                DB::table('ventes')->where('id', $v->id)->update([
                    'montant_initial' => (float) $v->montant_total + (float) ($v->remise ?? 0),
                    'statut_paiement' => $statutPaiement,
                ]);
            }

            // Générer des références uniques sur les paiements historiques
            $paiements = DB::table('paiements')->whereNull('reference')->get();
            foreach ($paiements as $p) {
                DB::table('paiements')->where('id', $p->id)->update([
                    'reference' => 'PAY-HIST-' . str_pad((string) $p->id, 6, '0', STR_PAD_LEFT),
                ]);
            }

            // Rattacher tous les utilisateurs existants à l'entreprise par défaut
            $users = DB::table('users')->get();
            foreach ($users as $u) {
                $roleId = DB::table('model_has_roles')
                    ->where('model_id', $u->id)
                    ->where('model_type', 'App\\Models\\User')
                    ->value('role_id');
                $roleName = $roleId ? DB::table('roles')->where('id', $roleId)->value('name') : 'Administrateur';

                DB::table('users')->where('id', $u->id)->update([
                    'current_entreprise_id' => $defaultEntrepriseId,
                    'is_proprietaire'       => in_array($roleName, ['super admin', 'Administrateur', 'Propriétaire']),
                ]);

                DB::table('entreprise_user')->updateOrInsert(
                    ['entreprise_id' => $defaultEntrepriseId, 'user_id' => $u->id],
                    [
                        'role'       => $roleName ?: 'Administrateur',
                        'actif'      => (bool) $u->actif,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_operations');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('remboursement_avances');
        Schema::dropIfExists('avance_salaires');
        Schema::dropIfExists('employes');
        Schema::dropIfExists('retour_clients');
        Schema::dropIfExists('retour_fournisseurs');
        Schema::dropIfExists('paiement_fournisseurs');
        Schema::dropIfExists('detail_achats');
        Schema::dropIfExists('achats');
        Schema::dropIfExists('fournisseurs');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('entreprise_user');
        Schema::dropIfExists('entreprises');
    }
};

