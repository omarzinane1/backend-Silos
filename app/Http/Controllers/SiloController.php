<?php

namespace App\Http\Controllers;

use App\Models\Silo;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Exception;
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
    public function showUser()
    {
        $users = DB::table('users')->where('role','!=','admin')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'silo' => 'required|string|max:255',
            'produit' => 'required|string|max:255',
            'stocki' => 'required|numeric',
            'entre' => 'required|numeric',
            'consumation' => 'required|numeric',
        ]);
        $validatedData['stockf'] = $request->input('stockf', ($validatedData['stocki'] + $validatedData['entre']) - $validatedData['consumation']);
        $validatedData['datevalidation'] = $request->input('datevalidation', now());

        // Création de silo
        $silo = Silo::create($validatedData);

        // Retourne une réponse
        return response()->json([
            'message' => 'Silo ajouté avec succès !',
            'status' => 'success',
            'silo' => $silo
        ]);
    }
    public function deleteSilo($id)
    {
        try {
            $silo = Silo::findOrFail($id);
            $silo->delete();
            return response()->json(['message' => 'Silo supprimé avec succès !'], 200,);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'silo' => 'required|string|max:255',
            'produit' => 'required|string|max:255',
            'stocki' => 'required|numeric',
            'entre' => 'required|numeric',
            'consumation' => 'required|numeric',
        ]);

        try {

            $silo = Silo::findOrFail($id);

            $silo->silo = $validatedData['silo'];
            $silo->produit = $validatedData['produit'];
            $silo->stocki = $validatedData['stocki'];
            $silo->entre = $validatedData['entre'];
            $silo->consumation = $validatedData['consumation'];
            // Calcul de stockf
            $silo->stockf = max(0, ($validatedData['stocki'] + $validatedData['entre']) - $validatedData['consumation']);

            $silo->save();

            return response()->json([
                'success' => true,
                'message' => 'Silo mis à jour avec succès.',
                'data' => $silo,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Silo non trouvé.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getFilteredData(Request $request)
    {

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $data = Silo::whereBetween('datevalidation', [$startDate, $endDate])->get();

        return response()->json($data);
    }


    // http://localhost:8000/api/search?search=Blé
    public function search(Request $request)
    {
        $search = $request->query('search', '');

        $silos = DB::table('silos')
            ->where(function ($query) use ($search) {
                $query->where('silo', 'LIKE', '%' . $search . '%')
                    ->orWhere('produit', 'LIKE', '%' . $search . '%')
                    ->orWhere('stocki', 'LIKE', '%' . $search . '%')
                    ->orWhere('entre', 'LIKE', '%' . $search . '%')
                    ->orWhere('consumation', 'LIKE', '%' . $search . '%')
                    ->orWhere('stockf', 'LIKE', '%' . $search . '%')
                    ->orWhere('statut', 'LIKE', '%' . $search . '%')
                    ->orWhere('datevalidation', 'LIKE', '%' . $search . '%');
            })
            ->get();

        return response()->json([
            'message' => 'search succès !',
            'status' => 'success',
            'silo' => $silos
        ]);
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

        // la requête avec les paramètres
        $silos = $sql ? DB::select($sql, $parameters) : [];

        // Création du fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Titre
        $paddingColumns = 1;
        $mergeStart = 'A';
        $mergeEnd = chr(ord('G') + $paddingColumns);
        $sheet->mergeCells("{$mergeStart}1:{$mergeEnd}1");

        $sheet->setCellValue('A1', 'SUIVI DES FREINTES PREMELANGES');
        $sheet->getStyle("{$mergeStart}1:{$mergeEnd}1")->getFont()->setSize(16);
        $sheet->getStyle("{$mergeStart}1:{$mergeEnd}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$mergeStart}1:{$mergeEnd}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050');
        // Date
        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', 'DATE:' . $startDate . ' jusqu\'à ' . $endDate);
        $sheet->getStyle('A2:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A2:C2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

        // En-têtes de colonnes
        $sheet->setCellValue('A3', 'N Silo');
        $sheet->setCellValue('B3', 'Produit');
        $sheet->setCellValue('C3', 'Stock Initale');
        $sheet->setCellValue('D3', 'Entre');
        $sheet->setCellValue('E3', 'Consumation');
        $sheet->setCellValue('F3', 'Stock Final');
        $sheet->setCellValue('G3', 'Statut');
        $sheet->setCellValue('H3', 'Date Creation');

        $sheet->getStyle('A3:H3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFD700');
        $sheet->getStyle('A3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Remplir les données
        $row = 4;
        foreach ($silos as $silo) {
            $sheet->setCellValue("A{$row}", $silo->silo);
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
