<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'attendance_date',
        'attendance_time',
        'member_id',
        'name',
        'status',
        'source',
        'device_name',
        'note',
        'unique_hash',
        'raw_payload',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'raw_payload' => 'array',
    ];
}