<?php

namespace App\Models;

use App\Models\DataDictionary;
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
        'date_columns_summary',
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

    /**
     * Summary list of filterable date columns across this template's fields and tables.
     */
    public function getDateColumnsSummaryAttribute(): array
    {
        $config = $this->query_config ?? [];
        $selectedFields = $config['selected_fields'] ?? [];
        $dateCols = [];

        // 1. Check selected fields
        foreach ($selectedFields as $f) {
            $type = strtolower($f['data_type'] ?? '');
            $col  = strtolower($f['column'] ?? '');
            $alias = $f['alias'] ?? $f['column'] ?? '';
            if (in_array($type, ['date', 'datetime', 'timestamp'], true) || str_contains($col, 'date') || str_contains($col, '_at')) {
                $key = "{$f['table']}.{$f['column']}";
                $dateCols[$key] = [
                    'table'  => $f['table'],
                    'column' => $f['column'],
                    'label'  => $alias,
                    'type'   => $type ?: 'date',
                ];
            }
        }

        // 2. If none from selected fields, check referenced tables in data dictionary
        if (empty($dateCols)) {
            $tables = array_unique(array_filter(array_column($selectedFields, 'table')));
            if (!empty($tables)) {
                $dictCols = DataDictionary::whereIn('table_name', $tables)
                    ->whereIn('data_type', ['date', 'datetime', 'timestamp'])
                    ->where('is_filterable', true)
                    ->orderBy('sort_order')
                    ->get();

                foreach ($dictCols as $dc) {
                    $key = "{$dc->table_name}.{$dc->column_name}";
                    $dateCols[$key] = [
                        'table'  => $dc->table_name,
                        'column' => $dc->column_name,
                        'label'  => "{$dc->display_name} (" . \Illuminate\Support\Str::headline($dc->table_name) . ")",
                        'type'   => $dc->data_type,
                    ];
                }
            }
        }

        return array_values($dateCols);
    }
}
