<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleDocumentAttribute extends Model
{
    use HasFactory;
    protected $table = 'vehicle_document_attributes';
    protected $fillable = [
        'category_id',
        'attribute_name',
        'attribute_type',
        'options',
    ];
    public function category()
    {
        return $this->belongsTo(VehicleDocumentCategory::class, 'category_id');
    }
    public function values()
    {
        return $this->hasMany(VehicleDocumentAttributeValue::class, 'attribute_id');
    }
}
