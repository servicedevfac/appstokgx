<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GESTION_USP</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }
       

        .header, .footer {
            text-align: center !important;
        }

        h2, h3 {
            margin: 5px 0;
        }

        .details, .items, .totals {
            width: 100%;
            margin-top: 10px;
        }

        .items table, .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th, .items td, .totals th, .totals td {
            text-align: left;
            padding: 3px 0;
        }

        .items th {
            border-bottom: 1px dashed #000;
        }

        .items td {
            border-bottom: 1px dotted #000;
        }

        .totals td {
            font-weight: bold;
        }

        .right { text-align: right; }
        .center { text-align: center; }

        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 5px;
            }
        }
    </style>
</head>
<body>
  
    <div class="header">
        <p>San Pedro - 07 99 28 82 54</p>
        <hr>
    </div>

    <div class="totals">
        <table>
            <tr>
                <td>N° reçu de vente: {{ $paiement->vente->code_recu }}</td>
            
            </tr>

            <tr>
                <td><strong>Montant: {{ number_format($paiement->montant,0,',',' ') }} FCFA</strong></td>
               
            </tr>

            <tr>
                <td>Reste à payer:  {{ number_format(($paiement->reste_a_payer ?? 0),0,',',' ') }} FCFA</td>
                <!--<td class="right">-->
                   
                <!--</td>-->
            </tr>
            <tr>
                <td>Mode paiement: {{ ucfirst($paiement->mode_paiement ?? 'Espèces') }}</td>
                <!--<td class="right"></td>-->
            </tr>
        </table>
    </div>

    <div class="footer">
        <hr>
        <p>Merci pour votre achat !</p>
    </div>
    


    <script>
        // Ouvre directement la fenêtre d'impression
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
