@extends('layouts.app')

@section('title', 'Détails de l\'Utilisateur')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Détails de l'utilisateur</h5>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                <i class="bx bx-edit"></i> Modifier
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">ID</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Nom complet</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Date de création</th>
                        <td>{{ $user->created_at->format('d/m/Y à H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Dernière mise à jour</th>
                        <td>{{ $user->updated_at->format('d/m/Y à H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Email vérifié</th>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-label-success">Oui ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                            @else
                                <span class="badge bg-label-warning">Non</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($user->id !== auth()->id())
            <div class="mt-4">
                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur? Cette action est irréversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash"></i> Supprimer cet utilisateur
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

