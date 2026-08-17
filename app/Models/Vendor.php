<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_code',
        'name',
        'vendor_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'bank_name',
        'bank_account_no',
        'routing_number',
        'tin_vat_no',
        'services_offered',
        'rating',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'services_offered' => 'array',
        'metadata' => 'array',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function categories()
    {
        return $this->belongsToMany(VendorCategory::class, 'vendor_category_vendor')
                    ->withTimestamps();
    }

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function maintenanceParts()
    {
        return $this->hasMany(VehicleMaintenancePart::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function agreements()
    {
        return $this->hasMany(Agreement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('vendor_type', $type);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('vendor_categories.id', $categoryId);
        });
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where(function ($q) use ($module) {
            $q->whereHas('categories', function ($catQuery) use ($module) {
                $catQuery->where(function ($m) use ($module) {
                    $m->where('module_scope', $module)
                      ->orWhere('module_scope', 'general');
                });
            })->orWhereDoesntHave('categories'); // Include unassigned vendors by default for safety
        });
    }

    // Helpers
    public function getTotalMaintenanceCost($startDate = null, $endDate = null)
    {
        $query = $this->maintenances();
        if ($startDate) $query = $query->where('service_date', '>=', $startDate);
        if ($endDate)   $query = $query->where('service_date', '<=', $endDate);
        return $query->sum('total_service_cost');
    }

    public function getMaintenanceCount($startDate = null, $endDate = null)
    {
        $query = $this->maintenances();
        if ($startDate) $query = $query->where('service_date', '>=', $startDate);
        if ($endDate)   $query = $query->where('service_date', '<=', $endDate);
        return $query->count();
    }

    public function getVendorTypeLabel()
    {
        if ($this->relationLoaded('categories') && $this->categories->count() > 0) {
            return $this->categories->pluck('name')->implode(', ');
        }

        return match($this->vendor_type) {
            'workshop'       => 'Workshop',
            'parts_supplier' => 'Parts Supplier',
            'both'           => 'Workshop & Parts',
            default          => 'General Vendor',
        };
    }

    public function getCategoryBadgesHtml(): string
    {
        if ($this->relationLoaded('categories') && $this->categories->count() > 0) {
            $badges = $this->categories->map(function ($cat) {
                return '<span class="' . $cat->getModuleScopeBadgeClass() . '" title="' . e($cat->getModuleScopeLabel()) . '">' . e($cat->name) . '</span>';
            })->implode(' ');

            return '<div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 250px;">' . $badges . '</div>';
        }

        $label = $this->getVendorTypeLabel();
        return '<span class="badge bg-body-light text-dark border px-2 py-1">' . e($label) . '</span>';
    }

    public static function generateVendorCode()
    {
        $last = self::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->vendor_code, 4)) + 1 : 1;
        return 'VEN-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}