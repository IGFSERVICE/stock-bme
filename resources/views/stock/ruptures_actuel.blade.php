@extends('adminlte::page')

@section('title', 'Ruptures Actuelles')

@section('content_header')
    <h1>Ruptures Actuelles</h1>
@stop

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('stock.rupturesActuelExport') }}" class="btn btn-success">
        Exporter en Excel
    </a>
</div>

{{-- <form method="GET" action="{{ route('stock.rupturesActuel') }}" class="form-inline mb-3 justify-content-end">
    <input type="date" name="date_debut" class="form-control mr-2" value="{{ request('date_debut') }}">
    <input type="date" name="date_fin" class="form-control mr-2" value="{{ request('date_fin') }}">
    <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
    <a href="{{ route('stock.rupturesActuel') }}" class="btn btn-secondary">Réinitialiser</a>
</form> --}}

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-danger text-white">
                <tr>
                    <th>Référence</th>
                    <th>Désignation</th>
                    <th>Quantité</th>
                    <th>Prix Achat</th>
                    <th>Prix Vente</th>
                    <th>Date de Rupture</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ruptures as $rupture)
                    <tr>
                        <td>{{ $rupture->AR_Ref }}</td>
                        <td>{{ $rupture->AR_Design }}</td>
                        <td>{{ number_format($rupture->Qte, 0, ',', ' ') }}</td>
                        <td>{{ number_format($rupture->AR_PrixAch, 0, ',', ' ') }}</td>
                        <td>{{ number_format($rupture->AR_PrixVen, 0, ',', ' ') }}</td>
                        <td>{{ \Carbon\Carbon::parse($rupture->date_rupture)->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Aucun article trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-4">
            {!! $ruptures->links('pagination::bootstrap-4') !!}
        </div>
    </div>
</div>
@stop
