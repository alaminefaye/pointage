@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Générer un Rapport Mensuel</h5>
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
        
        <form action="{{ route('reports.monthly') }}" method="GET">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Employé *</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id" required>
                        <option value="">Sélectionner un employé</option>
                        @foreach(\App\Models\Employee::where('is_active', true)->get() as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mois *</label>
                    <select class="form-select @error('month') is-invalid @enderror" name="month" required>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('month', now()->month) == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $i, 1)->locale('fr')->monthName }}
                            </option>
                        @endfor
                    </select>
                    @error('month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Année *</label>
                    <input type="number" class="form-control @error('year') is-invalid @enderror" name="year" value="{{ old('year', now()->year) }}" min="2020" max="2100" required>
                    @error('year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Générer le rapport</button>
            </div>
        </form>
    </div>
</div>
@endsection

