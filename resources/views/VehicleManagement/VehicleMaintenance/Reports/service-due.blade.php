@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])
@section('title') Service Due Report @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Service Due Report</h3>
            <a href="{{ route('maintenance.reports.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left"></i> Reports List
            </a>
        </div>
        <div class="block-content">
            <!-- Filter -->
            <form method="GET" action="{{ route('maintenance.reports.service-due') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="days_ahead">Days Ahead</label>
                        <select class="form-select" id="days_ahead" name="days_ahead">
                            <option value="7" {{ $daysAhead == 7 ? 'selected' : '' }}>Next 7 Days</option>
                            <option value="15" {{ $daysAhead == 15 ? 'selected' : '' }}>Next 15 Days</option>
                            <option value="30" {{ $daysAhead == 30 ? 'selected' : '' }}>Next 30 Days</option>
                            <option value="60" {{ $daysAhead == 60 ? 'selected' : '' }}>Next 60 Days</option>
                            <option value="90" {{ $daysAhead == 90 ? 'selected' : '' }}>Next 90 Days</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Last Service Date</th>
                            <th>Next Due Date</th>
                            <th class="text-center">Days Until Due</th>
                            <th class="text-end">Next Due KM</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehiclesDue as $data)
                        <tr class="{{ $data['is_overdue'] ? 'table-danger' : '' }}">
                            <td>
                                <strong>{{ $data['vehicle']->registration_number }}</strong><br>
                                <small class="text-muted">{{ $data['vehicle']->brand }} {{ $data['vehicle']->model }}</small>
                            </td>
                            <td>
                                @if($data['last_maintenance'])
                                    {{ $data['last_maintenance']->start_datetime->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($data['last_maintenance'] && $data['last_maintenance']->next_service_due_date)
                                    {{ $data['last_maintenance']->next_service_due_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">Not Set</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($data['days_until_due'] !== null)
                                    @if($data['days_until_due'] < 0)
                                        <span class="badge bg-danger">{{ abs($data['days_until_due']) }} days overdue</span>
                                    @elseif($data['days_until_due'] <= 7)
                                        <span class="badge bg-warning">{{ $data['days_until_due'] }} days</span>
                                    @else
                                        <span class="badge bg-success">{{ $data['days_until_due'] }} days</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($data['last_maintenance'] && $data['last_maintenance']->next_service_due_km)
                                    {{ number_format($data['last_maintenance']->next_service_due_km) }} km
                                    @if($data['km_until_due'] !== null)
                                        <br><small class="text-muted">({{ number_format(abs($data['km_until_due'])) }} km {{ $data['km_until_due'] < 0 ? 'overdue' : 'remaining' }})</small>
                                    @endif
                                @else
                                    <span class="text-muted">Not Set</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($data['is_overdue'])
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-success">On Track</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No vehicles due for service in the selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vehiclesDue->count() > 0)
            <div class="alert alert-info mt-3">
                <i class="fa fa-info-circle"></i>
                <strong>Summary:</strong> {{ $vehiclesDue->count() }} vehicle(s) due for service in the next {{ $daysAhead }} days.
                {{ $vehiclesDue->where('is_overdue', true)->count() }} overdue.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection