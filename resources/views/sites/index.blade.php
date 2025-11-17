@extends('layouts.app')

@section('title', 'Sites')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des Sites</h5>
        <a href="{{ route('sites.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Ajouter un site
        </a>
    </div>
    <div class="card-body">
        <!-- Search Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="bx bx-search"></i> Recherche</h6>
                <form method="GET" action="{{ route('sites.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nom, adresse...">
                    </div>
                    <div class="col-md-3">
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
                    @if(request()->hasAny(['search', 'status']))
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('sites.index') }}" class="btn btn-secondary w-100">
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
                        <th>Adresse</th>
                        <th>Coordonnées</th>
                        <th>Rayon (m)</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                    <tr>
                        <td>{{ $site->name }}</td>
                        <td>{{ $site->address ?? '-' }}</td>
                        <td>{{ $site->latitude }}, {{ $site->longitude }}</td>
                        <td>{{ $site->radius }}m</td>
                        <td>
                            @if($site->is_active)
                                <span class="badge bg-label-success">Actif</span>
                            @else
                                <span class="badge bg-label-danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('sites.show', $site) }}" class="btn btn-sm btn-info">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('sites.edit', $site) }}" class="btn btn-sm btn-warning">
                                <i class="bx bx-edit"></i>
                            </a>
                            <form action="{{ route('sites.destroy', $site) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr?');">
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
                        <td colspan="6" class="text-center">Aucun site trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $sites->links() }}
        </div>
    </div>
</div>
@endsection

