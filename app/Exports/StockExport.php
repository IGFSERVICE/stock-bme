<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StockExport implements FromCollection
{
    public function collection()
    {
        return collect(DB::select('SELECT * FROM dbo.fn_GetStockArticles()'));
    }
}
