@extends('Partials.app', ['activeMenu' => 'tickets'])
@section('title')
    Manage Ticket {{ $ticket->ticket_number }} - {{ config('app.name') }}
@endsection

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .odo-thumbnail {
            width: 100%;
            max-width: 140px;
            height: 95px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .odo-thumbnail:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #0d6efd;
        }
        .timeline-item-custom {
            position: relative;
            padding-left: 24px;
            margin-bottom: 18px;
            border-left: 2px solid #e2e8f0;
        }
        .timeline-dot {
            position: absolute;
            left: -6px;
            top: 4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
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
            background: #2b303a;
            border-radius: 14px;
            border: 2px solid #1a1e24;
            overflow: visible;
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
            left: 0%;
            transform: translate(-50%, -50%);
            z-index: 10;
            transition: left 0.5s ease-out;
            pointer-events: none;
            display: flex;
            align-items: center;
        }
        .driving-car {
            font-size: 20px;
            color: #e74a3b;
            text-shadow: 0 0 8px rgba(231, 74, 59, 0.6);
            animation: car-wiggle 0.12s ease-in-out infinite alternate, car-bounce 0.35s ease-in-out infinite alternate;
            display: inline-block;
        }
        
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
    </style>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        Ticket {{ $ticket->ticket_number }}
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Admin management panel &bull; Submitted by <strong>{{ $ticket->user->name ?? 'User' }}</strong> on {{ $ticket->created_at->format('M d, Y h:i A') }}
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.tickets.index') }}">Tickets</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            {{ $ticket->ticket_number }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Main Content -->
            <div class="col-lg-8">
                <!-- Ticket Overview Block -->
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-file-lines text-primary me-1"></i> Ticket Information
                        </h3>
                        <div class="block-options">
                            <span class="badge bg-{{ $ticket->status_color }} fs-xs px-2 py-1">
                                {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            <span class="badge bg-{{ $ticket->priority_color }} fs-xs px-2 py-1 ms-1">
                                {{ ucfirst($ticket->priority) }} Priority
                            </span>
                        </div>
                    </div>
                    <div class="block-content">
                        <h4 class="h5 fw-bold mb-2">{{ $ticket->title }}</h4>
                        <p class="text-muted fs-sm mb-4">
                            <i class="fa fa-user me-1"></i> <strong>Requester:</strong> {{ $ticket->user->name }} ({{ $ticket->user->email }})
                            @if($ticket->company_id)
                                &nbsp;|&nbsp; <i class="fa fa-building me-1"></i> <strong>Company:</strong> {{ $ticket->company_id == 1 ? 'SComm' : 'STL' }}
                            @endif
                            @if($ticket->project_name)
                                &nbsp;|&nbsp; <i class="fa fa-diagram-project me-1"></i> <strong>Project:</strong> {{ $ticket->project_name }}
                            @endif
                        </p>

                        <div class="mb-4">
                            <h5 class="fs-sm text-uppercase fw-semibold text-muted mb-2">Description</h5>
                            <div class="p-3 bg-body-light rounded border fs-sm text-dark" style="white-space: pre-wrap;">{{ $ticket->description }}</div>
                        </div>

                        <!-- Vehicle Support Details -->
                        @if ($ticket->isVehicleSupport())
                            <div class="block block-rounded block-bordered mb-4">
                                <div class="block-header bg-body-light py-2">
                                    <h4 class="block-title fs-sm fw-bold text-primary">
                                        <i class="fa fa-car me-1"></i> Vehicle Support Specifications
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-3 mb-3">
                                        <div class="col-sm-6 col-md-3">
                                            <span class="text-muted d-block fs-xs text-uppercase">Vehicle Type</span>
                                            <strong>{{ $ticket->vehicleType->type_name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <span class="text-muted d-block fs-xs text-uppercase">Passengers</span>
                                            <strong>{{ $ticket->passenger_count }} Person(s)</strong>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <span class="text-muted d-block fs-xs text-uppercase">Start Time</span>
                                            <strong>{{ $ticket->trip_start_datetime ? $ticket->trip_start_datetime->format('M d, Y h:i A') : 'N/A' }}</strong>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <span class="text-muted d-block fs-xs text-uppercase">End Time</span>
                                            <strong>{{ $ticket->trip_end_datetime ? $ticket->trip_end_datetime->format('M d, Y h:i A') : 'N/A' }}</strong>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted d-block fs-xs text-uppercase">Trip Purpose</span>
                                            <div class="p-2 bg-body-light rounded border text-dark">{{ $ticket->trip_purpose ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    @if ($ticket->trip_location_details || $ticket->trip_location_coordinates)
                                        <h5 class="fs-xs fw-bold text-uppercase text-muted mb-2">Trip Stoppages / Route Legs</h5>
                                        <div class="row g-2 mb-3">
                                            @foreach ($ticket->formatted_trip_locations as $index => $location)
                                                <div class="col-12">
                                                    <div class="p-2 bg-body-light rounded border d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-primary fs-xs">Stop {{ $location['stop_order'] ?? ($index + 1) }}</span>
                                                            <div>
                                                                <span class="badge bg-success-light text-success me-1"><i class="fa fa-map-pin"></i> Start</span>
                                                                {{ $location['start']['address'] ?? 'N/A' }}
                                                                @if (!empty($location['start']['latitude']) && !empty($location['start']['longitude']))
                                                                    <small class="text-muted ms-1">(Lat: {{ number_format($location['start']['latitude'], 6) }}, Lng: {{ number_format($location['start']['longitude'], 6) }})</small>
                                                                @endif
                                                                &nbsp;&rarr;&nbsp;
                                                                <span class="badge bg-danger-light text-danger me-1"><i class="fa fa-flag-checkered"></i> End</span>
                                                                {{ $location['end']['address'] ?? 'N/A' }}
                                                                @if (!empty($location['end']['latitude']) && !empty($location['end']['longitude']))
                                                                    <small class="text-muted ms-1">(Lat: {{ number_format($location['end']['latitude'], 6) }}, Lng: {{ number_format($location['end']['longitude'], 6) }})</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Current Assignment & Odometer -->
                                    @if ($ticket->latestVehicleAssignment)
                                        <hr class="my-3">
                                        <h5 class="fs-sm fw-bold text-success mb-3">
                                            <i class="fa fa-clipboard-check me-1"></i> Current Assignment & Odometer Details
                                        </h5>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <div class="p-3 bg-body-light rounded border h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fa fa-user-tie fa-2x text-primary me-3"></i>
                                                        <div>
                                                            <span class="text-muted d-block fs-xs text-uppercase">Assigned Driver</span>
                                                            <h5 class="fs-sm fw-bold mb-0">
                                                                {{ $ticket->latestVehicleAssignment->driver ? $ticket->latestVehicleAssignment->driver->full_name : ($ticket->latestVehicleAssignment->notes ?? 'Ad-hoc driver') }}
                                                            </h5>
                                                            @if($ticket->latestVehicleAssignment->driver && $ticket->latestVehicleAssignment->driver->phone)
                                                                <a href="tel:{{ $ticket->latestVehicleAssignment->driver->phone }}" class="fs-xs text-primary">
                                                                    <i class="fa fa-phone me-1"></i>{{ $ticket->latestVehicleAssignment->driver->phone }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="p-3 bg-body-light rounded border h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fa fa-car-side fa-2x text-success me-3"></i>
                                                        <div>
                                                            <span class="text-muted d-block fs-xs text-uppercase">Assigned Vehicle</span>
                                                            <h5 class="fs-sm fw-bold mb-0">
                                                                {{ $ticket->latestVehicleAssignment->vehicle ? $ticket->latestVehicleAssignment->vehicle->registration_number : 'Ad-hoc Vehicle' }}
                                                            </h5>
                                                            <span class="fs-xs text-muted">
                                                                {{ $ticket->latestVehicleAssignment->vehicle ? $ticket->latestVehicleAssignment->vehicle->brand . ' ' . $ticket->latestVehicleAssignment->vehicle->model : '' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Odometer Readings & Photos -->
                                        <div class="p-3 bg-body-light rounded border">
                                            <div class="row g-3 align-items-center">
                                                <div class="col-sm-6 col-md-3 text-center text-sm-start">
                                                    <span class="text-muted d-block fs-xs text-uppercase">Start Odometer</span>
                                                    <h4 class="fs-5 fw-bold text-dark mb-1">
                                                        {{ $ticket->latestVehicleAssignment->start_odo_meter ? number_format($ticket->latestVehicleAssignment->start_odo_meter) . ' km' : 'N/A' }}
                                                    </h4>
                                                    @if($ticket->latestVehicleAssignment->start_odo_image)
                                                        <img src="{{ Storage::disk('public')->url($ticket->latestVehicleAssignment->start_odo_image) }}"
                                                             alt="Start Odometer Photo"
                                                             class="odo-thumbnail mt-1"
                                                             onclick="previewImage('{{ Storage::disk('public')->url($ticket->latestVehicleAssignment->start_odo_image) }}', 'Start Odometer Photo')">
                                                    @endif
                                                </div>

                                                <div class="col-sm-6 col-md-3 text-center text-sm-start">
                                                    <span class="text-muted d-block fs-xs text-uppercase">End Odometer</span>
                                                    <h4 class="fs-5 fw-bold text-dark mb-1">
                                                        {{ $ticket->latestVehicleAssignment->end_odo_meter ? number_format($ticket->latestVehicleAssignment->end_odo_meter) . ' km' : 'N/A' }}
                                                    </h4>
                                                    @if($ticket->latestVehicleAssignment->end_odo_image)
                                                        <img src="{{ Storage::disk('public')->url($ticket->latestVehicleAssignment->end_odo_image) }}"
                                                             alt="End Odometer Photo"
                                                             class="odo-thumbnail mt-1"
                                                             onclick="previewImage('{{ Storage::disk('public')->url($ticket->latestVehicleAssignment->end_odo_image) }}', 'End Odometer Photo')">
                                                    @endif
                                                </div>

                                                <div class="col-sm-6 col-md-3 text-center text-sm-start">
                                                    <span class="text-muted d-block fs-xs text-uppercase">Total Distance</span>
                                                    @if($ticket->latestVehicleAssignment->start_odo_meter && $ticket->latestVehicleAssignment->end_odo_meter)
                                                        <span class="badge bg-success fs-6 mt-1">
                                                            {{ number_format($ticket->latestVehicleAssignment->end_odo_meter - $ticket->latestVehicleAssignment->start_odo_meter) }} km
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary fs-xs mt-1">Pending</span>
                                                    @endif
                                                </div>

                                                <div class="col-sm-6 col-md-3 text-center text-sm-start">
                                                    <span class="text-muted d-block fs-xs text-uppercase">Trip State</span>
                                                    <span class="badge bg-{{ $ticket->latestVehicleAssignment->status === 'completed' ? 'success' : ($ticket->latestVehicleAssignment->status === 'active' ? 'warning' : 'info') }} fs-xs px-2 py-1 mt-1">
                                                        {{ ucfirst($ticket->latestVehicleAssignment->status) }}
                                                    </span>
                                                </div>

                                                @if($ticket->latestVehicleAssignment->remarks)
                                                    <div class="col-12 mt-2">
                                                        <span class="text-muted d-block fs-xs text-uppercase">Trip Remarks</span>
                                                        <div class="small text-dark p-2 bg-white rounded border">{{ $ticket->latestVehicleAssignment->remarks }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Asset Request Details -->
                        @if ($ticket->isAssetRequest())
                            <div class="block block-rounded block-bordered mb-4">
                                <div class="block-header bg-body-light py-2">
                                    <h4 class="block-title fs-sm fw-bold text-info">
                                        <i class="fa fa-box-open me-1"></i> Asset Request Details
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Category</span>
                                            <strong>{{ $ticket->assetCategory->category_name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Requested Asset</span>
                                            <strong>{{ $ticket->requested_asset_name }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Location</span>
                                            <strong>{{ $ticket->floor->building->building_name ?? '' }} - {{ $ticket->floor->floor_label ?? '' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Room / Desk</span>
                                            <strong>{{ $ticket->location_within_floor ?? 'N/A' }}</strong>
                                        </div>
                                        @if ($ticket->asset_specifications)
                                            <div class="col-12">
                                                <span class="text-muted d-block fs-xs text-uppercase">Specifications</span>
                                                <div class="p-2 bg-body-light rounded border text-dark">{{ $ticket->asset_specifications }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Asset Repair Details -->
                        @if ($ticket->isAssetRepair())
                            <div class="block block-rounded block-bordered mb-4">
                                <div class="block-header bg-body-light py-2">
                                    <h4 class="block-title fs-sm fw-bold text-warning">
                                        <i class="fa fa-wrench me-1"></i> Asset Repair Details
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Asset Tag</span>
                                            <strong>{{ $ticket->asset->asset_tag ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Asset Name</span>
                                            <strong>{{ $ticket->asset->asset_name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Category</span>
                                            <strong>{{ $ticket->asset->category->category_name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Location</span>
                                            <strong>{{ $ticket->asset->floor->building->building_name ?? '' }} - {{ $ticket->asset->floor->floor_label ?? '' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Attachments Section -->
                        @if ($ticket->attachments->count() > 0)
                            <div class="mb-4">
                                <h5 class="fs-sm text-uppercase fw-semibold text-muted mb-2">
                                    <i class="fa fa-paperclip me-1"></i> Attachments ({{ $ticket->attachments->count() }})
                                </h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($ticket->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                           class="btn btn-sm btn-alt-secondary d-flex align-items-center gap-2">
                                            <i class="fa fa-file text-primary"></i>
                                            <span class="fw-semibold">{{ $attachment->file_name }}</span>
                                            <span class="badge bg-secondary fs-xs">{{ $attachment->file_size_formatted }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- END Ticket Overview Block -->

                <!-- Updates and Comments Block -->
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-comments text-primary me-1"></i> Updates & Timeline
                        </h3>
                    </div>
                    <div class="block-content">
                        <form action="{{ route('admin.tickets.addUpdate', $ticket) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Add Internal Note / Public Update</label>
                                <textarea name="update_message" rows="3" class="form-control form-control-sm" required placeholder="Write your update or reply here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-paper-plane me-1"></i> Post Update
                            </button>
                        </form>
                        <hr class="my-4">

                        <!-- Timeline -->
                        <div class="updates-timeline">
                            @forelse($ticket->updates as $update)
                                <div class="timeline-item-custom">
                                    <div class="timeline-dot bg-{{ $update->update_type == 'system' ? 'info' : ($update->update_type == 'status_change' ? 'warning' : ($update->update_type == 'assignment' ? 'success' : 'primary')) }}"></div>
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <strong class="fs-sm">{{ $update->user->name ?? 'System' }}</strong>
                                            <span class="badge bg-{{ $update->update_type == 'system' ? 'info-light text-info' : ($update->update_type == 'status_change' ? 'warning-light text-warning' : ($update->update_type == 'assignment' ? 'success-light text-success' : 'primary-light text-primary')) }} fs-xs ms-1">
                                                {{ ucfirst(str_replace('_', ' ', $update->update_type)) }}
                                            </span>
                                        </div>
                                        <span class="fs-xs text-muted">{{ $update->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="fs-sm text-dark mb-1">{{ $update->update_message }}</p>
                                    @if ($update->old_status && $update->new_status)
                                        <span class="fs-xs text-muted">
                                            Status: <span class="badge bg-secondary">{{ ucfirst($update->old_status) }}</span> &rarr; <span class="badge bg-primary">{{ ucfirst($update->new_status) }}</span>
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted fs-sm">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i> No updates or comments recorded yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Action Blocks -->
            <div class="col-lg-4">
                <!-- Ticket Info Block -->
                <div class="block block-rounded mb-3">
                    <div class="block-header block-header-default">
                        <h3 class="block-title fs-sm">
                            <i class="fa fa-info-circle text-muted me-1"></i> Ticket Info
                        </h3>
                    </div>
                    <div class="block-content fs-sm">
                        <table class="table table-borderless table-sm mb-2">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold">Type</td>
                                    <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $ticket->ticket_type)) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Status</td>
                                    <td><span class="badge bg-{{ $ticket->status_color }}">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Priority</td>
                                    <td><span class="badge bg-{{ $ticket->priority_color }}">{{ ucfirst($ticket->priority) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Requester</td>
                                    <td>{{ $ticket->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Created</td>
                                    <td>{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @if ($ticket->assigned_at)
                                    <tr>
                                        <td class="text-muted fw-semibold">Assigned</td>
                                        <td>{{ $ticket->assigned_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @endif
                                @if ($ticket->completed_at)
                                    <tr>
                                        <td class="text-muted fw-semibold">Completed</td>
                                        <td>{{ $ticket->completed_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Admin Handler Assignment Block -->
                @if (!in_array($ticket->status, ['completed', 'cancelled']))
                    <div class="block block-rounded mb-3">
                        <div class="block-header block-header-default">
                            <h3 class="block-title fs-sm">
                                <i class="fa fa-user-gear text-primary me-1"></i> Admin Handler
                            </h3>
                        </div>
                        <div class="block-content pb-3">
                            <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label fs-xs fw-semibold">Assign To Admin Staff</label>
                                    <select name="assigned_to" class="form-select form-select-sm" required>
                                        <option value="">Select Admin</option>
                                        @foreach ($admins as $admin)
                                            <option value="{{ $admin->id }}"
                                                {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                                {{ $admin->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">
                                    <i class="fa fa-user-check me-1"></i> Assign Handler
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Vehicle & Driver Management Block -->
                @if ($ticket->isVehicleSupport())
                    <div class="block block-rounded mb-3 border-start border-4 border-primary">
                        <div class="block-header block-header-default">
                            <h3 class="block-title fs-sm text-primary">
                                <i class="fa fa-car-side me-1"></i> Vehicle & Trip Operations
                            </h3>
                        </div>
                        <div class="block-content pb-3">
                            @if ($ticket->latestVehicleAssignment)
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-body-light rounded border">
                                    <span class="text-muted fw-semibold fs-xs text-uppercase">Trip Status:</span>
                                    @if ($ticket->latestVehicleAssignment->status === 'scheduled')
                                        <span class="badge bg-info">Scheduled</span>
                                    @elseif ($ticket->latestVehicleAssignment->status === 'active')
                                        <span class="badge bg-warning text-white">Active / On Trip</span>
                                    @elseif ($ticket->latestVehicleAssignment->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif ($ticket->latestVehicleAssignment->status === 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($ticket->latestVehicleAssignment->status) }}</span>
                                    @endif
                                </div>
                            @endif

                            @if ($ticket->latestVehicleAssignment && ($ticket->status === 'completed' || in_array($ticket->latestVehicleAssignment->status, ['completed', 'complete'])))
                                <button type="button" class="btn btn-sm btn-info w-100"
                                    data-bs-toggle="modal" data-bs-target="#tripDetailsModal"
                                    onclick="showTripDetails()">
                                    <i class="fa fa-route me-1"></i> View Full Trip Details
                                </button>
                            @elseif ($ticket->status === 'completed')
                                <div class="alert alert-secondary mb-0 py-2 fs-xs text-center">
                                    <i class="fa fa-info-circle me-1"></i> No vehicle assignment was recorded.
                                </div>
                            @else
                                <button type="button" class="btn btn-sm btn-success w-100"
                                    onclick="initAssignmentModal({{ $ticket->id }})" data-bs-toggle="modal"
                                    data-bs-target="#assignmentModal">
                                    <i class="fa fa-car me-1"></i> Open Assignment Dashboard
                                </button>
                                <small class="text-muted d-block text-center mt-1 fs-xs">
                                    Assign or reassign vehicle and driver in real-time
                                </small>
                            @endif

                            <!-- Trip Start Form (if scheduled) -->
                            @if ($ticket->status !== 'completed' && $ticket->latestVehicleAssignment && $ticket->latestVehicleAssignment->status === 'scheduled')
                                <form action="{{ route('admin.tickets.trip.start', $ticket->scheduledVehicleAssignment->id) }}" method="POST" class="mt-3">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="fa fa-play me-1"></i> Start Trip (Web)
                                    </button>
                                </form>
                            @endif

                            <!-- Active Trip Live Tracker -->
                            @if ($ticket->status !== 'completed' && $ticket->latestVehicleAssignment && $ticket->latestVehicleAssignment->status === 'active')
                                <div class="my-3 p-3 border rounded bg-white shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-danger d-flex align-items-center px-2 py-1 fs-xs">
                                            <span class="pulse-dot"></span> Live Trip
                                        </span>
                                        <span class="text-primary fw-bold fs-xs" id="trip-progress-percent">0%</span>
                                    </div>

                                    <!-- Realistic Road Track -->
                                    <div class="position-relative my-4 py-2">
                                        <div class="road-track">
                                            <div class="road-line"></div>
                                            <div class="road-progress-fill" id="trip-progress-fill" style="width: 0%;"></div>
                                            <div class="road-marker start-marker" title="Start Point">
                                                <i class="fa fa-play text-success" style="font-size: 10px;"></i>
                                            </div>
                                            <div class="road-marker end-marker" title="End Point">
                                                <i class="fa fa-flag-checkered text-dark" style="font-size: 10px;"></i>
                                            </div>
                                            <div class="road-car-container" id="trip-car-container" style="left: 0%;">
                                                <div class="car-exhaust">
                                                    <div class="exhaust-bubble"></div>
                                                    <div class="exhaust-bubble"></div>
                                                    <div class="exhaust-bubble"></div>
                                                </div>
                                                <div class="driving-car">
                                                    <i class="fa fa-car-side"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between text-muted fs-xs mb-2">
                                        <div>
                                            <span class="d-block text-uppercase">Start</span>
                                            <strong class="text-dark">{{ $ticket->latestVehicleAssignment->start_datetime->format('h:i A') }}</strong>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-uppercase">Est. Arrival</span>
                                            <strong class="text-dark">{{ $ticket->latestVehicleAssignment->end_datetime->format('h:i A') }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center bg-body-light p-2 border rounded">
                                        <span class="text-muted d-block fs-xs">Elapsed Duration</span>
                                        <h5 class="mb-0 text-primary font-monospace fs-sm" id="trip-elapsed-timer">00:00:00</h5>
                                    </div>
                                </div>

                                <form action="{{ route('admin.tickets.trip.completed', $ticket->activeVehicleAssignment->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-2">
                                        <label for="remarks" class="form-label fs-xs text-muted mb-1">Completion Remarks</label>
                                        <textarea name="remarks" id="remarks" rows="2" class="form-control form-control-sm" placeholder="Enter trip completion notes..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm w-100">
                                        <i class="fa fa-flag-checkered me-1"></i> Complete Trip (Web)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Status Update Block -->
                @if (!in_array($ticket->status, ['completed', 'cancelled']))
                    <div class="block block-rounded mb-3">
                        <div class="block-header block-header-default">
                            <h3 class="block-title fs-sm">
                                <i class="fa fa-arrows-rotate text-warning me-1"></i> Update Ticket Status
                            </h3>
                        </div>
                        <div class="block-content pb-3">
                            <form action="{{ route('admin.tickets.updateStatus', $ticket) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label fs-xs fw-semibold">New Status</label>
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="assigned" {{ $ticket->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $ticket->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-xs fw-semibold">Update Message (Optional)</label>
                                    <textarea name="update_message" rows="2" class="form-control form-control-sm" placeholder="Add a note about this status change..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-alt-warning w-100 mt-2">
                                    <i class="fa fa-sync me-1"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    @include('TicketManagement.partials.assignment-modal')
    @include('TicketManagement.partials.trip-details-modal')

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-2 bg-dark">
                    <img src="" id="modalPreviewImg" class="img-fluid rounded" alt="Preview Image" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Trip Details scripts -->
    <script>
        let tripDetailsData = null;
        let tripMap = null;
        let actualPolyline = null;
        let plannedPolyline = null;
        let tripMarkers = [];

        function previewImage(url, title = 'Image Preview') {
            document.getElementById('modalPreviewImg').src = url;
            document.getElementById('imagePreviewModalLabel').textContent = title;
            var modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            modal.show();
        }

        async function getRoadPath(points) {
            if (!points || points.length < 2) return points;
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
                
                document.getElementById('dt-start-odo').textContent = tripDetailsData.startOdo && tripDetailsData.startOdo !== 'N/A' ? tripDetailsData.startOdo + ' km' : 'N/A';
                document.getElementById('dt-end-odo').textContent = tripDetailsData.endOdo && tripDetailsData.endOdo !== 'N/A' ? tripDetailsData.endOdo + ' km' : 'N/A';
                
                // Odometer Images
                const startImgContainer = document.getElementById('dt-start-odo-img-container');
                const startImg = document.getElementById('dt-start-odo-img');
                if (startImgContainer && startImg) {
                    if (tripDetailsData.startOdoImage) {
                        startImg.src = tripDetailsData.startOdoImage;
                        startImgContainer.style.display = 'block';
                    } else {
                        startImgContainer.style.display = 'none';
                    }
                }

                const endImgContainer = document.getElementById('dt-end-odo-img-container');
                const endImg = document.getElementById('dt-end-odo-img');
                if (endImgContainer && endImg) {
                    if (tripDetailsData.endOdoImage) {
                        endImg.src = tripDetailsData.endOdoImage;
                        endImgContainer.style.display = 'block';
                    } else {
                        endImgContainer.style.display = 'none';
                    }
                }

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
                            try { trackingPoints = JSON.parse(trackingPoints); } catch(e) { trackingPoints = []; }
                        }
                        
                        if (Array.isArray(trackingPoints) && trackingPoints.length > 0) {
                            const latLngs = trackingPoints.map(pt => [parseFloat(pt.latitude), parseFloat(pt.longitude)]);
                            latLngs.forEach(ll => mapBounds.push(ll));
                            
                            actualPolyline = L.polyline(latLngs, {
                                color: '#e74a3b',
                                weight: 5,
                                opacity: 0.9
                            }).addTo(tripMap);
                            
                            const startPt = latLngs[0];
                            const startMarker = L.marker(startPt, {
                                icon: L.divIcon({
                                    className: 'custom-fa-marker-start',
                                    html: '<i class="fa fa-play text-success fs-5"></i>',
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24]
                                })
                            }).bindPopup('Actual GPS Start').addTo(tripMap);
                            tripMarkers.push(startMarker);
                            
                            const lastPt = latLngs[latLngs.length - 1];
                            const endMarker = L.marker(lastPt, {
                                icon: L.divIcon({
                                    className: 'custom-fa-marker-car',
                                    html: '<i class="fa fa-car-side text-danger fs-5"></i>',
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24]
                                })
                            }).bindPopup('Latest GPS Position').addTo(tripMap);
                            tripMarkers.push(endMarker);
                        } else if (tripDetailsData.plannedCoordinates && tripDetailsData.plannedCoordinates.length > 0) {
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

                                const firstPt = plannedPoints[0];
                                const startMarker = L.marker([firstPt.lat, firstPt.lng], {
                                    icon: L.divIcon({
                                        className: 'custom-fa-marker-start',
                                        html: '<i class="fa fa-map-marker-alt text-success fs-4"></i>',
                                        iconSize: [24, 24],
                                        iconAnchor: [12, 24]
                                    })
                                }).bindPopup(`Planned Start: ${firstPt.address}`).addTo(tripMap);
                                tripMarkers.push(startMarker);

                                const lastPt = plannedPoints[plannedPoints.length - 1];
                                const endMarker = L.marker([lastPt.lat, lastPt.lng], {
                                    icon: L.divIcon({
                                        className: 'custom-fa-marker-end',
                                        html: '<i class="fa fa-flag-checkered text-dark fs-4"></i>',
                                        iconSize: [24, 24],
                                        iconAnchor: [12, 24]
                                    })
                                }).bindPopup(`Planned End: ${lastPt.address}`).addTo(tripMap);
                                tripMarkers.push(endMarker);

                                if (plannedPoints.length > 1) {
                                    const pathCoords = plannedPoints.map(pt => [pt.lat, pt.lng]);
                                    const plannedRoadPoints = await getRoadPath(pathCoords);
                                    
                                    plannedPolyline = L.polyline(plannedRoadPoints, { 
                                        color: '#0d6efd',
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
            plannedCoordinates: @json($ticket->formatted_trip_locations ?? []),
            vehicle: @json($ticket->latestVehicleAssignment->vehicle ? $ticket->latestVehicleAssignment->vehicle->registration_number . ' (' . $ticket->latestVehicleAssignment->vehicle->brand . ' ' . $ticket->latestVehicleAssignment->vehicle->model . ')' : ($ticket->latestVehicleAssignment->notes ?? 'Ad-hoc vehicle')),
            driver: @json($ticket->latestVehicleAssignment->driver ? $ticket->latestVehicleAssignment->driver->full_name : 'Ad-hoc driver'),
            driverPhone: @json($ticket->latestVehicleAssignment->driver ? $ticket->latestVehicleAssignment->driver->phone : 'N/A'),
            seating: @json($ticket->latestVehicleAssignment->vehicle ? $ticket->latestVehicleAssignment->vehicle->seating_capacity : 'N/A'),
            startOdo: @json($ticket->latestVehicleAssignment->start_odo_meter ?? 'N/A'),
            startOdoImage: @json($ticket->latestVehicleAssignment->start_odo_image ? Storage::disk('public')->url($ticket->latestVehicleAssignment->start_odo_image) : null),
            endOdo: @json($ticket->latestVehicleAssignment->end_odo_meter ?? 'N/A'),
            endOdoImage: @json($ticket->latestVehicleAssignment->end_odo_image ? Storage::disk('public')->url($ticket->latestVehicleAssignment->end_odo_image) : null),
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
