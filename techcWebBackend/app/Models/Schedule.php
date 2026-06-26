<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'schedule_student')
            ->withPivot('status')
            ->withTimestamps();
    }
}
