<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepenseController extends Controller
{
    private function checkPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && ! $user->can("{$action} depense")) {
            abort(403, 'Accès refusé. Vous n\'avez pas la permission d\'effectuer cette opération sur les dépenses.');
        }
    }

    private function ensureSameEntreprise(Depense $depense): void
    {
        $user = Auth::user();
        $currentId = EntrepriseContext::currentId($user);
        if (! $user->canAccessEntreprise((int) $depense->entreprise_id) || ($currentId && (int) $depense->entreprise_id !== (int) $currentId)) {
            abort(403, 'Accès interdit à la dépense d\'une autre entreprise.');
        }
    }

    public function index(Request $request)
    {
        $this->checkPermission('view');
        $query = Depense::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('beneficiaire', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->input('categorie'));
        }

        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->input('mode_paiement'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        $periode = $request->input('periode', 'mois');
        if ($periode === 'aujourdhui') {
            $query->whereDate('date_depense', Carbon::today());
        } elseif ($periode === 'semaine') {
            $query->whereBetween('date_depense', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($periode === 'mois') {
            $query->whereYear('date_depense', Carbon::now()->year)
                  ->whereMonth('date_depense', Carbon::now()->month);
        } elseif ($periode === 'annee') {
            $query->whereYear('date_depense', Carbon::now()->year);
        } elseif ($periode === 'custom') {
            if ($request->filled('date_debut')) {
                $query->whereDate('date_depense', '>=', $request->input('date_debut'));
            }
            if ($request->filled('date_fin')) {
                $query->whereDate('date_depense', '<=', $request->input('date_fin'));
            }
        }

        // Ne comptabiliser dans les KPIs que les dépenses validées (Section 7.4)
        $totalFiltre = (clone $query)->where('statut', 'validee')->sum('montant');
        $totalMois = Depense::where('statut', 'validee')
            ->whereYear('date_depense', Carbon::now()->year)
            ->whereMonth('date_depense', Carbon::now()->month)
            ->sum('montant');
        $totalJour = Depense::where('statut', 'validee')
            ->whereDate('date_depense', Carbon::today())
            ->sum('montant');

        $repartitionCategories = (clone $query)
            ->where('statut', 'validee')
            ->selectRaw('categorie, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->get();

        $topCategorie = $repartitionCategories->first();

        $depenses = $query->orderBy('date_depense', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15)
                          ->appends($request->all());

        $categories = Depense::categories();
        $modesPaiement = Depense::modesPaiement();

        return view('admin.depenses.index', compact(
            'depenses',
            'totalFiltre',
            'totalMois',
            'totalJour',
            'topCategorie',
            'repartitionCategories',
            'categories',
            'modesPaiement',
            'periode'
        ));
    }

    public function create()
    {
        $this->checkPermission('create');
        $categories = Depense::categoriesList();
        $modesPaiement = Depense::modesPaiement();

        return view('admin.depenses.create', compact('categories', 'modesPaiement'));
    }

    public function store(Request $request)
    {
        $this->checkPermission('create');

        $request->validate([
            'titre'            => 'required|string|max:255',
            'categorie'        => 'required|string',
            'montant'          => 'required|numeric|min:1',
            'date_depense'     => 'required|date',
            'mode_paiement'    => 'required|string',
            'beneficiaire'     => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'statut'           => 'nullable|in:validee,en_attente,annulee',
            'reference_unique' => 'nullable|string|max:100',
            'justificatif'     => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        if ($request->filled('reference_unique')) {
            if (Depense::withoutGlobalScope('entreprise')->where('reference_unique', $request->reference_unique)->exists()) {
                return back()->withInput()->with('error', 'Cette dépense a déjà été enregistrée (référence en doublon).');
            }
        }

        $data = $request->only([
            'titre',
            'categorie',
            'montant',
            'date_depense',
            'mode_paiement',
            'beneficiaire',
            'description',
        ]);

        $data['entreprise_id'] = EntrepriseContext::currentId();
        $data['user_id'] = Auth::id();
        $data['statut'] = $request->input('statut', 'validee');
        $data['reference_unique'] = $request->input('reference_unique');

        if ($request->hasFile('justificatif')) {
            $file = $request->file('justificatif');
            $uploadDir = public_path('uploads/justificatifs_depenses');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $fileName = 'justificatif_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $data['justificatif'] = 'uploads/justificatifs_depenses/' . $fileName;
        }

        $depense = Depense::create($data);

        AuditService::log(
            'depense.creation',
            $depense,
            $depense->id,
            ['titre' => $depense->titre, 'montant' => $depense->montant, 'statut' => $depense->statut],
            $depense->titre,
            (int) $depense->entreprise_id
        );

        return redirect()->route('depenses.index')
                         ->with('success', 'Dépense enregistrée avec succès.');
    }

    public function show($id)
    {
        $this->checkPermission('view');
        $depense = Depense::withoutGlobalScope('entreprise')->with('user')->findOrFail($id);
        $this->ensureSameEntreprise($depense);

        $categories = Depense::categories();

        return view('admin.depenses.show', compact('depense', 'categories'));
    }

    public function edit($id)
    {
        $this->checkPermission('edit');
        $depense = Depense::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($depense);

        $categories = Depense::categoriesList();
        $modesPaiement = Depense::modesPaiement();

        return view('admin.depenses.edit', compact('depense', 'categories', 'modesPaiement'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit');
        $depense = Depense::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($depense);

        $request->validate([
            'titre'         => 'required|string|max:255',
            'categorie'     => 'required|string',
            'montant'       => 'required|numeric|min:1',
            'date_depense'  => 'required|date',
            'mode_paiement' => 'required|string',
            'beneficiaire'  => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'statut'        => 'nullable|in:validee,en_attente,annulee',
            'justificatif'  => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        $data = $request->only([
            'titre',
            'categorie',
            'montant',
            'date_depense',
            'mode_paiement',
            'beneficiaire',
            'description',
        ]);

        if ($request->filled('statut')) {
            $data['statut'] = $request->input('statut');
        }

        if ($request->hasFile('justificatif')) {
            if ($depense->justificatif && File::exists(public_path($depense->justificatif))) {
                File::delete(public_path($depense->justificatif));
            }

            $file = $request->file('justificatif');
            $uploadDir = public_path('uploads/justificatifs_depenses');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $fileName = 'justificatif_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $data['justificatif'] = 'uploads/justificatifs_depenses/' . $fileName;
        }

        $depense->update($data);

        return redirect()->route('depenses.index')
                         ->with('success', 'Dépense mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $this->checkPermission('delete');
        $depense = Depense::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($depense);

        if ($depense->justificatif && File::exists(public_path($depense->justificatif))) {
            File::delete(public_path($depense->justificatif));
        }

        $depense->delete();

        return redirect()->route('depenses.index')
                         ->with('success', 'Dépense supprimée avec succès.');
    }

    public function exportPdf(Request $request)
    {
        $this->checkPermission('exporter');
        $query = Depense::with('user')->where('statut', 'validee');

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->input('categorie'));
        }
        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->input('mode_paiement'));
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->input('date_debut'));
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->input('date_fin'));
        }

        $depenses = $query->orderBy('date_depense', 'desc')->get();
        $total = $depenses->sum('montant');

        $repartition = $depenses->groupBy('categorie')->map(function ($items) {
            return [
                'total' => $items->sum('montant'),
                'count' => $items->count(),
            ];
        });

        $pdf = Pdf::loadView('admin.depenses.pdf', [
            'depenses'       => $depenses,
            'total'          => $total,
            'repartition'    => $repartition,
            'dateGeneration' => Carbon::now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('rapport_depenses_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->checkPermission('exporter');
        $query = Depense::with('user')->where('statut', 'validee');

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->input('categorie'));
        }
        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->input('mode_paiement'));
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->input('date_debut'));
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->input('date_fin'));
        }

        $depenses = $query->orderBy('date_depense', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dépenses');

        $headers = ['Date', 'Titre de la dépense', 'Catégorie', 'Montant (FCFA)', 'Mode de paiement', 'Bénéficiaire', 'Enregistré par'];
        $sheet->fromArray($headers, null, 'A1');

        $rowIndex = 2;
        foreach ($depenses as $d) {
            $sheet->setCellValue("A{$rowIndex}", Carbon::parse($d->date_depense)->format('d/m/Y'));
            $sheet->setCellValue("B{$rowIndex}", $d->titre);
            $sheet->setCellValue("C{$rowIndex}", $d->categorie);
            $sheet->setCellValue("D{$rowIndex}", $d->montant);
            $sheet->setCellValue("E{$rowIndex}", $d->mode_paiement);
            $sheet->setCellValue("F{$rowIndex}", $d->beneficiaire ?? '-');
            $sheet->setCellValue("G{$rowIndex}", $d->user?->nom ?? 'Admin');
            $rowIndex++;
        }

        $sheet->setCellValue("C{$rowIndex}", 'TOTAL DÉPENSES');
        $sheet->setCellValue("D{$rowIndex}", $depenses->sum('montant'));

        $writer = new Xlsx($spreadsheet);
        $fileName = 'rapport_depenses_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
