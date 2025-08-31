<?php

namespace App\Services;

use App\Exceptions\AdminOnlyActionException;
use App\Exceptions\Domain\TravelRequest\TravelRequestActionNotAllowedException;
use App\Models\TravelRequest;
use App\Models\TravelStatus;
use App\Notifications\TravelRequestStatusChanged;
use App\Repositories\TravelRequestRepository;
use App\Repositories\UserRepository;

class TravelRequestService
{
    public function __construct(
        protected TravelRequestRepository $repository,
        protected UserRepository $userRepository
    ) {}

    public function filterTravelRequests(array $filters, $user)
    {
        return $this->repository->filter($filters, $user);
    }

    public function getTravelRequestsByUserId(int $userId)
    {
        return $this->repository->getAllByUserId($userId);
    }

    public function findTravelRequest(int $id)
    {
        return $this->repository->findById($id);
    }

    public function findTravelRequestForUser(TravelRequest $travelRequest, $user)
    {
        if ($user->role !== 'admin' && $travelRequest->requester_id !== $user->id) {
            throw new TravelRequestActionNotAllowedException('Você não tem permissão para visualizar este pedido.');
        }
        return $travelRequest;
    }

    public function createTravelRequest(array $data, $user): TravelRequest
    {
        if ($user->role === 'admin' && isset($data['requester_id'])) {
            $data['requester_id'] = $data['requester_id'];
            if (!$requester = $this->userRepository->findById($data['requester_id'])) {
                throw new TravelRequestActionNotAllowedException('Usuário solicitante não encontrado.', 404);
            }
            $data['requester_name'] = $requester->name;
        } else if ($user->role === 'user' && isset($data['requester_id']) && $data['requester_id'] == $user->id) {
            $data['requester_id'] = $user->id;
            $data['requester_name'] = $user->name;
        } else {
            throw new TravelRequestActionNotAllowedException('Sem permissão para criar pedido para outro usuário.');
        }

        $status = TravelStatus::where('code', 'S')->first();
        if ($status) {
            $data['travel_status_id'] = $status->id;
        }

        return $this->repository->create($data);
    }

    public function updateTravelRequest(TravelRequest $travelRequest, array $data, $user): TravelRequest
    {
        if ($user->role !== 'admin' && $travelRequest->requester_id !== $user->id) {
            throw new TravelRequestActionNotAllowedException('Você não tem permissão para editar este pedido.');
        }

        $approvedStatus = TravelStatus::where('code', 'A')->first();
        if ($travelRequest->travel_status_id == ($approvedStatus?->id)) {
            throw new TravelRequestActionNotAllowedException('Não é possível editar pedido aprovado.');
        }

        $cancelledStatus = TravelStatus::where('code', 'C')->first();
        if ($travelRequest->travel_status_id == ($cancelledStatus?->id)) {
            throw new TravelRequestActionNotAllowedException('Não é possível editar pedido cancelado.');
        }

        return $this->repository->update($travelRequest, $data);
    }

    // public function deleteTravelRequest(TravelRequest $travelRequest): void
    // {
    //     $this->repository->delete($travelRequest);
    // }

    public function approveTravelRequest(TravelRequest $travelRequest, $user): TravelRequest
    {
        if ($user->role !== 'admin') {
            throw new AdminOnlyActionException('Somente administradores podem aprovar pedidos.');
        }

        $approvedStatus = TravelStatus::where('code', 'A')->first();
        if ($travelRequest->travel_status_id == ($approvedStatus?->id)) {
            throw new TravelRequestActionNotAllowedException('Pedido já está aprovado.');
        }

        $status = TravelStatus::where('code', 'A')->first();
        if ($status) {
            $data['travel_status_id'] = $status->id;
        }

        $updated = $this->repository->update($travelRequest, $data);
        // Notifica o usuário do pedido
        $requester = $this->userRepository->findById($updated->requester_id);
        if ($requester && method_exists($requester, 'notify')) {
            $requester->notify((new TravelRequestStatusChanged($updated, $approvedStatus->code))->onQueue('emails'));
        }
        return $updated;
    }

    public function cancelTravelRequest(TravelRequest $travelRequest, $user): TravelRequest
    {                
        $cancelledStatus = TravelStatus::where('code', 'C')->first();
        $approvedStatus = TravelStatus::where('code', 'A')->first();
        if ($travelRequest->travel_status_id != ($approvedStatus?->id)) {
            throw new TravelRequestActionNotAllowedException('Pedido só pode ser cancelado se estiver aprovado.');
        }

        // Admin pode cancelar qualquer pedido
        if ($user->role === 'admin') {
            if ($cancelledStatus) {
                $data['travel_status_id'] = $cancelledStatus->id;
            }
            $updated = $this->repository->update($travelRequest, $data);
            $requester = $this->userRepository->findById($updated->requester_id);
            if ($requester && method_exists($requester, 'notify')) {
                $requester->notify((new TravelRequestStatusChanged($updated, $cancelledStatus->code))->onQueue('emails'));
            }
            return $updated;
        }

        // Usuário só pode cancelar se for dele e estiver aprovado
        if ($user->id === $travelRequest->requester_id) {
            if ($cancelledStatus) {
                $data['travel_status_id'] = $cancelledStatus->id;
            }

            $updated = $this->repository->update($travelRequest, $data);
            $requester = $this->userRepository->findById($updated->requester_id);
            if ($requester && method_exists($requester, 'notify')) {
                $requester->notify((new TravelRequestStatusChanged($updated, $cancelledStatus->code))->onQueue('emails'));
            }
            return $updated;
        }

        throw new TravelRequestActionNotAllowedException('Não autorizado a cancelar esse pedido');
    }
}
