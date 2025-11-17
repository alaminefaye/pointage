@extends('layouts.app')

@section('title', 'Gestion des Employés')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des Employés</h5>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Ajouter un employé
        </a>
    </div>
    <div class="card-body">
        <!-- Search Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="bx bx-search"></i> Recherche et Filtres</h6>
                <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nom, email, code...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Département</label>
                        <select class="form-select" name="department_id">
                            <option value="">Tous</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            <option value="">Tous</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search"></i> Rechercher
                        </button>
                    </div>
                    @if(request()->hasAny(['search', 'department_id', 'status']))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-x"></i> Réinitialiser
                        </a>
                    </div>
                    @endif
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Département</th>
                        <th>Poste</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->department->name }}</td>
                        <td>{{ $employee->position }}</td>
                        <td>
                            @if($employee->is_active)
                                <span class="badge bg-label-success">Actif</span>
                            @else
                                <span class="badge bg-label-danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-info">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-warning">
                                <i class="bx bx-edit"></i>
                            </a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet employé?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucun employé trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection

