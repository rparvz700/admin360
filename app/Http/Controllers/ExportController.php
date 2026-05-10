<?php

namespace App\Http\Controllers;

use App\Exports\TableQueryExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function table(Request $request, string $table): BinaryFileResponse
    {
        $config = $this->tableConfig($table);
        abort_unless($config, 404);

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $model = $config['model'];
        $query = $model::query();

        if (! empty($config['relations'])) {
            $query->with($config['relations']);
        }

        $this->applySearch($query, $request, $config);
        $this->applyFilters($query, $request, $config);
        $this->applyOrdering($query, $request, $config);

        $fileName = ($config['file_name'] ?? $table) . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new TableQueryExport($query, $config['columns'], (int) config('table_exports.chunk_size', 1000)),
            $fileName
        );
    }

    private function tableConfig(string $table): ?array
    {
        $tables = config('table_exports.tables', []);
        $config = $tables[$table] ?? null;

        if (! $config) {
            return null;
        }

        if (! empty($config['extends']) && isset($tables[$config['extends']])) {
            $config = array_replace_recursive($tables[$config['extends']], Arr::except($config, 'extends'));
        }

        return $config;
    }

    private function applySearch(Builder $query, Request $request, array $config): void
    {
        $search = $request->input('search.value', $request->input('search'));

        if (! is_string($search) || trim($search) === '') {
            return;
        }

        $searchable = array_filter($config['search'] ?? [], fn ($column) => ! Str::contains($column, '.'));

        if ($searchable === []) {
            return;
        }

        $query->where(function (Builder $subQuery) use ($searchable, $search) {
            foreach ($searchable as $column) {
                $subQuery->orWhere($column, 'like', '%' . $search . '%');
            }
        });
    }

    private function applyFilters(Builder $query, Request $request, array $config): void
    {
        foreach (($config['filters'] ?? []) as $requestKey => $column) {
            $value = $request->input($requestKey);

            if ($value === null || $value === '' || $value === 'all') {
                continue;
            }

            if (Str::contains($column, '.')) {
                [$relation, $relationColumn] = explode('.', $column, 2);
                $query->whereHas($relation, fn (Builder $relationQuery) => $relationQuery->where($relationColumn, $value));
                continue;
            }

            $query->where($column, $value);
        }
    }

    private function applyOrdering(Builder $query, Request $request, array $config): void
    {
        $orderIndex = $request->input('order.0.column');
        $direction = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $columns = $request->input('columns', []);
        $dataColumn = is_numeric($orderIndex) ? data_get($columns, $orderIndex . '.data') : null;

        if (is_string($dataColumn) && isset($config['columns'][$dataColumn]) && ! Str::contains($dataColumn, '.')) {
            $query->orderBy($dataColumn, $direction);
            return;
        }

        $query->latest('id');
    }
}
