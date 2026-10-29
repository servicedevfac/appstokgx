<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Horaire;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EntrepriseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $entreprises = $user->accessibleEntreprises();
        $currentEntrepriseId = EntrepriseContext::currentId($user);
        $activeEntreprise = EntrepriseContext::current($user);

        if (request()->wantsJson()) {
            return response()->json([
                'entreprises'           => $entreprises,
                'current_entreprise_id' => $currentEntrepriseId,
            ]);
        }

        return view('admin.entreprises.index', compact('entreprises', 'currentEntrepriseId', 'activeEntreprise'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasRole('super admin')) {
            abort(403, 'Seul le Super Administrateur de la plateforme SaaS peut créer une nouvelle entreprise.');
        }

        $validated = $request->validate([
            'nom'                    => 'required|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'telephone'              => 'nullable|string|max:30',
            'adresse'                => 'nullable|string|max:255',
            'identifiant_fiscal'     => 'nullable|string|max:100',
            'devise'                 => 'nullable|string|max:10',
            'plafond_avance_salaire' => 'nullable|numeric|min:0',
            'proprietaire_id'        => 'required|integer|exists:users,id',
            'logo'                   => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $proprietaireId = (int) $validated['proprietaire_id'];

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

        $entreprise = DB::transaction(function () use ($validated, $proprietaireId, $logoPath) {
            $code = 'ENT-' . strtoupper(Str::random(6));
            $ent = Entreprise::create([
                'nom'                    => $validated['nom'],
                'code'                   => $code,
                'logo'                   => $logoPath,
                'email'                  => $validated['email'] ?? null,
                'telephone'              => $validated['telephone'] ?? null,
                'adresse'                => $validated['adresse'] ?? null,
                'identifiant_fiscal'     => $validated['identifiant_fiscal'] ?? null,
                'devise'                 => $validated['devise'] ?? 'FCFA',
                'plafond_avance_salaire' => $validated['plafond_avance_salaire'] ?? 100000,
                'proprietaire_id'        => $proprietaireId,
                'actif'                  => true,
            ]);

            DB::table('entreprise_user')->updateOrInsert(
                ['entreprise_id' => $ent->id, 'user_id' => $proprietaireId],
                ['role' => 'Propriétaire', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            $ent->ensureDefaultSubscription('pro', $proprietaireId);

            // Initialiser les horaires d'ouverture par défaut pour l'entreprise
            foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'] as $jour) {
                Horaire::withoutGlobalScope('entreprise')->create([
                    'entreprise_id'   => $ent->id,
                    'jour_semaine'    => $jour,
                    'heure_ouverture' => '00:00',
                    'heure_fermeture' => '23:59',
                    'id_utilisateur'  => $proprietaireId,
                ]);
            }

            AuditService::log(
                'entreprise.creation',
                $ent,
                $ent->id,
                ['nom' => $ent->nom, 'code' => $ent->code],
                'Création d\'une nouvelle entreprise',
                $ent->id
            );

            return $ent;
        });

        return back()->with('success', 'Entreprise créée avec succès.');
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $user = Auth::user();
        if (! $user->canAccessEntreprise($entreprise->id)) {
            abort(403, 'Accès interdit à cette entreprise.');
        }

        if (! $user->hasRole('super admin')
            && (int) $entreprise->proprietaire_id !== (int) $user->id
            && ! in_array($user->roleInEntreprise($entreprise->id), ['Propriétaire', 'Administrateur'], true)
        ) {
            abort(403, 'Seul le propriétaire ou un administrateur de l\'entreprise peut modifier ses paramètres.');
        }

        $validated = $request->validate([
            'nom'                    => 'required|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'telephone'              => 'nullable|string|max:30',
            'adresse'                => 'nullable|string|max:255',
            'identifiant_fiscal'     => 'nullable|string|max:100',
            'devise'                 => 'nullable|string|max:10',
            'plafond_avance_salaire' => 'nullable|numeric|min:0',
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

        $entreprise->update($validated);

        AuditService::log(
            'entreprise.modification',
            $entreprise,
            $entreprise->id,
            $validated,
            'Modification des paramètres de l\'entreprise',
            $entreprise->id
        );

        return back()->with('success', 'Paramètres de l\'entreprise mis à jour avec succès.');
    }

    /**
     * Changement sécurisé de l'entreprise active (Section 3.2 & 3.4)
     */
    public function switchEntreprise(Request $request, $entreprise = null)
    {
        $targetIdRaw = $entreprise instanceof Entreprise
            ? $entreprise->id
            : ($entreprise ?? $request->input('entreprise_id'));

        $targetEntrepriseId = (int) $targetIdRaw;
        if (! $targetEntrepriseId || ! Entreprise::where('id', $targetEntrepriseId)->exists()) {
            abort(404, 'Entreprise introuvable.');
        }

        $user = Auth::user();

        if (! $user->canAccessEntreprise($targetEntrepriseId)) {
            abort(403, 'Accès refusé : vous n\'êtes pas autorisé à accéder à cette entreprise.');
        }

        session([
            'current_entreprise_id' => $targetEntrepriseId,
            'active_entreprise_id'  => $targetEntrepriseId,
        ]);
        $user->update(['current_entreprise_id' => $targetEntrepriseId]);
        EntrepriseContext::setOverride($targetEntrepriseId);

        return back()->with('success', 'Entreprise active changée avec succès.');
    }

    /**
     * Attribution d'un rôle et de permissions à un utilisateur pour une entreprise donnée (Section 3.3)
     */
    public function assignUserRole(Request $request, Entreprise $entreprise)
    {
        $user = Auth::user();
        if (! $user->canAccessEntreprise($entreprise->id)) {
            abort(403, 'Accès interdit à cette entreprise.');
        }

        if (! $user->hasRole('super admin')
            && (int) $entreprise->proprietaire_id !== (int) $user->id
            && ! in_array($user->roleInEntreprise($entreprise->id), ['Propriétaire', 'Administrateur'], true)
        ) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'user_id'     => 'required|integer|exists:users,id',
            'role'        => 'required|string',
            'permissions' => 'nullable|array',
            'actif'       => 'nullable|boolean',
        ]);

        $normalizedRole = User::normalizeRoleName($validated['role']);
        if ($normalizedRole === 'super admin' && ! $user->hasRole('super admin')) {
            abort(403, 'Impossible d\'attribuer le rôle super admin.');
        }

        $targetUser = User::findOrFail($validated['user_id']);

        $linkedElsewhere = ($targetUser->current_entreprise_id && (int) $targetUser->current_entreprise_id !== (int) $entreprise->id)
            || DB::table('entreprise_user')
                ->where('user_id', $targetUser->id)
                ->where('entreprise_id', '!=', $entreprise->id)
                ->exists();

        if ($linkedElsewhere) {
            abort(403, 'Cet utilisateur est déjà rattaché à une autre entreprise. Un utilisateur ne peut être lié qu\'à une seule entreprise.');
        }

        DB::table('entreprise_user')->updateOrInsert(
            ['entreprise_id' => $entreprise->id, 'user_id' => $targetUser->id],
            [
                'role'        => $normalizedRole,
                'permissions' => isset($validated['permissions']) ? json_encode($validated['permissions']) : null,
                'actif'       => $request->boolean('actif', true),
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );

        if (! $targetUser->current_entreprise_id) {
            $targetUser->update(['current_entreprise_id' => $entreprise->id]);
        }

        AuditService::log(
            'permission.changement',
            $targetUser,
            $targetUser->id,
            [
                'entreprise_id' => $entreprise->id,
                'role'          => $normalizedRole,
                'permissions'   => $validated['permissions'] ?? null,
            ],
            "Attribution du rôle {$normalizedRole} dans l'entreprise {$entreprise->nom}",
            $entreprise->id
        );

        return back()->with('success', 'Rôle de l\'utilisateur mis à jour pour cette entreprise.');
    }

    /**
     * Suppression sécurisée d'une entreprise (Super Admin uniquement)
     */
    public function destroy(Entreprise $entreprise)
    {
        $user = Auth::user();
        if (! $user->hasRole('super admin')) {
            abort(403, 'Seul le Super Administrateur peut supprimer une entreprise.');
        }

        if ($entreprise->is_demo) {
            return back()->with('error', 'L\'entreprise de démonstration ne peut pas être supprimée.');
        }

        if ($entreprise->ventes()->exists() || $entreprise->achats()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette entreprise car des ventes ou achats réels y sont rattachés. Vous pouvez la désactiver pour suspendre son accès.');
        }

        DB::transaction(function () use ($entreprise) {
            Horaire::withoutGlobalScope('entreprise')->where('entreprise_id', $entreprise->id)->delete();
            DB::table('abonnements')->where('entreprise_id', $entreprise->id)->delete();
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
}
