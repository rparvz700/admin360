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
        'title',
        'description',
        'priority',
        'status',
        'vehicle_type_id',
        'trip_start_datetime',
        'trip_end_datetime',
        'pickup_location',
        'destination',
        'passenger_count',
        'trip_purpose',
        'asset_id',
        'asset_category_id',
        'requested_asset_name',
        'asset_specifications',
        'floor_id',
        'location_within_floor',
        'assigned_to',
        'assigned_driver_id',
        'assigned_vehicle_id',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'trip_start_datetime' => 'datetime',
        'trip_end_datetime' => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
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
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function assignedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
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
}
