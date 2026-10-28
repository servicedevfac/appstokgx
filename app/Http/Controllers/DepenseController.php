<?php

namespace App\Http\Controllers;

use App\Models\Depense;
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
    /**
     * Liste des dépenses avec filtres et KPIs
     */
    public function index(Request $request)
    {
        $query = Depense::with('user');

        // 1. Filtre par recherche textuelle
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('beneficiaire', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Filtre par catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->input('categorie'));
        }

        // 3. Filtre par mode de paiement
        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->input('mode_paiement'));
        }

        // 4. Filtre par période
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

        // Calcul des totaux KPIs
        $totalFiltre = (clone $query)->sum('montant');
        $totalMois = Depense::whereYear('date_depense', Carbon::now()->year)
                            ->whereMonth('date_depense', Carbon::now()->month)
                            ->sum('montant');
        $totalJour = Depense::whereDate('date_depense', Carbon::today())->sum('montant');

        // Répartition par catégorie sur la sélection filtrée
        $repartitionCategories = (clone $query)
            ->selectRaw('categorie, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->get();

        $topCategorie = $repartitionCategories->first();

        // Pagination
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

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        $categories = Depense::categoriesList();
        $modesPaiement = Depense::modesPaiement();

        return view('admin.depenses.create', compact('categories', 'modesPaiement'));
    }

    /**
     * Enregistrement d'une dépense
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'categorie' => 'required|string',
            'montant' => 'required|numeric|min:1',
            'date_depense' => 'required|date',
            'mode_paiement' => 'required|string',
            'beneficiaire' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'justificatif' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120', // Max 5MB
        ], [
            'titre.required'         => 'Le libellé de la dépense est obligatoire.',
            'titre.max'              => 'Le libellé ne peut pas dépasser 255 caractères.',
            'categorie.required'     => 'Veuillez sélectionner une catégorie.',
            'montant.required'       => 'Le montant de la dépense est obligatoire.',
            'montant.numeric'        => 'Le montant doit être une valeur numérique.',
            'montant.min'            => 'Le montant doit être d\'au moins 1 FCFA.',
            'date_depense.required'  => 'La date de la dépense est obligatoire.',
            'date_depense.date'      => 'La date de la dépense n\'est pas valide.',
            'mode_paiement.required' => 'Veuillez sélectionner un mode de paiement.',
            'justificatif.file'      => 'Le justificatif doit être un fichier valide.',
            'justificatif.mimes'     => 'Le justificatif doit être au format JPG, PNG, WEBP ou PDF.',
            'justificatif.max'       => 'La taille du justificatif ne doit pas dépasser 5 Mo.',
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

        $data['user_id'] = Auth::id();

        // Traitement du justificatif uploadé
        if ($request->hasFile('justificatif')) {
            $file = $request->file('justificatif');
            $uploadDir = public_path('uploads/justificatifs_depenses');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $fileName = 'justificatif_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $data['justificatif'] = 'uploads/justificatifs_depenses/' . $fileName;
        }

        Depense::create($data);

        return redirect()->route('depenses.index')
                         ->with('success', 'Dépense enregistrée avec succès.');
    }

    /**
     * Fiche d'une dépense
     */
    public function show(Depense $depense)
    {
        $depense->load('user');
        $categories = Depense::categories();

        return view('admin.depenses.show', compact('depense', 'categories'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Depense $depense)
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin']) && !Auth::user()->can('edit depense')) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à modifier une dépense.');
        }

        $categories = Depense::categoriesList();
        $modesPaiement = Depense::modesPaiement();

        return view('admin.depenses.edit', compact('depense', 'categories', 'modesPaiement'));
    }

    /**
     * Mise à jour d'une dépense
     */
    public function update(Request $request, Depense $depense)
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin']) && !Auth::user()->can('edit depense')) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à modifier une dépense.');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'categorie' => 'required|string',
            'montant' => 'required|numeric|min:1',
            'date_depense' => 'required|date',
            'mode_paiement' => 'required|string',
            'beneficiaire' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'justificatif' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ], [
            'titre.required'         => 'Le libellé de la dépense est obligatoire.',
            'titre.max'              => 'Le libellé ne peut pas dépasser 255 caractères.',
            'categorie.required'     => 'Veuillez sélectionner une catégorie.',
            'montant.required'       => 'Le montant de la dépense est obligatoire.',
            'montant.numeric'        => 'Le montant doit être une valeur numérique.',
            'montant.min'            => 'Le montant doit être d\'au moins 1 FCFA.',
            'date_depense.required'  => 'La date de la dépense est obligatoire.',
            'date_depense.date'      => 'La date de la dépense n\'est pas valide.',
            'mode_paiement.required' => 'Veuillez sélectionner un mode de paiement.',
            'justificatif.file'      => 'Le justificatif doit être un fichier valide.',
            'justificatif.mimes'     => 'Le justificatif doit être au format JPG, PNG, WEBP ou PDF.',
            'justificatif.max'       => 'La taille du justificatif ne doit pas dépasser 5 Mo.',
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

        // Remplacement du justificatif
        if ($request->hasFile('justificatif')) {
            // Suppression de l'ancien fichier
            if ($depense->justificatif && File::exists(public_path($depense->justificatif))) {
                File::delete(public_path($depense->justificatif));
            }

            $file = $request->file('justificatif');
            $uploadDir = public_path('uploads/justificatifs_depenses');
            if (!File::isDirectory($uploadDir)) {
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

    /**
     * Suppression d'une dépense
     */
    public function destroy(Depense $depense)
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin']) && !Auth::user()->can('delete depense')) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à supprimer une dépense.');
        }

        if ($depense->justificatif && File::exists(public_path($depense->justificatif))) {
            File::delete(public_path($depense->justificatif));
        }

        $depense->delete();

        return redirect()->route('depenses.index')
                         ->with('success', 'Dépense supprimée avec succès.');
    }

    /**
     * Export PDF des dépenses
     */
    public function exportPdf(Request $request)
    {
        $query = Depense::with('user');

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
            'depenses' => $depenses,
            'total' => $total,
            'repartition' => $repartition,
            'dateGeneration' => Carbon::now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('rapport_depenses_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export Excel des dépenses
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $query = Depense::with('user');

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

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A237E']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

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

        // Ligne Total
        $sheet->setCellValue("C{$rowIndex}", 'TOTAL DÉPENSES');
        $sheet->setCellValue("D{$rowIndex}", $depenses->sum('montant'));
        $sheet->getStyle("C{$rowIndex}:D{$rowIndex}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EEEEEE']],
        ]);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'rapport_depenses_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
