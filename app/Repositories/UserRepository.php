<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;

class UserRepository extends BaseRepository
{
    public function findByIdMember(string $idMember): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, id_member, password FROM users_login WHERE id_member = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("s", $idMember);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while finding user by ID member: " . $e->getMessage());
        }
    }

    public function create(string $idMember, string $password): bool
    {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO users_login (id_member, password, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ss", $idMember, $password_hash);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while creating user: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, id_member FROM users_login WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while finding user by ID: " . $e->getMessage());
        }
    }

    public function getAll(): array
    {
        try {
            $result = $this->conn->query("SELECT id, id_member, created_at FROM users_login ORDER BY created_at DESC");
            if (!$result) {
                throw new DatabaseException("Query failed: " . $this->conn->error);
            }

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            return $users;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while fetching all users: " . $e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            // Build dynamic query based on provided data
            $allowed_fields = ['password', 'status'];
            $set_parts = [];
            $params = [];
            $param_types = '';

            foreach ($data as $field => $value) {
                if (in_array($field, $allowed_fields)) {
                    $set_parts[] = "{$field} = ?";
                    $params[] = $value;
                    $param_types .= is_int($value) ? 'i' : 's';
                }
            }

            if (empty($set_parts)) {
                return false;
            }

            $params[] = $id;
            $param_types .= 'i';

            $sql = "UPDATE users_login SET " . implode(', ', $set_parts) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param($param_types, ...$params);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while updating user: " . $e->getMessage());
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users_login WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while deleting user: " . $e->getMessage());
        }
    }
}