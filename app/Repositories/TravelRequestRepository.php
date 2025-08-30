<?php

namespace App\Repositories;

use App\Models\TravelRequest;
use App\Models\TravelStatus;

class TravelRequestRepository
{
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return TravelRequest::all();
    }
    
    public function getAllByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return TravelRequest::where('requester_id', $userId)->get();
    }

    public function filter(array $filters, $user): \Illuminate\Database\Eloquent\Collection
    {
        $query = TravelRequest::query();
        if ($user->role !== 'admin') {
            $query->where('requester_id', $user->id);
        }
        if (!empty($filters['status_code'])) {
            $status = TravelStatus::where('code', $filters['status_code'])->first();
            if ($status) {
                $query->where('travel_status_id', $status->id);
            }
        }
        if (!empty($filters['destination'])) {
            $query->where('destination', $filters['destination']);
        }
        if (!empty($filters['start_date'])) {
            $startDate = $filters['start_date'] . ' 00:00:00';
            $query->where('departure_date', '>=', $startDate);
        }
        if (!empty($filters['end_date'])) {
            $endDate = $filters['end_date'] . ' 23:59:59';
            $query->where('return_date', '<=', $endDate);
        }
        return $query->get();
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
