<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesFloor extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'properties_floors';

    protected $fillable = [
        'building_id',
        'agreement_id',
        'owner_id',
        'floor_label',
        'floor_area_sft',
        'premises_type',
        'car_parking',
        'dg_space_sft',
        'store_space_sft',
        'project_id',
        'status',
        'created_at',
        'updated_at',
    ];

    public function building()
    {
        return $this->belongsTo(PropertiesBuilding::class, 'building_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // ->logOnly(['agreement_id', 'building_id'])
            ->logAll()
            ->logExcept(['updated_at'])
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs(); 
    }
}
