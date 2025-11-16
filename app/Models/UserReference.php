<?php

namespace App\Models;

use mysqli;
use App\Exceptions\DatabaseException;
use App\Repositories\UserReferenceRepository;

class UserReference
{
    private readonly mysqli $conn;
    private Database $database;
    private UserReferenceRepository $repository;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->conn = $this->database->getConnection();
        $this->repository = new UserReferenceRepository();
    }

    /**
     * Add a submission to user's references
     */
    public function addReference(int $userId, int $submissionId): array
    {
        return $this->repository->addReference($userId, $submissionId);
    }

    /**
     * Remove a submission from user's references
     */
    public function removeReference(int $userId, int $submissionId): array
    {
        return $this->repository->removeReference($userId, $submissionId);
    }

    /**
     * Check if a submission is already in user's references
     */
    public function isReference(int $userId, int $submissionId): bool
    {
        return $this->repository->isReference($userId, $submissionId);
    }

    /**
     * Get all references for a user
     */
    public function getReferencesByUser(int $userId): array
    {
        return $this->repository->getReferencesByUser($userId);
    }

    /**
     * Get submission details for a user's reference
     */
    public function getReferenceSubmission(int $userId, int $submissionId): ?array
    {
        return $this->repository->getReferenceSubmission($userId, $submissionId);
    }
}