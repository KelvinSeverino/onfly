<?php

namespace App\Http\Controllers;

use App\Services\TravelRequestService;
use App\Http\Requests\TravelRequestRequest;
use App\Models\TravelRequest;
use Illuminate\Http\Request;
use App\Http\Resources\TravelRequestResource;

class TravelRequestController extends Controller
{
    public function __construct(
        protected TravelRequestService $service
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $travelRequests = $this->service->getTravelRequestsByUserId($userId);
        return TravelRequestResource::collection($travelRequests)
                    ->response()
                    ->setStatusCode(200);
    }

    public function store(TravelRequestRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $travelRequest = $this->service->createTravelRequest($data);
        return (new TravelRequestResource($travelRequest))
                ->response()
                ->setStatusCode(201);
    }

    public function show(TravelRequest $travelRequest)
    {
        $travelRequest = $this->service->findTravelRequest($travelRequest->id);
        return new TravelRequestResource($travelRequest);
    }

    public function update(TravelRequestRequest $request, TravelRequest $travelRequest)
    {
        $travelRequestUpdated = $this->service->updateTravelRequest($travelRequest, $request->validated());
        return (new TravelRequestResource($travelRequestUpdated))
                    ->response()
                    ->setStatusCode(200);
    }

    public function destroy(TravelRequest $travelRequest)
    {
        $this->service->deleteTravelRequest($travelRequest);
        return response()->json(null, 204);
    }
}
