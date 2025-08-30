<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return User::get();
    }

    public function findById(int $id): User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}