<?php

namespace Paw\Models;

use Paw\Enums\UserRole;

class User {

    public function __construct(
        public readonly int      $id,
        public readonly string   $name,
        public readonly UserRole $role
    ) {}

    public function isAdmin(): bool {
        return $this->role === UserRole::ADMIN;
    }

    public function isClient(): bool {
        return $this->role === UserRole::CLIENT;
    }

    public function toArray(): array {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'role' => $this->role->value,
        ];
    }
}