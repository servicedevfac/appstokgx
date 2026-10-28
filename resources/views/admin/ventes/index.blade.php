@extends('layouts.base')
@section('content')
<div class="col-12 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header card-heade d-flex justify-content-between align-items-center">
            <h3 class="text-white">📊 Graphique des ventes</h3>
        </div>
        <div class="card-body">

            <style>
                .filtres {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-bottom: 20px;
                    align-items: center;
                }
                .filtres input, .filtres button, .filtres select {
                    padding: 8px 12px;
                    border-radius: 4px;
                    border: 1px solid #ddd;
                }
                .filtres button {
                    cursor: pointer;
                    font-weight: bold;
                }
                #btnFiltrer { background-color: #007bff; color: white; border: none; }
                #btnReset { background-color: #6c757d; color: white; border: none; }
                #btnPDF { background-color: #dc3545; color: white; border: none; }
                #btnExportImage { background-color: #28a745; color: white; border: none; }
                #btnExportExcel { background-color: #17a2b8; color: white; border: none; }
                #ventesChart { max-width: 100%; height: auto; margin: 0 auto; }
                .chart-container { position: relative; width: 100%; padding-bottom: 20px; }
            </style>

            <div class="filtres">
                <select id="periode-select">
                    <option value="jour">Jour</option>
                    <option value="semaine">Semaine</option>
                    <option value="mois" selected>Mois</option>
                    <option value="annee">Année</option>
                </select>
                <input type="date" id="date_debut" value="{{ \Carbon\Carbon::now()->subMonth()->format('Y-m-d') }}">
                <input type="date" id="date_fin" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                <input type="text" class="d-none" id="q" placeholder="Recherche...">

                <button id="btnFiltrer">Filtrer</button>
                <button id="btnReset">Réinitialiser</button>
                <button id="btnPDF">📊 Exporter en PDF</button>
                <button id="btnExportExcel">Exporter en Excel</button>
                <select id="qualiteExport">
                    <option value="1">Qualité normale</option>
                    <option value="2">Qualité moyenne</option>
                    <option value="3" selected>Qualité haute</option>
                    <option value="4">Qualité très haute</option>
                </select>
                <button id="btnExportImage">Exporter en png</button>

                <select id="typeGraph">
                    <option value="line" selected>Courbe</option>
                    <option value="bar">Histogramme</option>
                    <option value="pie">Camembert</option>
                </select>
            </div>
<div class="chart-container" style="background: white; padding: 20px; border-radius: 8px;">
    <div style="width: 100%; max-width: 900px; margin: auto;">
        <canvas id="ventesChart" width="900" height="400"></canvas>
    </div>
</div>

</div>
</div>





