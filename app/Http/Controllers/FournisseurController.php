<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FournisseurController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('afficher fournisseurs') || Auth::user()->can('afficher achats'), 403);

        $fournisseurs = Fournisseur::withCount('achats')->orderBy('nom')->get();
        return view('admin.fournisseurs.index', compact('fournisseurs'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->can('afficher fournisseurs') || Auth::user()->can('afficher achats'), 403);

        $fournisseur = Fournisseur::with([
            'achats.details.produit',
            'achats.paiements',
            'achats.retours.produit',
            'paiements',
        ])->findOrFail($id);

        return view('admin.fournisseurs.show', compact('fournisseur'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('créer fournisseur'), 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
        ]);

        $validated['entreprise_id'] = EntrepriseContext::currentId();

        $fournisseur = Fournisseur::create($validated);

        AuditService::log(
            'fournisseur.created',
            "Création du fournisseur {$fournisseur->nom}",
            $fournisseur,
            null,
            $fournisseur->toArray()
        );

        return redirect()->back()->with('success', 'Fournisseur ajouté avec succès.');
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('modifier fournisseur'), 403);

        $fournisseur = Fournisseur::findOrFail($id);
        $old = $fournisseur->toArray();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
        ]);

        $fournisseur->update($validated);

        AuditService::log(
            'fournisseur.updated',
            "Modification du fournisseur {$fournisseur->nom}",
            $fournisseur,
            $old,
            $fournisseur->toArray()
        );

        return redirect()->back()->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->can('supprimer fournisseur'), 403);

        $fournisseur = Fournisseur::withCount('achats')->findOrFail($id);

        if ($fournisseur->achats_count > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer un fournisseur ayant des achats enregistrés.');
        }

        $old = $fournisseur->toArray();
        $fournisseur->delete();

        AuditService::log(
            'fournisseur.deleted',
            "Suppression du fournisseur {$old['nom']}",
            null,
            $old,
            null
        );

        return redirect()->back()->with('success', 'Fournisseur supprimé avec succès.');
    }
}
