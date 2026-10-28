<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Vente;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportationEcontroller extends Controller
{
    /**
     * Export Excel de la liste des ventes
     */
    public function exportation(Request $request): StreamedResponse
    {
        $filename = "Liste_des_ventes_" . now()->format('Ymd_His') . ".xlsx";

        $ventes = Vente::with('client')
            ->where('statut', 'valide')
            ->select('client_id', 'created_at', 'code_recu', 'mode_paiement', 'montant_paye', 'reste_a_payer', 'montant_total')
            ->orderByDesc('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Date', 'Code Reçu', 'Nom client', 'Mode de Paiement', 'Montant payé', 'Reste à payer', 'Montant Total'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '007ACC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        $rowIndex = 2;
        foreach ($ventes as $vente) {
            $sheet->setCellValue("A{$rowIndex}", Carbon::parse($vente->created_at)->format('d/m/Y'));
            $sheet->setCellValue("B{$rowIndex}", $vente->code_recu);
            $sheet->setCellValue("C{$rowIndex}", $vente->client?->nom ?? '-');
            $sheet->setCellValue("D{$rowIndex}", ucfirst($vente->mode_paiement));
            $sheet->setCellValue("E{$rowIndex}", $vente->montant_paye);
            $sheet->setCellValue("F{$rowIndex}", $vente->reste_a_payer);
            $sheet->setCellValue("G{$rowIndex}", $vente->montant_total);
            $rowIndex++;
        }

        // Ligne Total
        $sheet->setCellValue("E{$rowIndex}", 'TOTAL');
        $sheet->setCellValue("G{$rowIndex}", $ventes->sum('montant_total'));

        $sheet->getStyle("E{$rowIndex}:G{$rowIndex}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE4D6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $sheet->getStyle("A1:G{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Export Excel des mouvements de stock
     */
    public function exportMouvementStock(Request $request): StreamedResponse
    {
        $filename = "Mouvements_de_stock_" . now()->format('Ymd_His') . ".xlsx";

        $mouvements = MouvementStock::with('produit')
            ->orderByDesc('date_mouvement')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Produit', 'Type de Mouvement', 'Quantité', 'Date', 'Motif'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '02228b']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        $rowIndex = 2;
        foreach ($mouvements as $mouvement) {
            $sheet->setCellValue("A{$rowIndex}", $mouvement->produit->nom ?? '-');
            $sheet->setCellValue("B{$rowIndex}", ucfirst($mouvement->type_mouvement));
            $sheet->setCellValue("C{$rowIndex}", $mouvement->quantite);
            $sheet->setCellValue("D{$rowIndex}", Carbon::parse($mouvement->date_mouvement)->format('d/m/Y'));
            $sheet->setCellValue("E{$rowIndex}", $mouvement->motif);
            $rowIndex++;
        }

        $sheet->getStyle("A1:E" . ($rowIndex - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Export des produits (Excel ou PDF)
     */
    public function exportProducts(string $format = 'excel'): StreamedResponse
    {
        $products = Produit::with('mouvements')->orderBy('nom')->get();
        $headers = ['Nom', 'Prix', 'Stock actuel', 'Seuil alerte'];

        if ($format === 'excel') {
            return $this->generateProductExcel($products, $headers, 'produits_' . now()->format('Ymd_His') . '.xlsx');
        }

        if ($format === 'pdf') {
            return $this->generateProductPDF($products, $headers, 'produits_' . now()->format('Ymd_His') . '.pdf');
        }

        abort(404, 'Format non supporté.');
    }

    /**
     * Génération Excel des produits
     */
    private function generateProductExcel($products, array $headers, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '02228b']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        $rowIndex = 2;
        foreach ($products as $product) {
            $sheet->setCellValue("A{$rowIndex}", $product->nom);
            $sheet->setCellValue("B{$rowIndex}", $product->prix);
            $sheet->setCellValue("C{$rowIndex}", $product->stock_actuel);
            $sheet->setCellValue("D{$rowIndex}", $product->seuil_alerte);

            if ($product->stock_actuel <= $product->seuil_alerte) {
                $sheet->getStyle("C{$rowIndex}:D{$rowIndex}")->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFB6C1']],
                ]);
            }

            $rowIndex++;
        }

        $sheet->getStyle("A1:D" . ($rowIndex - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Génération PDF des produits
     */
    private function generateProductPDF($products, array $headers, string $filename): StreamedResponse
    {
        $pdf = app()->make('dompdf.wrapper');

        $html = '<style>
            table { width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 13px; }
            th { background-color: #02228b; color: white; padding: 8px; text-align: left; }
            td { border: 1px solid #ddd; padding: 8px; }
            .low-stock { background-color: #FFB6C1; font-weight: bold; }
        </style>';
        $html .= '<p style="font-size: 11px; color: #666;">Édité le : ' . now()->format('d/m/Y H:i') . '</p>';
        $html .= '<h2>Liste des Produits</h2>';
        $html .= '<table><tr>';

        foreach ($headers as $header) {
            $html .= "<th>{$header}</th>";
        }
        $html .= '</tr>';

        foreach ($products as $product) {
            $lowStockClass = $product->stock_actuel <= $product->seuil_alerte ? 'class="low-stock"' : '';

            $html .= '<tr>';
            $html .= "<td>{$product->nom}</td>";
            $html .= "<td>" . number_format($product->prix, 0, ',', ' ') . " FCFA</td>";
            $html .= "<td {$lowStockClass}>{$product->stock_actuel}</td>";
            $html .= "<td>{$product->seuil_alerte}</td>";
            $html .= '</tr>';
        }

        $html .= '</table>';

        $pdf->loadHTML($html);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $filename);
    }

    /**
     * Export Excel des clients
     */
    public function exportClients(Request $request): StreamedResponse
    {
        $filename = "Liste_des_clients_" . now()->format('Ymd_His') . ".xlsx";

        $clients = Client::select('nom', 'prenom', 'telephone', 'code_client', 'adresse')
            ->orderBy('nom')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Code Client', 'Nom', 'Prénom', 'Téléphone', 'Adresse'];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '02228b']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        $rowIndex = 2;
        foreach ($clients as $client) {
            $sheet->setCellValue("A{$rowIndex}", $client->code_client);
            $sheet->setCellValue("B{$rowIndex}", $client->nom);
            $sheet->setCellValue("C{$rowIndex}", $client->prenom);
            $sheet->setCellValue("D{$rowIndex}", $client->telephone ?? '-');
            $sheet->setCellValue("E{$rowIndex}", $client->adresse ?? '-');
            $rowIndex++;
        }

        $sheet->getStyle("A1:E" . ($rowIndex - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
