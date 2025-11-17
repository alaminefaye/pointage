@extends('layouts.app')

@section('title', 'Dashboard')

@push('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-user text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Employés Actifs</span>
                <h3 class="card-title mb-2">{{ \App\Models\Employee::where('is_active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-time text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Pointages Aujourd'hui</span>
                <h3 class="card-title mb-2">{{ \App\Models\AttendanceRecord::whereDate('date', today())->whereNotNull('check_in_time')->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-bell text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Alertes Non Lues</span>
                <h3 class="card-title mb-2">{{ \App\Models\Alert::where('is_read', false)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-building text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Départements</span>
                <h3 class="card-title mb-2">{{ \App\Models\Department::count() }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Derniers Pointages</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Date</th>
                                <th>Entrée</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\AttendanceRecord::with('employee')->latest()->take(5)->get() as $record)
                            <tr>
                                <td>{{ $record->employee->full_name }}</td>
                                <td>{{ $record->date->format('d/m/Y') }}</td>
                                <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($record->is_absent)
                                        <span class="badge bg-label-danger">Absent</span>
                                    @elseif($record->is_late)
                                        <span class="badge bg-label-warning">Retard</span>
                                    @else
                                        <span class="badge bg-label-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
// Update unread alerts count
async function updateUnreadAlerts() {
    try {
        const response = await fetch('{{ route("alerts.unread-count") }}');
        const data = await response.json();
        const countEl = document.getElementById('unread-alerts-count');
        if (countEl) {
            countEl.textContent = data.count;
            countEl.style.display = data.count > 0 ? 'inline-block' : 'none';
        }
    } catch (error) {
        console.error('Error updating alerts:', error);
    }
}

updateUnreadAlerts();
setInterval(updateUnreadAlerts, 60000); // Update every minute
</script>
@endpush
