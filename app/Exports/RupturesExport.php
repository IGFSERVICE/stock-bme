<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RupturesExport implements FromCollection, WithHeadings
{
    protected $dateDebut;
    protected $dateFin;

    public function __construct($dateDebut = null, $dateFin = null)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function collection()
    {
        $query = DB::table('rupture');

        if ($this->dateDebut && $this->dateFin) {
            $query->whereBetween('date_rupture', [$this->dateDebut, $this->dateFin]);
        } elseif ($this->dateDebut) {
            $query->where('date_rupture', '>=', $this->dateDebut);
        } elseif ($this->dateFin) {
            $query->where('date_rupture', '<=', $this->dateFin);
        }

        return $query->select('AR_Ref', 'AR_Design', 'Qte', 'AR_PrixAch', 'AR_PrixVen', 'date_rupture')->get();
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Désignation',
            'Quantité',
            'Prix Achat',
            'Prix Vente',
            'Date de Rupture',
        ];
    }
}
