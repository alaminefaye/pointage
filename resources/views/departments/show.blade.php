@extends('layouts.app')

@section('title', 'Détails Département')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Détails du Département</h5>
        <div>
            <a href="{{ route('departments.edit', $department) }}" class="btn btn-warning">
                <i class="bx bx-edit"></i> Modifier
            </a>
            <a href="{{ route('departments.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    <div class="card-body">
        <h6>Informations</h6>
        <p><strong>Nom:</strong> {{ $department->name }}</p>
        <p><strong>Description:</strong> {{ $department->description ?? '-' }}</p>
        
        <hr>
        
        <h6>Employés ({{ $department->employees->count() }})</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Poste</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($department->employees as $employee)
                    <tr>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->position }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Aucun employé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

