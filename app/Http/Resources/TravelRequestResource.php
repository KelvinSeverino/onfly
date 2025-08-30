<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TravelRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'requester_name' => $this->requester_name,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date,
            'return_date' => $this->return_date,
            'status_code' => $this->status->code,
            'status' => $this->status->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
