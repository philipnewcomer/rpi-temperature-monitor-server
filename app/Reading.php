<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    public $fillable = [
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
            'temperature' => round($this->temperature),
            'timestamp' => $datetime->toDayDateTimeString()
        ];
    }
}
