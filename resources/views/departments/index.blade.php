@extends('layouts.app')

@section('title', 'Départements')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des Départements</h5>
        <a href="{{ route('departments.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Ajouter un département
        </a>
    </div>
    <div class="card-body">
        <!-- Search Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="bx bx-search"></i> Recherche</h6>
                <form method="GET" action="{{ route('departments.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nom, description...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search"></i> Rechercher
                        </button>
                    </div>
                    @if(request()->has('search'))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary w-100">
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
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Nombre d'employés</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                    <tr>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->description ?? '-' }}</td>
                        <td>{{ $department->employees_count }}</td>
                        <td>
                            <a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-info">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-warning">
                                <i class="bx bx-edit"></i>
                            </a>
                            <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr?');">
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
                        <td colspan="4" class="text-center">Aucun département trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $departments->links() }}
        </div>
    </div>
</div>
@endsection

