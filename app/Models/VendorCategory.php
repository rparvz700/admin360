<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'module_scope',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_category_vendor')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where(function ($q) use ($module) {
            $q->where('module_scope', $module)
              ->orWhere('module_scope', 'general');
        });
    }

    public function getModuleScopeLabel(): string
    {
        return match($this->module_scope) {
            'vehicle'    => 'Vehicle Management',
            'facilities' => 'Facilities Management',
            'utility'    => 'Electricity & Utilities',
            'general'    => 'General / All Modules',
            default      => ucfirst($this->module_scope),
        };
    }

    public function getModuleScopeBadgeClass(): string
    {
        return match($this->module_scope) {
            'vehicle'    => 'badge bg-warning-light text-warning fw-semibold px-2 py-1',
            'facilities' => 'badge bg-info-light text-info fw-semibold px-2 py-1',
            'utility'    => 'badge bg-success-light text-success fw-semibold px-2 py-1',
            'general'    => 'badge bg-primary-light text-primary fw-semibold px-2 py-1',
            default      => 'badge bg-body-light text-dark border px-2 py-1',
        };
    }
}
