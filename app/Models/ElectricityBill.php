<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NumberFormatter;

class ElectricityBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'electricity_bills';

    protected $fillable = [
        'requisition_no',
        'bill_type',
        'meter_id',
        'building_id',
        'rio_id',
        'project_name',
        'billing_month',
        'previous_reading',
        'current_reading',
        'units_consumed',
        'rate_per_unit',
        'net_amount',
        'vat_amount',
        'total_amount',
        'received_subcenter_date',
        'last_payment_date',
        'cheque_name',
        'payment_mode',
        'payment_account_details',
        'status',
        'created_by',
        'paid_by',
        'payment_date',
        'payment_reference',
        'bill_file_path',
        'remarks',
    ];

    protected $casts = [
        'previous_reading'        => 'decimal:2',
        'current_reading'         => 'decimal:2',
        'units_consumed'          => 'decimal:2',
        'rate_per_unit'           => 'decimal:2',
        'net_amount'              => 'decimal:2',
        'vat_amount'              => 'decimal:2',
        'total_amount'            => 'decimal:2',
        'received_subcenter_date' => 'date',
        'last_payment_date'       => 'date',
        'payment_date'            => 'date',
    ];

    // Relationships
    public function meter()
    {
        return $this->belongsTo(ElectricityMeter::class, 'meter_id');
    }

    public function building()
    {
        return $this->belongsTo(PropertiesBuilding::class, 'building_id');
    }

    public function rio()
    {
        return $this->belongsTo(Rio::class, 'rio_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Status Badges & Labels
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'generated' => 'Pending Payment',
            'paid'      => 'Paid',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'generated' => 'warning-light text-warning',
            'paid'      => 'success-light text-success',
            'cancelled' => 'danger-light text-danger',
            default     => 'secondary-light text-secondary',
        };
    }

    // Number to words helper (Taka in words)
    public function getAmountInWordsAttribute()
    {
        $amount = (float) $this->total_amount;
        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $words = ucfirst($formatter->format((int)$amount));
            return "Taka " . $words . " Only";
        }
        return "Taka " . number_format($amount, 2) . " Only";
    }

    public static function generateRequisitionNo($paymentMode = 'BEFTN')
    {
        $year = date('Y');
        $monthStr = date('M-d');
        $prefix = "Admin/E-bill/{$paymentMode}/{$year}/{$monthStr}/";

        $last = self::where('requisition_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? intval(substr($last->requisition_no, -3)) + 1 : 1;
        return $prefix . 'F' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
