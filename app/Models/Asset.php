<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'asset_tag',
        'asset_name',
        'category_id',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'warranty_expiry',
        'floor_id',
        'location_within_floor',
        'parent_id',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function floor()
    {
        return $this->belongsTo(\App\Models\PropertiesFloor::class, 'floor_id');
    }

    public function parent()
    {
        return $this->belongsTo(Asset::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Asset::class, 'parent_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(AssetAttributeValue::class, 'asset_id');
    }
}
