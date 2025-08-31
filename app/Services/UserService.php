<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function getUsers()
    {
        return $this->repository->getAll();
    }

    public function findUser(int $id)
    {
        return $this->repository->findById($id);
    }

    public function createUser(array $data): User
    {
        $user = $this->repository->create($data);

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user = $this->repository->update($user, $data);

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $this->repository->delete($user);
    }
}