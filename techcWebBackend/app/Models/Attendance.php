<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

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

        // legacy kolom lama
        'tanggal',
        'jam',
        'nama',
        'uid',
        'rfid_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'raw_payload' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'member_id', 'id');
    }
}