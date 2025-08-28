<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleDocument extends Model
{
    use HasFactory;
    protected $table = 'vehicle_documents';
    protected $fillable = [
        'vehicle_id',
        'category_id',
        'issue_date',
        'expiry_date',
        'file_path',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function category()
    {
        return $this->belongsTo(VehicleDocumentCategory::class, 'category_id');
    }
    public function attributes()
    {
        return $this->hasMany(VehicleDocumentAttribute::class, 'category_id', 'category_id');
    }
    public function attributeValues()
    {
        return $this->hasMany(VehicleDocumentAttributeValue::class, 'document_id');
    }
}
