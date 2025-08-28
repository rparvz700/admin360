<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleDocumentAttributeValue extends Model
{
    use HasFactory;
    protected $table = 'vehicle_document_attribute_values';
    protected $fillable = [
        'document_id',
        'attribute_id',
        'value',
    ];
    public function document()
    {
        return $this->belongsTo(VehicleDocument::class, 'document_id');
    }
    public function attribute()
    {
        return $this->belongsTo(VehicleDocumentAttribute::class, 'attribute_id');
    }
}
