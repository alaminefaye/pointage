@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Paramètres de Géolocalisation par Site</h5>
            </div>
            <div class="card-body">
                @if($sites->count() > 0)
                    @foreach($sites as $site)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ $site->name }}</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('settings.update-geolocation') }}" method="POST">
                                @csrf
                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                @if ($errors->any())
                                    <div class="alert alert-danger mb-3">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Latitude *</label>
                                        <input type="text" step="any" class="form-control @error('latitude') is-invalid @enderror" name="latitude" value="{{ old('latitude', $site->latitude) }}" required>
                                        <small class="text-muted">Coordonnée latitude de la zone autorisée</small>
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Longitude *</label>
                                        <input type="text" step="any" class="form-control @error('longitude') is-invalid @enderror" name="longitude" value="{{ old('longitude', $site->longitude) }}" required>
                                        <small class="text-muted">Coordonnée longitude de la zone autorisée</small>
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Rayon (mètres) *</label>
                                        <input type="number" step="0.1" class="form-control @error('radius') is-invalid @enderror" name="radius" value="{{ old('radius', $site->radius) }}" min="1" max="1000" required>
                                        <small class="text-muted">Rayon autorisé autour du point (défaut: 50m)</small>
                                        @error('radius')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted">Aucun site actif. <a href="{{ route('sites.create') }}">Créer un site</a></p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Paramètres d'Heures Supplémentaires</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update-overtime-threshold') }}" method="POST">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Seuil d'alerte (heures) *</label>
                        <input type="number" step="0.1" class="form-control @error('overtime_threshold_hours') is-invalid @enderror" name="overtime_threshold_hours" value="{{ old('overtime_threshold_hours', $overtimeThreshold) }}" min="1" max="100" required>
                        <small class="text-muted">Seuil d'heures supplémentaires par jour pour déclencher une alerte</small>
                        @error('overtime_threshold_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
