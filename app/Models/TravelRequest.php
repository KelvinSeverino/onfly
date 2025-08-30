<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelRequest extends Model
{
    protected $fillable = [
        'user_id', 'travel_status_id',
        'requester_name', 'destination', 
        'departure_date', 'return_date',
    ];

    public function status()
    {
        return $this->belongsTo(TravelStatus::class, 'travel_status_id');
    }
}
