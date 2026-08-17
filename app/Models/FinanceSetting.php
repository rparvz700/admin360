<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceSetting extends Model
{
    use HasFactory;

    protected $table = 'finance_settings';

    protected $fillable = [
        'key',
        'label',
        'description',
        'value_numeric',
        'group',
        'is_active',
    ];

    protected $casts = [
        'value_numeric' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public static function getValue(string $key, float $default = 0.0): float
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('finance_settings')) {
                return $default;
            }
            $setting = static::where('key', $key)->where('is_active', true)->first();
            if ($setting && $setting->value_numeric !== null) {
                return (float) $setting->value_numeric;
            }
        } catch (\Throwable $e) {
            // Fallback if table doesn't exist yet
        }
        return $default;
    }

    public static function setValue(string $key, float $value, string $label = '', string $description = ''): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'label' => $label ?: ucfirst(str_replace('_', ' ', $key)),
                'description' => $description,
                'value_numeric' => $value,
                'is_active' => true,
            ]
        );
    }
}
