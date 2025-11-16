<?php

namespace App\Models;

use App\Repositories\UserRepository;

class User
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function findByIdMember(string $idMember): ?array
    {
        return $this->repository->findByIdMember($idMember);
    }

    public function create(string $idMember, string $password): bool
    {
        return $this->repository->create($idMember, $password);
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteById(int $id): bool
    {
        return $this->repository->deleteById($id);
    }
}