<?php

use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AvanceSalaireController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\ExportationEcontroller;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\HoraireController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\paiementController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserContoller;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\VenteController;
use Illuminate\Support\Facades\Route;

// Landing Page SaaS publique GESTOCK / STOKGX
Route::get('/', fn() => view('welcome_saas', [
    'plans' => \App\Models\Abonnement::PLANS,
]))->name('landing');

// Accès direct au mode démonstration isolé (RM-075 à RM-077)
Route::post('/demo/login', [DemoController::class, 'loginDemo'])->name('demo.login');
Route::get('/demo/login', [DemoController::class, 'loginDemo'])->name('demo.login.get');

// Groupe pour utilisateurs authentifiés
Route::middleware('auth')->group(function () {
    // Réinitialisation du mode démonstration
    Route::post('/demo/reset', [DemoController::class, 'resetDemo'])->name('demo.reset');

    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tableau de bord et vue consolidée propriétaire
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard-consolide', [DashboardController::class, 'consolide'])->name('dashboard.consolide');

    // Multi-entreprises (Propriétaire / Changement d'entreprise active) & Mon Abonnement
    Route::get('/entreprises', [EntrepriseController::class, 'index'])->name('entreprises.index');
    Route::post('/entreprises', [EntrepriseController::class, 'store'])->name('entreprises.store');
    Route::put('/entreprises/{entreprise}', [EntrepriseController::class, 'update'])->name('entreprises.update');
    Route::delete('/entreprises/{entreprise}', [EntrepriseController::class, 'destroy'])->name('entreprises.destroy');
    Route::post('/entreprises/{entreprise}/switch', [EntrepriseController::class, 'switchEntreprise'])->name('entreprises.switch');
    Route::post('/entreprises/{entreprise}/assign-role', [EntrepriseController::class, 'assignUserRole'])->name('entreprises.assign-role');
    Route::get('/mon-abonnement', [AbonnementController::class, 'index'])->name('abonnement.index');

    // Clients
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::get('/clients/export-pdf', [ClientController::class, 'exportPdfListClients'])->name('clients.exportclien.pdf');
    Route::get('/clients/export-excel', [ExportationEcontroller::class, 'exportClients'])->name('clients.export-excel');
    Route::resource('clients', ClientController::class)->except('destroy');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Catégories de produits
    Route::resource('categories', CategorieController::class)->only(['index', 'store', 'update', 'destroy']);

    // Produits
    Route::get('produits', [ProduitController::class, 'index'])->name('produits.index');
    Route::resource('produits', ProduitController::class)->except('index');

    // Mouvements de stock
    Route::get('mouvementStocks', [MouvementStockController::class, 'index'])->name('mouvementStocks.index');
    Route::resource('mouvementStocks', MouvementStockController::class)->only('create', 'store', 'update', 'destroy');
    Route::get('/mouvementStocks/{mouvementStock}/edit', [MouvementStockController::class, 'edit'])->name('mouvementStocks.edit');

    // Fournisseurs et Achats
    Route::resource('fournisseurs', FournisseurController::class);
    Route::resource('achats', AchatController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/achats/{achat}/receptionner', [AchatController::class, 'receptionner'])->name('achats.receptionner');
    Route::post('/achats/{achat}/payer', [AchatController::class, 'payer'])->name('achats.payer');
    Route::post('/achats/{achat}/retour', [AchatController::class, 'retourFournisseur'])->name('achats.retour');

    // Ventes, Paiements clients et Retours clients
    Route::get('/ventes', [VenteController::class, 'index'])->name('ventes.index');
    Route::get('/ventes-filtrees', [VenteController::class, 'ventesFiltrees'])->name('ventes.filtrees');
    Route::get('/ventes-filtrees1', [VenteController::class, 'ventesFiltrees1'])->name('ventes.filtrees1');
    Route::get('/ventes/create', [VenteController::class, 'create'])->name('ventes.create')->middleware('verifier.heure.vente');
    Route::post('/ventes', [VenteController::class, 'store'])->name('ventes.store')->middleware('verifier.heure.vente');
    Route::get('/ventes/{vente}', [VenteController::class, 'show'])->name('ventes.show');
    Route::delete('/ventes/{vente}', [VenteController::class, 'destroy'])->name('ventes.destroy');
    Route::post('/ventes/{vente}/annuler', [VenteController::class, 'annulerVente'])->name('ventes.annuler');
    Route::post('/ventes/{vente}/retour', [VenteController::class, 'enregistrerRetour'])->name('ventes.retour');
    Route::post('/ventes/{vente}/payer', [VenteController::class, 'payer'])->name('ventes.payer');
    Route::post('/ventes/{vente}/paiement', [paiementController::class, 'store'])->name('paiements.store');
    Route::get('/ventes/{id}/ticket', [VenteController::class, 'imprimerTicket'])->name('ventes.ticket');
    Route::get('/paiements/{id}/ticket', [paiementController::class, 'ticketpaiement'])->name('paiements.ticket');

    // Exportations
    Route::get('/simple-exel/expot', [ExportationEcontroller::class, 'exportation'])->name('export');
    Route::get('/mouvementStocks/export-excel', [ExportationEcontroller::class, 'exportMouvementStock'])->name('mouvementStocks.export-excel');
    Route::get('/ventes-export-pdf', [VenteController::class, 'exportPDF'])->name('ventes.export.pdf');
    Route::get('/ventes/export/pdf', [VenteController::class, 'exportPdflist'])->name('ventes.exportlist.pdf');
    Route::get('/export/products/{format?}', [ExportationEcontroller::class, 'exportProducts'])
        ->name('export.products')
        ->where('format', 'excel|pdf');

    // Dépenses et Trésorerie
    Route::get('/depenses/export-excel', [DepenseController::class, 'exportExcel'])->name('depenses.export-excel');
    Route::get('/depenses/export-pdf', [DepenseController::class, 'exportPdf'])->name('depenses.export-pdf');
    Route::resource('depenses', DepenseController::class);

    // Employés et Avances sur salaire
    Route::resource('employes', EmployeController::class);
    Route::get('/avances-salaires', [AvanceSalaireController::class, 'index'])->name('avances-salaires.index');
    Route::post('/avances-salaires', [AvanceSalaireController::class, 'store'])->name('avances-salaires.store');
    Route::post('/avances-salaires/{avance}/rembourser', [AvanceSalaireController::class, 'rembourser'])->name('avances-salaires.rembourser');

    // Importation de données Excel / CSV
    Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
    Route::post('/imports/preview', [ImportController::class, 'preview'])->name('imports.preview');
    Route::get('/imports/{batch}/preview', [ImportController::class, 'showPreview'])->name('imports.show-preview');
    Route::post('/imports/{batch}/confirm', [ImportController::class, 'confirm'])->name('imports.confirm');

    // Journal d'audit et historique de connexions
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/user-logins', [UserLoginController::class, 'index'])->name('user-logins.index');
    Route::get('/user-logins/{user}', [UserLoginController::class, 'show'])->name('user-logins.show');

    // Gestion des horaires
    Route::get('/horaires', [HoraireController::class, 'index'])->name('admin.horaires.index');
    Route::get('/horaires/edit', [HoraireController::class, 'edit'])->name('admin.horaires.edit');
    Route::post('/horaires', [HoraireController::class, 'update'])->name('admin.horaires.update');
    Route::get('/admin/horaires/historique', [HoraireController::class, 'historique'])->name('admin.horaires.historique');

    // Gestion des utilisateurs
    Route::resource('users', UserContoller::class);
    Route::post('/users/{user}/toggle', [UserContoller::class, 'toggle'])->name('users.toggle');
});

// Groupe pour les super administrateurs de la plateforme SaaS globale
Route::middleware(['auth', 'is.superAdmin'])->group(function () {
    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    // Administration SaaS des entreprises hébergées (CRUD Complet)
    Route::get('/superadmin/entreprises', [SuperAdminController::class, 'entreprises'])->name('superadmin.entreprises.index');
    Route::post('/superadmin/entreprises', [SuperAdminController::class, 'storeEntreprise'])->name('superadmin.entreprises.store');
    Route::get('/superadmin/entreprises/{entreprise}', [SuperAdminController::class, 'showEntreprise'])->name('superadmin.entreprises.show');
    Route::put('/superadmin/entreprises/{entreprise}', [SuperAdminController::class, 'updateEntreprise'])->name('superadmin.entreprises.update');
    Route::delete('/superadmin/entreprises/{entreprise}', [SuperAdminController::class, 'destroyEntreprise'])->name('superadmin.entreprises.destroy');
    Route::post('/superadmin/entreprises/{entreprise}/toggle', [SuperAdminController::class, 'toggleEntreprise'])->name('superadmin.entreprises.toggle');

    // Administration SaaS des propriétaires et de leurs entreprises
    Route::get('/superadmin/proprietaires', [SuperAdminController::class, 'index'])->name('superadmin.proprietaires.index');
    Route::post('/superadmin/proprietaires', [SuperAdminController::class, 'storeProprietaire'])->name('superadmin.proprietaires.store');
    Route::put('/superadmin/proprietaires/{proprietaire}', [SuperAdminController::class, 'updateProprietaire'])->name('superadmin.proprietaires.update');
    Route::post('/superadmin/proprietaires/{proprietaire}/toggle', [SuperAdminController::class, 'toggleProprietaire'])->name('superadmin.proprietaires.toggle');
    Route::post('/superadmin/proprietaires/{proprietaire}/entreprises', [SuperAdminController::class, 'storeEntrepriseForProprietaire'])->name('superadmin.proprietaires.store-entreprise');

    // Gestion des abonnements SaaS par entreprise
    Route::get('/superadmin/abonnements', [SuperAdminController::class, 'abonnements'])->name('superadmin.abonnements.index');
    Route::post('/superadmin/entreprises/{entreprise}/abonnement', [SuperAdminController::class, 'storeOrRenewAbonnement'])->name('superadmin.abonnements.store');
    Route::post('/superadmin/abonnements/{abonnement}/toggle', [SuperAdminController::class, 'toggleAbonnementStatut'])->name('superadmin.abonnements.toggle');
});

require __DIR__ . '/auth.php';
