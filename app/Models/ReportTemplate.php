<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'report_templates';

    protected $fillable = [
        'name',
        'description',
        'query_config',
        'is_shared',
        'created_by',
    ];

    protected $casts = [
        'query_config' => 'array',
        'is_shared'    => 'boolean',
    ];

    /**
     * User who created the template.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for templates visible to the given user:
     * Either public/shared templates OR private templates created by the user.
     */
    public function scopeVisibleTo($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_shared', true)
              ->orWhere('created_by', $userId);
        });
    }

    /**
     * Scope for shared (public) templates.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope for private templates owned by the user.
     */
    public function scopePrivateTo($query, $userId)
    {
        return $query->where('created_by', $userId)->where('is_shared', false);
    }

    /**
     * Append human-readable summary accessors.
     */
    protected $appends = [
        'tables_summary',
        'columns_summary',
    ];

    /**
     * Summary list of tables referenced in this template.
     */
    public function getTablesSummaryAttribute(): array
    {
        $fields = $this->query_config['selected_fields'] ?? [];
        $tables = [];
        foreach ($fields as $f) {
            if (!empty($f['table'])) {
                $tables[$f['table']] = \Illuminate\Support\Str::headline($f['table']);
            }
        }
        return array_values($tables);
    }

    /**
     * Summary list of column display labels in this template.
     */
    public function getColumnsSummaryAttribute(): array
    {
        $fields = $this->query_config['selected_fields'] ?? [];
        $cols = [];
        foreach ($fields as $f) {
            $cols[] = $f['alias'] ?? $f['column'] ?? '';
        }
        return array_values(array_filter($cols));
    }
}
