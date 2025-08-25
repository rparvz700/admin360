<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcenter extends Model
{
    use HasFactory;

    protected $guarded  = [];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }


    public function subHead()
    {
        return  $this->belongsTo(User::class, 'head_id', 'id');
    }
}
