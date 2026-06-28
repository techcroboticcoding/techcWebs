<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    protected $table = 'student_progress';

    protected $fillable = [
        'student_id',
        'progress',
        'level',
        'status',
        'teacher_note',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}