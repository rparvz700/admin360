@extends('Partials.app', ['activeMenu' => 'tickets'])

@section('title') Tickets List @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>My Tickets</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Ticket
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tickets.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Ticket Type</label>
                    <select name="ticket_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="vehicle_support" {{ request('ticket_type') == 'vehicle_support' ? 'selected' : '' }}>Vehicle Support</option>
                        <option value="asset_request" {{ request('ticket_type') == 'asset_request' ? 'selected' : '' }}>Asset Request</option>
                        <option value="asset_repair" {{ request('ticket_type') == 'asset_repair' ? 'selected' : '' }}>Asset Repair</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucwords(str_replace('_', ' ', $ticket->ticket_type)) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->title }}</td>
                                <td>
                                    <span class="badge bg-{{ $ticket->priority_color }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $ticket->status_color }}">
                                        {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No tickets found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
