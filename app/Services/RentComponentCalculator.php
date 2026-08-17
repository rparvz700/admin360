<?php

namespace App\Services;

use App\Models\PropertiesFloor;
use App\Models\VatTax;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RentComponentCalculator
{
    public const COMPONENTS = [
        'floor_area' => [
            'label' => 'Floor Area',
            'area_field' => 'floor_area_sft',
        ],
        'car_parking' => [
            'label' => 'Car Parking',
            'area_field' => 'car_parking',
        ],
        'dg_space' => [
            'label' => 'DG Space',
            'area_field' => 'dg_space_sft',
        ],
        'store_space' => [
            'label' => 'Store Space',
            'area_field' => 'store_space_sft',
        ],
    ];

    public function rowsFromRequest(Request $request, ?VatTax $vatTax = null): Collection
    {
        $input = collect($request->input('rent_components', []));
        $areaFallbacks = $this->areasFromRequest($request);
        $isAtSource = $request->boolean('is_at_source');

        return collect(self::COMPONENTS)
            ->map(function (array $meta, string $type) use ($input, $areaFallbacks, $vatTax, $isAtSource) {
                $component = $input->get($type, []);
                $area = $this->moneyValue($component['area_sft'] ?? $areaFallbacks[$type] ?? 0);
                $rentAmount = $this->moneyValue($component['rent_amount'] ?? 0);

                return $this->calculateRow($type, $area, $rentAmount, $vatTax, $isAtSource);
            })
            ->filter(fn (array $row) => $row['area_sft'] > 0 || $row['rent_amount'] > 0)
            ->values();
    }

    public function rowsFromFloor(?PropertiesFloor $floor, ?VatTax $vatTax = null, Collection|array $existingRows = [], bool $isAtSource = false): Collection
    {
        $existing = collect($existingRows)->keyBy('component_type');

        return collect(self::COMPONENTS)
            ->map(function (array $meta, string $type) use ($floor, $vatTax, $existing, $isAtSource) {
                $existingRow = $existing->get($type);
                $area = $existingRow ? $this->moneyValue($existingRow->area_sft ?? 0) : $this->areaFromFloor($floor, $type);
                $rentAmount = $existingRow ? $this->moneyValue($existingRow->rent_amount ?? 0) : 0;

                return $this->calculateRow($type, $area, $rentAmount, $vatTax, $isAtSource);
            });
    }

    public function totals(Collection $rows): array
    {
        return [
            'base_rent' => round($rows->sum('rent_amount'), 2),
            'vat' => round($rows->sum('vat_amount'), 2),
            'tax' => round($rows->sum('tax_amount'), 2),
            'total' => round($rows->sum('total_amount'), 2),
        ];
    }

    public function saveRows(int $rentBaseId, Collection $rows): void
    {
        foreach ($rows as $row) {
            \App\Models\RentComponent::create([
                'rent_base_id' => $rentBaseId,
                'component_type' => $row['component_type'],
                'area_sft' => $row['area_sft'],
                'rate' => $row['rate'],
                'rent_amount' => $row['rent_amount'],
                'vat_applicable' => $row['vat_applicable'],
                'vat_amount' => $row['vat_amount'],
                'tax_amount' => $row['tax_amount'],
                'total_amount' => $row['total_amount'],
            ]);
        }
    }

    public function floorAreaPayload(?PropertiesFloor $floor): array
    {
        return collect(self::COMPONENTS)
            ->mapWithKeys(fn (array $meta, string $type) => [$type => $this->areaFromFloor($floor, $type)])
            ->all();
    }

    public function floorAreaPayloadFromFloors(Collection $floors): array
    {
        return [
            'floor_area' => (float) $floors->sum('floor_area_sft'),
            'car_parking' => (float) $floors->sum('car_parking'),
            'dg_space' => (float) $floors->sum('dg_space_sft'),
            'store_space' => (float) $floors->sum('store_space_sft'),
        ];
    }

    private function calculateRow(string $type, float $area, float $rentAmount, ?VatTax $vatTax, bool $isAtSource = false): array
    {
        $rentAmount = round($rentAmount, 2);
        $rate = $area > 0 ? round($rentAmount / $area, 2) : 0.0;
        $vatApplicable = $area >= (float) config('facilities.rent_taxable_area_sft', 150);
        $vatPercent = $vatTax ? $this->moneyValue($vatTax->vat) : 0;
        $taxPercent = $vatTax ? $this->moneyValue($vatTax->tax) : 0;

        if ($isAtSource && $taxPercent > 0) {
            $taxDecimal = $taxPercent / 100.0;
            $denominator = 1.0 - $taxDecimal;
            $grossBeforeVat = $denominator > 0 ? ($rentAmount / $denominator) : $rentAmount;
            $taxAmount = round($grossBeforeVat - $rentAmount, 2);
            $vatAmount = $vatApplicable ? round(($grossBeforeVat * $vatPercent) / 100, 2) : 0.0;
            $totalAmount = round($grossBeforeVat + $vatAmount, 2);
        } else {
            $vatAmount = $vatApplicable ? round(($rentAmount * $vatPercent) / 100, 2) : 0.0;
            $taxAmount = round(($rentAmount * $taxPercent) / 100, 2);
            $totalAmount = round($rentAmount + $vatAmount + $taxAmount, 2);
        }

        return [
            'component_type' => $type,
            'label' => self::COMPONENTS[$type]['label'],
            'area_sft' => $area,
            'rate' => $rate,
            'rent_amount' => $rentAmount,
            'vat_applicable' => $vatApplicable,
            'vat_amount' => $vatAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }

    private function areasFromRequest(Request $request): array
    {
        return [
            'floor_area' => $this->moneyValue($request->input('floor_area_sft')),
            'car_parking' => $this->moneyValue($request->input('car_parking')),
            'dg_space' => $this->moneyValue($request->input('dg_space_sft')),
            'store_space' => $this->moneyValue($request->input('store_space_sft')),
        ];
    }

    private function areaFromFloor(?PropertiesFloor $floor, string $type): float
    {
        if (!$floor) {
            return 0.0;
        }

        $field = self::COMPONENTS[$type]['area_field'];

        return $this->moneyValue($floor->{$field});
    }

    private function moneyValue($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
