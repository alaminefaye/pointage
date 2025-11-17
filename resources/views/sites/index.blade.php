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

