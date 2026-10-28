<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * Liste des clients avec filtre de recherche
     */
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%")
                  ->orWhere('code_client', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Formulaire de création d'un client
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Enregistrement d'un nouveau client avec génération atomique du code
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:255',
        ], [
            'nom.required' => 'Le nom du client est obligatoire.',
            'nom.max'      => 'Le nom ne peut pas dépasser 255 caractères.',
            'telephone.max'=> 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
        ]);

        DB::transaction(function () use ($validated) {
            $now = now();
            $prefix = 'CLT-' . $now->format('my') . '-';

            $lastClient = Client::where('code_client', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('code_client')
                ->first();

            $nextNumber = 1;
            if ($lastClient) {
                $lastSuffix = (int) Str::afterLast($lastClient->code_client, '-');
                $nextNumber = $lastSuffix + 1;
            }

            $codeClient = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            Client::create([
                'code_client' => $codeClient,
                'nom'         => $validated['nom'],
                'prenom'      => $validated['prenom'],
                'telephone'   => $validated['telephone'] ?? null,
                'adresse'     => $validated['adresse'] ?? null,
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }

    /**
     * Affichage de la fiche client et de ses ventes
     */
    public function show(Client $client)
    {
        $ventes = $client->ventes()->with('user')->orderByDesc('date_vente')->paginate(10);

        return view('admin.clients.show', compact('client', 'ventes'));
    }

    /**
     * Formulaire de modification d'un client (réservé aux admins)
     */
    public function edit(Client $client)
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin']) && !Auth::user()->can('edit client')) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à modifier un client.');
        }

        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Mise à jour des informations client
     */
    public function update(Request $request, Client $client)
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin']) && !Auth::user()->can('edit client')) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à modifier un client.');
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:255',
        ], [
            'nom.required' => 'Le nom du client est obligatoire.',
            'nom.max'      => 'Le nom ne peut pas dépasser 255 caractères.',
            'telephone.max'=> 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
        ]);

        $client->update([
            'nom'       => $validated['nom'],
            'prenom'    => $validated['prenom'],
            'telephone' => $validated['telephone'] ?? null,
            'adresse'   => $validated['adresse'] ?? null,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Suppression d'un client avec vérification d'intégrité
     */
    public function destroy(Client $client)
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin']) && !Auth::user()->can('delete client')) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à supprimer un client.');
        }

        if ($client->ventes()->count() > 0) {
            return redirect()->route('clients.index')->with('error', 'Impossible de supprimer ce client : des ventes lui sont associées.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Recherche AJAX des clients pour autocomplétion
     */
    public function search(Request $request)
    {
        $search = trim($request->get('q', ''));

        if (empty($search)) {
            return response()->json([]);
        }

        $clients = Client::where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('code_client', 'like', "%{$search}%");
            })
            ->limit(25)
            ->get(['id', 'nom', 'prenom', 'telephone', 'code_client']);

        return response()->json($clients);
    }

    /**
     * Export PDF de la liste des clients
     */
    public function exportPdfListClients(Request $request)
    {
        $query = Client::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'like', "%{$q}%")
                    ->orWhere('prenom', 'like', "%{$q}%")
                    ->orWhere('telephone', 'like', "%{$q}%")
                    ->orWhere('code_client', 'like', "%{$q}%");
            });
        }

        $clients = $query->orderBy('nom')->get();

        $pdf = Pdf::loadView('admin.clients.pdf', compact('clients'));
        return $pdf->download('clients_' . now()->format('Ymd_His') . '.pdf');
    }
}
