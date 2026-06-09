<?php

namespace Paw\Interfaces;

interface ReservationRepositoryInterface {
    public function save(string $nombre, string $email, string $telefono, array $libros): int;
    public function findAll(int $offset, int $limit): array;
    public function countAll(): int;
}
