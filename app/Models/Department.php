<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $guarded  = [];

    public function depHead()
    {
        return $this->belongsTo(User::class, 'hod', 'id');
    }
}
