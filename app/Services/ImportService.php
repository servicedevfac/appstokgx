<?php

namespace App\Services;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\ImportBatch;
use App\Models\MouvementStock;
use App\Models\Produit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use InvalidArgumentException;

class ImportService
{
    public const SUPPORTED_TYPES = [
        'produits',
        'categories',
        'clients',
        'fournisseurs',
        'stocks_initiaux',
    ];

    public const REQUIRED_COLUMNS = [
        'categories'      => ['nom'],
        'produits'        => ['nom', 'prix'],
        'clients'         => ['nom'],
        'fournisseurs'    => ['nom'],
        'stocks_initiaux' => ['produit', 'quantite'],
    ];

    /**
     * Étapes 1 à 7 : Analyse, validation, détection des doublons et création de l'aperçu
     */
    public static function analyzeAndCreatePreview(
        UploadedFile $file,
        string $typeImport,
        int $entrepriseId,
        int $userId
    ): ImportBatch {
        if (! in_array($typeImport, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException("Type d'importation non supporté : {$typeImport}");
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt', 'xlsx', 'xls'], true)) {
            throw new InvalidArgumentException('Format de fichier invalide. Seuls les fichiers Excel (.xlsx, .xls) et CSV (.csv) sont acceptés.');
        }

        $rows = self::readRows($file, $ext);
        if (count($rows) < 2) {
            throw new InvalidArgumentException('Le fichier est vide ou ne contient aucune ligne de données après l\'en-tête.');
        }

        $rawHeaders = array_map(fn ($h) => self::normalizeHeader((string) $h), $rows[0]);
        $requiredCols = self::REQUIRED_COLUMNS[$typeImport];

        // Synonymes d'en-têtes
        $headerAliases = [
            'prix_vente'   => 'prix',
            'nom_produit'  => 'produit',
            'code_produit' => 'reference',
            'sku'          => 'reference',
            'stock_initial'=> 'quantite',
            'stock'        => 'quantite',
        ];
        $headers = array_map(fn ($h) => $headerAliases[$h] ?? $h, $rawHeaders);

        $missingCols = array_diff($requiredCols, $headers);
        if (! empty($missingCols)) {
            throw new InvalidArgumentException(
                'Colonnes obligatoires manquantes pour ' . $typeImport . ' : ' . implode(', ', $missingCols)
            );
        }

        $validLines = [];
        $rejectedLines = [];
        $ignoredLines = [];
        $seenKeysInFile = [];

        for ($i = 1, $total = count($rows); $i < $total; $i++) {
            $lineNumber = $i + 1;
            $rowValues = $rows[$i];

            // Ignorer les lignes totalement vides
            if (empty(array_filter($rowValues, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $colIndex => $colName) {
                if ($colName !== '') {
                    $assoc[$colName] = isset($rowValues[$colIndex]) ? trim((string) $rowValues[$colIndex]) : '';
                }
            }

            $validation = self::validateRow($typeImport, $assoc, $lineNumber, $entrepriseId, $seenKeysInFile);

            if ($validation['status'] === 'valid') {
                $seenKeysInFile[$validation['unique_key']] = true;
                $validLines[] = [
                    'ligne' => $lineNumber,
                    'data'  => $validation['data'],
                ];
            } elseif ($validation['status'] === 'duplicate') {
                $ignoredLines[] = [
                    'ligne'  => $lineNumber,
                    'raison' => $validation['reason'],
                    'data'   => $assoc,
                ];
            } else {
                $rejectedLines[] = [
                    'ligne'  => $lineNumber,
                    'raison' => $validation['reason'],
                    'data'   => $assoc,
                ];
            }
        }

        return ImportBatch::create([
            'entreprise_id'   => $entrepriseId,
            'user_id'         => $userId,
            'type_import'     => $typeImport,
            'nom_fichier'     => $file->getClientOriginalName(),
            'statut'          => 'apercu',
            'lignes_valides'  => $validLines,
            'lignes_rejetees' => $rejectedLines,
            'lignes_ignorees' => $ignoredLines,
            'resume'          => [
                'total_analyse' => count($validLines) + count($rejectedLines) + count($ignoredLines),
                'valides'       => count($validLines),
                'rejetees'      => count($rejectedLines),
                'ignorees'      => count($ignoredLines),
                'importees'     => 0,
            ],
        ]);
    }

    /**
     * Étapes 8 à 10 : Confirmation transactionnelle et rapport final
     */
    public static function confirmImport(ImportBatch $batch): ImportBatch
    {
        if ($batch->statut === 'confirme') {
            return $batch;
        }

        $entrepriseId = (int) $batch->entreprise_id;
        $userId = (int) ($batch->user_id ?: Auth::id());
        $validLines = $batch->lignes_valides ?? [];

        DB::transaction(function () use ($batch, $entrepriseId, $userId, $validLines) {
            $importedCount = 0;

            foreach ($validLines as $item) {
                $data = $item['data'];
                $lineNumber = $item['ligne'] ?? 0;

                switch ($batch->type_import) {
                    case 'categories':
                        Categorie::withoutGlobalScope('entreprise')->firstOrCreate(
                            ['entreprise_id' => $entrepriseId, 'nom' => $data['nom']],
                            ['description' => $data['description'] ?? null]
                        );
                        $importedCount++;
                        break;

                    case 'produits':
                        $categorieId = null;
                        if (! empty($data['categorie'])) {
                            $cat = Categorie::withoutGlobalScope('entreprise')->firstOrCreate(
                                ['entreprise_id' => $entrepriseId, 'nom' => $data['categorie']]
                            );
                            $categorieId = $cat->id;
                        }

                        $produit = Produit::withoutGlobalScope('entreprise')->create([
                            'entreprise_id'  => $entrepriseId,
                            'categorie_id'   => $categorieId,
                            'nom'            => $data['nom'],
                            'reference'      => $data['reference'] ?: null,
                            'prix_achat'     => (float) ($data['prix_achat'] ?? 0),
                            'prix'           => (float) $data['prix'],
                            'seuil_alerte'   => (int) ($data['seuil_alerte'] ?? 0),
                            'alerte_envoyee' => false,
                        ]);

                        // Si un stock initial est fourni lors de l'import produit, créer un mouvement d'ouverture traçable (RM-066)
                        $stockInitial = (int) ($data['stock_initial'] ?? 0);
                        if ($stockInitial > 0) {
                            MouvementStock::withoutGlobalScope('entreprise')->create([
                                'entreprise_id'     => $entrepriseId,
                                'produit_id'        => $produit->id,
                                'user_id'           => $userId,
                                'quantite'          => $stockInitial,
                                'type_mouvement'    => 'entree',
                                'date_mouvement'    => now()->format('Y-m-d'),
                                'motif'             => "Stock initial d'ouverture (Import #{$batch->id}, ligne {$lineNumber})",
                                'reference_externe' => "IMP-PROD-{$batch->id}-L{$lineNumber}",
                            ]);
                        }
                        $importedCount++;
                        break;

                    case 'clients':
                        $codeClient = ! empty($data['code_client'])
                            ? $data['code_client']
                            : self::generateUniqueClientCode($entrepriseId);

                        Client::withoutGlobalScope('entreprise')->create([
                            'entreprise_id' => $entrepriseId,
                            'code_client'   => $codeClient,
                            'nom'           => $data['nom'],
                            'prenom'        => $data['prenom'] ?: null,
                            'telephone'     => $data['telephone'] ?: null,
                            'email'         => $data['email'] ?: null,
                            'adresse'       => $data['adresse'] ?: null,
                        ]);
                        $importedCount++;
                        break;

                    case 'fournisseurs':
                        $codeFournisseur = ! empty($data['code_fournisseur'])
                            ? $data['code_fournisseur']
                            : self::generateUniqueFournisseurCode($entrepriseId);

                        Fournisseur::withoutGlobalScope('entreprise')->create([
                            'entreprise_id'    => $entrepriseId,
                            'code_fournisseur' => $codeFournisseur,
                            'nom'              => $data['nom'],
                            'contact'          => $data['contact'] ?: null,
                            'telephone'        => $data['telephone'] ?: null,
                            'email'            => $data['email'] ?: null,
                            'adresse'          => $data['adresse'] ?: null,
                        ]);
                        $importedCount++;
                        break;

                    case 'stocks_initiaux':
                        $produit = Produit::withoutGlobalScope('entreprise')
                            ->where('entreprise_id', $entrepriseId)
                            ->where(function ($q) use ($data) {
                                $q->where('nom', $data['produit'])
                                  ->orWhere('reference', $data['produit']);
                            })
                            ->lockForUpdate()
                            ->firstOrFail();

                        MouvementStock::withoutGlobalScope('entreprise')->create([
                            'entreprise_id'     => $entrepriseId,
                            'produit_id'        => $produit->id,
                            'user_id'           => $userId,
                            'quantite'          => (int) $data['quantite'],
                            'type_mouvement'    => 'entree',
                            'date_mouvement'    => $data['date_mouvement'] ?: now()->format('Y-m-d'),
                            'motif'             => "Stock initial d'ouverture (Import #{$batch->id}, ligne {$lineNumber})",
                            'reference_externe' => "IMP-INIT-{$batch->id}-L{$lineNumber}",
                        ]);
                        $importedCount++;
                        break;
                }
            }

            $resume = $batch->resume ?? [];
            $resume['importees'] = $importedCount;

            $batch->update([
                'statut' => 'confirme',
                'resume' => $resume,
            ]);

            AuditService::log(
                'import.donnees',
                $batch,
                $batch->id,
                [
                    'type_import' => $batch->type_import,
                    'nom_fichier' => $batch->nom_fichier,
                    'resume'      => $resume,
                ],
                "Importation de données ({$batch->type_import})",
                $entrepriseId,
                $userId
            );
        });

        return $batch->fresh();
    }

    private static function validateRow(
        string $type,
        array $row,
        int $lineNumber,
        int $entrepriseId,
        array $seenKeysInFile
    ): array {
        switch ($type) {
            case 'categories':
                $nom = trim($row['nom'] ?? '');
                if ($nom === '') {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le nom de la catégorie est obligatoire."];
                }
                $key = mb_strtolower($nom);
                if (isset($seenKeysInFile[$key])) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Doublon dans le fichier pour la catégorie '{$nom}'."];
                }
                if (Categorie::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->where('nom', $nom)->exists()) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : La catégorie '{$nom}' existe déjà dans l'entreprise."];
                }
                return [
                    'status'     => 'valid',
                    'unique_key' => $key,
                    'data'       => [
                        'nom'         => $nom,
                        'description' => $row['description'] ?? null,
                    ],
                ];

            case 'produits':
                $nom = trim($row['nom'] ?? '');
                $prixRaw = $row['prix'] ?? '';
                if ($nom === '') {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le nom du produit est obligatoire."];
                }
                if ($prixRaw === '' || ! is_numeric($prixRaw) || (float) $prixRaw < 0) {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le prix de vente ('{$prixRaw}') est invalide ou négatif."];
                }
                $prixAchat = $row['prix_achat'] ?? '0';
                if ($prixAchat !== '' && (! is_numeric($prixAchat) || (float) $prixAchat < 0)) {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le prix d'achat ('{$prixAchat}') est invalide."];
                }
                $seuil = $row['seuil_alerte'] ?? '0';
                if ($seuil !== '' && (! is_numeric($seuil) || (int) $seuil < 0)) {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le seuil d'alerte ('{$seuil}') est invalide."];
                }
                $stockInit = $row['quantite'] ?? ($row['stock_initial'] ?? '0');
                if ($stockInit !== '' && (! is_numeric($stockInit) || (int) $stockInit < 0)) {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le stock initial ('{$stockInit}') est invalide."];
                }

                $reference = trim($row['reference'] ?? '');
                $key = mb_strtolower($nom);
                if (isset($seenKeysInFile[$key]) || ($reference !== '' && isset($seenKeysInFile['ref:' . mb_strtolower($reference)]))) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Doublon dans le fichier pour le produit '{$nom}'."];
                }

                $existsInDb = Produit::withoutGlobalScope('entreprise')
                    ->where('entreprise_id', $entrepriseId)
                    ->where(function ($q) use ($nom, $reference) {
                        $q->where('nom', $nom);
                        if ($reference !== '') {
                            $q->orWhere('reference', $reference);
                        }
                    })
                    ->exists();

                if ($existsInDb) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Le produit '{$nom}' (ou sa référence) existe déjà dans l'entreprise."];
                }

                return [
                    'status'     => 'valid',
                    'unique_key' => $key,
                    'data'       => [
                        'nom'           => $nom,
                        'reference'     => $reference ?: null,
                        'categorie'     => trim($row['categorie'] ?? ''),
                        'prix_achat'    => (float) ($prixAchat ?: 0),
                        'prix'          => (float) $prixRaw,
                        'seuil_alerte'  => (int) ($seuil ?: 0),
                        'stock_initial' => (int) ($stockInit ?: 0),
                    ],
                ];

            case 'clients':
                $nom = trim($row['nom'] ?? '');
                if ($nom === '') {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le nom du client est obligatoire."];
                }
                $codeClient = trim($row['code_client'] ?? '');
                $telephone = trim($row['telephone'] ?? '');
                $key = $codeClient !== '' ? 'code:' . mb_strtolower($codeClient) : mb_strtolower($nom . '|' . $telephone);

                if (isset($seenKeysInFile[$key])) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Doublon dans le fichier pour le client '{$nom}'."];
                }

