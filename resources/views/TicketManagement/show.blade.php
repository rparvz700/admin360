@extends('Partials.app', ['activeMenu' => 'tickets'])

@section('title')
    Ticket {{ $ticket->ticket_number }} - {{ config('app.name') }}
@endsection

@section('styles')
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
                        Submitted on {{ $ticket->created_at->format('M d, Y \a\t h:i A') }} by {{ $ticket->user->name }}
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('tickets.index') }}">Tickets</a>
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
                            <i class="fa fa-info-circle text-primary me-1"></i> Ticket Overview
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
                            <i class="fa fa-tag me-1"></i> <strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $ticket->ticket_type)) }}
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
                                        <i class="fa fa-car me-1"></i> Vehicle Support Information
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
                                            <span class="text-muted d-block fs-xs text-uppercase">Trip Start</span>
                                            <strong>{{ $ticket->trip_start_datetime ? $ticket->trip_start_datetime->format('M d, Y h:i A') : 'N/A' }}</strong>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <span class="text-muted d-block fs-xs text-uppercase">Trip End</span>
                                            <strong>{{ $ticket->trip_end_datetime ? $ticket->trip_end_datetime->format('M d, Y h:i A') : 'N/A' }}</strong>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted d-block fs-xs text-uppercase">Trip Purpose</span>
                                            <div class="p-2 bg-body-light rounded border text-dark">{{ $ticket->trip_purpose ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    @if ($ticket->trip_location_details)
                                        <h5 class="fs-xs fw-bold text-uppercase text-muted mb-2">Trip Stoppages / Route</h5>
                                        <div class="row g-2 mb-3">
                                            @foreach ($ticket->trip_location_details as $index => $location)
                                                <div class="col-12">
                                                    <div class="p-2 bg-body-light rounded border d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-primary fs-xs">Stop {{ $index + 1 }}</span>
                                                            <div>
                                                                <span class="badge bg-success-light text-success me-1"><i class="fa fa-map-pin"></i> Start</span> {{ $location['start'] ?? 'N/A' }}
                                                                &nbsp;&rarr;&nbsp;
                                                                <span class="badge bg-danger-light text-danger me-1"><i class="fa fa-flag-checkered"></i> End</span> {{ $location['end'] ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Assignment & Odometer Section -->
                                    @if ($ticket->latestVehicleAssignment)
                                        <hr class="my-3">
                                        <h5 class="fs-sm fw-bold text-success mb-3">
                                            <i class="fa fa-clipboard-check me-1"></i> Assignment & Trip Status
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
                                        <i class="fa fa-box-open me-1"></i> Asset Request Information
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Category</span>
                                            <strong>{{ $ticket->assetCategory->category_name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Requested Asset Name</span>
                                            <strong>{{ $ticket->requested_asset_name }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Floor</span>
                                            <strong>{{ $ticket->floor->building->building_name ?? '' }} - {{ $ticket->floor->floor_label ?? '' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Location within Floor</span>
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
                                        <i class="fa fa-wrench me-1"></i> Asset Repair Information
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
                        @if (!in_array($ticket->status, ['completed', 'cancelled']))
                            <form action="{{ route('tickets.addUpdate', $ticket) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Add Comment / Note</label>
                                    <textarea name="update_message" rows="3" class="form-control form-control-sm" required placeholder="Type your message or inquiry here..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-comment me-1"></i> Post Comment
                                </button>
                            </form>
                            <hr class="my-4">
                        @endif

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

            <!-- Right Column: Sidebar Meta -->
            <div class="col-lg-4">
                <!-- Meta Information Block -->
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-sliders text-muted me-1"></i> Meta Information
                        </h3>
                    </div>
                    <div class="block-content fs-sm">
                        <table class="table table-borderless table-sm mb-2">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 120px;">Ticket #</td>
                                    <td class="fw-bold">{{ $ticket->ticket_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Status</td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->status_color }}">
                                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Priority</td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->priority_color }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
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
                                @if ($ticket->assignedTo)
                                    <tr>
                                        <td class="text-muted fw-semibold">Handler</td>
                                        <td><i class="fa fa-user-gear me-1"></i> {{ $ticket->assignedTo->name }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Block (Cancel Ticket) -->
                @if (!in_array($ticket->status, ['completed', 'cancelled']))
                    <div class="block block-rounded border-start border-4 border-danger">
                        <div class="block-header block-header-default">
                            <h3 class="block-title text-danger">
                                <i class="fa fa-triangle-exclamation me-1"></i> Ticket Actions
                            </h3>
                        </div>
                        <div class="block-content pb-3">
                            <p class="fs-xs text-muted mb-3">If this request is no longer needed, you can cancel it.</p>
                            <form action="{{ route('tickets.cancel', $ticket) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel this ticket? This action cannot be undone.');">
                                @csrf
                                <button type="submit" class="btn btn-alt-danger btn-sm w-100">
                                    <i class="fa fa-times-circle me-1"></i> Cancel This Ticket
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

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
@endsection

@section('scripts')
    <script>
        function previewImage(url, title = 'Image Preview') {
            document.getElementById('modalPreviewImg').src = url;
            document.getElementById('imagePreviewModalLabel').textContent = title;
            var modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            modal.show();
        }
    </script>
@endsection
