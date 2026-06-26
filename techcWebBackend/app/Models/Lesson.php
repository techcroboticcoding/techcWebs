<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $guarded = [];

    public function categoryData()
    {
        return $this->belongsTo(LessonCategory::class, 'lesson_category_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function progressRecords()
    {
        return $this->hasMany(ProgressRecord::class);
    }
}
