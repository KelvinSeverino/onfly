<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterTravelRequestRequest;
use App\Http\Requests\StoreTravelRequestRequest;
use App\Services\TravelRequestService;
use App\Http\Requests\TravelRequestRequest;
use App\Http\Requests\UpdateTravelRequestRequest;
use App\Models\TravelRequest;
use Illuminate\Http\Request;
use App\Http\Resources\TravelRequestResource;

class TravelRequestController extends Controller
{
    public function __construct(
        protected TravelRequestService $service
    ) {}

    public function index(FilterTravelRequestRequest $request)
    {
        $user = $request->user();
        
        $filters = $request->only(['status_code', 'travel_status_id', 'destination', 'start_date', 'end_date']);
        $travelRequests = $this->service->filterTravelRequests($filters, $user);

        return TravelRequestResource::collection($travelRequests)
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreTravelRequestRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $travelRequest = $this->service->createTravelRequest($data, $user);

        return (new TravelRequestResource($travelRequest))
                ->response()
                ->setStatusCode(201);
    }

    public function show(Request $request, TravelRequest $travelRequest)
    {
        $user = $request->user();
        $travelRequest = $this->service->findTravelRequestForUser($travelRequest, $user);

        return new TravelRequestResource($travelRequest);
    }

    public function update(UpdateTravelRequestRequest $request, TravelRequest $travelRequest)
    {
        $user = $request->user();
        $travelRequestUpdated = $this->service->updateTravelRequest($travelRequest, $request->validated(), $user);

        return (new TravelRequestResource($travelRequestUpdated))
                    ->response()
                    ->setStatusCode(200);
    }

    public function approve(Request $request, TravelRequest $travelRequest)
    {
        $user = $request->user();
        $travelRequest = $this->service->approveTravelRequest($travelRequest, $user);

        return (new TravelRequestResource($travelRequest))
            ->response()
            ->setStatusCode(200);
    }

    public function cancel(Request $request, TravelRequest $travelRequest)
    {
        $user = $request->user();
        $travelRequest = $this->service->cancelTravelRequest($travelRequest, $user);
        
        return (new TravelRequestResource($travelRequest))
            ->response()
            ->setStatusCode(200);
    }

    // public function destroy(TravelRequest $travelRequest)
    // {
    //     $this->service->deleteTravelRequest($travelRequest);
    //     return response()->json(null, 204);
    // }
}
