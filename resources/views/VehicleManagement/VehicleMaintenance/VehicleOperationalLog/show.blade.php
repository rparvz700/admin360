@extends('Partials.app', ['activeMenu' => 'operational-logs'])
@section('title') Log Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Operational Log Details</h3>
            <a href="{{ route('maintenance.operational-logs.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th width="30%">Vehicle</th>
                        <td>{{ $operationalLog->vehicle->registration_number ?? 'N/A' }} - {{ $operationalLog->vehicle->brand ?? '' }} {{ $operationalLog->vehicle->model ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Log Type</th>
                        <td>
                            <span class="badge bg-{{ $operationalLog->getLogTypeBadge() }}">
                                {{ $operationalLog->getLogTypeLabel() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Logged At</th>
                        <td>{{ $operationalLog->logged_at->format('d M Y H:i') }}</td>
                    </tr>
                    
                    @if($operationalLog->log_type === 'meter_reading')
                    <tr>
                        <th>Meter Reading</th>
                        <td>{{ $operationalLog->meter_reading ? number_format($operationalLog->meter_reading) . ' km' : 'N/A' }}</td>
                    </tr>
                    @endif
                    
                    @if($operationalLog->log_type === 'assignment')
                    <tr>
                        <th>Assigned Department</th>
                        <td>{{ $operationalLog->assigned_department ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Assigned User</th>
                        <td>{{ $operationalLog->assignedUser->name ?? 'N/A' }}</td>
                    </tr>
                    @endif
                    
                    @if($operationalLog->log_type === 'status_change')
                    <tr>
                        <th>Vehicle Status</th>
                        <td>
                            @if($operationalLog->vehicle_status)
                                <span class="badge bg-{{ $operationalLog->getStatusBadge() }}">
                                    {{ ucfirst(str_replace('_', ' ', $operationalLog->vehicle_status)) }}
                                </span>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @endif
                    
                    <tr>
                        <th>Logged By</th>
                        <td>{{ $operationalLog->logger->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td>{{ $operationalLog->remarks ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection