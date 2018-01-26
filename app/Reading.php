<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    public $fillable = [
        'remote_ip',
        'temperature',
        'timestamp'
    ];

    public $timestamps = false;

    public static $warningLevel = 40;
    public static $dangerLevel = 32;

    public function toArray()
    {
        $datetime = new Carbon($this->timestamp);

        return [
            'human_time_diff' => $datetime->diffForHumans(),
            'remote_ip' => $this->remote_ip,
            'temperature' => round($this->temperature),
            'timestamp' => $datetime->toDayDateTimeString()
        ];
    }
}
