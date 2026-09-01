<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'ticket_type',
        'user_id',
        'company_id',
        'project_name',
        'title',
        'description',
        'priority',
        'status',
        'vehicle_type_id',
        'trip_start_datetime',
        'trip_end_datetime',
        'trip_location_details',
        'trip_location_coordinates',
        'passenger_count',
        'trip_purpose',
        'asset_id',
        'asset_category_id',
        'requested_asset_name',
        'asset_specifications',
        'floor_id',
        'location_within_floor',
        'assigned_to',
        'assigned_at',
        'completed_at',
        'assigned_driver_id',
        'assigned_vehicle_id'
    ];

    protected $casts = [
        'trip_start_datetime' => 'datetime',
        'trip_end_datetime' => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'trip_location_details' => 'array',
        'trip_location_coordinates' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            $ticket->ticket_number = self::generateTicketNumber();
        });
    }

    public static function generateTicketNumber()
    {
        $prefix = 'TKT';
        $date = date('Ymd');
        $lastTicket = self::whereDate('created_at', today())->latest()->first();
        $number = $lastTicket ? intval(substr($lastTicket->ticket_number, -4)) + 1 : 1;
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function assignedDriver()
    {
        return $this->hasOneThrough(
            Driver::class,
            VehicleAssignment::class,
            'ticket_id',      // Foreign key on vehicle_assignments table
            'id',             // Foreign key on drivers table
            'id',             // Local key on tickets table
            'driver_id'       // Local key on vehicle_assignments table
        )->latest();
    }

    public function assignedVehicle()
    {
        return $this->hasOneThrough(
            Vehicle::class,
            VehicleAssignment::class,
            'ticket_id',      // Foreign key on vehicle_assignments table
            'id',             // Foreign key on vehicles table
            'id',             // Local key on tickets table
            'vehicle_id'      // Local key on vehicle_assignments table
        )->latest();
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(VehicleAssignment::class, 'ticket_id');
    }

    public function activeVehicleAssignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'ticket_id')
            ->where('status', 'active')
            ->latest();
    }

    public function scheduledVehicleAssignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'ticket_id')
            ->where('status', 'scheduled')
            ->latest();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function floor()
    {
        return $this->belongsTo(PropertiesFloor::class, 'floor_id');
    }

    public function updates()
    {
        return $this->hasMany(TicketUpdate::class)->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    // Scopes
    public function scopeVehicleSupport($query)
    {
        return $query->where('ticket_type', 'vehicle_support');
    }

    public function scopeAssetRequest($query)
    {
        return $query->where('ticket_type', 'asset_request');
    }

    public function scopeAssetRepair($query)
    {
        return $query->where('ticket_type', 'asset_repair');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    // Helper methods
    public function isVehicleSupport()
    {
        return $this->ticket_type === 'vehicle_support';
    }

    public function isAssetRequest()
    {
        return $this->ticket_type === 'asset_request';
    }

    public function isAssetRepair()
    {
        return $this->ticket_type === 'asset_repair';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'assigned' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }


    public function latestVehicleAssignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'ticket_id')->latest();
    }

    public function getFormattedTripLocationsAttribute(): array
    {
        $locations = $this->trip_location_details ?? [];
        $coordinates = $this->trip_location_coordinates ?? [];

        return collect($locations)->map(function (array $location, int $index) use ($coordinates) {
            $coordinate = $coordinates[$index] ?? [];

            $startLat = $coordinate['start']['latitude'] ?? null;
            $startLng = $coordinate['start']['longitude'] ?? null;
            $startAddr = $location['start'] ?? null;

            if (($startLat === null || $startLng === null) && $startAddr) {
                if (preg_match('/Lat:\s*(-?\d+(?:\.\d+)?).*Lng:\s*(-?\d+(?:\.\d+)?)/i', $startAddr, $matches)) {
                    $startLat = (float) $matches[1];
                    $startLng = (float) $matches[2];
                    $startAddr = trim(preg_replace('/\s*\(Lat:\s*-?\d+(?:\.\d+)?,\s*Lng:\s*-?\d+(?:\.\d+)?\)\s*/i', '', $startAddr));
                }
            }

            $endLat = $coordinate['end']['latitude'] ?? null;
            $endLng = $coordinate['end']['longitude'] ?? null;
            $endAddr = $location['end'] ?? null;

            if (($endLat === null || $endLng === null) && $endAddr) {
                if (preg_match('/Lat:\s*(-?\d+(?:\.\d+)?).*Lng:\s*(-?\d+(?:\.\d+)?)/i', $endAddr, $matches)) {
                    $endLat = (float) $matches[1];
                    $endLng = (float) $matches[2];
                    $endAddr = trim(preg_replace('/\s*\(Lat:\s*-?\d+(?:\.\d+)?,\s*Lng:\s*-?\d+(?:\.\d+)?\)\s*/i', '', $endAddr));
                }
            }

            return [
                'stop_order' => $location['stop_order'] ?? $coordinate['stop_order'] ?? $index + 1,
                'start' => [
                    'address' => $startAddr,
                    'latitude' => $startLat !== null ? (float) $startLat : null,
                    'longitude' => $startLng !== null ? (float) $startLng : null,
                ],
                'end' => [
                    'address' => $endAddr,
                    'latitude' => $endLat !== null ? (float) $endLat : null,
                    'longitude' => $endLng !== null ? (float) $endLng : null,
                ],
            ];
        })->values()->all();
    }
}