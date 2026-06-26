<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function progressRecords()
    {
        return $this->hasMany(ProgressRecord::class);
    }

    public function notifications()
    {
        return $this->hasMany(StudentNotification::class);
    }

    public function projects()
    {
        return $this->hasMany(StudentProject::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
