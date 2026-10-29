<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\AuditLog;
use App\Models\Entreprise;
use App\Models\Horaire;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->hasRole('super admin')) {
            abort(403, 'Accès réservé au superadministrateur de la plateforme.');
        }
    }

    /**
     * Tableau de bord global de la plateforme SaaS (Section 3.1)
     */
    public function dashboard()
    {
        $this->ensureSuperAdmin();

        $entreprises = Entreprise::with(['proprietaire', 'abonnements'])
            ->where('is_demo', false)
            ->orderByDesc('created_at')
            ->get();

        $abonnements = Abonnement::with('entreprise')
            ->whereHas('entreprise', fn ($q) => $q->where('is_demo', false))
            ->get();

        $stats = [
            'total_proprietaires'          => User::where('is_proprietaire', true)->where('is_demo', false)->count(),
            'proprietaires_actifs'         => User::where('is_proprietaire', true)->where('is_demo', false)->where('actif', true)->count(),
            'total_entreprises'            => $entreprises->count(),
            'entreprises_actives'          => $entreprises->where('actif', true)->count(),
            'total_utilisateurs'           => User::where('is_demo', false)->count(),
            'abonnements_actifs'           => $abonnements->filter(fn ($a) => $a->isActive())->count(),
            'abonnements_essai'            => $abonnements->where('statut', 'essai')->filter(fn ($a) => ! $a->isExpired())->count(),
            'abonnements_expires'          => $abonnements->filter(fn ($a) => $a->isExpired() || $a->statut === 'suspendu')->count(),
            'abonnements_expirant_bientot' => $abonnements->filter(fn ($a) => $a->isActive() && $a->joursRestants() <= 7)->count(),
            'revenu_saas_total'            => (float) $abonnements->sum('montant'),
            'revenu_saas_mois'             => (float) Abonnement::whereHas('entreprise', fn ($q) => $q->where('is_demo', false))
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('montant'),
        ];

        $dernieresEntreprises = $entreprises->take(8);
        $derniersLogs = AuditLog::with(['user', 'entreprise'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $plans = Abonnement::PLANS;

        return view('admin.superadmin.dashboard', compact(
            'stats',
            'dernieresEntreprises',
            'derniersLogs',
            'plans'
        ));
    }

    /**
     * Liste des propriétaires et de leurs entreprises (Section 3.1)
     */
    public function index()
    {
        return $this->indexProprietaires();
    }

    public function indexProprietaires()
    {
        $this->ensureSuperAdmin();

        $proprietaires = User::with(['entreprisesPossedees.abonnements'])
            ->where(function ($q) {
                $q->where('is_proprietaire', true)
                  ->orWhereHas('roles', fn ($r) => $r->where('name', 'Propriétaire'));
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        $statsPlateforme = [
            'total_proprietaires' => User::where('is_proprietaire', true)->count(),
            'total_entreprises'   => Entreprise::where('is_demo', false)->count(),
            'entreprises_actives' => Entreprise::where('is_demo', false)->where('actif', true)->count(),
            'total_utilisateurs'  => User::where('is_demo', false)->count(),
        ];

        $plans = Abonnement::PLANS;

        if (request()->wantsJson()) {
            return response()->json([
                'proprietaires' => $proprietaires->items(),
                'stats'         => $statsPlateforme,
            ]);
        }

        return view('admin.entreprises.proprietaires', compact('proprietaires', 'statsPlateforme', 'plans'));
    }

    /**
     * Création d'un compte propriétaire par le superadministrateur (Section 3.1)
     * Rattache obligatoirement le propriétaire à une entreprise et crée son abonnement initial.
     */
    public function storeProprietaire(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'nom'                => 'required|string|max:255',
            'prenom'             => 'nullable|string|max:255',
            'email'              => 'required|email|max:255|unique:users,email',
            'telephone'          => 'nullable|string|max:30',
            'password'           => 'required|string|min:6',
            'nom_entreprise'     => 'nullable|string|max:255',
            'entreprise_nom'     => 'nullable|string|max:255',
            'entreprise_adresse' => 'nullable|string|max:255',
            'plan'               => 'nullable|string|in:essai,starter,pro,enterprise',
            'periodicite'        => 'nullable|string|in:mensuel,annuel',
            'duree_mois'         => 'nullable|integer|min:1|max:60',
        ]);

        // Tout utilisateur non-superadmin doit être lié à une entreprise
        $nomEntreprise = $validated['nom_entreprise']
            ?? $validated['entreprise_nom']
            ?? ('Entreprise ' . $validated['nom']);

        $planKey = $validated['plan'] ?? 'pro';
        $periodicite = $validated['periodicite'] ?? 'mensuel';
        $dureeMois = (int) ($validated['duree_mois'] ?? ($periodicite === 'annuel' ? 12 : 1));
        $planConfig = Abonnement::PLANS[$planKey] ?? Abonnement::PLANS['pro'];

        DB::transaction(function () use ($validated, $nomEntreprise, $planKey, $periodicite, $dureeMois, $planConfig) {
            Role::firstOrCreate(['name' => 'Propriétaire', 'guard_name' => 'web']);

            $user = User::create([
                'nom'             => $validated['nom'],
                'prenom'          => $validated['prenom'] ?? null,
                'email'           => $validated['email'],
                'telephone'       => $validated['telephone'] ?? null,
                'password'        => Hash::make($validated['password']),
                'actif'           => true,
                'is_proprietaire' => true,
            ]);

            $user->assignRole('Propriétaire');

            AuditService::log(
                'proprietaire.creation',
                $user,
                $user->id,
                ['email' => $user->email, 'nom' => $user->nom],
                'Création de compte propriétaire par le superadministrateur'
            );

            $code = 'ENT-' . strtoupper(Str::random(6));
            $entreprise = Entreprise::create([
                'nom'             => $nomEntreprise,
                'code'            => $code,
                'adresse'         => $validated['entreprise_adresse'] ?? null,
                'proprietaire_id' => $user->id,
                'actif'           => true,
            ]);

            $user->update(['current_entreprise_id' => $entreprise->id]);
            DB::table('entreprise_user')->insert([
                'entreprise_id' => $entreprise->id,
                'user_id'       => $user->id,
                'role'          => 'Propriétaire',
                'actif'         => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $montant = $periodicite === 'annuel' ? $planConfig['prix_annuel'] : $planConfig['prix_mensuel'];
            Abonnement::create([
                'entreprise_id'    => $entreprise->id,
                'plan'             => $planKey,
                'nom_plan'         => $planConfig['nom'],
                'periodicite'      => $periodicite,
                'montant'          => $montant,
                'devise'           => $entreprise->devise ?: 'FCFA',
                'date_debut'       => now()->toDateString(),
                'date_fin'         => $planKey === 'essai'
                    ? now()->addDays(14)->toDateString()
                    : now()->addMonths($dureeMois)->toDateString(),
                'statut'           => $planKey === 'essai' ? 'essai' : 'actif',
                'max_utilisateurs' => $planConfig['max_utilisateurs'],
                'max_produits'     => $planConfig['max_produits'],
                'mode_paiement'    => 'Activation SaaS',
                'notes'            => 'Abonnement initial créé avec l\'entreprise.',
                'cree_par'         => Auth::id(),
            ]);

            AuditService::log(
                'entreprise.creation',
                $entreprise,
                $entreprise->id,
                ['nom' => $entreprise->nom, 'proprietaire_id' => $user->id, 'plan' => $planKey],
                'Création initiale d\'entreprise et abonnement pour le propriétaire',
                $entreprise->id
            );
        });

        return back()->with('success', 'Compte propriétaire, entreprise et abonnement créés avec succès.');
    }

    /**
     * Modification d'un propriétaire (Section 3.1)
     */
    public function updateProprietaire(Request $request, User $proprietaire)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'nullable|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $proprietaire->id,
            'telephone' => 'nullable|string|max:30',
        ]);

        $proprietaire->update($validated);

        AuditService::log(
            'proprietaire.modification',
            $proprietaire,
            $proprietaire->id,
            $validated,
            'Modification des informations du propriétaire par le superadministrateur'
        );

        return back()->with('success', 'Informations du propriétaire mises à jour.');
    }

    /**
     * Activation / Désactivation d'un compte propriétaire (Section 3.1)
     */
    public function toggleProprietaire(User $proprietaire)
    {
        $this->ensureSuperAdmin();

        $proprietaire->actif = ! $proprietaire->actif;
        $proprietaire->save();

        AuditService::log(
            $proprietaire->actif ? 'proprietaire.activation' : 'proprietaire.desactivation',
            $proprietaire,
            $proprietaire->id,
            ['actif' => $proprietaire->actif],
            $proprietaire->actif ? 'Activation du compte propriétaire' : 'Désactivation du compte propriétaire'
        );

        return back()->with('success', $proprietaire->actif ? 'Propriétaire activé.' : 'Propriétaire désactivé.');
    }

    /**
     * Enregistrement d'une entreprise + abonnement pour un propriétaire par le superadministrateur (Section 3.1)
     * Un utilisateur / propriétaire ne peut être lié qu'à une seule entreprise.
     */
    public function storeEntrepriseForProprietaire(Request $request, User $proprietaire)
    {
        $this->ensureSuperAdmin();

        $alreadyLinked = $proprietaire->current_entreprise_id !== null
            || Entreprise::where('proprietaire_id', $proprietaire->id)->exists()
            || DB::table('entreprise_user')->where('user_id', $proprietaire->id)->exists();

        if ($alreadyLinked) {
            return back()->with('error', 'Cet utilisateur est déjà rattaché à une entreprise. Un utilisateur ne peut être lié qu\'à une seule entreprise.');
        }

        $validated = $request->validate([
            'nom'                    => 'required|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'telephone'              => 'nullable|string|max:30',
            'adresse'                => 'nullable|string|max:255',
            'devise'                 => 'nullable|string|max:10',
            'plafond_avance_salaire' => 'nullable|numeric|min:0',
            'plan'                   => 'nullable|string|in:essai,starter,pro,enterprise',
            'periodicite'            => 'nullable|string|in:mensuel,annuel',
        ]);

        $planKey = $validated['plan'] ?? 'pro';
        $periodicite = $validated['periodicite'] ?? 'mensuel';
        $planConfig = Abonnement::PLANS[$planKey] ?? Abonnement::PLANS['pro'];

        DB::transaction(function () use ($validated, $proprietaire, $planKey, $periodicite, $planConfig) {
            $code = 'ENT-' . strtoupper(Str::random(6));
            $entreprise = Entreprise::create([
                'nom'                    => $validated['nom'],
                'code'                   => $code,
                'email'                  => $validated['email'] ?? null,
                'telephone'              => $validated['telephone'] ?? null,
                'adresse'                => $validated['adresse'] ?? null,
                'devise'                 => $validated['devise'] ?? 'FCFA',
                'plafond_avance_salaire' => $validated['plafond_avance_salaire'] ?? 100000,
                'proprietaire_id'        => $proprietaire->id,
                'actif'                  => true,
            ]);

            DB::table('entreprise_user')->updateOrInsert(
                ['entreprise_id' => $entreprise->id, 'user_id' => $proprietaire->id],
                ['role' => 'Propriétaire', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            if (! $proprietaire->current_entreprise_id) {
                $proprietaire->update(['current_entreprise_id' => $entreprise->id]);
            }

            $montant = $periodicite === 'annuel' ? $planConfig['prix_annuel'] : $planConfig['prix_mensuel'];
            Abonnement::create([
                'entreprise_id'    => $entreprise->id,
                'plan'             => $planKey,
                'nom_plan'         => $planConfig['nom'],
                'periodicite'      => $periodicite,
                'montant'          => $montant,
                'devise'           => $entreprise->devise ?: 'FCFA',
                'date_debut'       => now()->toDateString(),
                'date_fin'         => $planKey === 'essai'
                    ? now()->addDays(14)->toDateString()
                    : ($periodicite === 'annuel' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString()),
                'statut'           => $planKey === 'essai' ? 'essai' : 'actif',
                'max_utilisateurs' => $planConfig['max_utilisateurs'],
                'max_produits'     => $planConfig['max_produits'],
                'mode_paiement'    => 'Activation SaaS',
                'cree_par'         => Auth::id(),
            ]);

            AuditService::log(
                'entreprise.creation',
                $entreprise,
                $entreprise->id,
                ['nom' => $entreprise->nom, 'proprietaire_id' => $proprietaire->id, 'plan' => $planKey],
                'Enregistrement d\'une nouvelle entreprise et de son abonnement pour le propriétaire',
                $entreprise->id
            );
        });

        return back()->with('success', 'Entreprise et abonnement enregistrés pour le propriétaire avec succès.');
    }

    /**
     * =========================================================================
     * CRUD COMPLET DES ENTREPRISES (SECTION 3.1 & GESTION SAAS)
     * =========================================================================
     */

    /**
     * Liste et filtres de toutes les entreprises hébergées sur le SaaS
     */
    public function entreprises(Request $request)
    {
        $this->ensureSuperAdmin();

        $search = trim((string) $request->input('search', ''));
        $statut = $request->input('statut');

        $query = Entreprise::with(['proprietaire', 'abonnements'])
            ->withCount(['utilisateurs', 'produits', 'ventes'])
            ->where('is_demo', false)
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhereHas('proprietaire', function ($pq) use ($search) {
                      $pq->where('nom', 'like', "%{$search}%")
                         ->orWhere('prenom', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($statut === 'actif') {
            $query->where('actif', true);
        } elseif ($statut === 'inactif') {
            $query->where('actif', false);
        }

        $entreprises = $query->paginate(15)->withQueryString();

        foreach ($entreprises as $ent) {
            if ($ent->abonnements->isEmpty()) {
                $ent->ensureDefaultSubscription('pro', Auth::id());
                $ent->load('abonnements');
            }
        }

        $stats = [
            'total_entreprises'     => Entreprise::where('is_demo', false)->count(),
            'entreprises_actives'   => Entreprise::where('is_demo', false)->where('actif', true)->count(),
            'entreprises_inactives' => Entreprise::where('is_demo', false)->where('actif', false)->count(),
            'total_utilisateurs'    => DB::table('entreprise_user')->count(),
        ];

        // Utilisateurs éligibles pour être assignés comme Propriétaire
        $proprietairesDisponibles = User::where(function ($q) {
                $q->where('is_proprietaire', true)
                  ->orWhereHas('roles', fn ($r) => $r->where('name', 'Propriétaire'));
            })
            ->where('is_demo', false)
            ->orderBy('nom')
            ->get();

        $plans = Abonnement::PLANS;

        return view('admin.superadmin.entreprises', compact('entreprises', 'stats', 'proprietairesDisponibles', 'plans', 'search', 'statut'));
    }

    /**
     * Création directe d'une entreprise avec nouveau ou ancien propriétaire et son abonnement
     */
    public function storeEntreprise(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'owner_mode'             => 'required|in:new,existing',
            // Nouveau propriétaire
            'owner_nom'              => 'required_if:owner_mode,new|nullable|string|max:255',
            'owner_prenom'           => 'nullable|string|max:255',
            'owner_email'            => 'required_if:owner_mode,new|nullable|email|max:255|unique:users,email',
            'owner_telephone'        => 'nullable|string|max:30',
            'owner_password'         => 'required_if:owner_mode,new|nullable|string|min:6',
            // Propriétaire existant
            'proprietaire_id'        => 'required_if:owner_mode,existing|nullable|integer|exists:users,id',
            // Champs entreprise
            'nom'                    => 'required|string|max:255',
            'code'                   => 'nullable|string|max:50|unique:entreprises,code',
            'email'                  => 'nullable|email|max:255',
            'telephone'              => 'nullable|string|max:30',
            'adresse'                => 'nullable|string|max:255',
            'identifiant_fiscal'     => 'nullable|string|max:100',
            'devise'                 => 'nullable|string|max:10',
            'plafond_avance_salaire' => 'nullable|numeric|min:0',
            'logo'                   => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
            // Abonnement initial
            'plan'                   => 'nullable|string|in:essai,starter,pro,enterprise',
            'periodicite'            => 'nullable|string|in:mensuel,annuel',
            'duree_mois'             => 'nullable|integer|min:1|max:60',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uploadDir = public_path('uploads/logos_entreprises');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $fileName = 'logo_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $logoPath = 'uploads/logos_entreprises/' . $fileName;
        }

        $planKey = $validated['plan'] ?? 'pro';
        $periodicite = $validated['periodicite'] ?? 'mensuel';
        $dureeMois = (int) ($validated['duree_mois'] ?? ($periodicite === 'annuel' ? 12 : 1));
        $planConfig = Abonnement::PLANS[$planKey] ?? Abonnement::PLANS['pro'];

        DB::transaction(function () use ($validated, $logoPath, $planKey, $periodicite, $dureeMois, $planConfig) {
            // 1. Gérer le propriétaire
            if ($validated['owner_mode'] === 'new') {
                Role::firstOrCreate(['name' => 'Propriétaire', 'guard_name' => 'web']);
                $owner = User::create([
                    'nom'             => $validated['owner_nom'],
                    'prenom'          => $validated['owner_prenom'] ?? null,
                    'email'           => $validated['owner_email'],
                    'telephone'       => $validated['owner_telephone'] ?? null,
                    'password'        => Hash::make($validated['owner_password']),
                    'actif'           => true,
                    'is_proprietaire' => true,
                ]);
                $owner->assignRole('Propriétaire');

                AuditService::log(
                    'proprietaire.creation',
                    $owner,
                    $owner->id,
                    ['email' => $owner->email, 'nom' => $owner->nom],
                    'Création du compte propriétaire lors de la création d\'entreprise'
                );
            } else {
                $owner = User::findOrFail($validated['proprietaire_id']);
                $owner->is_proprietaire = true;
                $owner->save();
                if (! $owner->hasRole('Propriétaire')) {
                    $owner->assignRole('Propriétaire');
                }
            }

            // 2. Créer l'entreprise
            $code = ! empty($validated['code'])
                ? strtoupper(trim($validated['code']))
                : 'ENT-' . strtoupper(Str::random(6));

            $entreprise = Entreprise::create([
                'nom'                    => $validated['nom'],
                'code'                   => $code,
                'logo'                   => $logoPath,
                'email'                  => $validated['email'] ?? null,
                'telephone'              => $validated['telephone'] ?? null,
                'adresse'                => $validated['adresse'] ?? null,
                'identifiant_fiscal'     => $validated['identifiant_fiscal'] ?? null,
                'devise'                 => $validated['devise'] ?? 'FCFA',
                'plafond_avance_salaire' => $validated['plafond_avance_salaire'] ?? 100000,
                'proprietaire_id'        => $owner->id,
                'actif'                  => true,
            ]);

            // 3. Lier l'utilisateur à l'entreprise
            DB::table('entreprise_user')->updateOrInsert(
                ['entreprise_id' => $entreprise->id, 'user_id' => $owner->id],
                ['role' => 'Propriétaire', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            if (! $owner->current_entreprise_id) {
                $owner->update(['current_entreprise_id' => $entreprise->id]);
            }

            // 4. Initialiser les horaires d'ouverture par défaut
            foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'] as $jour) {
                Horaire::withoutGlobalScope('entreprise')->create([
                    'entreprise_id'   => $entreprise->id,
                    'jour_semaine'    => $jour,
                    'heure_ouverture' => '00:00',
                    'heure_fermeture' => '23:59',
                    'id_utilisateur'  => $owner->id,
                ]);
            }

            // 5. Initialiser l'abonnement
            $montant = $periodicite === 'annuel' ? $planConfig['prix_annuel'] : $planConfig['prix_mensuel'];
            Abonnement::create([
                'entreprise_id'    => $entreprise->id,
                'plan'             => $planKey,
                'nom_plan'         => $planConfig['nom'],
                'periodicite'      => $periodicite,
                'montant'          => $montant,
                'devise'           => $entreprise->devise ?: 'FCFA',
                'date_debut'       => now()->toDateString(),
                'date_fin'         => $planKey === 'essai'
                    ? now()->addDays(14)->toDateString()
                    : now()->addMonths($dureeMois)->toDateString(),
                'statut'           => $planKey === 'essai' ? 'essai' : 'actif',
                'max_utilisateurs' => $planConfig['max_utilisateurs'],
                'max_produits'     => $planConfig['max_produits'],
                'mode_paiement'    => 'Activation SaaS',
                'notes'            => 'Abonnement initial créé avec l\'entreprise.',
                'cree_par'         => Auth::id(),
            ]);

            AuditService::log(
                'entreprise.creation',
                $entreprise,
                $entreprise->id,
                ['nom' => $entreprise->nom, 'code' => $entreprise->code, 'proprietaire_id' => $owner->id, 'plan' => $planKey],
                'Création d\'une entreprise par le superadministrateur',
                $entreprise->id
            );
        });

        return back()->with('success', 'Entreprise créée avec succès.');
    }

    /**
     * Consultation des détails complets d'une entreprise
     */
    public function showEntreprise(Entreprise $entreprise)
    {
        $this->ensureSuperAdmin();

        $entreprise->load(['proprietaire', 'abonnements', 'utilisateurs']);
        $sub = $entreprise->abonnementActuel();

        $stats = [
            'utilisateurs' => $entreprise->utilisateurs()->count(),
            'produits'     => $entreprise->produits()->count(),
            'ventes'       => $entreprise->ventes()->count(),
            'clients'      => $entreprise->clients()->count(),
            'fournisseurs' => $entreprise->fournisseurs()->count(),
            'depenses'     => $entreprise->depenses()->count(),
        ];

        if (request()->wantsJson()) {
            return response()->json([
                'entreprise' => $entreprise,
                'abonnement' => $sub,
                'stats'      => $stats,
            ]);
        }

        return redirect()->route('superadmin.entreprises.index', ['show' => $entreprise->id]);
    }

    /**
     * Modification d'une entreprise par le SuperAdmin
     */
    public function updateEntreprise(Request $request, Entreprise $entreprise)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'nom'                    => 'required|string|max:255',
            'code'                   => 'required|string|max:50|unique:entreprises,code,' . $entreprise->id,
            'email'                  => 'nullable|email|max:255',
            'telephone'              => 'nullable|string|max:30',
            'adresse'                => 'nullable|string|max:255',
            'identifiant_fiscal'     => 'nullable|string|max:100',
            'devise'                 => 'nullable|string|max:10',
            'plafond_avance_salaire' => 'nullable|numeric|min:0',
            'proprietaire_id'        => 'nullable|integer|exists:users,id',
            'actif'                  => 'nullable|boolean',
            'logo'                   => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uploadDir = public_path('uploads/logos_entreprises');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $fileName = 'logo_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $validated['logo'] = 'uploads/logos_entreprises/' . $fileName;
        }

        $validated['actif'] = $request->boolean('actif', $entreprise->actif);

        DB::transaction(function () use ($entreprise, $validated) {
            $oldProprietaireId = (int) $entreprise->proprietaire_id;
            $newProprietaireId = isset($validated['proprietaire_id']) ? (int) $validated['proprietaire_id'] : $oldProprietaireId;

            $entreprise->update($validated);

            if ($newProprietaireId && $newProprietaireId !== $oldProprietaireId) {
                $newOwner = User::find($newProprietaireId);
                if ($newOwner) {
                    $newOwner->is_proprietaire = true;
                    $newOwner->save();
                    if (! $newOwner->hasRole('Propriétaire')) {
                        $newOwner->assignRole('Propriétaire');
                    }
                    DB::table('entreprise_user')->updateOrInsert(
                        ['entreprise_id' => $entreprise->id, 'user_id' => $newOwner->id],
                        ['role' => 'Propriétaire', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
                    );
                    if (! $newOwner->current_entreprise_id) {
                        $newOwner->update(['current_entreprise_id' => $entreprise->id]);
                    }
                }
            }

            AuditService::log(
                'entreprise.modification',
                $entreprise,
                $entreprise->id,
                $validated,
                'Modification de l\'entreprise par le superadministrateur',
                $entreprise->id
            );
        });

        return back()->with('success', 'Entreprise mise à jour avec succès.');
    }

    /**
     * Activation / Désactivation rapide d'une entreprise
     */
    public function toggleEntreprise(Entreprise $entreprise)
    {
        $this->ensureSuperAdmin();

        $entreprise->actif = ! $entreprise->actif;
        $entreprise->save();

        AuditService::log(
            $entreprise->actif ? 'entreprise.activation' : 'entreprise.desactivation',
            $entreprise,
            $entreprise->id,
            ['actif' => $entreprise->actif],
            $entreprise->actif ? 'Activation de l\'entreprise' : 'Désactivation de l\'entreprise',
            $entreprise->id
        );

        return back()->with('success', $entreprise->actif ? 'Entreprise activée avec succès.' : 'Entreprise désactivée avec succès.');
    }

    /**
     * Suppression sécurisée d'une entreprise
     */
    public function destroyEntreprise(Entreprise $entreprise)
    {
        $this->ensureSuperAdmin();

        if ($entreprise->is_demo) {
            return back()->with('error', 'L\'entreprise de démonstration ne peut pas être supprimée.');
        }

        // Vérifier si des ventes ou achats réels existent (Protection de l'intégrité comptable)
        $hasVentes = $entreprise->ventes()->exists();
        $hasAchats = $entreprise->achats()->exists();

        if ($hasVentes || $hasAchats) {
            return back()->with('error', 'Impossible de supprimer cette entreprise car des ventes ou achats réels y sont rattachés. Pour suspendre ses accès en conservant l\'intégrité comptable, veuillez simplement la désactiver.');
        }

        DB::transaction(function () use ($entreprise) {
            Horaire::withoutGlobalScope('entreprise')->where('entreprise_id', $entreprise->id)->delete();
            Abonnement::where('entreprise_id', $entreprise->id)->delete();
            DB::table('entreprise_user')->where('entreprise_id', $entreprise->id)->delete();
            User::where('current_entreprise_id', $entreprise->id)->update(['current_entreprise_id' => null]);

            AuditService::log(
                'entreprise.suppression',
                $entreprise,
                $entreprise->id,
                ['nom' => $entreprise->nom, 'code' => $entreprise->code],
                'Suppression de l\'entreprise par le superadministrateur',
                $entreprise->id
            );

            $entreprise->delete();
        });

        return back()->with('success', 'Entreprise supprimée avec succès.');
    }

    /**
     * Gestion globale des abonnements de toutes les entreprises
     */
    public function abonnements(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = Entreprise::with(['proprietaire', 'abonnements'])
            ->where('is_demo', false)
            ->orderByDesc('created_at');

        $entreprises = $query->get();

        // Assurer que chaque entreprise a au moins un abonnement initialisé
        foreach ($entreprises as $ent) {
            if ($ent->abonnements->isEmpty()) {
                $ent->ensureDefaultSubscription('pro', Auth::id());
                $ent->load('abonnements');
            }
        }

        if ($request->filled('statut')) {
            $statutFilter = $request->input('statut');
            $entreprises = $entreprises->filter(function (Entreprise $ent) use ($statutFilter) {
                $sub = $ent->abonnementActuel();
                return $sub && $sub->statutEffectif() === $statutFilter;
            })->values();
        }

        $plans = Abonnement::PLANS;

        $allAbonnements = Abonnement::whereHas('entreprise', fn ($q) => $q->where('is_demo', false))->get();
        $statsAbonnements = [
            'actifs'       => $allAbonnements->filter(fn ($a) => $a->isActive())->count(),
            'essai'        => $allAbonnements->where('statut', 'essai')->filter(fn ($a) => ! $a->isExpired())->count(),
            'expires'      => $allAbonnements->filter(fn ($a) => $a->isExpired() || $a->statut === 'suspendu')->count(),
            'revenu_total' => $allAbonnements->sum('montant'),
        ];

        return view('admin.superadmin.abonnements', compact('entreprises', 'plans', 'statsAbonnements'));
    }

    /**
     * Création / Renouvellement / Mise à jour de l'abonnement d'une entreprise par le Super Admin
     */
    public function storeOrRenewAbonnement(Request $request, Entreprise $entreprise)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'plan'               => 'required|string|in:essai,starter,pro,enterprise',
            'periodicite'        => 'required|string|in:mensuel,annuel',
            'montant'            => 'nullable|numeric|min:0',
            'date_debut'         => 'required|date',
            'date_fin'           => 'required|date|after_or_equal:date_debut',
            'statut'             => 'required|string|in:actif,essai,expire,suspendu',
            'max_utilisateurs'   => 'nullable|integer|min:1',
            'max_produits'       => 'nullable|integer|min:1',
            'mode_paiement'      => 'nullable|string|max:100',
            'reference_paiement' => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $planConfig = Abonnement::PLANS[$validated['plan']] ?? Abonnement::PLANS['pro'];
        $montant = $validated['montant'] !== null && $validated['montant'] !== ''
            ? (float) $validated['montant']
            : ($validated['periodicite'] === 'annuel' ? $planConfig['prix_annuel'] : $planConfig['prix_mensuel']);

        $abonnement = Abonnement::create([
            'entreprise_id'      => $entreprise->id,
            'plan'               => $validated['plan'],
            'nom_plan'           => $planConfig['nom'],
            'periodicite'        => $validated['periodicite'],
            'montant'            => $montant,
            'devise'             => $entreprise->devise ?: 'FCFA',
            'date_debut'         => $validated['date_debut'],
            'date_fin'           => $validated['date_fin'],
            'statut'             => $validated['statut'],
            'max_utilisateurs'   => $validated['max_utilisateurs'] ?? $planConfig['max_utilisateurs'],
            'max_produits'       => $validated['max_produits'] ?? $planConfig['max_produits'],
            'mode_paiement'      => $validated['mode_paiement'] ?? 'Renouvellement SaaS',
            'reference_paiement' => $validated['reference_paiement'] ?? null,
            'notes'              => $validated['notes'] ?? null,
            'cree_par'           => Auth::id(),
        ]);

        AuditService::log(
            'abonnement.mise_a_jour',
            $abonnement,
            $abonnement->id,
            [
                'entreprise_id' => $entreprise->id,
                'plan'          => $abonnement->plan,
                'statut'        => $abonnement->statut,
                'date_fin'      => Carbon::parse($abonnement->date_fin)->format('Y-m-d'),
                'montant'       => $abonnement->montant,
            ],
            'Mise à jour / renouvellement de l\'abonnement de l\'entreprise ' . $entreprise->nom,
            $entreprise->id
        );

        return back()->with('success', 'Abonnement de l\'entreprise ' . $entreprise->nom . ' mis à jour avec succès.');
    }

    /**
     * Suspension / Réactivation d'un abonnement par le Super Admin
     */
    public function toggleAbonnementStatut(Abonnement $abonnement)
    {
        $this->ensureSuperAdmin();

        $nouveauStatut = $abonnement->statut === 'suspendu' ? 'actif' : 'suspendu';
        $abonnement->update(['statut' => $nouveauStatut]);

        AuditService::log(
            $nouveauStatut === 'suspendu' ? 'abonnement.suspension' : 'abonnement.reactivation',
            $abonnement,
            $abonnement->id,
            ['statut' => $nouveauStatut],
            ($nouveauStatut === 'suspendu' ? 'Suspension' : 'Réactivation') . ' de l\'abonnement #' . $abonnement->id,
            $abonnement->entreprise_id
        );

        return back()->with(
            'success',
            $nouveauStatut === 'suspendu' ? 'Abonnement suspendu.' : 'Abonnement réactivé avec succès.'
        );
    }
}
