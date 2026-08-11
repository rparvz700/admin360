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
        'last_recharge_amount',
        'last_recharge_date',
        'balance_after_last_recharge',
        'last_balance',
        'recharge_amount',
        'current_balance',
        'per_day_consumption',
        'recharge_date',
        'is_consumption_edited',
        'consumption_edit_remarks',
        'consumption_edit_attachment',
        'previous_peak_reading',
        'current_peak_reading',
        'units_peak_consumed',
        'rate_peak_per_unit',
        'amount_peak',
        'amount_offpeak',
        'late_fee',
        'meter_charge',
        'others_amount',
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
        'last_recharge_amount'    => 'decimal:2',
        'last_recharge_date'      => 'date',
        'balance_after_last_recharge' => 'decimal:2',
        'last_balance'            => 'decimal:2',
        'recharge_amount'         => 'decimal:2',
        'current_balance'         => 'decimal:2',
        'per_day_consumption'     => 'decimal:2',
        'recharge_date'           => 'date',
        'is_consumption_edited'   => 'boolean',
        'previous_peak_reading'   => 'decimal:2',
        'current_peak_reading'    => 'decimal:2',
        'units_peak_consumed'     => 'decimal:2',
        'rate_peak_per_unit'      => 'decimal:2',
        'amount_peak'             => 'decimal:2',
        'amount_offpeak'          => 'decimal:2',
        'late_fee'                => 'decimal:2',
        'meter_charge'            => 'decimal:2',
        'others_amount'           => 'decimal:2',
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
