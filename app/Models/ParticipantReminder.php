<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantReminder extends Model
{
    protected $fillable = ['user_id', 'key', 'sent_on'];

    protected $casts = ['sent_on' => 'date'];
}
