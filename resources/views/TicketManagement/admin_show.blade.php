@extends('Partials.app', ['activeMenu' => 'tickets'])
@section('title') View Ticket @endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Tickets</a></li>
                    <li class="breadcrumb-item active">{{ $ticket->ticket_number }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $ticket->ticket_number }}</h4>
                    <div>
                        <span class="badge bg-{{ $ticket->status_color }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span class="badge bg-{{ $ticket->priority_color }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <h5>{{ $ticket->title }}</h5>
                    <p class="text-muted mb-3">
                        <small>
                            <i class="fas fa-user"></i> {{ $ticket->user->name }} | 
                            <i class="fas fa-calendar"></i> {{ $ticket->created_at->format('M d, Y H:i') }}
                        </small>
                    </p>
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p>{{ $ticket->description }}</p>
                    </div>

                    @if($ticket->isVehicleSupport())
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h4 class="card-title">Vehicle Support Details</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Vehicle Type:</strong> {{ $ticket->vehicleType->type_name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Passengers:</strong> {{ $ticket->passenger_count }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Start:</strong> {{ $ticket->trip_start_datetime->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>End:</strong> {{ $ticket->trip_end_datetime->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-12">
                                        <p><strong>Purpose:</strong> {{ $ticket->trip_purpose }}</p>
                                    </div>
                                    <div class="col-md-12">
                                        @if($ticket->trip_location_details)
                                            <h6>Trip Route:</h6>
                                            @foreach($ticket->trip_location_details as $index => $location)
                                                <div class="trip-stop">
                                                    <span class="badge bg-info fs-5">Stop {{ $index + 1 }}</span>
                                                    <br><span class="badge bg-success">Start:</span>&nbsp;{{ $location['start'] }} 
                                                    <br><span class="badge bg-danger">End:</span>&nbsp;{{ $location['end'] }}<br>
                                                    @if(isset($location['notes']))
                                                        <small>{{ $location['notes'] }}</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                @if($ticket->assignedDriver || $ticket->assignedVehicle)
                                    <hr>
                                    <h6 class="text-success">Current Assignment</h6>
                                    @if($ticket->assignedDriver)
                                        <p><strong>Driver:</strong> {{ $ticket->assignedDriver->name }} {{ $ticket->assignedDriver->sur_name }}</p>
                                        <p><strong>Contact:</strong> {{ $ticket->assignedDriver->phone }}</p>
                                    @endif
                                    @if($ticket->assignedVehicle)
                                        <p><strong>Vehicle:</strong> {{ $ticket->assignedVehicle->brand }} {{ $ticket->assignedVehicle->model }}</p>
                                        <p><strong>Registration:</strong> {{ $ticket->assignedVehicle->registration_number }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($ticket->isAssetRequest())
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Asset Request Details</h6>
                                <p><strong>Category:</strong> {{ $ticket->assetCategory->category_name ?? 'N/A' }}</p>
                                <p><strong>Requested Asset:</strong> {{ $ticket->requested_asset_name }}</p>
                                <p><strong>Location:</strong> {{ $ticket->floor->building->building_name ?? '' }} - {{ $ticket->floor->floor_label ?? '' }}</p>
                                <p><strong>Specific Location:</strong> {{ $ticket->location_within_floor }}</p>
                                @if($ticket->asset_specifications)
                                    <p><strong>Specifications:</strong> {{ $ticket->asset_specifications }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($ticket->isAssetRepair())
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Asset Repair Details</h6>
                                <p><strong>Asset Tag:</strong> {{ $ticket->asset->asset_tag ?? 'N/A' }}</p>
                                <p><strong>Asset Name:</strong> {{ $ticket->asset->asset_name ?? 'N/A' }}</p>
                                <p><strong>Category:</strong> {{ $ticket->asset->category->category_name ?? 'N/A' }}</p>
                                <p><strong>Location:</strong> {{ $ticket->asset->floor->building->building_name ?? '' }} - {{ $ticket->asset->floor->floor_label ?? '' }}</p>
                            </div>
                        </div>
                    @endif

                    @if($ticket->attachments->count() > 0)
                        <div class="mt-3">
                            <h6>Attachments</h6>
                            <ul class="list-unstyled">
                                @foreach($ticket->attachments as $attachment)
                                    <li class="mb-2">
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-paperclip"></i> {{ $attachment->file_name }}
                                            <small class="text-muted">({{ $attachment->file_size_formatted }})</small>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Updates Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Updates & Comments</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.addUpdate', $ticket) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Add Update</label>
                            <textarea name="update_message" rows="3" class="form-control" required placeholder="Write your update here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-comment"></i> Add Update
                        </button>
                    </form>
                    <hr>

                    <div class="updates-timeline">
                        @forelse($ticket->updates as $update)
                            <div class="update-item mb-3 p-3 border-start border-3 
                                @if($update->update_type == 'system') border-info
                                @elseif($update->update_type == 'status_change') border-warning
                                @elseif($update->update_type == 'assignment') border-success
                                @else border-primary @endif
                            ">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $update->user->name }}</strong>
                                        <span class="badge bg-{{ 
                                            $update->update_type == 'system' ? 'info' :
                                            ($update->update_type == 'status_change' ? 'warning' :
                                            ($update->update_type == 'assignment' ? 'success' : 'primary'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $update->update_type)) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $update->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 mt-2">{{ $update->update_message }}</p>
                                @if($update->old_status && $update->new_status)
                                    <small class="text-muted">
                                        Status: <strong>{{ ucfirst($update->old_status) }}</strong> → <strong>{{ ucfirst($update->new_status) }}</strong>
                                    </small>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted text-center">No updates yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ticket Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Ticket Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Type:</strong> 
                        <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $ticket->ticket_type)) }}</span>
                    </p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $ticket->status_color }}">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
                    </p>
                    <p><strong>Priority:</strong> 
                        <span class="badge bg-{{ $ticket->priority_color }}">{{ ucfirst($ticket->priority) }}</span>
                    </p>
                    <p><strong>Requester:</strong> {{ $ticket->user->name }}</p>
                    <p><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}</p>
                    @if($ticket->assigned_at)
                        <p><strong>Assigned:</strong> {{ $ticket->assigned_at->format('M d, Y H:i') }}</p>
                    @endif
                    @if($ticket->completed_at)
                        <p><strong>Completed:</strong> {{ $ticket->completed_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <!-- Assignment Section -->
            @if(!in_array($ticket->status, ['completed', 'cancelled']))
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Assign Ticket</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Assign To Admin</label>
                                <select name="assigned_to" class="form-select" required>
                                    <option value="">Select Admin</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-check"></i> Assign Ticket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Vehicle Assignment (for vehicle support tickets) -->
                @if($ticket->isVehicleSupport())
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Assign Vehicle & Driver</h6>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-success w-100" onclick="initAssignmentModal({{ $ticket->id }})" data-bs-toggle="modal" data-bs-target="#assignmentModal">
                                <i class="fas fa-car"></i> Open Assignment Dashboard
                            </button>
                            <small class="text-muted d-block mt-2">
                                View real-time availability and assign resources
                            </small>
                        </div>
                    </div>
                @endif

                <!-- Status Update -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tickets.updateStatus', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">New Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="assigned" {{ $ticket->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $ticket->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Update Message (Optional)</label>
                                <textarea name="update_message" rows="2" class="form-control" placeholder="Add a note about this status change..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-sync"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Include Assignment Modal -->
@include('TicketManagement.partials.assignment-modal')

@endsection
