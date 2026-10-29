<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('afficher employés') || Auth::user()->can('afficher avances salaires'), 403);

        $employes = Employe::with(['avances.remboursements'])->orderBy('nom')->get();
        $entreprise = EntrepriseContext::current();

        return view('admin.employes.index', compact('employes', 'entreprise'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->can('afficher employés') || Auth::user()->can('afficher avances salaires'), 403);

        $employe = Employe::with(['avances.remboursements.user', 'avances.user', 'remboursements'])->findOrFail($id);

        return view('admin.employes.show', compact('employe'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('créer employé'), 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'poste' => 'required|string|max:255',
            'salaire_base' => 'required|numeric|min:0',
            'plafond_avance' => 'nullable|numeric|min:0',
            'date_embauche' => 'nullable|date',
        ]);

        $validated['entreprise_id'] = EntrepriseContext::currentId();
        $validated['actif'] = true;

        $employe = Employe::create($validated);

        AuditService::log(
            'employe.created',
            "Création de l'employé {$employe->nom} {$employe->prenom} ({$employe->poste})",
            $employe,
            null,
            $employe->toArray()
        );

        return redirect()->back()->with('success', 'Employé enregistré avec succès.');
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('modifier employé'), 403);

        $employe = Employe::findOrFail($id);
        $old = $employe->toArray();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'poste' => 'required|string|max:255',
            'salaire_base' => 'required|numeric|min:0',
            'plafond_avance' => 'nullable|numeric|min:0',
            'date_embauche' => 'nullable|date',
            'actif' => 'nullable|boolean',
        ]);

        $employe->update($validated);

        AuditService::log(
            'employe.updated',
            "Mise à jour de l'employé {$employe->nom} {$employe->prenom}",
            $employe,
            $old,
            $employe->toArray()
        );

        return redirect()->back()->with('success', 'Employé mis à jour avec succès.');
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->can('supprimer employé'), 403);

        $employe = Employe::withCount('avances')->findOrFail($id);

        if ($employe->avances_count > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer un employé ayant un historique d\'avances sur salaire. Vous pouvez le désactiver.');
        }

        $old = $employe->toArray();
        $employe->delete();

        AuditService::log(
            'employe.deleted',
            "Suppression de l'employé {$old['nom']}",
            null,
            $old,
            null
        );

        return redirect()->back()->with('success', 'Employé supprimé avec succès.');
    }
}

