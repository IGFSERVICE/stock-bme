@extends('adminlte::page')

@section('title', 'Stock Articles')

@section('content_header')
    <h1>Stock des Articles</h1>
@stop

@section('content')

    <form method="GET" action="{{ route('stock.index') }}" class="form-inline mb-3 justify-content-end">
        <input type="text" name="reference" class="form-control mr-2" placeholder="Rechercher..." value="{{ request('reference') }}">
        <select name="sort" class="form-control mr-2">
            <option value="">-- Trier par Quantité --</option>
            <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Quantité Croissante</option>
            <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Quantité Décroissante</option>
        </select>
        
        {{-- <select name="qte" class="form-control mr-2">
            <option value="">-- Filtrer Quantité --</option>
            <option value="positif" {{ request('qte') == 'positif' ? 'selected' : '' }}>Quantité >= 0</option>
            <option value="negatif" {{ request('qte') == 'negatif' ? 'selected' : '' }}>Quantité < 0</option>
            <option value="zero" {{ request('qte') == 'zero' ? 'selected' : '' }}>Quantité = 0</option>
        </select> --}}

        <button type="submit" class="btn btn-primary mr-2">Rechercher</button>
        <a href="{{ route('stock.index') }}" class="btn btn-secondary">Réinitialiser</a>
    </form>

    <form method="POST" action="{{ route('stock.rupture') }}">
        @csrf

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th><input type="checkbox" id="check-all"></th>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Quantité</th>
                            <th>Prix Achat</th>
                            <th>Prix Vente</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($articles as $article)
                            <tr>
                                <td>
                                    @if ($article->Qte <= 0)
                                        <input type="checkbox" name="rupture_refs[]" value="{{ $article->AR_Ref }}">
                                    @else
                                        <input type="checkbox" disabled>
                                    @endif
                                </td>
                                <td>{{ $article->AR_Ref }}</td>
                                <td>{{ $article->AR_Design }}</td>
                                <td>{{ number_format($article->Qte, 0, ',', ' ') }}</td>
                                <td>{{ number_format($article->AR_PrixAch, 0, ',', ' ') }}</td>
                                <td>{{ number_format($article->AR_PrixVen, 0, ',', ' ') }}</td>
                                <td><a href="{{ route('stock.show', $article->AR_Ref) }}" class="btn btn-sm btn-info">Détails</a></td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun article trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('stock.export') }}" class="btn btn-success">Exporter vers Excel</a>
                    <button type="submit" class="btn btn-danger">Marquer comme en rupture</button>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {!! $articles->links('pagination::bootstrap-4') !!}
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    document.getElementById('check-all').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('input[name="rupture_refs[]"]');
        checkboxes.forEach(cb => {
            if (!cb.disabled) cb.checked = this.checked;
        });
    });
</script>
@stop
