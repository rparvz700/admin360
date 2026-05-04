<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenericDocumentCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'generic_document_categories';
    protected $fillable = [
        'category_name',
        'description',
    ];
    public function documents()
    {
        return $this->hasMany(GenericDocument::class, 'category_id');
    }
}
