<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_date',
        'partner_name',
        'title',
        'category',
        'content',
        'status',
        'reporter_name',
        'reporter_email',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];
}