                if ($codeClient !== '' && Client::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->where('code_client', $codeClient)->exists()) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Le code client '{$codeClient}' existe déjà."];
                }

                return [
                    'status'     => 'valid',
                    'unique_key' => $key,
                    'data'       => [
                        'code_client' => $codeClient ?: null,
                        'nom'         => $nom,
                        'prenom'      => trim($row['prenom'] ?? ''),
                        'telephone'   => $telephone ?: null,
                        'email'       => trim($row['email'] ?? ''),
                        'adresse'     => trim($row['adresse'] ?? ''),
                    ],
                ];

            case 'fournisseurs':
                $nom = trim($row['nom'] ?? '');
                if ($nom === '') {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le nom du fournisseur est obligatoire."];
                }
                $codeFournisseur = trim($row['code_fournisseur'] ?? '');
                $key = $codeFournisseur !== '' ? 'code:' . mb_strtolower($codeFournisseur) : mb_strtolower($nom);

                if (isset($seenKeysInFile[$key])) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Doublon dans le fichier pour le fournisseur '{$nom}'."];
                }

                if (Fournisseur::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->where('nom', $nom)->exists()) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Le fournisseur '{$nom}' existe déjà."];
                }

                return [
                    'status'     => 'valid',
                    'unique_key' => $key,
                    'data'       => [
                        'code_fournisseur' => $codeFournisseur ?: null,
                        'nom'              => $nom,
                        'contact'          => trim($row['contact'] ?? ''),
                        'telephone'        => trim($row['telephone'] ?? ''),
                        'email'            => trim($row['email'] ?? ''),
                        'adresse'          => trim($row['adresse'] ?? ''),
                    ],
                ];

            case 'stocks_initiaux':
                $produitIdent = trim($row['produit'] ?? '');
                $quantiteRaw = $row['quantite'] ?? '';
                if ($produitIdent === '') {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Le nom ou la référence du produit est obligatoire."];
                }
                if ($quantiteRaw === '' || ! is_numeric($quantiteRaw) || (int) $quantiteRaw <= 0) {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : La quantité de stock initial ('{$quantiteRaw}') doit être un entier strictement positif."];
                }

                $produit = Produit::withoutGlobalScope('entreprise')
                    ->where('entreprise_id', $entrepriseId)
                    ->where(function ($q) use ($produitIdent) {
                        $q->where('nom', $produitIdent)
                          ->orWhere('reference', $produitIdent);
                    })
                    ->first();

                if (! $produit) {
                    return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Produit introuvable ('{$produitIdent}') dans l'entreprise active."];
                }

                $key = 'prod:' . $produit->id;
                if (isset($seenKeysInFile[$key])) {
                    return ['status' => 'duplicate', 'reason' => "Ligne {$lineNumber} : Doublon de stock initial pour le produit '{$produit->nom}'."];
                }

                return [
                    'status'     => 'valid',
                    'unique_key' => $key,
                    'data'       => [
                        'produit'        => $produit->nom,
                        'produit_id'     => $produit->id,
                        'quantite'       => (int) $quantiteRaw,
                        'date_mouvement' => ! empty($row['date']) ? $row['date'] : now()->format('Y-m-d'),
                    ],
                ];
        }

        return ['status' => 'rejected', 'reason' => "Ligne {$lineNumber} : Type inconnu."];
    }

    private static function readRows(UploadedFile $file, string $ext): array
    {
        if (in_array($ext, ['csv', 'txt'], true)) {
            $lines = [];
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return [];
            }

            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = (substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',')) ? ';' : ',';

            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $data[0]);
                }
                $lines[] = $data;
            }
            fclose($handle);
            return $lines;
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    private static function normalizeHeader(string $header): string
    {
        $h = Str::ascii(mb_strtolower(trim($header)));
        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
        return trim((string) $h, '_');
    }

    private static function generateUniqueClientCode(int $entrepriseId): string
    {
        $prefix = 'CLT-' . now()->format('my') . '-';
        $count = Client::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->count() + 1;
        do {
            $code = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (Client::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->where('code_client', $code)->exists());

        return $code;
    }

    private static function generateUniqueFournisseurCode(int $entrepriseId): string
    {
        $prefix = 'FRN-' . now()->format('my') . '-';
        $count = Fournisseur::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->count() + 1;
        do {
            $code = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (Fournisseur::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->where('code_fournisseur', $code)->exists());

        return $code;
    }
}

