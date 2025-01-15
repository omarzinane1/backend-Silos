<!-- resources/views/cheques/report.blade.php -->
<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Rapport des Silos</title>
    <style>
        /* Ajoutez des styles CSS si nécessaire */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: center; border-radius: 50px;"><img src="images/radeejicon.png" style="width: 40%;" alt="AKSAM"></div>
        <div style="border: black solid 1px; padding: 10px; text-align: center;margin-top: 3px;"><h3>SUIVI DES FREINTES PREMELANGES</h3></div>

    </div>
    <h3 style="text-align: center;">Rapport des Silos du {{ $silos->first()->datevalidation }} au  {{ $silos->last()->datevalidation }} </h3>
    <table>
        <thead>
            <tr>
                <th>Numéro Silo</th>
                <th>Produit</th>
                <th>Stock init</th>
                <th>Entre</th>
                <th>Consumation</th>
                <th>Stock Final</th>
                <th>Statut</th>
                <th>Date Validation</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($silos as $silo)
            <tr>
                <td>{{ $silo->numsilo }}</td>
                <td>{{ $silo->produit }}</td>
                <td>{{ $silo->stocki }}</td>
                <td>{{ $silo->entre }}</td>
                <td>{{ $silo->consumation }}</td>
                <td>{{ $silo->stockf }}</td>
                <td>{{ $silo->statut }}</td>
                <td>{{ $silo->datevalidation }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
