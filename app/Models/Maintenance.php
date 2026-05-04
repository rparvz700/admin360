<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance';

    protected $fillable = [
        'agreement_id',
        'maintain_from',
        'meter_documents_handover',
        'created_at',
        'updated_at',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }
}
