<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataDictionary extends Model
{
    use HasFactory;

    protected $table = 'data_dictionaries';

    protected $fillable = [
        'table_name',
        'column_name',
        'display_name',
        'description',
        'data_type',
        'module_name',
        'is_reportable',
        'is_filterable',
        'is_aggregatable',
        'sort_order',
    ];

    protected $casts = [
        'is_reportable'   => 'boolean',
        'is_filterable'   => 'boolean',
        'is_aggregatable' => 'boolean',
        'sort_order'      => 'integer',
    ];

    /**
     * Scope for fields that can be included in reports.
     */
    public function scopeReportable($query)
    {
        return $query->where('is_reportable', true);
    }

    /**
     * Scope for fields that can be used in filters.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope for fields that can be aggregated (SUM, AVG, etc.).
     */
    public function scopeAggregatable($query)
    {
        return $query->where('is_aggregatable', true);
    }

    /**
     * Scope for a specific table.
     */
    public function scopeForTable($query, string $table)
    {
        return $query->where('table_name', $table);
    }

    /**
     * Scope for a specific module.
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module_name', $module);
    }

    /**
     * Get the full table.column identifier.
     */
    public function getFullIdentifierAttribute(): string
    {
        return "{$this->table_name}.{$this->column_name}";
    }
}