<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header  d-flex justify-content-between align-items-center card-heade">
                <h3 class="text-white m-0"><i class="fas fa-list me-2"></i> Liste des ventes</h3>
                @can('create vente')
                <a href="{{ route('ventes.create') }}" class="btn btn-header  fw-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i> Nouvelle vente
                </a>
                @endcan
            </div>
            <div class="card-body">
    <form method="GET" action="" class="mb-3">
        <div class="filtres">
                <select name="periode"  id="periode-select">
                    <option value="jour" @if(request('periode')=='jour') selected @endif>Jour</option>
                    <option value="semaine" @if(request('periode')=='semaine') selected @endif>Semaine</option>
                    <option value="mois" @if(request('periode')=='mois') selected @endif>Mois</option>
                    <option value="annee" @if(request('periode')=='annee') selected @endif>Année</option>
                </select>
                <label>Date de début</label>
                <input type="date" name="date_debut"  value="{{ request('date_debut') }}">
                <label>Date de fin</label>
                <input type="date" name="date_fin"  value="{{ request('date_fin') }}">
                <label>Recherche</label>
                <input type="text" name="q"  placeholder="Client, code reçu, utilisateur..." value="{{ request('q') }}">
                <a href="{{ route('ventes.exportlist.pdf', request()->query()) }}" class="btn btn-danger"> Exporter en PDF</a>
                <a href="{{ route('export')}}" class="btn btn-success"> Exporter en excel</a>

                <button  id="btn-filtrer" type="submit" class="btn btn-primary">Filtrer</button>
            </div>

    </form>
    @if (session()->has('error'))
    <div class="alert alert-danger">
        {{ session()->get('error') }}
    </div>
    @endif
    
    <div class="table-responsive">
            <table class="table table-hover table-bordered dt-responsive nowrap w-100">
        <thead class="card-heade  table-dark">
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Date</th>
                <th>Montant total (FCFA)</th>
                <th>Mode de paiement</th>
                <th>Montant payé (FCFA)</th>
                <th>Reste à payer (FCFA)</th>
                
                <th>Statut</th>
                <th>Date de vente </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventes as $vente)
            <tr class="align-middle text-center" @if ($vente->statut=='valide') style="background-color:#d4edda;"
            @elseif ($vente->statut=='annulee') style="background-color:#f8d7da ;"

            @else style="background-color:#e2e3e5 ;"
            @endif>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $vente->client->nom }} {{ $vente->client->prenom }}</td>
                <td>{{ $vente->created_at }}</td>
                <td>{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</td>
                <td>{{ $vente->mode_paiement }}</td>
                <td>{{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA</td>
                <td >@if ($vente->est_paye)
                    <span class="text-white badge bg-success">Payé</span>
                @else
                    <span class="text-white badge bg-danger">Non payé</span>
                @endif
                </td>
                 <td>{{ $vente->date_vente}}</td>
                <td style="display:flex;flex-direction:row;justify-content:center; gap: 5px; ">
                    @if($vente->code_recu)
                        <a href="{{ asset('storage/recus/recu_vente_'.$vente->client->nom.'_'.$vente->code_recu.'.pdf') }}" class="btn btn-header1 btn-lg" target="_blank"><i class="fas fa-file-pdf"></i></a>
                    @else
                        <span class="text-muted">Pas de reçu</span>
                    @endif
                    <a href="{{ route('ventes.ticket', $vente->id) }}" target="_blank" class="btn  btn-header1 btn-lg"><i class="fas fa-print"></i></a>
                </td>
                <td style="display:flex;flex-direction:row;justify-content:center; gap: 5px; ">
                    @can('view vente')
                        <a href="{{ route('ventes.show', $vente->id) }}" class="btn btn-header1 text-white-bold btn-lg rounded-3"><i class="fas fa-eye"></i></a>
                    @endcan
                        @can('edit vente')
                    <form id="form-annuler-{{ $vente->id }}" action="{{ route('ventes.annuler', $vente->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button"
                            class="btn btn-delete btn-lg btn-toggle"
                            data-form-id="form-annuler-{{ $vente->id }}"
                            title="Annuler">
                            <i class="fas fa-ban"></i>
                        </button>
                    </form>
        @endcan

                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="11" class="table-empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h5 class="empty-title">Aucune vente enregistrée</h5>
                        <p class="empty-desc">Aucune vente ne correspond à vos critères de recherche.</p>
                        @can('create vente')
                            <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm mt-3">
                                <i class="fas fa-plus me-1"></i> Créer une vente
                            </a>
                        @endcan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{ $ventes->appends(request()->query())->links() }}
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('ventesChart').getContext('2d');

    function formatFCFA(val) {
        return new Intl.NumberFormat('fr-FR').format(val) + " FCFA";
    }

    function createChart(labels, ventes, paiements, reste, type="line") {
        if(window.ventesChart && typeof window.ventesChart.destroy === 'function') {
            window.ventesChart.destroy();
        }

        const gradientVentes = ctx.createLinearGradient(0, 0, 0, 400);
        gradientVentes.addColorStop(0, 'rgba(54, 162, 235, 0.7)');
        gradientVentes.addColorStop(1, 'rgba(54, 162, 235, 0.1)');

        const gradientPaiements = ctx.createLinearGradient(0, 0, 0, 400);
        gradientPaiements.addColorStop(0, 'rgba(40, 167, 69, 0.7)');
        gradientPaiements.addColorStop(1, 'rgba(40, 167, 69, 0.1)');

        const gradientReste = ctx.createLinearGradient(0, 0, 0, 400);
        gradientReste.addColorStop(0, 'rgba(220, 53, 69, 0.7)');
        gradientReste.addColorStop(1, 'rgba(220, 53, 69, 0.1)');

        window.ventesChart = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ventes',
                        data: ventes,
                        backgroundColor: type==="pie"?undefined:gradientVentes,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        fill: type!=="pie",
                        tension: 0.4
                    },
                    {
                        label: 'Paiements',
                        data: paiements,
                        backgroundColor: type==="pie"?undefined:gradientPaiements,
                        borderColor: 'rgba(40, 167, 69,1)',
                        fill: type!=="pie",
                        tension: 0.4
                    },
                    {
                        label: 'Reste à encaisser',
                        data: reste,
                        backgroundColor: type==="pie"?undefined:gradientReste,
                        borderColor: 'rgba(220, 53, 69,1)',
                        fill: type!=="pie",
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => formatFCFA(ctx.raw)
                        }
                    },
                    title: {
                        display: true,
                        text: 'Évolution des ventes'
                    }
                },
                scales: type!=="pie" ? { y: { beginAtZero: true } } : {}
            }
        });
    }

    function loadData() {
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;
        const recherche = document.getElementById('q').value;
        const periode = document.getElementById('periode-select').value;

        fetch(`/ventes-filtrees?date_debut=${dateDebut}&date_fin=${dateFin}&q=${recherche}&periode=${periode}`)
            .then(r => r.json())
            .then(data => {
                createChart(data.labels, data.ventes, data.paiements, data.reste, document.getElementById('typeGraph').value);
            })
            .catch(err => console.error(err));
    }

    function exportChartToPDF() {
        const loading = document.createElement('div');
        loading.innerHTML = '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.8);color:white;padding:20px;border-radius:5px;z-index:9999;">Génération du PDF...</div>';
        document.body.appendChild(loading);

        const chartContainer = document.querySelector('.chart-container') || document.getElementById('ventesChart').parentElement;
        const qualite = parseInt(document.getElementById('qualiteExport').value);

        html2canvas(chartContainer, {
            scale: qualite,
            backgroundColor: '#ffffff',
            useCORS: true
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png', 1.0);
            const { jsPDF } = window.jspdf;

            const pdf = new jsPDF('landscape', 'mm', 'a4');
            const pdfWidth = 297;
            const pdfHeight = 210;
            const ratio = canvas.height / canvas.width;
            let finalWidth = pdfWidth - 40;
            let finalHeight = finalWidth * ratio;
            if (finalHeight > pdfHeight - 40) {
                finalHeight = pdfHeight - 40;
                finalWidth = finalHeight / ratio;
            }

            pdf.setFontSize(20);
            pdf.setFont(undefined, 'bold');
            pdf.text('Rapport des Ventes', 20, 25);

            pdf.setFontSize(12);
            pdf.setFont(undefined, 'normal');
            const today = new Date().toLocaleDateString('fr-FR');
            pdf.text(`Date d'export: ${today}`, 20, 35);

            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            const periode = document.getElementById('periode-select').value;
            if (dateDebut && dateFin) pdf.text(`Période: du ${dateDebut} au ${dateFin}`, 20, 42);
            pdf.text(`Type: ${periode}`, 20, 49);

            const xOffset = (pdfWidth - finalWidth) / 2;
            const yOffset = 60;
            pdf.addImage(imgData, 'PNG', xOffset, yOffset, finalWidth, finalHeight);

            pdf.setFontSize(8);
            pdf.text('Généré par le système de gestion des ventes', 20, pdfHeight - 10);

            const fileName = `rapport_ventes_${today.replace(/\//g, '-')}.pdf`;
            pdf.save(fileName);
            document.body.removeChild(loading);

            if(typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Succès!', text: 'PDF téléchargé avec succès', icon: 'success', timer: 2000, showConfirmButton: false });
            } else alert('PDF téléchargé avec succès !');

        }).catch(error => {
            console.error('Erreur:', error);
            document.body.removeChild(loading);
            if(typeof Swal !== 'undefined') Swal.fire('Erreur', 'Impossible de générer le PDF', 'error');
            else alert('Erreur lors de la génération du PDF');
        });
    }

    // Event listeners
    document.getElementById('btnFiltrer').addEventListener('click', loadData);
    document.getElementById('typeGraph').addEventListener('change', loadData);
    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('date_debut').value = '';
        document.getElementById('date_fin').value = '';
        document.getElementById('q').value = '';
        document.getElementById('periode-select').selectedIndex = 0;
        loadData();
    });

    document.getElementById('btnPDF').addEventListener('click', exportChartToPDF);

    document.getElementById('btnExportExcel').addEventListener('click', () => {
        const labels = window.ventesChart.data.labels;
        const ventes = window.ventesChart.data.datasets[0].data;
        const paiements = window.ventesChart.data.datasets[1].data;
        const reste = window.ventesChart.data.datasets[2].data;

        const ws = XLSX.utils.json_to_sheet(labels.map((l,i)=>({
            Date: l,
            Ventes: ventes[i],
            Paiements: paiements[i],
            Reste: reste[i]
        })));
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Ventes");
        XLSX.writeFile(wb, "ventes.xlsx");
    });

    document.getElementById('btnExportImage').addEventListener('click', () => {
        const qualite = parseInt(document.getElementById('qualiteExport').value);
        if(typeof Swal !== 'undefined') Swal.fire({title:'Exportation...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
        html2canvas(document.querySelector('.chart-container') || document.getElementById('ventesChart').parentElement, {scale:qualite}).then(canvas=>{
            const img = canvas.toDataURL('image/png', 1.0);
            const link = document.createElement('a');
            link.href = img;
            link.download = 'graphique_ventes.png';
            link.click();
            if(typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({title:'Succès', text:'Graphique exporté !', icon:'success', timer:2000, showConfirmButton:false});
            }
        });
    });

    // Confirmation SweetAlert pour les boutons "Annuler"
    document.querySelectorAll('.btn-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const formId = this.dataset.formId;
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, annuler !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById(formId).submit();
            });
        });
    });

    loadData();
});


</script>
@endsection

