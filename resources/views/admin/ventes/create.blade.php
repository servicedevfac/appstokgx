@extends('layouts.base')


@section('head')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Veuillez corriger les erreurs ci-dessous :</strong>
        </div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-cart-plus"></i>
                    <span>Nouvelle vente</span>
                </h3>
                <a href="{{ route('ventes.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
            <div class="card-body p-4">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('ventes.store') }}" class="needs-validation" novalidate>
                    @csrf

                    {{-- Client --}}
                    <div class="mb-4">
                        <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                        <select id="client-select" name="client_id" class="form-control @error('client_id') is-invalid @enderror" required style="width: 100%;">
                            <!-- AJAX rempli par Select2 -->
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tableau produits --}}
                    @error('produits')
                        <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center gap-2 font-size-13">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle mb-0" id="produits-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produit</th>
                                    <th style="width: 180px;">Prix unitaire</th>
                                    <th style="width: 140px;">Quantité</th>
                                    <th style="width: 180px;">Total</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="ligne-produits">
                                @php $oldProduits = old('produits', [['produit_id' => '', 'prix' => '', 'quantite' => 1]]) @endphp

                                @foreach($oldProduits as $i => $produit)
                                    <tr>
                                        <td>
                                            <select name="produits[{{ $i }}][produit_id]" class="form-control produit-select" required>
                                                <option value="">-- Choisir un produit --</option>
                                                @foreach($produits as $p)
                                                    <option value="{{ $p->id }}"
                                                        data-prix="{{ $p->prix }}"
                                                        {{ $produit['produit_id'] == $p->id ? 'selected' : '' }}>
                                                        {{ $p->nom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="produits[{{ $i }}][prix]" class="form-control prix"
                                                value="{{ $produit['prix'] ?? '' }}" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="produits[{{ $i }}][quantite]" class="form-control quantite"
                                                value="{{ $produit['quantite'] ?? 1 }}" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control total" value="0" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-delete btn-sm supprimer-ligne">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-header1 btn-sm mb-4" id="ajouter-ligne">
                        <i class="fas fa-plus me-1"></i> Ajouter une ligne produit
                    </button>

                    {{-- Remise et total --}}
                    <div class="row g-3 p-3 bg-light rounded-3 mb-3">
                        <div class="col-md-6">
                            <label for="remise" class="form-label">Remise (FCFA)</label>
                            <input type="number" name="remise" id="remise" class="form-control" value="0" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="montant_total" class="form-label">Total net à payer (FCFA)</label>
                            <input type="number" name="montant_total" id="montant_total" class="form-control fw-bold fs-5 text-primary" readonly>
                        </div>
                    </div>

                    {{-- Date et paiement --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date_vente" class="form-label">Date de vente <span class="text-danger">*</span></label>
                            <input type="date" name="date_vente" id="date_vente" class="form-control @error('date_vente') is-invalid @enderror" value="{{ old('date_vente', now()->format('Y-m-d')) }}" required>
                            @error('date_vente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="mode_paiement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                            <select name="mode_paiement" id="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror" required>
                                <option value="espèces" {{ old('mode_paiement') == 'espèces' ? 'selected' : '' }}>Espèces</option>
                                <option value="mobile money" {{ old('mode_paiement') == 'mobile money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="carte" {{ old('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte</option>
                            </select>
                            @error('mode_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Champ Avance (uniquement si crédit) --}}
                        <div class="col-md-6 d-none" id="avance_section">
                            <label for="montant_paye" class="form-label">Avance versée (FCFA)</label>
                            <input type="number" step="0.01" min="0" name="montant_paye" id="montant_paye"
                                class="form-control" placeholder="Saisir le montant avancé">
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="est_paye" id="est_paye" readonly>
                        <label class="form-check-label" for="est_paye">
                            Est payé
                        </label>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-header1 btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Enregistrer la vente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection



@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let index = 1;

// --- Fonction pour recalculer les totaux ---
function recalculerTotals() {
    let total = 0;
    document.querySelectorAll('#ligne-produits tr').forEach(row => {
        const prix = parseFloat(row.querySelector('.prix')?.value || 0);
        const quantite = parseFloat(row.querySelector('.quantite')?.value || 0);
        const ligneTotal = prix * quantite;
        row.querySelector('.total').value = ligneTotal.toFixed(0);
        total += ligneTotal;
    });

    const remise = parseFloat(document.getElementById('remise')?.value || 0);
    let montantApayer = total - remise;
    if (montantApayer < 0) montantApayer = 0;
    document.getElementById('montant_total').value = montantApayer.toFixed(0);

    // Vérifier avance vs total
    const avanceInput = document.getElementById('montant_paye');
    let avance = parseFloat(avanceInput?.value || 0);
    const estPaye = document.getElementById('est_paye');
    const sectionAvance = document.getElementById('avance_section');

    if (avanceInput) {
        avanceInput.max = montantApayer;
        if (avance > montantApayer && montantApayer > 0) {
            avance = montantApayer;
            avanceInput.value = montantApayer.toFixed(0);
        }
    }

    if (avance >= montantApayer && montantApayer > 0) {
        estPaye.checked = true;
        sectionAvance.classList.add('d-none');
    } else {
        estPaye.checked = false;
        sectionAvance.classList.remove('d-none');
    }
}

// --- Fonction pour initialiser une ligne ---
function initLigne(row) {
    // Supprimer ligne
    row.querySelector('.supprimer-ligne').addEventListener('click', function(e) {
        const rows = document.querySelectorAll('#ligne-produits tr');
        if (rows.length > 1) {
            e.target.closest('tr').remove();
            recalculerTotals();
        }
    });

    // Changement produit => remplir prix
    row.querySelector('.produit-select').addEventListener('change', function(e) {
        const prix = e.target.selectedOptions[0]?.getAttribute('data-prix') || 0;
        row.querySelector('.prix').value = prix;
        recalculerTotals();
    });

    // Quantité modifiée
    row.querySelector('.quantite').addEventListener('input', recalculerTotals);
}

// --- Ajouter une nouvelle ligne ---
document.getElementById('ajouter-ligne').addEventListener('click', function() {
    const table = document.getElementById('ligne-produits');
    const firstRow = table.querySelector('tr');
    const nouvelleLigne = firstRow.cloneNode(true);

    // Réinitialiser valeurs et index
    nouvelleLigne.querySelectorAll('input, select').forEach(el => {
        if (el.name && el.name.includes('produits')) {
            const base = el.name.split('[')[0];
            const champ = el.name.substring(el.name.indexOf(']') + 1);
            el.name = `${base}[${index}]${champ}`;
        }
        if (el.classList.contains('prix') || el.classList.contains('total')) el.value = '';
        if (el.classList.contains('quantite')) el.value = 1;
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });

    initLigne(nouvelleLigne);
    table.appendChild(nouvelleLigne);
    index++;
    recalculerTotals();
});

// --- Initialisation première ligne ---
initLigne(document.querySelector('#ligne-produits tr'));

// --- Remise en temps réel ---
document.getElementById('remise').addEventListener('input', recalculerTotals);

// --- Select2 client ---
$('#client-select').select2({
    placeholder: 'Rechercher un client...',
    ajax: {
        url: '{{ route("clients.search") }}',
        dataType: 'json',
        delay: 250,
        data: params => ({ q: params.term }),
        processResults: data => ({
            results: data.map(client => ({
                id: client.id,
                text: client.nom + ' ' + client.prenom + ' ' + client.telephone
            }))
        }),
        cache: true
    }
});

// --- Avance modifiée ---
document.getElementById('montant_paye').addEventListener('input', recalculerTotals);

// --- Checkbox est_paye contrôle affichage avance ---
document.getElementById('est_paye').addEventListener('change', function() {
    const sectionAvance = document.getElementById('avance_section');
    const avanceInput = document.getElementById('montant_paye');
    const montantTotal = parseFloat(document.getElementById('montant_total').value || 0);

    if (this.checked) {
        // Si payé => cacher champ avance
        sectionAvance.classList.add('d-none');
        avanceInput.value = montantTotal;
    } else {
        // Si non payé => afficher champ avance
        sectionAvance.classList.remove('d-none');
        avanceInput.value = 0;
    }
    recalculerTotals();
});

// --- Initialiser affichage au chargement ---
window.addEventListener('DOMContentLoaded', function() {
    document.getElementById('est_paye').dispatchEvent(new Event('change'));
    recalculerTotals();
});
</script>
@endsection




