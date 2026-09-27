<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomReportExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize
{
    protected $query;
    protected array $headers;

    public function __construct($query, array $headers)
    {
        $this->query   = $query;
        $this->headers = $headers;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return array_map(function ($h) {
            $alias = $h['alias'];
            if (!empty($h['aggregate'])) {
                $agg = strtoupper($h['aggregate']);
                return "{$alias} ({$agg})";
            }
            return $alias;
        }, $this->headers);
    }

    public function map($row): array
    {
        $values = [];
        foreach ($this->headers as $h) {
            $key = $h['key'];
            $val = $row->$key ?? null;

            // Format numbers and dates nicely if needed
            if ($h['data_type'] === 'decimal' && is_numeric($val)) {
                $values[] = number_format((float)$val, 2, '.', '');
            } else {
                $values[] = $val ?? '';
            }
        }
        return $values;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
