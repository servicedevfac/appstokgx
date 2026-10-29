<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Services\EntrepriseContext;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ImportController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('importer données'), 403);

        $batches = ImportBatch::with('user')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return view('admin.imports.index', compact('batches'));
    }

    /**
     * Analyse un fichier Excel/CSV et génère un aperçu (RM-061 à RM-063).
     */
    public function preview(Request $request)
    {
        abort_unless(Auth::user()->can('importer données'), 403);

        $validated = $request->validate([
            'type' => 'required|in:produits,categories,clients,fournisseurs,stocks_initiaux',
            'file' => 'required|file|max:10240',
        ]);

        $entrepriseId = EntrepriseContext::currentId();

        try {
            $batch = ImportService::analyzeAndCreatePreview(
                $request->file('file'),
                $validated['type'],
                $entrepriseId,
                Auth::id()
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'file' => $e->getMessage(),
            ]);
        }

        return view('admin.imports.preview', compact('batch'));
    }

    /**
     * Affiche un aperçu existant.
     */
    public function showPreview($id)
    {
        abort_unless(Auth::user()->can('importer données'), 403);

        $batch = ImportBatch::findOrFail($id);
        return view('admin.imports.preview', compact('batch'));
    }

    /**
     * Confirme l'importation des lignes valides après aperçu (RM-063 à RM-066).
     */
    public function confirm(Request $request, $id)
    {
        abort_unless(Auth::user()->can('importer données'), 403);

        $batch = ImportBatch::findOrFail($id);

        if ($batch->statut === 'confirme') {
            return redirect()->route('imports.index')->with('info', 'Ce lot d\'importation a déjà été traité.');
        }

        $batch = ImportService::confirmImport($batch);

        return redirect()->route('imports.index')->with(
            'success',
            "Importation terminée : {$batch->imported_rows} ligne(s) importée(s), {$batch->rejected_rows} ligne(s) rejetée(s)."
        );
    }
}
