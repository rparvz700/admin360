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

    public function getInvoiceItemHtmlAttribute()
    {
        $type = $this->invoice_type;

        if ($type === 'rent') {
            $rentBases = collect();
            if ($this->relationLoaded('rentBases') && $this->rentBases->count() > 0) {
                $rentBases = $rentBases->merge($this->rentBases);
            }
            if ($this->relationLoaded('rentBasePivot') && $this->rentBasePivot->count() > 0) {
                $rentBases = $rentBases->merge($this->rentBasePivot);
            }

            if ($rentBases->isEmpty()) {
                $rentBases = $this->rentBases()->with('agreement.floors.building')->get();
                $pivotBases = $this->rentBasePivot()->with('agreement.floors.building')->get();
                $rentBases = $rentBases->merge($pivotBases);
            }

            $rentBases = $rentBases->unique('id');

            if ($rentBases->count() > 0) {
                $items = [];
                foreach ($rentBases as $rent) {
                    $agrRef = $rent->agreement->agreement_ref_no ?? null;
                    $floors = $rent->agreement->floors ?? collect();
                    
                    $siteCodes = $floors->map(fn($f) => $f->building->site_code ?? $f->building->code ?? null)->filter()->unique()->implode(', ');
                    $buildingNames = $floors->map(fn($f) => $f->building->site_name ?? null)->filter()->unique()->implode(', ');
                    $floorLabels = $floors->pluck('floor_label')->filter()->unique()->implode(', ');

                    $locParts = array_filter([
                        $siteCodes ? "Site: {$siteCodes}" : null,
                        $buildingNames ? "Building: {$buildingNames}" : null,
                        $floorLabels ? "Floor: {$floorLabels}" : null,
                    ]);
                    $locStr = implode(' | ', $locParts);
                    
                    $bMonth = $rent->pivot->billing_month ?? $this->billing_month;
                    $bMonthFormatted = $bMonth ? \Carbon\Carbon::parse($bMonth . '-01')->format('M Y') : null;

                    $rentUrl = route('rent.show', $rent->id);

                    $html = '<div class="mb-1">';
                    $html .= '<a href="' . $rentUrl . '" class="fw-semibold text-primary text-decoration-none">';
                    $html .= '<i class="fa fa-file-contract me-1"></i> Agreement: ' . e($agrRef ?: 'N/A') . '</a>';
                    if ($locStr) {
                        $html .= '<div class="fs-xs text-muted"><i class="fa fa-building me-1 text-info"></i> ' . e($locStr) . '</div>';
                    }
                    if ($bMonthFormatted) {
                        $html .= '<div class="fs-xs mt-1"><span class="badge bg-info-light text-info border border-info-subtle"><i class="fa fa-calendar-alt me-1"></i> Month: ' . e($bMonthFormatted) . '</span></div>';
                    }
                    $html .= '</div>';
                    $items[] = $html;
                }
                return implode('', $items);
            }

            return '<span class="badge bg-info-light text-info">Rent Requisition</span>';
        }

        if ($type === 'maintenance') {
            $maintenances = $this->relationLoaded('maintenances') && $this->maintenances->count() > 0 
                ? $this->maintenances 
                : $this->maintenances()->with('vehicle')->get();

            if ($maintenances->count() > 0) {
                $items = [];
                foreach ($maintenances as $m) {
                    $regNo = $m->vehicle->registration_number ?? 'N/A';
                    $mType = $m->getMaintenanceTypeLabel();
                    $mBadge = $m->getMaintenanceTypeBadge();
                    $desc = $m->service_description ? \Illuminate\Support\Str::limit($m->service_description, 40) : null;

                    $html = '<div class="mb-1">';
                    $html .= '<span class="fw-semibold text-dark"><i class="fa fa-car me-1 text-primary"></i> ' . e($regNo) . '</span>';
                    $html .= '<div class="fs-xs text-muted">';
                    $html .= '<span class="badge bg-' . $mBadge . ' me-1">' . e($mType) . '</span>';
                    if ($desc) {
                        $html .= e($desc);
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    $items[] = $html;
                }
                return implode('', $items);
            }

            return '<span class="badge bg-primary-light text-primary">Vehicle Maintenance</span>';
        }

        // General Service
        $remarksShort = $this->remarks ? \Illuminate\Support\Str::limit($this->remarks, 50) : 'General Vendor Service';
        return '<div class="mb-1"><span class="fw-semibold text-secondary"><i class="fa fa-cog me-1"></i> General Service</span><div class="fs-xs text-muted">' . e($remarksShort) . '</div></div>';
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