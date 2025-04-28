<?php

namespace App\Http\Controllers;

use App\Exports\StockExport;
use Illuminate\Http\Request;
use App\Exports\RupturesExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use JeroenNoten\LaravelAdminLte\Http\Controllers\Controller;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // Exécuter la procédure pour mettre à jour historique_stock
        DB::statement('EXEC dbo.sp_InsertStockHistorique');

        // Archiver en 1 seul UPDATE tous les articles réapprovisionnés
        DB::update("
            UPDATE rupture
            SET archived = 1
            FROM rupture r
            JOIN historique_stock h ON r.AR_Ref = h.AR_Ref
            WHERE h.Qte > 0 AND r.archived = 0
        ");

        // Filtres
        $reference = $request->input('reference');
        $qteFilter = $request->input('qte');

        $query = DB::table('historique_stock')
            ->whereNotIn('AR_Ref', function($subQuery) {
                $subQuery->select('AR_Ref')
                         ->from('rupture')
                         ->where('archived', 0);
            });

        if (!empty($reference)) {
            $query->where('AR_Design', 'like', '%' . $reference . '%');
        }

        if ($qteFilter === 'positif') {
            $query->where('Qte', '>=', 0);
        }  elseif ($qteFilter === 'zero') {
            $query->where('Qte', '=', 0);
        }
        // elseif ($qteFilter === 'negatif') {
        //     $query->where('Qte', '<', 0);
        // }

        if (request('sort') == 'asc') {
            $query->orderBy('Qte', 'asc');
        } elseif (request('sort') == 'desc') {
            $query->orderBy('Qte', 'desc');
        } else {
            $query->orderBy('Qte', 'asc'); // tri par défaut si aucun tri demandé
        }

        $articles = $query->paginate(15);

        return view('stock.index', compact('articles'));
    }

    public function markRupture(Request $request)
    {
        $selectedRefs = $request->input('rupture_refs', []);

        if (!empty($selectedRefs)) {
            DB::transaction(function () use ($selectedRefs) {
                $articles = DB::table('historique_stock')
                    ->whereIn('AR_Ref', $selectedRefs)
                    ->get();

                foreach ($articles as $article) {
                    DB::table('rupture')->insert([
                        'AR_Ref'       => $article->AR_Ref,
                        'AR_Design'    => $article->AR_Design,
                        'Qte'          => $article->Qte,
                        'AR_PrixAch'   => $article->AR_PrixAch,
                        'AR_PrixVen'   => $article->AR_PrixVen,
                        'date_rupture' => today()->format('Y-m-d'),
                        'archived'     => 0,
                    ]);
                }
            });
        }

        return redirect()->route('stock.index')->with('success', 'Articles marqués comme en rupture.');
    }

    public function export()
    {
        return Excel::download(new StockExport, 'stock_articles.xlsx');
    }
    public function exportRuptures(Request $request)
{
    $dateDebut = $request->input('date_debut');
    $dateFin = $request->input('date_fin');

    return Excel::download(new RupturesExport($dateDebut, $dateFin), 'ruptures_articles.xlsx');
}

    public function ruptures(Request $request)
{
    // Récupérer les dates du filtre
    $dateDebut = $request->input('date_debut');
    $dateFin = $request->input('date_fin');

    $query = DB::table('rupture');

    if ($dateDebut && $dateFin) {
        $query->whereBetween('date_rupture', [$dateDebut, $dateFin]);
    } elseif ($dateDebut) {
        $query->where('date_rupture', '>=', $dateDebut);
    } elseif ($dateFin) {
        $query->where('date_rupture', '<=', $dateFin);
    }

    $ruptures = $query->paginate(15);

    return view('stock.ruptures', compact('ruptures'));
}

}
