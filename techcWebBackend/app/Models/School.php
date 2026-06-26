<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $guarded = [];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function places()
    {
        return $this->hasMany(Place::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
