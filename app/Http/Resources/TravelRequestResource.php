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
            'requester_id' => $this->requester_id,
            'requester_name' => $this->requester_name,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date->format('Y-m-d H:i:s'),
            'return_date' => $this->return_date->format('Y-m-d H:i:s'),
            'status_code' => $this->status->code,
            'status' => $this->status->name,
        ];
    }
}
