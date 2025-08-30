<?php

namespace App\Repositories;

use App\Models\TravelRequest;

class TravelRequestRepository
{
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return TravelRequest::all();
    }
    
    public function getAllByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return TravelRequest::where('user_id', $userId)->get();
    }

    public function findById(int $id): ?TravelRequest
    {
        return TravelRequest::find($id);
    }

    public function create(array $data): TravelRequest
    {
        return TravelRequest::create($data);
    }

    public function update(TravelRequest $travelRequest, array $data): TravelRequest
    {
        $travelRequest->update($data);
        return $travelRequest;
    }

    public function delete(TravelRequest $travelRequest): void
    {
        $travelRequest->delete();
    }
}
