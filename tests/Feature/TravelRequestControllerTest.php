<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\TravelStatus;

class TravelRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected static function getCookieName()
    {
        return 'token_' . env('APP_NAME', 'onfly');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->disableCookieEncryption();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        Artisan::call('migrate');
    }

    protected function actingAsUser($role = 'user')
    {
        $email = $role === 'admin' ? 'admin@example.com' : 'user@example.com';
        $password = '123456';

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
            'role' => $role,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $setCookie = $response->headers->get('Set-Cookie');
        $cookieName = self::getCookieName();
        preg_match("/{$cookieName}=([^;]+)/", $setCookie, $matches);
        $token = $matches[1] ?? null;

        return [$user, $token];
    }

    public function test_list_travel_requests_requires_authentication()
    {
        $response = $this->getJson('/api/viagens');
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Token não encontrado']);
    }

    public function test_user_can_list_own_travel_requests()
    {
        [$user, $token] = $this->actingAsUser();

        TravelRequest::factory()->create(['requester_id' => $user->id]);

        $response = $this->withCookie(self::getCookieName(), $token)
                         ->get('/api/viagens');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_index_returns_all_for_admin()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        TravelRequest::factory()->count(3)->create();

        $response = $this->withCookie(self::getCookieName(), $token)
            ->get('/api/viagens');
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_returns_only_user_requests()
    {
        [$user, $token] = $this->actingAsUser();

        TravelRequest::factory()->create(['requester_id' => $user->id]);
        TravelRequest::factory()->create(); // outro usuário

        $response = $this->withCookie(self::getCookieName(), $token)
            ->get('/api/viagens');
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_filters_by_status_code()
    {
        [$user, $token] = $this->actingAsUser();

        $status = TravelStatus::factory()->create(['code' => 'A']);
        TravelRequest::factory()->create(['requester_id' => $user->id, 'travel_status_id' => $status->id]);
        TravelRequest::factory()->create(['requester_id' => $user->id]);

        $response = $this->withCookie(self::getCookieName(), $token)
            ->get('/api/viagens?status_code=A');
        $response->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_filters_by_destination()
    {
        [$user, $token] = $this->actingAsUser();
        
        TravelRequest::factory()->create(['requester_id' => $user->id, 'destination' => 'Paris']);
        TravelRequest::factory()->create(['requester_id' => $user->id, 'destination' => 'London']);

        $response = $this->withCookie(self::getCookieName(), $token)
            ->get('/api/viagens?destination=Paris');
        $response->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_filters_by_dates()
    {
        [$user, $token] = $this->actingAsUser();

        TravelRequest::factory()->create([
            'requester_id' => $user->id,
            'departure_date' => '2025-08-30 10:00:00',
            'return_date' => '2025-09-05 18:00:00',
        ]);
        TravelRequest::factory()->create([
            'requester_id' => $user->id,
            'departure_date' => '2025-09-10 10:00:00',
            'return_date' => '2025-09-15 18:00:00',
        ]);

        $response = $this->withCookie(self::getCookieName(), $token)
            ->get('/api/viagens?start_date=2025-08-30&end_date=2025-09-05');
        $response->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_can_create_travel_request_for_self()
    {
        [$user, $token] = $this->actingAsUser();

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $payload = [
            'requester_id' => $user->id,
            'destination' => 'Belo Horizonte',
            'departure_date' => '2025-09-01 10:00:00',
            'return_date' => '2025-09-05 18:00:00',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/viagens', $payload);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');

        $this->assertEquals($user->id, $data['requester_id']);
        $this->assertEquals('Belo Horizonte', $data['destination']);
        $this->assertEquals('S', $data['status_code']);
    }

    public function test_user_cannot_create_travel_request_for_another_user()
    {
        [$user, $token] = $this->actingAsUser();

        $otherUser = User::factory()->create();
        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $payload = [
            'requester_id' => $otherUser->id,
            'destination' => 'Belo Horizonte',
            'departure_date' => '2025-09-01 10:00:00',
            'return_date' => '2025-09-05 18:00:00',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens', $payload);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Sem permissão para criar pedido para outro usuário.']);
    }

    public function test_admin_can_create_travel_request_for_self()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $payload = [
            'requester_id' => $admin->id,
            'destination' => 'Berlin',
            'departure_date' => '2025-09-20 10:00:00',
            'return_date' => '2025-09-25 18:00:00',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens', $payload);
        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertEquals($admin->id, $data['requester_id']);
        $this->assertEquals('Berlin', $data['destination']);
        $this->assertEquals('S', $data['status_code']);
    }

    public function test_admin_can_create_travel_request_for_another_user()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $otherUser = User::factory()->create();
        $payload = [
            'requester_id' => $otherUser->id,
            'destination' => 'Berlin',
            'departure_date' => '2025-09-20 10:00:00',
            'return_date' => '2025-09-25 18:00:00',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens', $payload);
        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertEquals($otherUser->id, $data['requester_id']);
        $this->assertEquals('Berlin', $data['destination']);
        $this->assertEquals('S', $data['status_code']);
    }

    public function test_store_fails_with_invalid_dates()
    {
        [$user, $token] = $this->actingAsUser();

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $payload = [
            'destination' => 'Rome',
            'departure_date' => '2025-09-10 10:00:00',
            'return_date' => '2025-09-05 18:00:00', // return before departure
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['return_date']);
    }

    public function test_store_fails_without_destination()
    {
        [$user, $token] = $this->actingAsUser();

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $payload = [
            'departure_date' => '2025-09-10 10:00:00',
            'return_date' => '2025-09-15 18:00:00',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['destination']);
    }

    public function test_user_can_view_own_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $travelRequest = TravelRequest::factory()->create(['requester_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/viagens/' . $travelRequest->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');

        $this->assertEquals($user->id, $data['requester_id']);
        $this->assertEquals($travelRequest->id, $data['id']);
    }

    public function test_user_cannot_view_other_users_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['requester_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/viagens/' . $travelRequest->id);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Você não tem permissão para visualizar este pedido.']);
    }

    public function test_admin_can_view_any_travel_request()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['requester_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/viagens/' . $travelRequest->id);
        $response->assertStatus(200);
        
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');

        $this->assertEquals($otherUser->id, $data['requester_id']);
        $this->assertEquals($travelRequest->id, $data['id']);
    }

    public function test_user_can_update_own_travel_request()
    {
        [$user, $token] = $this->actingAsUser();
        
        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $user->id,
            'travel_status_id' => $requestedStatus->id,
            'destination' => 'Original',
            'departure_date' => '2025-09-10 08:00:00',
            'return_date' => '2025-09-15 20:00:00',
        ]);

        $payload = [
            'destination' => 'Caraguatatuba',
            'departure_date' => '2025-09-15 08:00:00',
            'return_date' => '2025-09-20 20:00:00',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/viagens/' . $travelRequest->id, $payload);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals('Caraguatatuba', $data['destination']);
        $this->assertEquals('2025-09-15 08:00:00', $data['departure_date']);
        $this->assertEquals('2025-09-20 20:00:00', $data['return_date']);
    }

    public function test_admin_can_update_any_travel_request()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $otherUser->id,
            'travel_status_id' => $requestedStatus->id,
            'destination' => 'Original',
            'departure_date' => '2025-09-10 08:00:00',
            'return_date' => '2025-09-15 20:00:00',
        ]);

        $payload = [
            'destination' => 'Caraguatatuba',
            'departure_date' => '2025-09-15 08:00:00',
            'return_date' => '2025-09-20 20:00:00',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/viagens/' . $travelRequest->id, $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');

        $this->assertEquals('Caraguatatuba', $data['destination']);
        $this->assertEquals('2025-09-15 08:00:00', $data['departure_date']);
        $this->assertEquals('2025-09-20 20:00:00', $data['return_date']);
    }

    public function test_user_cannot_update_other_users_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $otherUser->id,
            'destination' => 'Rio de Janeiro',
        ]);
        $payload = ['destination' => 'Tentativa de edição'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/viagens/' . $travelRequest->id, $payload);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Você não tem permissão para editar este pedido.']);
    }

    public function test_cannot_update_approved_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A', 'name' => 'Aprovado']);
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $user->id,
            'travel_status_id' => $approvedStatus->id,
            'destination' => 'Rio de Janeiro',
        ]);
        $payload = ['destination' => 'Porto Alegre'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/viagens/' . $travelRequest->id, $payload);
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Não é possível editar pedido aprovado.']);
    }

    public function test_admin_can_approve_travel_request()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A', 'name' => 'Aprovado']);
        $travelRequest = TravelRequest::factory()->create([
            'travel_status_id' => $requestedStatus->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $travelRequest->id . '/aprovar');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');

        $this->assertEquals('A', $data['status_code']);
        $this->assertEquals('Aprovado', $data['status']);
    }

    public function test_non_admin_cannot_approve_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $travelRequest = TravelRequest::factory()->create([
            'travel_status_id' => $requestedStatus->id,
        ]);
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $travelRequest->id . '/aprovar');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Somente administradores podem aprovar pedidos.']);
    }

    public function test_admin_can_cancel_approved_travel_request()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        $approvedStatus = TravelStatus::factory()->create(['code' => 'A', 'name' => 'Aprovado']);
        $cancelledStatus = TravelStatus::factory()->create(['code' => 'C', 'name' => 'Cancelado']);

        $approvedRequest = TravelRequest::factory()->create([
            'travel_status_id' => $approvedStatus->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $approvedRequest->id . '/cancelar');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');
        $this->assertEquals('C', $data['status_code']);
        $this->assertEquals('Cancelado', $data['status']);
    }

    public function test_admin_cannot_cancel_unapproved_travel_request()
    {
        [$admin, $token] = $this->actingAsUser('admin');

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $unapprovedRequest = TravelRequest::factory()->create([
            'travel_status_id' => $requestedStatus->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $unapprovedRequest->id . '/cancelar');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Pedido só pode ser cancelado se estiver aprovado.']);
    }

    public function test_user_can_cancel_own_approved_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $approvedStatus = TravelStatus::factory()->create(['code' => 'A', 'name' => 'Aprovado']);
        $cancelledStatus = TravelStatus::factory()->create(['code' => 'C', 'name' => 'Cancelado']);
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $user->id,
            'travel_status_id' => $approvedStatus->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $travelRequest->id . '/cancelar');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'requester_id',
                'requester_name',
                'destination',
                'departure_date',
                'return_date',
                'status_code',
                'status',
            ]
        ]);
        $data = $response->json('data');

        $this->assertEquals('C', $data['status_code']);
        $this->assertEquals('Cancelado', $data['status']);
    }

    public function test_user_cannot_cancel_unapproved_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        $requestedStatus = TravelStatus::factory()->create(['code' => 'S', 'name' => 'Solicitado']);
        $cancelledStatus = TravelStatus::factory()->create(['code' => 'C', 'name' => 'Cancelado']);
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $user->id,
            'travel_status_id' => $requestedStatus->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $travelRequest->id . '/cancelar');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Pedido só pode ser cancelado se estiver aprovado.']);
    }

    public function test_user_cannot_cancel_other_users_travel_request()
    {
        [$user, $token] = $this->actingAsUser();

        $approvedStatus = TravelStatus::factory()->create(['code' => 'A', 'name' => 'Aprovado']);
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'requester_id' => $otherUser->id,
            'travel_status_id' => $approvedStatus->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/viagens/' . $travelRequest->id . '/cancelar');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Não autorizado a cancelar esse pedido']);
    }
}
