<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAttributeValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_attribute_values';

    protected $fillable = [
        'asset_id',
        'attribute_id',
        'value',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function attribute()
    {
        return $this->belongsTo(AssetAttribute::class, 'attribute_id');
    }
}
