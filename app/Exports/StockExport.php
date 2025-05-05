<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockExport implements FromCollection,WithHeadings
{
    public function collection()
    {
        return collect(DB::select('SELECT AR_Ref,AR_Design,Qte,AR_PrixAch,AR_PrixVen FROM dbo.fn_GetStockArticles()'));
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Désignation',
            'Quantité',
            'Prix Achat',
            'Prix Vente',
        ];
    }
}
