<?php

namespace App\Http\Controllers;

use App\Exports\StockExport;
use Illuminate\Http\Request;
use App\Exports\RupturesExport;
use App\Exports\RupturesActuelExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
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
            // On découpe les mots saisis par espace
            $keywords = explode(' ', $reference);

            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where('AR_Design', 'like', '%' . $word . '%');
                }
            });
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

        $articles = $query->paginate(15)->appends($request->all());

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

   public function ruptures(Request $request)
{
    // Récupérer les dates du filtre
    $dateDebut = $request->input('date_debut');
    $dateFin = $request->input('date_fin');

    // Construire la requête de base
    $query = DB::table('rupture')->where('archived', 0);

    // Appliquer les filtres de date si présents
    if ($dateDebut && $dateFin) {
        $query->whereBetween('date_rupture', [$dateDebut, $dateFin]);
    } elseif ($dateDebut) {
        $query->where('date_rupture', '>=', $dateDebut);
    } elseif ($dateFin) {
        $query->where('date_rupture', '<=', $dateFin);
    }

    // Exécuter la requête avec pagination
    $ruptures = $query->paginate(15)->appends($request->all());

    // Retourner la vue avec les résultats
    return view('stock.ruptures', compact('ruptures'));
}

    
	  public function rupturesActuel(Request $request)
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        // Exécution de la fonction SQL
        $results = DB::select('SELECT * FROM [dbo].[fn_GetRupturekArticles]()');

        // Transformer en collection Laravel
        $ruptures = collect($results);

        // Filtrage en PHP si dates fournies
        if ($dateDebut) {
            $ruptures = $ruptures->filter(function ($item) use ($dateDebut) {
                return $item->date_rupture >= $dateDebut;
            });
        }

        if ($dateFin) {
            $ruptures = $ruptures->filter(function ($item) use ($dateFin) {
                return $item->date_rupture <= $dateFin;
            });
        }

        // Re-indexer après filtrage
        $ruptures = $ruptures->values();

        // Pagination manuelle
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $ruptures->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedRuptures = new LengthAwarePaginator(
            $currentPageItems,
            $ruptures->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('stock.ruptures_actuel', ['ruptures' => $paginatedRuptures]);
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

     public function rupturesActuelExport()
    {
        return Excel::download(new RupturesActuelExport, 'Ruprures_actuelles.xlsx');
    }

    public function show($ref)
    {
        $excludedDepots = [
            '6', '32', '119', '120', '121', '122', '123', '124', '125', '126', '127',
            '130', '131', '132', '133', '134', '135', '136', '137', '138', '139',
            '140', '142', '145', '150', '154', '162', '165', '166', '167', '168',
            '169', '170', '171', '172', '173', '174', '179', '191', '193', '194',
            '195', '196', '197', '198', '199', '200', '202', '203', '204', '205',
            '206', '207', '208', '209', '210', '211', '212', '213', '214', '215',
            '216', '217', '218', '219', '220', '221', '222', '223', '224', '225',
            '226', '231', '232', '233', '237', '240', '241', '242', '243', '244',
            '245', '246', '247', '248', '249', '251', '254', '255', '256', '257',
            '201','143','129','268','131','264'
        ];

        $articles = DB::table('cstock21bme.dbo.f_artstock as a')
            ->join('cstock21bme.dbo.F_DEPOT as d', 'a.DE_No', '=', 'd.DE_No')
            ->select(
                'a.AR_Ref',
                'a.DE_No',
                'a.AS_QteSto',
                'd.DE_Intitule'
            )
            ->where('a.AR_Ref', $ref)
            ->whereNotIn('a.DE_No', $excludedDepots)
            ->get();

            $lastDoc = DB::table('CSTOCK21BME.dbo.F_DOCLIGNE')
            ->select('cbCreation', 'DL_Qte','DL_CMUP')
            ->where('AR_Ref', $ref)
            ->whereIn('DO_Type', [16, 17])
            ->orderByDesc('cbCreation')
            ->first();
        return view('stock.show', compact('articles','lastDoc'));
    }

      public function indexMBoup(Request $request)
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

        $articles = $query->paginate(15)->appends($request->all());

        return view('stock.mboup', compact('articles'));
    }
}
