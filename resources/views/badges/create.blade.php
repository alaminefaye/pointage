@extends('layouts.app')

@section('title', 'Créer un Badge')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Créer un Badge</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('badges.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employé *</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id" required>
                        <option value="">Sélectionner un employé</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Numéro de Badge *</label>
                    <input type="text" class="form-control @error('badge_number') is-invalid @enderror" name="badge_number" value="{{ old('badge_number') }}" required placeholder="Ex: BADGE-001">
                    <small class="text-muted">Numéro unique pour identifier le badge</small>
                    @error('badge_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date d'émission</label>
                    <input type="date" class="form-control @error('issued_at') is-invalid @enderror" name="issued_at" value="{{ old('issued_at', date('Y-m-d')) }}">
                    <small class="text-muted">Date à laquelle le badge a été émis (par défaut: aujourd'hui)</small>
                    @error('issued_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date d'expiration</label>
                    <input type="date" class="form-control @error('expires_at') is-invalid @enderror" name="expires_at" value="{{ old('expires_at') }}">
                    <small class="text-muted">Date d'expiration du badge (optionnel)</small>
                    @error('expires_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3" placeholder="Notes optionnelles sur le badge...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Badge actif</label>
                    </div>
                </div>
            </div>
            <div class="alert alert-info">
                <i class="bx bx-info-circle"></i> Un code QR unique sera automatiquement généré pour ce badge lors de la création.
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Créer le badge</button>
                <a href="{{ route('badges.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

