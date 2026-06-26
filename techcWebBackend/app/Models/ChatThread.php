<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatThread extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
