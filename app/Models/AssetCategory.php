<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetCategory extends Model
{
    use HasFactory;

    protected $table = 'asset_categories';

    protected $fillable = [
        'category_name',
        'description',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function attributes()
    {
        return $this->hasMany(AssetAttribute::class, 'category_id');
    }
}
