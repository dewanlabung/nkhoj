<?php

namespace App\Models\LoggingAnalytics;

use Illuminate\Database\Eloquent\Model;

class OutgoingEmailLog extends Model
{
    protected $table = 'outgoing_email_log';
    protected $guarded = [];

    protected $hidden = ['mime'];
}
