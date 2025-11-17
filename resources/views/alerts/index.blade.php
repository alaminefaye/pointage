@extends('layouts.app')

@section('title', 'Alertes')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Alertes</h5>
        <button class="btn btn-sm btn-primary" onclick="markAllAsRead()">Marquer tout comme lu</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Titre</th>
                        <th>Message</th>
                        <th>Employé</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr class="{{ !$alert->is_read ? 'table-warning' : '' }}">
                        <td>
                            <span class="badge bg-label-{{ $alert->type == 'absence' ? 'danger' : ($alert->type == 'late' ? 'warning' : 'info') }}">
                                {{ ucfirst($alert->type) }}
                            </span>
                        </td>
                        <td>{{ $alert->title }}</td>
                        <td>{{ $alert->message }}</td>
                        <td>{{ $alert->employee ? $alert->employee->full_name : 'Système' }}</td>
                        <td>{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($alert->is_read)
                                <span class="badge bg-label-success">Lu</span>
                            @else
                                <span class="badge bg-label-danger">Non lu</span>
                            @endif
                        </td>
                        <td>
                            @if(!$alert->is_read)
                                <button class="btn btn-sm btn-primary" onclick="markAsRead({{ $alert->id }})">Marquer comme lu</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucune alerte</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $alerts->links() }}
        </div>
    </div>
</div>

@push('page-js')
<script>
// Update unread alerts count (make it available globally)
async function updateUnreadAlerts() {
    try {
        const response = await fetch('{{ route("alerts.unread-count") }}');
        const data = await response.json();
        const countEl = document.getElementById('unread-alerts-count');
        if (countEl) {
            countEl.textContent = data.count;
            if (data.count > 0) {
                countEl.style.display = 'inline-block';
            } else {
                countEl.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error updating alerts:', error);
    }
}

function markAsRead(alertId) {
    fetch(`/alerts/${alertId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        // Update the badge count
        updateUnreadAlerts();
        // Reload to update the table
        location.reload();
    });
}

function markAllAsRead() {
    fetch('/alerts/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        // Update the badge count
        updateUnreadAlerts();
        // Reload to update the table
        location.reload();
    });
}
</script>
@endpush
@endsection

