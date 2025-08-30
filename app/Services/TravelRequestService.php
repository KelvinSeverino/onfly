<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Repositories\TravelRequestRepository;

class TravelRequestService
{
    public function __construct(
        protected TravelRequestRepository $repository
    ) {}

    public function getTravelRequests()
    {
        return $this->repository->getAll();
    }

    public function getTravelRequestsByUserId(int $userId)
    {
        return $this->repository->getAllByUserId($userId);
    }

    public function findTravelRequest(int $id)
    {
        return $this->repository->findById($id);
    }

    public function createTravelRequest(array $data): TravelRequest
    {
        return $this->repository->create($data);
    }

    public function updateTravelRequest(TravelRequest $travelRequest, array $data): TravelRequest
    {
        return $this->repository->update($travelRequest, $data);
    }

    public function deleteTravelRequest(TravelRequest $travelRequest): void
    {
        $this->repository->delete($travelRequest);
    }
}
