<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'level',
    ];

    // A grade level has many sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
