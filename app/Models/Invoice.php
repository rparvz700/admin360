<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'vendor_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_status',
        'paid_amount',
        'payment_date',
        'payment_method',
        'invoice_file_path',
        'remarks',
        'billing_month',
    ];

    protected $casts = [
        'invoice_date'    => 'date',
        'due_date'        => 'date',
        'payment_date'    => 'date',
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'billing_month'   => 'string',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function rentBases()
    {
        return $this->hasMany(RentBase::class);
    }

    public function rentBasePivot()
    {
        return $this->belongsToMany(RentBase::class, 'rent_invoices')
                    ->withPivot('billing_month')
                    ->withTimestamps();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('payment_status', 'overdue')
              ->orWhere(function ($q2) {
                  $q2->where('payment_status', 'pending')
                     ->where('due_date', '<', now()->toDateString());
              });
        });
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    // Helpers
    public function getOutstandingAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isOverdue()
    {
        return $this->due_date
            && $this->due_date < now()->toDateString()
            && $this->payment_status !== 'paid';
    }

    public function getPaymentStatusLabel()
    {
        return match($this->payment_status) {
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid'    => 'Paid',
            'overdue' => 'Overdue',
            default   => 'Unknown',
        };
    }

    public function getPaymentStatusBadge()
    {
        return match($this->payment_status) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid'    => 'success',
            'overdue' => 'danger',
            default   => 'secondary',
        };
    }

    public function getInvoiceTypeAttribute()
    {
        if ($this->relationLoaded('rentBases') && $this->rentBases->count() > 0) {
            return 'rent';
        }
        if ($this->relationLoaded('rentBasePivot') && $this->rentBasePivot->count() > 0) {
            return 'rent';
        }
        if ($this->relationLoaded('maintenances') && $this->maintenances->count() > 0) {
            return 'maintenance';
        }

        if ($this->rentBases()->exists() || $this->rentBasePivot()->exists()) {
            return 'rent';
        }
        if ($this->maintenances()->exists()) {
            return 'maintenance';
        }

        return 'general';
    }

    public function getInvoiceTypeLabelAttribute()
    {
        return match($this->invoice_type) {
            'rent'        => 'Rent Requisition',
            'maintenance' => 'Vehicle Maintenance',
            default       => 'General Service',
        };
    }

    public function getInvoiceTypeBadgeAttribute()
    {
        return match($this->invoice_type) {
            'rent'        => 'info-light text-info',
            'maintenance' => 'primary-light text-primary',
            default       => 'secondary-light text-secondary',
        };
    }

    public static function generateInvoiceNumber()
    {
        $year   = date('Y');
        $last   = self::where('invoice_number', 'like', "INV-{$year}-%")
                      ->orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->invoice_number, 9)) + 1 : 1;
        return "INV-{$year}-" . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}