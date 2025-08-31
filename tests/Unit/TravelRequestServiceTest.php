<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\TravelStatus;
use App\Repositories\TravelRequestRepository;
use App\Services\TravelRequestService;
use App\Exceptions\Domain\TravelRequest\TravelRequestActionNotAllowedException;
use App\Exceptions\AdminOnlyActionException;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TravelRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;
    protected $userRepository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TravelRequestRepository::class);
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->service = new TravelRequestService($this->repository, $this->userRepository);
    }

    public function test_admin_can_create_travel_request_for_another_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $status = TravelStatus::factory()->create(['code' => 'S']);

        $data = [
            'requester_id' => $otherUser->id,
            'destination' => 'Paris',
            'departure_date' => '2025-09-01 10:00:00',
            'return_date' => '2025-09-05 18:00:00',
        ];

        $this->userRepository
            ->shouldReceive('findById')
            ->with($otherUser->id)
            ->andReturn($otherUser);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function($d) use ($otherUser, $status) {
                return $d['requester_id'] === $otherUser->id
                    && $d['requester_name'] === $otherUser->name
                    && $d['travel_status_id'] === $status->id;
            }))
            ->andReturn(new TravelRequest(array_merge($data, [
                'requester_name' => $otherUser->name,
                'travel_status_id' => $status->id
            ])));

        $result = $this->service->createTravelRequest($data, $admin);

        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals($otherUser->id, $result->requester_id);
    }

    public function test_user_cannot_create_travel_request_for_another_user()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $status = TravelStatus::factory()->create(['code' => 'S']);

        $data = [
            'requester_id' => $otherUser->id,
            'destination' => 'Paris',
            'departure_date' => '2025-09-01 10:00:00',
            'return_date' => '2025-09-05 18:00:00',
        ];

        $this->service->createTravelRequest($data, $user);
    }

    public function test_user_can_create_own_travel_request()
    {
        $user = User::factory()->create(['role' => 'user']);
        $status = TravelStatus::factory()->create(['code' => 'S']);

        $data = [
            'requester_id'   => $user->id,
            'destination'    => 'Paris',
            'departure_date' => '2025-09-01 10:00:00',
            'return_date'    => '2025-09-05 18:00:00',
        ];

        $expectedData = $data;
        $expectedData['travel_status_id'] = $status->id;

        $this->userRepository
            ->shouldReceive('findById')
            ->with($user->id)
            ->andReturn($user);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function($d) use ($user, $expectedData) {
                return $d['requester_id'] === $user->id
                    && $d['requester_name'] === $user->name
                    && $d['travel_status_id'] === $expectedData['travel_status_id'];
            }))
            ->andReturn(new TravelRequest(array_merge($expectedData, ['requester_name' => $user->name])));

        $result = $this->service->createTravelRequest($data, $user);

        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals($user->id, $result->requester_id);
    }

    public function test_user_can_update_own_travel_request()
    {
        $user = User::factory()->create(['role' => 'user']);
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S']);

        $travelRequest = new TravelRequest(['requester_id' => $user->id, 'travel_status_id' => 1]);
        $data = ['destination' => 'Atualizado'];

        $this->repository->shouldReceive('update')->once()->andReturn(new TravelRequest($data));
        $result = $this->service->updateTravelRequest($travelRequest, $data, $user);

        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals('Atualizado', $result->destination);
    }

    public function test_user_cannot_update_other_users_travel_request()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S']);

        $travelRequest = new TravelRequest([
            'requester_id'     => $otherUser->id,
            'travel_status_id' => $requestedStatus->id
        ]);

        $data = [
            'destination' => 'Atualizado'
        ];

        $this->repository->shouldReceive('update')->never();

        $this->service->updateTravelRequest($travelRequest, $data, $user);
    }

    public function test_cannot_update_approved_travel_request()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $user = User::factory()->create(['role' => 'user']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A']);

        $travelRequest = new TravelRequest(['requester_id' => $user->id, 'travel_status_id' => $approvedStatus->id]);
        $data = ['destination' => 'Atualizado'];

        $this->service->updateTravelRequest($travelRequest, $data, $user);
    }    

    public function test_admin_can_approve_travel_request()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A']);

        $travelRequest = new TravelRequest(['travel_status_id' => $requestedStatus->id]);

        $expectedData = ['travel_status_id' => $approvedStatus->id];
        $this->repository->shouldReceive('update')
            ->once()
            ->with($travelRequest, Mockery::on(function($d) use ($expectedData) {
                return $d['travel_status_id'] === $expectedData['travel_status_id'];
            }))
            ->andReturn(new TravelRequest(array_merge($travelRequest->toArray(), $expectedData)));

        $result = $this->service->approveTravelRequest($travelRequest, $admin);

        $this->assertInstanceOf(TravelRequest::class, $result);
    }

    public function test_admin_cannot_approve_already_approved_travel_request()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $admin = User::factory()->create(['role' => 'admin']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A']);

        $travelRequest = new TravelRequest([
            'travel_status_id' => $approvedStatus->id
        ]);

        $this->service->approveTravelRequest($travelRequest, $admin);
    }

    public function test_user_cannot_approve_travel_request()
    {
        $this->expectException(AdminOnlyActionException::class);

        $user = User::factory()->create(['role' => 'user']);
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S']);

        $travelRequest = new TravelRequest(['travel_status_id' => 1]);

        $this->service->approveTravelRequest($travelRequest, $user);
    }

    public function test_admin_can_cancel_any_travel_request()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $statusAprovado = TravelStatus::factory()->create(['code' => 'A']);
        $statusCancelado = TravelStatus::factory()->create(['code' => 'C']);
        $travelRequest = new TravelRequest([
            'travel_status_id' => $statusAprovado->id
        ]);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($travelRequest, Mockery::on(function($d) use ($statusCancelado) {
                return $d['travel_status_id'] === $statusCancelado->id;
            }))
            ->andReturn(new TravelRequest([
                'travel_status_id' => $statusCancelado->id
            ]));

        $result = $this->service->cancelTravelRequest($travelRequest, $admin);
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals($statusCancelado->id, $result->travel_status_id);
    }

    public function test_admin_cannot_cancel_own_non_approved_travel_request()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $admin = User::factory()->create(['role' => 'admin']);
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S']);

        $travelRequest = new TravelRequest([
            'requester_id' => $admin->id,
            'travel_status_id' => $requestedStatus->id
        ]);

        $this->service->cancelTravelRequest($travelRequest, $admin);
    }

    public function test_user_can_cancel_own_approved_travel_request()
    {
        $user = User::factory()->create(['role' => 'user']);

        $approvedStatus = TravelStatus::factory()->create(['code' => 'A']);
        $cancelledStatus = TravelStatus::factory()->create(['code' => 'C']);

        $travelRequest = new TravelRequest([
            'requester_id' => $user->id,
            'travel_status_id' => $approvedStatus->id
        ]);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($travelRequest, Mockery::on(function($d) use ($cancelledStatus) {
                return $d['travel_status_id'] === $cancelledStatus->id;
            }))
            ->andReturn(new TravelRequest([
                'travel_status_id' => $cancelledStatus->id
            ]));

        $result = $this->service->cancelTravelRequest($travelRequest, $user);
        
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals($cancelledStatus->id, $result->travel_status_id);
    }

    public function test_user_cannot_cancel_own_non_approved_travel_request()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $user = User::factory()->create(['role' => 'user']);
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S']);

        $travelRequest = new TravelRequest([
            'requester_id' => $user->id,
            'travel_status_id' => $requestedStatus->id
        ]);

        $this->service->cancelTravelRequest($travelRequest, $user);
    }

    public function test_user_cannot_cancel_other_users_travel_request()
    {
        $this->expectException(TravelRequestActionNotAllowedException::class);

        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A']);

        $travelRequest = new TravelRequest([
            'requester_id' => $otherUser->id,
            'travel_status_id' => $approvedStatus->id
        ]);

        $this->service->cancelTravelRequest($travelRequest, $user);
    }
}
