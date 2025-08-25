<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetAttribute extends Model
{
    use HasFactory;

    protected $table = 'asset_attributes';

    protected $fillable = [
        'category_id',
        'attribute_name',
        'attribute_type',
        'options',
    ];

    protected $casts = [
        'options' => 'array', // store/retrieve as array (json)
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function values()
    {
        return $this->hasMany(\App\Models\AssetAttributeValue::class, 'attribute_id');
    }
}
