<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TravelRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'requester_id', 'travel_status_id',
        'requester_name', 'destination', 
        'departure_date', 'return_date',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function status()
    {
        return $this->belongsTo(TravelStatus::class, 'travel_status_id');
    }
}
