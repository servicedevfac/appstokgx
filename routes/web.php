<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportationEcontroller;
use App\Http\Controllers\HoraireController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\paiementController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserContoller; // ✅ Correction du nom
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\VenteController;
use Illuminate\Support\Facades\Route;
use App\Notifications\StockAlerte;
use Illuminate\Support\Facades\Notification;

// Page de connexion par défaut
Route::get('/', fn() => view('auth.login'))->middleware('guest');
// Groupe pour utilisateurs authentifiés
Route::middleware('auth')->group(function () {
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    // Profil utilisateur
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/ventes/{id}/ticket', [VenteController::class, 'imprimerTicket'])->name('ventes.ticket');
    Route::get('/paiements/{id}/ticket', [paiementController::class, 'ticketpaiement'])->name('paiements.ticket');


    // Tableau de bord
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clients (hors suppression)
    Route::get('/clients/export-pdf', [ClientController::class, 'exportPdfListClients'])->name('clients.exportclien.pdf');
    Route::resource('clients', ClientController::class)->except('destroy');

    // Produits (uniquement index)
    Route::get('produits', [ProduitController::class, 'index'])->name('produits.index');
    Route::post('/ventes/{vente}/payer', [VenteController::class, 'payer'])->name('ventes.payer');


    // Mouvements de stock (uniquement index)
    Route::get('mouvementStocks', [MouvementStockController::class, 'index'])->name('mouvementStocks.index');
    Route::get('/user-logins', [UserLoginController::class, 'index'])->name('user-logins.index');
    Route::get('/user-logins/{user}', [UserLoginController::class, 'show'])->name('user-logins.show');
    // Exportation Excel
    Route::get('/simple-exel/expot', [ExportationEcontroller::class, 'exportation'])->name('export');
    Route::get('/mouvementStocks/export-excel', [ExportationEcontroller::class, 'exportMouvementStock'])->name('mouvementStocks.export-excel');
    Route::get('/ventes-export-pdf', [VenteController::class, 'exportPDF'])->name('ventes.export.pdf');
    Route::get('/ventes/export/pdf', [VenteController::class, 'exportPdflist'])->name('ventes.exportlist.pdf');
    Route::get('/export/products/{format?}', [ExportationEcontroller::class, 'exportProducts'])
        ->name('export.products')
        ->where('format', 'excel|pdf');

    // Ventes (affichage + création)
    Route::get('/ventes', [VenteController::class, 'index'])->name('ventes.index');
    Route::get('/ventes-filtrees', [VenteController::class, 'ventesFiltrees'])->name('ventes.filtrees');
    Route::get('/ventes-filtrees1', [VenteController::class, 'ventesFiltrees1'])->name('ventes.filtrees1');
    Route::get('/ventes/create', [VenteController::class, 'create'])->name('ventes.create')->middleware('verifier.heure.vente');
    Route::post('/ventes', [VenteController::class, 'store'])->name('ventes.store')->middleware('verifier.heure.vente');
    Route::get('/ventes/{vente}', [VenteController::class, 'show'])->name('ventes.show');

    // Gestion des horaires
    Route::delete('/ventes/{vente}', [VenteController::class, 'destroy'])->name('ventes.destroy');
    Route::post('/ventes/{vente}/annuler', [VenteController::class, 'annulerVente'])->name('ventes.annuler');
    // Suppression des clients
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    // Produits (sauf index)
    Route::resource('produits', ProduitController::class)->except('index');
    // Mouvements de stock (hors index)
    Route::resource('mouvementStocks', MouvementStockController::class)->only('create', 'store', 'update', 'destroy');
    Route::get('/mouvementStocks/{mouvementStock}/edit', [MouvementStockController::class, 'edit'])->name('mouvementStocks.edit');
    // Gestion des horaires
    Route::get('/horaires', [HoraireController::class, 'index'])->name('admin.horaires.index');
    Route::get('/horaires/edit', [HoraireController::class, 'edit'])->name('admin.horaires.edit');
    Route::post('/horaires', [HoraireController::class, 'update'])->name('admin.horaires.update');
    Route::resource('users', UserContoller::class);
    Route::post('/users/{user}/toggle', [UserContoller::class, 'toggle'])->name('users.toggle');
    route::post('/ventes/{vente}/paiement', [paiementController::class, 'store'])->name('paiements.store');
    Route::get('/admin/horaires/historique', [HoraireController::class, 'historique'])->name('admin.horaires.historique');

    // Gestion des dépenses
    Route::get('/depenses/export-excel', [DepenseController::class, 'exportExcel'])->name('depenses.export-excel');
    Route::get('/depenses/export-pdf', [DepenseController::class, 'exportPdf'])->name('depenses.export-pdf');
    Route::resource('depenses', DepenseController::class);
});
// Groupe pour les super administrateurs
Route::middleware(['web', 'verified', 'auth', 'is.superAdmin'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

});

Route::get('/test-mail-attouco', function () {


    $produits = [
        [
            'nom' => 'Riz 25kg',
            'stock' => 2,
            'seuil' => 10,
        ],
        [
            'nom' => 'Huile 5L',
            'stock' => 1,
            'seuil' => 5,
        ],
    ];


    // ✅ ENVOI DIRECT DE LA NOTIFICATION (CORRECT)
    Notification::route('mail', 'djuekouassi@attouco.com')
        ->notifyNow(new StockAlerte($produits));


    return '✅ Mail envoyé directement à djuekouassi@attouco.com';
});



require __DIR__ . '/auth.php';
