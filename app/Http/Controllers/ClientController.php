<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\EntrepriseContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    private function checkPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && ! $user->can("{$action} client")) {
            abort(403, 'Accès refusé. Vous n\'avez pas la permission d\'effectuer cette action sur les clients.');
        }
    }

    private function ensureSameEntreprise(Client $client): void
    {
        $user = Auth::user();
        $currentId = EntrepriseContext::currentId($user);
        if (! $user->canAccessEntreprise((int) $client->entreprise_id) || ($currentId && (int) $client->entreprise_id !== (int) $currentId)) {
            abort(403, 'Accès interdit au client d\'une autre entreprise.');
        }
    }

    public function index(Request $request)
    {
        $this->checkPermission('view');
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

    public function create()
    {
        $this->checkPermission('create');
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $this->checkPermission('create');

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'adresse'   => 'nullable|string|max:255',
        ], [
            'nom.required'  => 'Le nom du client est obligatoire.',
            'nom.max'       => 'Le nom ne peut pas dépasser 255 caractères.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
        ]);

        $entrepriseId = EntrepriseContext::currentId();

        DB::transaction(function () use ($validated, $entrepriseId) {
            $now = now();
            $prefix = 'CLT-' . $now->format('my') . '-';

            $lastClient = Client::withoutGlobalScope('entreprise')
                ->where('entreprise_id', $entrepriseId)
                ->where('code_client', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('code_client')
                ->first();

            $nextNumber = 1;
            if ($lastClient) {
                $lastSuffix = (int) Str::afterLast($lastClient->code_client, '-');
                $nextNumber = $lastSuffix + 1;
            }

            $codeClient = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            Client::create([
                'entreprise_id' => $entrepriseId,
                'code_client'   => $codeClient,
                'nom'           => $validated['nom'],
                'prenom'        => $validated['prenom'] ?? null,
                'telephone'     => $validated['telephone'] ?? null,
                'email'         => $validated['email'] ?? null,
                'adresse'       => $validated['adresse'] ?? null,
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }

    public function show($id)
    {
        $this->checkPermission('view');
        $client = Client::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($client);

        $ventes = $client->ventes()->with('user')->orderByDesc('date_vente')->paginate(10);

        return view('admin.clients.show', compact('client', 'ventes'));
    }

    public function edit($id)
    {
        $this->checkPermission('edit');
        $client = Client::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($client);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit');
        $client = Client::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($client);

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'adresse'   => 'nullable|string|max:255',
        ], [
            'nom.required'  => 'Le nom du client est obligatoire.',
            'nom.max'       => 'Le nom ne peut pas dépasser 255 caractères.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
        ]);

        $client->update([
            'nom'       => $validated['nom'],
            'prenom'    => $validated['prenom'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'email'     => $validated['email'] ?? $client->email,
            'adresse'   => $validated['adresse'] ?? null,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $this->checkPermission('delete');
        $client = Client::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($client);

        if ($client->ventes()->withoutGlobalScope('entreprise')->count() > 0) {
            return redirect()->route('clients.index')->with('error', 'Impossible de supprimer ce client : des ventes lui sont associées.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès.');
    }

    public function search(Request $request)
    {
        $this->checkPermission('view');
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

    public function exportPdfListClients(Request $request)
    {
        $this->checkPermission('exporter');
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
