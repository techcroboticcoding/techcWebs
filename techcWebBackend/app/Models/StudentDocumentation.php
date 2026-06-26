<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDocumentation extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'description',
        'image_path',
        'status',
    ];

    protected $appends = [
        'image_url',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }
}