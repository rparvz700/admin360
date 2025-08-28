<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleDocumentCategory extends Model
{
    use HasFactory;
    protected $table = 'vehicle_document_categories';
    protected $fillable = [
        'category_name',
        'description',
    ];
    public function documents()
    {
        return $this->hasMany(VehicleDocument::class, 'category_id');
    }
}
