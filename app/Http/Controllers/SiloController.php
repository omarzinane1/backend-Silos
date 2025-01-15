<?php

namespace App\Http\Controllers;

use App\Models\Silo;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SiloController extends Controller
{
    public function index()
    {
        $silo = Silo::all();
        return response()->json($silo);
    }

    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'numsilo' => 'required|string|max:255|unique:silos,numsilo',
            'produit' => 'required|string|max:255',
            'stocki' => 'required|numeric',
            'entre' => 'required|numeric',
            'consumation' => 'required|numeric',
            'statut' => 'required|string|max:255',
        ]);
        $validatedData['stockf'] = $request->input('stockf', ($validatedData['stocki'] + $validatedData['entre']) - $validatedData['consumation']);
        $validatedData['datevalidation'] = $request->input('datevalidation', now());

        // Création de silo
        $silo = Silo::create($validatedData);

        // Retourne une réponse
        return response()->json([
            'message' => 'Silo ajouté avec succès !',
            'silo' => $silo
        ]);
    }



    // http://localhost:8000/api/search?search=Blé
    public function search(Request $request)
    {
        $search = $request->query('search', '');

        $silos = DB::table('silos')
            ->where(function ($query) use ($search) {
                $query->where('numsilo', 'LIKE', '%' . $search . '%')
                    ->orWhere('produit', 'LIKE', '%' . $search . '%')
                    ->orWhere('stocki', 'LIKE', '%' . $search . '%')
                    ->orWhere('entre', 'LIKE', '%' . $search . '%')
                    ->orWhere('consumation', 'LIKE', '%' . $search . '%')
                    ->orWhere('stockf', 'LIKE', '%' . $search . '%')
                    ->orWhere('statut', 'LIKE', '%' . $search . '%')
                    ->orWhere('datevalidation', 'LIKE', '%' . $search . '%');
            })
            ->get();

        return response()->json($silos);
    }

    public function ExporterDATA(Request $request)
    {
        $startDate = $request->query('startDate'); // Assurez-vous que les clés correspondent
        $endDate = $request->query('endDate');

        $parameters = [];
        $sql = '';

        if ($startDate && $endDate) {
            $sql = "SELECT * FROM silos
                WHERE datevalidation BETWEEN :startDate AND :endDate
                ORDER BY datevalidation ASC";

            // Associez les valeurs des paramètres
            $parameters = [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
        }

        // Exécution de la requête avec les paramètres
        $silos = $sql ? DB::select($sql, $parameters) : [];

        // Création du fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Titre
        $paddingColumns = 1;
        $mergeStart = 'A';
        $mergeEnd = chr(ord('I') + $paddingColumns);
        $sheet->mergeCells("{$mergeStart}1:{$mergeEnd}1");

        $sheet->setCellValue('A1', 'Liste des silos');
        $sheet->getStyle("{$mergeStart}1:{$mergeEnd}1")->getFont()->setSize(16);
        $sheet->getStyle("{$mergeStart}1:{$mergeEnd}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$mergeStart}1:{$mergeEnd}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

        // En-têtes de colonnes
        $sheet->setCellValue('A2', 'numsilo');
        $sheet->setCellValue('B2', 'produit');
        $sheet->setCellValue('C2', 'stocki');
        $sheet->setCellValue('D2', 'entre');
        $sheet->setCellValue('E2', 'consumation');
        $sheet->setCellValue('F2', 'stockf');
        $sheet->setCellValue('G2', 'statut');
        $sheet->setCellValue('H2', 'datevalidation');

        $sheet->getStyle('A2:H2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFD700');
        $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Remplir les données
        $row = 3;
        foreach ($silos as $silo) {
            $sheet->setCellValue("A{$row}", $silo->numsilo);
            $sheet->setCellValue("B{$row}", $silo->produit);
            $sheet->setCellValue("C{$row}", $silo->stocki);
            $sheet->setCellValue("D{$row}", $silo->entre);
            $sheet->setCellValue("E{$row}", $silo->consumation);
            $sheet->setCellValue("F{$row}", $silo->stockf);
            $sheet->setCellValue("G{$row}", $silo->statut);
            $sheet->setCellValue("H{$row}", $silo->datevalidation);
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Générer le fichier Excel
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response()->make($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="silos.xlsx"',
        ]);
    }
    //Méthode pour export PDF
    public function exportPDF(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $parameters = [];
        $sql = '';


        $parameters = [];
        $sql = '';

        if ($startDate && $endDate) {
            $sql = "SELECT * FROM silos
                WHERE datevalidation BETWEEN :startDate AND :endDate
                ORDER BY datevalidation ASC";

            // Associez les valeurs des paramètres
            $parameters = [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
        }

        // Exécution de la requête avec les paramètres
        if ($sql !== '') {
            $silos = DB::select($sql, $parameters);
            $silos = collect($silos);
        } else {
            $silos = collect();
        }

        $pdf = FacadePdf::loadView('silosPdf', compact('silos'));

        return $pdf->download('silosDATA.pdf');
    }
}
