<?php

namespace App\Services;

use App\Models\DataDictionary;
use App\Models\DataDictionaryJoin;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReportQueryCompiler
{
    /**
     * Cached whitelist of reportable data dictionary fields.
     */
    protected ?array $whitelist = null;

    /**
     * Cached join adjacency map.
     */
    protected ?array $joinMap = null;

    /**
     * Compiled headers for display/export.
     */
    protected array $headers = [];

    /**
     * Compile AST query configuration into a Laravel Query Builder.
     *
     * @param array $config JSON configuration from builder
     * @return array ['query' => Builder, 'headers' => array, 'sql' => string, 'base_table' => string]
     */
    public function compile(array $config): array
    {
        $this->loadMetadata();

        $selectedFields = $config['selected_fields'] ?? [];
        if (empty($selectedFields)) {
            throw new InvalidArgumentException("At least one column must be selected.");
        }

        // 1. Whitelist validation for selected fields
        $this->validateSelectedFields($selectedFields);

        // 2. Validate filter conditions
        $filters = $config['filters'] ?? null;
        if (!empty($filters)) {
            $this->validateFiltersRecursive($filters);
        }

        // 3. Resolve all referenced tables
        $allTables = $this->collectAllReferencedTables($selectedFields, $filters);
        $baseTable = $this->determineBaseTable($selectedFields, $allTables);

        // 4. Initialize Query Builder on base table
        $query = DB::table($baseTable);

        // 5. Resolve and apply JOIN graph
        $joinedTables = [$baseTable => true];
        $this->applyJoinGraph($query, $baseTable, $allTables, $joinedTables);

        // 6. Apply SoftDeletes where applicable
        $this->applySoftDeletes($query, array_keys($joinedTables));

        // 7. Compile Columns, Aggregates & GROUP BY
        $hasAggregates = false;
        $selectExpressions = [];
        $groupByColumns = [];
        $this->headers = [];

        foreach ($selectedFields as $idx => $field) {
            $table     = $field['table'];
            $column    = $field['column'];
            $rawAlias  = trim($field['alias'] ?? '');
            $aggregate = strtolower(trim($field['aggregate'] ?? ''));

            $dictEntry = $this->whitelist[$table][$column];
            $aliasName = $rawAlias !== '' ? $rawAlias : $dictEntry->display_name;
            // Clean alias for SQL (strip double quotes)
            $safeAlias = str_replace('"', '', $aliasName);

            $this->headers[] = [
                'key'         => "col_{$idx}",
                'alias'       => $safeAlias,
                'table'       => $table,
                'column'      => $column,
                'aggregate'   => $aggregate,
                'data_type'   => $dictEntry->data_type,
            ];

            if ($aggregate !== '' && in_array($aggregate, ['sum', 'avg', 'count', 'max', 'min'], true)) {
                $hasAggregates = true;
                $aggUpper = strtoupper($aggregate);

                if ($aggregate === 'avg') {
                    $selectExpressions[] = DB::raw("ROUND(AVG({$table}.{$column})::numeric, 2) as \"col_{$idx}\"");
                } elseif ($aggregate === 'count') {
                    $selectExpressions[] = DB::raw("COUNT({$table}.{$column}) as \"col_{$idx}\"");
                } else {
                    $selectExpressions[] = DB::raw("{$aggUpper}({$table}.{$column}) as \"col_{$idx}\"");
                }
            } else {
                $selectExpressions[] = DB::raw("{$table}.{$column} as \"col_{$idx}\"");
                $groupByColumns[] = "{$table}.{$column}";
            }
        }

        $query->select($selectExpressions);

        // If aggregates are present, all non-aggregated columns MUST be in GROUP BY
        if ($hasAggregates && !empty($groupByColumns)) {
            $query->groupBy($groupByColumns);
        }

        // 8. Compile Filters
        if (!empty($filters) && !empty($filters['conditions'])) {
            $this->compileFilterGroup($query, $filters);
        }

        // 9. Compile Sorting
        $sorts = $config['sorts'] ?? [];
        $hasSort = false;
        if (!empty($sorts)) {
            foreach ($sorts as $sort) {
                $sTable = $sort['table'] ?? null;
                $sCol   = $sort['column'] ?? null;
                $sDir   = strtolower($sort['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

                if ($sTable && $sCol && isset($this->whitelist[$sTable][$sCol])) {
                    $query->orderBy("{$sTable}.{$sCol}", $sDir);
                    $hasSort = true;
                }
            }
        }

        // Fallback order by to guarantee predictable pagination and chunking
        if (!$hasSort) {
            if ($hasAggregates && !empty($groupByColumns)) {
                $query->orderBy($groupByColumns[0], 'asc');
            } else {
                $firstField = $selectedFields[0];
                $query->orderBy("{$firstField['table']}.{$firstField['column']}", 'asc');
            }
        }

        // 10. Generate Pretty Raw SQL for UI Preview
        $rawSql = $this->generateSqlString($query);

        return [
            'query'      => $query,
            'headers'    => $this->headers,
            'sql'        => $rawSql,
            'base_table' => $baseTable,
        ];
    }

    /**
     * Load whitelist and join graph into memory.
     */
    protected function loadMetadata(): void
    {
        if ($this->whitelist === null) {
            $fields = DataDictionary::reportable()->get();
            $this->whitelist = [];
            foreach ($fields as $field) {
                $this->whitelist[$field->table_name][$field->column_name] = $field;
            }
        }

        if ($this->joinMap === null) {
            $this->joinMap = DataDictionaryJoin::getJoinAdjacencyMap();
        }
    }

    /**
     * Validate selected fields against the whitelist.
     */
    protected function validateSelectedFields(array $fields): void
    {
        foreach ($fields as $field) {
            $table = $field['table'] ?? null;
            $column = $field['column'] ?? null;

            if (!$table || !$column) {
                throw new InvalidArgumentException("Selected field missing table or column name.");
            }

            if (!isset($this->whitelist[$table][$column])) {
                throw new InvalidArgumentException("Field '{$table}.{$column}' is not registered or not reportable.");
            }

            $aggregate = strtolower(trim($field['aggregate'] ?? ''));
            if ($aggregate !== '') {
                if (!in_array($aggregate, ['sum', 'avg', 'count', 'max', 'min'], true)) {
                    throw new InvalidArgumentException("Unsupported aggregate function '{$aggregate}'.");
                }
                if (in_array($aggregate, ['sum', 'avg'], true) && !$this->whitelist[$table][$column]->is_aggregatable) {
                    throw new InvalidArgumentException("Cannot apply {$aggregate} to non-numeric column '{$table}.{$column}'.");
                }
            }
        }
    }

    /**
     * Validate filters recursively against whitelist.
     */
    protected function validateFiltersRecursive(array $group): void
    {
        $conditions = $group['conditions'] ?? [];
        foreach ($conditions as $cond) {
            if (isset($cond['logical_operator']) && isset($cond['conditions'])) {
                $this->validateFiltersRecursive($cond);
            } else {
                $table = $cond['table'] ?? null;
                $column = $cond['column'] ?? null;

                if (!$table || !$column) {
                    throw new InvalidArgumentException("Filter condition missing table or column.");
                }

                if (!isset($this->whitelist[$table][$column])) {
                    throw new InvalidArgumentException("Filter field '{$table}.{$column}' is not permitted.");
                }

                if (!$this->whitelist[$table][$column]->is_filterable) {
                    throw new InvalidArgumentException("Column '{$table}.{$column}' is marked as non-filterable.");
                }
            }
        }
    }

    /**
     * Collect all unique tables from selection and filters.
     */
    protected function collectAllReferencedTables(array $fields, ?array $filters): array
    {
        $tables = [];
        foreach ($fields as $f) {
            $tables[$f['table']] = true;
        }

        if (!empty($filters)) {
            $this->extractFilterTables($filters, $tables);
        }

        return array_keys($tables);
    }

    protected function extractFilterTables(array $group, array &$tables): void
    {
        foreach ($group['conditions'] ?? [] as $cond) {
            if (isset($cond['logical_operator'])) {
                $this->extractFilterTables($cond, $tables);
            } else {
                if (!empty($cond['table'])) {
                    $tables[$cond['table']] = true;
                }
            }
        }
    }

    /**
     * Determine primary base table (table with highest occurrence in selected fields).
     */
    protected function determineBaseTable(array $fields, array $allTables): string
    {
        $counts = array_count_values(array_column($fields, 'table'));
        arsort($counts);
        return array_key_first($counts) ?: $allTables[0];
    }

    /**
     * BFS Multi-Hub Join Resolver.
     * Finds shortest join paths to all target tables and safely applies LEFT JOINs.
     */
    protected function applyJoinGraph(Builder $query, string $baseTable, array $allTables, array &$joinedTables): void
    {
        $unjoined = array_diff($allTables, [$baseTable]);

        foreach ($unjoined as $targetTable) {
            if (isset($joinedTables[$targetTable])) {
                continue;
            }

            // Find shortest path from any already-joined table to $targetTable
            $path = $this->findShortestJoinPath(array_keys($joinedTables), $targetTable);

            if ($path === null) {
                throw new InvalidArgumentException("No registered join path connects '{$baseTable}' to '{$targetTable}'. Please register this relationship in the Data Dictionary Joins.");
            }

            // Apply each step along the path
            foreach ($path as $edge) {
                $target = $edge['target_table'];
                if (!isset($joinedTables[$target])) {
                    $joinMethod = strtolower($edge['join_type']) === 'inner' ? 'join' : 'leftJoin';
                    $query->$joinMethod(
                        $target,
                        "{$edge['from_table']}.{$edge['from_column']}",
                        '=',
                        "{$edge['to_table']}.{$edge['to_column']}"
                    );
                    $joinedTables[$target] = true;
                }
            }
        }
    }

    /**
     * BFS Pathfinding: finds shortest path from any source table to target table.
     */
    protected function findShortestJoinPath(array $sourceTables, string $targetTable): ?array
    {
        $queue = [];
        $visited = [];

        foreach ($sourceTables as $src) {
            $queue[] = ['table' => $src, 'path' => []];
            $visited[$src] = true;
        }

        while (!empty($queue)) {
            $current = array_shift($queue);
            $currTable = $current['table'];
            $currPath  = $current['path'];

            if ($currTable === $targetTable) {
                return $currPath;
            }

            $neighbors = $this->joinMap[$currTable] ?? [];
            foreach ($neighbors as $edge) {
                $neighborTable = $edge['target_table'];
                if (!isset($visited[$neighborTable])) {
                    $visited[$neighborTable] = true;
                    $newPath = $currPath;
                    $newPath[] = $edge;
                    $queue[] = ['table' => $neighborTable, 'path' => $newPath];
                }
            }
        }

        return null;
    }

    /**
     * Apply soft delete where clauses for tables with deleted_at column.
     */
    protected function applySoftDeletes(Builder $query, array $tables): void
    {
        foreach ($tables as $table) {
            if (isset($this->whitelist[$table]['deleted_at'])) {
                $query->whereNull("{$table}.deleted_at");
            }
        }
    }

    /**
     * Recursive Filter AST compilation.
     */
    protected function compileFilterGroup(Builder $query, array $group, bool $isOr = false): void
    {
        $logicalOp = strtoupper($group['logical_operator'] ?? 'AND');
        $conditions = $group['conditions'] ?? [];

        if (empty($conditions)) {
            return;
        }

        $method = $isOr ? 'orWhere' : 'where';

        $query->$method(function (Builder $subQuery) use ($logicalOp, $conditions) {
            foreach ($conditions as $idx => $cond) {
                $isChildOr = ($logicalOp === 'OR');

                if (isset($cond['logical_operator']) && isset($cond['conditions'])) {
                    // Nested Group
                    $this->compileFilterGroup($subQuery, $cond, $isChildOr);
                } else {
                    // Leaf condition
                    $this->applyCondition($subQuery, $cond, $isChildOr);
                }
            }
        });
    }

    /**
     * Apply a single leaf condition.
     */
    protected function applyCondition(Builder $query, array $cond, bool $isOr): void
    {
        $table    = $cond['table'];
        $column   = $cond['column'];
        $operator = strtolower(trim($cond['operator'] ?? '='));
        $value    = $cond['value'] ?? null;
        $fieldRef = "{$table}.{$column}";

        $whereMethod = $isOr ? 'orWhere' : 'where';
        $whereNullMethod = $isOr ? 'orWhereNull' : 'whereNull';
        $whereNotNullMethod = $isOr ? 'orWhereNotNull' : 'whereNotNull';
        $whereInMethod = $isOr ? 'orWhereIn' : 'whereIn';
        $whereNotInMethod = $isOr ? 'orWhereNotIn' : 'whereNotIn';
        $whereBetweenMethod = $isOr ? 'orWhereBetween' : 'whereBetween';
        $whereRawMethod = $isOr ? 'orWhereRaw' : 'whereRaw';

        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
                $query->$whereMethod($fieldRef, $operator, $value);
                break;

            case 'contains':
                // PostgreSQL ILIKE for case-insensitive partial match
                $query->$whereRawMethod("{$fieldRef}::text ILIKE ?", ["%{$value}%"]);
                break;

            case 'starts_with':
                $query->$whereRawMethod("{$fieldRef}::text ILIKE ?", ["{$value}%"]);
                break;

            case 'ends_with':
                $query->$whereRawMethod("{$fieldRef}::text ILIKE ?", ["%{$value}"]);
                break;

            case 'in':
                $arr = is_array($value) ? $value : array_map('trim', explode(',', (string)$value));
                $query->$whereInMethod($fieldRef, $arr);
                break;

            case 'not_in':
                $arr = is_array($value) ? $value : array_map('trim', explode(',', (string)$value));
                $query->$whereNotInMethod($fieldRef, $arr);
                break;

            case 'is_null':
                $query->$whereNullMethod($fieldRef);
                break;

            case 'is_not_null':
                $query->$whereNotNullMethod($fieldRef);
                break;

            case 'between':
                $range = is_array($value) ? $value : array_map('trim', explode(',', (string)$value));
                if (count($range) >= 2) {
                    $query->$whereBetweenMethod($fieldRef, [$range[0], $range[1]]);
                }
                break;

            default:
                $query->$whereMethod($fieldRef, '=', $value);
                break;
        }
    }

    /**
     * Generate syntax-ready SQL string with bindings substituted for display.
     */
    protected function generateSqlString(Builder $query): string
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . addslashes((string)$binding) . "'";
            $sql = preg_replace('/\?/', (string)$value, $sql, 1);
        }

        return $sql;
    }
}
