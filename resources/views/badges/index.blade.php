@extends('layouts.app')

@section('title', 'Badges')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des Badges</h5>
        <a href="{{ route('badges.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Créer un badge
        </a>
    </div>
    <div class="card-body">
        <!-- Search Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="bx bx-search"></i> Recherche et Filtres</h6>
                <form method="GET" action="{{ route('badges.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Numéro, code QR, nom employé...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employé</label>
                        <select class="form-select" name="employee_id">
                            <option value="">Tous</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }}
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
                    @if(request()->hasAny(['search', 'employee_id', 'status']))
                    <div class="col-md-1 d-flex align-items-end">
                        <a href="{{ route('badges.index') }}" class="btn btn-secondary w-100">
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
                        <th>Numéro Badge</th>
                        <th>Employé</th>
                        <th>Code QR</th>
                        <th>Date d'émission</th>
                        <th>Date d'expiration</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($badges as $badge)
                    <tr>
                        <td><strong>{{ $badge->badge_number }}</strong></td>
                        <td>
                            {{ $badge->employee->full_name }}
                            <br>
                            <small class="text-muted">{{ $badge->employee->employee_code }}</small>
                        </td>
                        <td>
                            <code class="text-primary">{{ substr($badge->qr_code, 0, 20) }}...</code>
                        </td>
                        <td>{{ $badge->issued_at ? $badge->issued_at->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($badge->expires_at)
                                @if($badge->isExpired())
                                    <span class="text-danger">{{ $badge->expires_at->format('d/m/Y') }} (Expiré)</span>
                                @else
                                    {{ $badge->expires_at->format('d/m/Y') }}
                                @endif
                            @else
                                <span class="text-muted">Sans expiration</span>
                            @endif
                        </td>
                        <td>
                            @if($badge->is_active && !$badge->isExpired())
                                <span class="badge bg-label-success">Actif</span>
                            @elseif($badge->isExpired())
                                <span class="badge bg-label-danger">Expiré</span>
                            @else
                                <span class="badge bg-label-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('badges.show', $badge) }}" class="btn btn-sm btn-info" title="Voir">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('badges.print', $badge) }}" class="btn btn-sm btn-primary" title="Imprimer" target="_blank">
                                <i class="bx bx-printer"></i>
                            </a>
                            <a href="{{ route('badges.edit', $badge) }}" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="bx bx-edit"></i>
                            </a>
                            <a href="{{ route('badges.download-qr', $badge) }}" class="btn btn-sm btn-success" title="Télécharger QR Code">
                                <i class="bx bx-download"></i>
                            </a>
                            <form action="{{ route('badges.toggle-status', $badge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $badge->is_active ? 'btn-secondary' : 'btn-primary' }}" title="{{ $badge->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="bx bx-{{ $badge->is_active ? 'x' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('badges.destroy', $badge) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce badge?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucun badge trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $badges->links() }}
        </div>
    </div>
</div>
@endsection

