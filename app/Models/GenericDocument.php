<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GenericDocument extends Model
{   
    // Tell Laravel to include virtual attributes in arrays/JSON
    protected $appends = ['primary_text'];

    use HasFactory;
    protected $table = 'generic_documents';
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'category_id',
        'issue_date',
        'expiry_date',
        'file_path',
    ];
    public function documentable()
    {
        return $this->morphTo();
    }
    public function category()
    {
        return $this->belongsTo(GenericDocumentCategory::class, 'category_id');
    }
    public function attributes()
    {
        return $this->hasMany(GenericDocumentAttribute::class, 'category_id', 'category_id');
    }
    public function attributeValues()
    {
        return $this->hasMany(GenericDocumentAttributeValue::class, 'document_id');
    }

    public function getPrimaryTextAttribute()
    {
        // Return empty string if no related model is loaded
        if (!$this->documentable) {
            return '';
        }

        // Load config
        $documentableTypes = config('app.documentableTypes');
        $owner = $this->documentable;
        $ownerClass = get_class($owner);
        // Find the matching config entry
        foreach ($documentableTypes as $type => $data) {
            if (isset($data['class']) && $data['class'] === $ownerClass) {
                $field = $data['display_field'] ?? 'name';
                return $owner->{$field} ?? '';
            }
        }

        // Fallback if no match found
        return '';
    }



}
