<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GenericDocumentAttribute extends Model
{
    use HasFactory;
    protected $table = 'generic_document_attributes';
    protected $fillable = [
        'category_id',
        'attribute_name',
        'attribute_type',
        'options',
    ];
    public function category()
    {
        return $this->belongsTo(GenericeDocumentCategory::class, 'category_id');
    }
    public function values()
    {
        return $this->hasMany(GenericDocumentAttributeValue::class, 'attribute_id');
    }
}
