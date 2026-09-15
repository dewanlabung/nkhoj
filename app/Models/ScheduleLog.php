<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleLog extends Model
{
    protected $table = 'schedule_log';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'id'                  => 'integer',
        'ran_at'              => 'datetime',
        'duration'            => 'integer',
        'count_in_last_hour'  => 'integer',
        'exit_code'           => 'integer',
    ];
}
