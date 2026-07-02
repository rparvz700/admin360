@extends('Partials.app', ['activeMenu' => 'tickets'])
@section('title')
    View Ticket
@endsection

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .trip-timeline-container {
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e3e6f0;
        }
        .pulse-dot {
            width: 10px;
            height: 10px;
            background-color: #e74a3b;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7);
            animation: pulse-red 2s infinite;
            vertical-align: middle;
            margin-right: 4px;
        }
        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(231, 74, 59, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            }
        }

        /* Realistic Road Track Styling */
        .road-track {
            position: relative;
            height: 28px;
            background: #2b303a; /* Asphalt gray */
            border-radius: 14px;
            border: 2px solid #1a1e24;
            overflow: visible; /* to allow markers and car to overflow */
            display: flex;
            align-items: center;
            box-shadow: inset 0 3px 6px rgba(0,0,0,0.6), 0 2px 4px rgba(0,0,0,0.15);
        }
        .road-line {
            position: absolute;
            top: 50%;
            left: 15px;
            right: 15px;
            height: 2px;
            transform: translateY(-50%);
            background: repeating-linear-gradient(90deg, #ffc107 0px, #ffc107 8px, transparent 8px, transparent 16px);
            z-index: 1;
        }
        .road-progress-fill {
            position: absolute;
            height: 100%;
            left: 0;
            border-radius: 12px 0 0 12px;
            background: linear-gradient(90deg, rgba(13, 110, 253, 0.45), rgba(13, 110, 253, 0.8));
            transition: width 0.5s ease-out;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
            z-index: 2;
        }
        .road-marker {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            width: 26px;
            height: 26px;
            background: #fff;
            border-radius: 50%;
            border: 2px solid #1a1e24;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 6px rgba(0,0,0,0.25);
        }
        .road-marker.start-marker {
            left: -10px;
        }
        .road-marker.end-marker {
            right: -10px;
        }
        .road-car-container {
            position: absolute;
            top: 50%;
            left: 0%; /* updated by JS */
            transform: translate(-50%, -50%);
            z-index: 10;
            transition: left 0.5s ease-out;
            pointer-events: none;
            display: flex;
            align-items: center;
        }
        .driving-car {
            font-size: 20px;
            color: #e74a3b; /* Vibrant red */
            text-shadow: 0 0 8px rgba(231, 74, 59, 0.6);
            animation: car-wiggle 0.12s ease-in-out infinite alternate, car-bounce 0.35s ease-in-out infinite alternate;
            display: inline-block;
        }
        
        /* Exhaust smoke animation */
        .car-exhaust {
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 2px;
            padding-right: 4px;
        }
        .exhaust-bubble {
            width: 4px;
            height: 4px;
            background-color: rgba(180, 185, 195, 0.65);
            border-radius: 50%;
            animation: smoke-fade 0.5s infinite;
        }
        .exhaust-bubble:nth-child(2) {
            animation-delay: 0.15s;
            width: 6px;
            height: 6px;
        }
        .exhaust-bubble:nth-child(3) {
            animation-delay: 0.3s;
            width: 8px;
            height: 8px;
        }
        
        @keyframes car-wiggle {
            0% { transform: rotate(-1.5deg); }
            100% { transform: rotate(1.5deg); }
        }
        @keyframes car-bounce {
            0% { transform: translateY(-0.5px); }
            100% { transform: translateY(-2.5px); }
        }
        @keyframes smoke-fade {
            0% {
                transform: scale(0.2) translate(0, 0);
                opacity: 0.8;
            }
            100% {
                transform: scale(1.4) translate(-12px, -3px);
                opacity: 0;
            }
        }
        .fs-xs {
            font-size: 0.75rem;
        }
        .text-xs {
            font-size: 0.7rem;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid">
        <div class="row mb-4 mt-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.tickets.index') }}">Tickets</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ $ticket->ticket_number }}
                        </li>
                    </ol>
                </nav>

            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
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

                        @if ($ticket->isVehicleSupport())
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h4 class="card-title">Vehicle Support Details</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Vehicle Type:</strong>
                                                {{ $ticket->vehicleType->type_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Passengers:</strong> {{ $ticket->passenger_count }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Start:</strong>
                                                {{ $ticket->trip_start_datetime->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>End:</strong> {{ $ticket->trip_end_datetime->format('M d, Y H:i') }}
                                            </p>
                                        </div>
                                        <div class="col-md-12">
                                            <p><strong>Purpose:</strong> {{ $ticket->trip_purpose }}</p>
                                        </div>
                                        <div class="col-md-12">
                                            @if ($ticket->trip_location_details)
                                                <h6>Trip Route:</h6>
                                                @foreach ($ticket->trip_location_details as $index => $location)
                                                    <div class="trip-stop">
                                                        <span class="badge bg-info fs-5">Stop {{ $index + 1 }}</span>
                                                        <br><span
                                                            class="badge bg-success">Start:</span>&nbsp;{{ $location['start'] }}
                                                        <br><span
                                                            class="badge bg-danger">End:</span>&nbsp;{{ $location['end'] }}<br>
                                                        @if (isset($location['notes']))
                                                            <small>{{ $location['notes'] }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    @if ($ticket->assignedDriver || $ticket->assignedVehicle)
                                        <hr>
                                        <h6 class="text-success">Current Assignment</h6>
                                        @if ($ticket->assignedDriver)
                                            <p><strong>Driver:</strong> {{ $ticket->assignedDriver->name }}
                                                {{ $ticket->assignedDriver->sur_name }}</p>
                                            <p><strong>Contact:</strong> {{ $ticket->assignedDriver->phone }}</p>
                                        @endif
                                        @if ($ticket->assignedVehicle)
                                            <p><strong>Vehicle:</strong> {{ $ticket->assignedVehicle->brand }}
                                                {{ $ticket->assignedVehicle->model }}</p>
                                            <p><strong>Registration:</strong>
                                                {{ $ticket->assignedVehicle->registration_number }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($ticket->isAssetRequest())
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Asset Request Details</h6>
                                    <p><strong>Category:</strong> {{ $ticket->assetCategory->category_name ?? 'N/A' }}</p>
                                    <p><strong>Requested Asset:</strong> {{ $ticket->requested_asset_name }}</p>
                                    <p><strong>Location:</strong> {{ $ticket->floor->building->building_name ?? '' }} -
                                        {{ $ticket->floor->floor_label ?? '' }}</p>
                                    <p><strong>Specific Location:</strong> {{ $ticket->location_within_floor }}</p>
                                    @if ($ticket->asset_specifications)
                                        <p><strong>Specifications:</strong> {{ $ticket->asset_specifications }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($ticket->isAssetRepair())
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Asset Repair Details</h6>
                                    <p><strong>Asset Tag:</strong> {{ $ticket->asset->asset_tag ?? 'N/A' }}</p>
                                    <p><strong>Asset Name:</strong> {{ $ticket->asset->asset_name ?? 'N/A' }}</p>
                                    <p><strong>Category:</strong> {{ $ticket->asset->category->category_name ?? 'N/A' }}
                                    </p>
                                    <p><strong>Location:</strong>
                                        {{ $ticket->asset->floor->building->building_name ?? '' }} -
                                        {{ $ticket->asset->floor->floor_label ?? '' }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($ticket->attachments->count() > 0)
                            <div class="mt-3">
                                <h6>Attachments</h6>
                                <ul class="list-unstyled">
                                    @foreach ($ticket->attachments as $attachment)
                                        <li class="mb-2">
                                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                                class="text-decoration-none">
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
                                <div
                                    class="update-item mb-3 p-3 border-start border-3 
                                @if ($update->update_type == 'system') border-info
                                @elseif($update->update_type == 'status_change') border-warning
                                @elseif($update->update_type == 'assignment') border-success
                                @else border-primary @endif
                            ">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $update->user->name }}</strong>
                                            <span
                                                class="badge bg-{{ $update->update_type == 'system'
                                                    ? 'info'
                                                    : ($update->update_type == 'status_change'
                                                        ? 'warning'
                                                        : ($update->update_type == 'assignment'
                                                            ? 'success'
                                                            : 'primary')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $update->update_type)) }}
                                            </span>
                                        </div>
                                        <small class="text-muted">{{ $update->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 mt-2">{{ $update->update_message }}</p>
                                    @if ($update->old_status && $update->new_status)
                                        <small class="text-muted">
                                            Status: <strong>{{ ucfirst($update->old_status) }}</strong> →
                                            <strong>{{ ucfirst($update->new_status) }}</strong>
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
                            <span
                                class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $ticket->ticket_type)) }}</span>
                        </p>
                        <p><strong>Status:</strong>
                            <span
                                class="badge bg-{{ $ticket->status_color }}">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
                        </p>
                        <p><strong>Priority:</strong>
                            <span class="badge bg-{{ $ticket->priority_color }}">{{ ucfirst($ticket->priority) }}</span>
                        </p>
                        <p><strong>Requester:</strong> {{ $ticket->user->name }}</p>
                        <p><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}</p>
                        @if ($ticket->assigned_at)
                            <p><strong>Assigned:</strong> {{ $ticket->assigned_at->format('M d, Y H:i') }}</p>
                        @endif
                        @if ($ticket->completed_at)
                            <p><strong>Completed:</strong> {{ $ticket->completed_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Assignment Section -->
                @if (!in_array($ticket->status, ['completed', 'cancelled']))
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
                                        @foreach ($admins as $admin)
                                            <option value="{{ $admin->id }}"
                                                {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
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
                    @if ($ticket->isVehicleSupport())
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Assign Vehicle & Driver</h6>
                            </div>
                            <div class="card-body">
                                @if ($ticket->latestVehicleAssignment && in_array($ticket->latestVehicleAssignment->status, ['completed', 'complete']))
                                    <button type="button" class="btn btn-info w-100"
                                        data-bs-toggle="modal" data-bs-target="#tripDetailsModal"
                                        onclick="showTripDetails()">
                                        <i class="fas fa-route"></i> Trip Details
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success w-100"
                                        onclick="initAssignmentModal({{ $ticket->id }})" data-bs-toggle="modal"
                                        data-bs-target="#assignmentModal">
                                        <i class="fas fa-car"></i> Open Assignment Dashboard
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        View real-time availability and assign resources
                                    </small>
                                @endif


                                @if ($ticket->latestVehicleAssignment && $ticket->latestVehicleAssignment->status === 'scheduled')
                                    <form
                                        action="{{ route('admin.tickets.trip.start', $ticket->scheduledVehicleAssignment->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-success w-100 btn-sm mt-4">
                                            <i class="fas fa-play"></i> Trip Start
                                        </button>
                                    </form>
                                @endif

                                @if ($ticket->latestVehicleAssignment && $ticket->latestVehicleAssignment->status === 'active')
                                    <!-- Animated Timeline -->
                                    <div class="trip-timeline-container my-3 p-4 border rounded bg-white shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-danger d-flex align-items-center px-2 py-1 fs-7">
                                                <span class="pulse-dot"></span> Live Trip
                                            </span>
                                            <span class="text-primary fw-bold" id="trip-progress-percent">0%</span>
                                        </div>

                                        <!-- Realistic Road Track -->
                                        <div class="position-relative my-4 py-2">
                                            <div class="road-track">
                                                <!-- Road yellow dashed line -->
                                                <div class="road-line"></div>
                                                
                                                <!-- Progress Fill (Glowing Blue) -->
                                                <div class="road-progress-fill" id="trip-progress-fill" style="width: 0%;"></div>
                                                
                                                <!-- Start marker -->
                                                <div class="road-marker start-marker" title="Start Point">
                                                    <i class="fas fa-play text-success fs-xs"></i>
                                                </div>

                                                <!-- End marker -->
                                                <div class="road-marker end-marker" title="End Point">
                                                    <i class="fas fa-flag-checkered text-dark fs-xs"></i>
                                                </div>

                                                <!-- Animated Car Container -->
                                                <div class="road-car-container" id="trip-car-container" style="left: 0%;">
                                                    <div class="car-exhaust">
                                                        <div class="exhaust-bubble"></div>
                                                        <div class="exhaust-bubble"></div>
                                                        <div class="exhaust-bubble"></div>
                                                    </div>
                                                    <div class="driving-car">
                                                        <i class="fas fa-car-side"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between text-muted small mb-3">
                                            <div>
                                                <span class="d-block text-xs text-uppercase tracking-wider">Start Time</span>
                                                <strong class="text-dark">{{ $ticket->latestVehicleAssignment->start_datetime->format('h:i A') }}</strong>
                                            </div>
                                            <div class="text-end">
                                                <span class="d-block text-xs text-uppercase tracking-wider">Est. Arrival</span>
                                                <strong class="text-dark">{{ $ticket->latestVehicleAssignment->end_datetime->format('h:i A') }}</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center bg-light p-2 border rounded">
                                            <span class="text-muted d-block small">Elapsed Duration</span>
                                            <h5 class="mb-0 text-primary font-monospace" id="trip-elapsed-timer">00:00:00</h5>
                                        </div>
                                    </div>

                                    <form
                                        action="{{ route('admin.tickets.trip.completed', $ticket->activeVehicleAssignment->id) }}"
                                        method="POST" class="mt-3">
                                        @csrf
                                        @method('PATCH')

                                        <div class="mb-3">
                                            <label for="remarks" class="form-label small text-muted">Remarks (Optional)</label>
                                            <textarea name="remarks" id="remarks" rows="2" class="form-control form-control-sm" placeholder="Enter trip completion remarks..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-warning w-100 btn-sm">
                                            <i class="fas fa-pause"></i> Trip End
                                        </button>
                                    </form>
                                @endif

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
                                        <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="assigned" {{ $ticket->status == 'assigned' ? 'selected' : '' }}>
                                            Assigned</option>
                                        <option value="in_progress"
                                            {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $ticket->status == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                        <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Update Message (Optional)</label>
                                    <textarea name="update_message" rows="2" class="form-control"
                                        placeholder="Add a note about this status change..."></textarea>
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
    @include('TicketManagement.partials.trip-details-modal')

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Trip Details scripts inside content block -->
    <script>
        let tripDetailsData = null;
        let tripMap = null;
        let actualPolyline = null;
        let plannedPolyline = null;
        let tripMarkers = [];

        async function getRoadPath(points) {
            if (!points || points.length < 2) return points;
            
            // OSRM coordinates format is longitude,latitude separated by semicolons
            const coordString = points.map(p => `${p[1]},${p[0]}`).join(';');
            const url = `https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=geojson`;
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                    return data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                }
            } catch (e) {
                console.error("OSRM Routing API failed:", e);
            }
            return points;
        }

        function showTripDetails() {
            if (!tripDetailsData) {
                console.error("Trip details data is not available.");
                return;
            }
            try {
                document.getElementById('dt-ticket-no').textContent = tripDetailsData.ticketNo || 'N/A';
                document.getElementById('dt-purpose').textContent = tripDetailsData.purpose || 'N/A';
                document.getElementById('dt-passengers').textContent = tripDetailsData.passengers || 'N/A';
                
                const priorityBadge = document.getElementById('dt-priority');
                if (priorityBadge) {
                    priorityBadge.textContent = (tripDetailsData.priority || 'medium').toUpperCase();
                    priorityBadge.className = 'badge bg-' + (tripDetailsData.priorityColor || 'secondary');
                }
                
                const routeDiv = document.getElementById('dt-planned-route');
                if (routeDiv) {
                    routeDiv.innerHTML = '';
                    if (tripDetailsData.plannedLocations && tripDetailsData.plannedLocations.length > 0) {
                        tripDetailsData.plannedLocations.forEach((loc, idx) => {
                            const start = loc.start || 'Start';
                            const end = loc.end || 'End';
                            routeDiv.innerHTML += `<div><strong>Leg ${idx + 1}:</strong> ${start} &rarr; ${end}</div>`;
                        });
                    } else {
                        routeDiv.textContent = 'No planned route details available.';
                    }
                }
                
                document.getElementById('dt-vehicle').textContent = tripDetailsData.vehicle || 'N/A';
                document.getElementById('dt-driver').textContent = tripDetailsData.driver || 'N/A';
                document.getElementById('dt-driver-phone').textContent = tripDetailsData.driverPhone || 'N/A';
                document.getElementById('dt-seating').textContent = tripDetailsData.seating || 'N/A';
                
                document.getElementById('dt-start-odo').textContent = tripDetailsData.startOdo || 'N/A';
                document.getElementById('dt-end-odo').textContent = tripDetailsData.endOdo || 'N/A';
                
                const distanceSpan = document.getElementById('dt-distance');
                if (distanceSpan) {
                    if (tripDetailsData.startOdo !== 'N/A' && tripDetailsData.endOdo !== 'N/A' && !isNaN(tripDetailsData.startOdo) && !isNaN(tripDetailsData.endOdo)) {
                        const dist = parseFloat(tripDetailsData.endOdo) - parseFloat(tripDetailsData.startOdo);
                        distanceSpan.textContent = dist.toFixed(1) + ' km';
                        distanceSpan.className = 'badge bg-success';
                    } else {
                        distanceSpan.textContent = 'N/A';
                        distanceSpan.className = 'badge bg-secondary';
                    }
                }
                
                document.getElementById('dt-remarks').textContent = tripDetailsData.remarks || 'No remarks provided';
                
                const trackingStatus = document.getElementById('dt-tracking-status');
                if (trackingStatus) {
                    const hasTracking = Array.isArray(tripDetailsData.locationTracking) && tripDetailsData.locationTracking.length > 0;
                    trackingStatus.textContent = hasTracking ? 'GPS Tracked' : 'Planned Route Only';
                    trackingStatus.className = 'badge bg-' + (hasTracking ? 'success' : 'warning');
                }
            } catch (e) {
                console.error("Error displaying trip details:", e);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('tripDetailsModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', async function () {
                    if (!tripDetailsData) return;
                    try {
                        if (!tripMap) {
                            tripMap = L.map('trip-map');
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(tripMap);
                        } else {
                            tripMap.invalidateSize();
                        }
                        
                        if (actualPolyline) {
                            tripMap.removeLayer(actualPolyline);
                            actualPolyline = null;
                        }
                        if (plannedPolyline) {
                            tripMap.removeLayer(plannedPolyline);
                            plannedPolyline = null;
                        }
                        tripMarkers.forEach(m => tripMap.removeLayer(m));
                        tripMarkers = [];
                        
                        const mapBounds = [];
                        
                        let trackingPoints = tripDetailsData.locationTracking;
                        if (typeof trackingPoints === 'string') {
                            try {
                                trackingPoints = JSON.parse(trackingPoints);
                            } catch(err) {
                                trackingPoints = [];
                            }
                        }
                        
                        if (Array.isArray(trackingPoints) && trackingPoints.length > 0) {
                            const points = trackingPoints.map(pt => [pt.latitude, pt.longitude]);
                            points.forEach(p => mapBounds.push(p));

                            // Query OSRM to get road-wise coordinates
                            const roadPoints = await getRoadPath(points);
                            
                            actualPolyline = L.polyline(roadPoints, { 
                                color: '#0d6efd', // Blue
                                weight: 6, 
                                opacity: 0.9,
                                lineJoin: 'round',
                                lineCap: 'round'
                            }).addTo(tripMap);
                            
                            const startPt = points[0];
                            const startMarker = L.marker(startPt, {
                                icon: L.divIcon({
                                    className: 'custom-map-marker-start',
                                    html: '<i class="fas fa-play-circle" style="color: #0d6efd; font-size: 22px; background: white; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></i>',
                                    iconSize: [22, 22],
                                    iconAnchor: [11, 11],
                                    popupAnchor: [0, -11]
                                })
                            }).bindPopup('Actual GPS Start').addTo(tripMap);
                            tripMarkers.push(startMarker);
                            
                            const endPt = points[points.length - 1];
                            const endMarker = L.marker(endPt, {
                                icon: L.divIcon({
                                    className: 'custom-map-marker-end',
                                    html: '<i class="fas fa-stop-circle" style="color: #dc3545; font-size: 22px; background: white; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></i>',
                                    iconSize: [22, 22],
                                    iconAnchor: [11, 11],
                                    popupAnchor: [0, -11]
                                })
                            }).bindPopup('Actual GPS End').addTo(tripMap);
                            tripMarkers.push(endMarker);
                        }
                        
                        if (tripDetailsData.plannedCoordinates && tripDetailsData.plannedCoordinates.length > 0) {
                            const plannedPoints = [];
                            
                            tripDetailsData.plannedCoordinates.forEach((coord) => {
                                if (coord.start && coord.start.latitude && coord.start.longitude) {
                                    plannedPoints.push({
                                        lat: parseFloat(coord.start.latitude),
                                        lng: parseFloat(coord.start.longitude),
                                        address: coord.start.address || ''
                                    });
                                }
                                if (coord.end && coord.end.latitude && coord.end.longitude) {
                                    plannedPoints.push({
                                        lat: parseFloat(coord.end.latitude),
                                        lng: parseFloat(coord.end.longitude),
                                        address: coord.end.address || ''
                                    });
                                }
                            });

                            if (plannedPoints.length > 0) {
                                plannedPoints.forEach(pt => mapBounds.push([pt.lat, pt.lng]));

                                // 1. First Start Marker of the entire list
                                const firstPt = plannedPoints[0];
                                const startMarker = L.marker([firstPt.lat, firstPt.lng], {
                                    icon: L.divIcon({
                                        className: 'custom-fa-marker-start',
                                        html: '<i class="fas fa-map-marker-alt" style="color: #28a745; font-size: 28px; text-shadow: 0 0 3px rgba(0,0,0,0.3);"></i>',
                                        iconSize: [28, 28],
                                        iconAnchor: [14, 28],
                                        popupAnchor: [0, -28]
                                    })
                                }).bindPopup(`Planned Start: ${firstPt.address}`).addTo(tripMap);
                                tripMarkers.push(startMarker);

                                // 2. Last End Marker of the entire list
                                const lastPt = plannedPoints[plannedPoints.length - 1];
                                const endMarker = L.marker([lastPt.lat, lastPt.lng], {
                                    icon: L.divIcon({
                                        className: 'custom-fa-marker-end',
                                        html: '<i class="fas fa-flag-checkered" style="color: #1a1a1a; font-size: 26px; text-shadow: 0 0 3px rgba(0,0,0,0.3);"></i>',
                                        iconSize: [26, 26],
                                        iconAnchor: [13, 26],
                                        popupAnchor: [0, -26]
                                    })
                                }).bindPopup(`Planned End: ${lastPt.address}`).addTo(tripMap);
                                tripMarkers.push(endMarker);

                                // 3. Intermediate Stop Waypoints (if any)
                                for (let i = 1; i < plannedPoints.length - 1; i++) {
                                    const pt = plannedPoints[i];
                                    const prevPt = plannedPoints[i - 1];
                                    
                                    // Skip duplicate intermediate transitions (e.g., Leg 1 end equals Leg 2 start)
                                    if (Math.abs(pt.lat - prevPt.lat) < 0.0001 && Math.abs(pt.lng - prevPt.lng) < 0.0001) {
                                        continue;
                                    }
                                    
                                    const wpMarker = L.marker([pt.lat, pt.lng], {
                                        icon: L.divIcon({
                                            className: 'custom-fa-marker-waypoint',
                                            html: '<div style="background-color: #6c757d; width: 10px; height: 10px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3);"></div>',
                                            iconSize: [10, 10],
                                            iconAnchor: [5, 5]
                                        })
                                    }).bindPopup(`Stop Waypoint: ${pt.address}`).addTo(tripMap);
                                    tripMarkers.push(wpMarker);
                                }

                                // Draw planned route path line (routed road-wise)
                                if (plannedPoints.length > 1) {
                                    const pathCoords = plannedPoints.map(pt => [pt.lat, pt.lng]);
                                    const plannedRoadPoints = await getRoadPath(pathCoords);
                                    
                                    plannedPolyline = L.polyline(plannedRoadPoints, { 
                                        color: '#0d6efd', // Blue
                                        weight: 4, 
                                        dashArray: '6, 12', 
                                        opacity: 0.8 
                                    }).addTo(tripMap);
                                }
                            }
                        }
                        
                        if (mapBounds.length > 0) {
                            tripMap.fitBounds(mapBounds, { padding: [50, 50] });
                        } else {
                            tripMap.setView([23.7516691, 90.3901753], 13);
                        }
                    } catch (e) {
                        console.error("Error loading map:", e);
                    }
                });
            }
        });
    </script>

    @if ($ticket->latestVehicleAssignment)
    <script>
        tripDetailsData = {
            ticketNo: @json($ticket->ticket_number),
            purpose: @json($ticket->trip_purpose ?? 'N/A'),
            passengers: @json($ticket->passenger_count ?? 'N/A'),
            priority: @json($ticket->priority ?? 'medium'),
            priorityColor: @json($ticket->priority_color ?? 'secondary'),
            plannedLocations: @json($ticket->trip_location_details ?? []),
            plannedCoordinates: @json($ticket->trip_location_coordinates ?? []),
            vehicle: @json($ticket->latestVehicleAssignment->vehicle ? $ticket->latestVehicleAssignment->vehicle->registration_number . ' (' . $ticket->latestVehicleAssignment->vehicle->brand . ' ' . $ticket->latestVehicleAssignment->vehicle->model . ')' : ($ticket->latestVehicleAssignment->notes ?? 'Ad-hoc vehicle')),
            driver: @json($ticket->latestVehicleAssignment->driver ? $ticket->latestVehicleAssignment->driver->full_name : 'Ad-hoc driver'),
            driverPhone: @json($ticket->latestVehicleAssignment->driver ? $ticket->latestVehicleAssignment->driver->phone : 'N/A'),
            seating: @json($ticket->latestVehicleAssignment->vehicle ? $ticket->latestVehicleAssignment->vehicle->seating_capacity : 'N/A'),
            startOdo: @json($ticket->latestVehicleAssignment->start_odo_meter ?? 'N/A'),
            endOdo: @json($ticket->latestVehicleAssignment->end_odo_meter ?? 'N/A'),
            remarks: @json($ticket->latestVehicleAssignment->remarks ?? 'No remarks provided'),
            locationTracking: @json($ticket->latestVehicleAssignment->location_tracking ?? [])
        };
    </script>
    @endif

    @if ($ticket->latestVehicleAssignment && $ticket->latestVehicleAssignment->status === 'active')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startTimeStr = "{{ $ticket->latestVehicleAssignment->start_datetime->toIso8601String() }}";
            const endTimeStr = "{{ $ticket->latestVehicleAssignment->end_datetime->toIso8601String() }}";
            
            const startTime = new Date(startTimeStr);
            const endTime = new Date(endTimeStr);
            
            const timerElement = document.getElementById('trip-elapsed-timer');
            const roadProgressFill = document.getElementById('trip-progress-fill');
            const roadCarContainer = document.getElementById('trip-car-container');
            const progressPercentText = document.getElementById('trip-progress-percent');
            
            function tick() {
                const now = new Date();
                const elapsedMs = now - startTime;
                
                if (elapsedMs < 0) {
                    if (timerElement) timerElement.textContent = "Scheduled to start soon";
                    if (roadProgressFill) roadProgressFill.style.width = '0%';
                    if (roadCarContainer) roadCarContainer.style.left = '0%';
                    if (progressPercentText) progressPercentText.textContent = '0%';
                    return;
                }
                
                const totalSecs = Math.floor(elapsedMs / 1000);
                const hours = Math.floor(totalSecs / 3600);
                const minutes = Math.floor((totalSecs % 3600) / 60);
                const seconds = totalSecs % 60;
                
                if (timerElement) {
                    timerElement.textContent = 
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
                    
                const totalDurationMs = endTime - startTime;
                let percent = (elapsedMs / totalDurationMs) * 100;
                
                if (percent >= 100) {
                    percent = 100;
                    const drivingCarElement = document.querySelector('.driving-car');
                    if (drivingCarElement) {
                        drivingCarElement.style.animation = 'none';
                    }
                    const carExhaustElement = document.querySelector('.car-exhaust');
                    if (carExhaustElement) {
                        carExhaustElement.style.display = 'none';
                    }
                }
                
                if (roadProgressFill) roadProgressFill.style.width = percent.toFixed(2) + '%';
                if (roadCarContainer) roadCarContainer.style.left = percent.toFixed(2) + '%';
                if (progressPercentText) progressPercentText.textContent = Math.round(percent) + '%';
            }
            
            tick();
            setInterval(tick, 1000);
        });
    </script>
    @endif

@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        One.helpersOnLoad(['jq-select2']);
    </script>
@endsection
