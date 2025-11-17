@extends('layouts.app')

@section('title', 'Modifier un Badge')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Modifier un Badge</h5>
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
        
        <form action="{{ route('badges.update', $badge) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employé *</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id" id="employee_id" required>
                        <option value="">Sélectionner un employé</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" data-employee-code="{{ $employee->employee_code }}" {{ old('employee_id', $badge->employee_id) == $employee->id ? 'selected' : '' }}>
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
                    <input type="text" class="form-control @error('badge_number') is-invalid @enderror" name="badge_number" id="badge_number" value="{{ old('badge_number', $badge->badge_number) }}" required>
                    <small class="text-muted">Numéro unique pour identifier le badge (rempli automatiquement avec le code de l'employé)</small>
                    @error('badge_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date d'émission</label>
                    <input type="date" class="form-control @error('issued_at') is-invalid @enderror" name="issued_at" value="{{ old('issued_at', $badge->issued_at ? $badge->issued_at->format('Y-m-d') : '') }}">
                    @error('issued_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date d'expiration</label>
                    <input type="date" class="form-control @error('expires_at') is-invalid @enderror" name="expires_at" value="{{ old('expires_at', $badge->expires_at ? $badge->expires_at->format('Y-m-d') : '') }}">
                    @error('expires_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3">{{ old('notes', $badge->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', $badge->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Badge actif</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('badges.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const employeeSelect = document.getElementById('employee_id');
        const badgeNumberInput = document.getElementById('badge_number');
        
        // Remplir automatiquement au chargement si un employé est déjà sélectionné
        if (employeeSelect.value) {
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.employeeCode) {
                // Ne remplir que si le champ est vide ou contient l'ancien numéro de badge
                if (!badgeNumberInput.value || badgeNumberInput.value === '{{ $badge->badge_number }}') {
                    badgeNumberInput.value = selectedOption.dataset.employeeCode;
                }
            }
        }
        
        // Écouter les changements du select employé
        employeeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.employeeCode) {
                badgeNumberInput.value = selectedOption.dataset.employeeCode;
            }
        });
    });
</script>
@endpush
@endsection

