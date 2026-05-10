<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TableQueryExport implements FromQuery, WithHeadings, WithMapping, WithCustomChunkSize
{
    use Exportable;

    public function __construct(
        private readonly Builder $query,
        private readonly array $columns,
        private readonly int $chunkSize = 1000
    ) {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($row): array
    {
        return array_map(fn (string $key) => $this->valueFor($row, $key), array_keys($this->columns));
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    private function valueFor($row, string $key): mixed
    {
        $value = data_get($row, $key);

        if ($value instanceof Collection) {
            return $value->pluck('name')->filter()->implode(', ');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
