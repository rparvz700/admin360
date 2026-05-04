<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenericDocumentAttributeValue extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'generic_document_attribute_values';
    protected $fillable = [
        'document_id',
        'attribute_id',
        'value',
    ];
    public function document()
    {
        return $this->belongsTo(GenericDocument::class, 'document_id');
    }
    public function attribute()
    {
        return $this->belongsTo(GenericDocumentAttribute::class, 'attribute_id');
    }
}
