@extends('adminlte::page')

@section('title', 'Détails de l\'Article')

@section('content_header')
    <h1>Détails de l'Article - Référence : {{ $articles->first()->AR_Ref ?? '' }}</h1>
@stop

@section('content')
@if($lastDoc)
    <div class="alert alert-info">
        <strong>Dernier Achat :</strong> {{ \Carbon\Carbon::parse($lastDoc->cbCreation)->format('d/m/Y') }}<br>
        <strong>Quantité :</strong> {{ number_format($lastDoc->DL_Qte, 0, ',', ' ') }} <br>
        <strong>CMUP :</strong> {{ number_format($lastDoc->DL_CMUP, 0, ',', ' ') }}
    </div>
@endif
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>Dépôt</th>
                        <th>Quantité</th>
                       
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                        <tr>
                            <td>{{ $article->DE_Intitule }}</td>
                            <td>{{ number_format($article->AS_QteSto, 0, ',', ' ') }}</td>
                           
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <a href="{{ route('stock.index') }}" class="btn btn-secondary mt-3">Retour</a>
        </div>
    </div>
@stop
