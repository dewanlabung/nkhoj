<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingEmailLog extends Model
{
    protected $table = 'outgoing_email_log';
    protected $guarded = [];

    protected $hidden = ['mime'];
}
