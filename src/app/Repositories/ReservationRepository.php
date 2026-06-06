<?php

namespace Paw\Repositories;

use Paw\Interfaces\ReservationRepositoryInterface;

class ReservationRepository implements ReservationRepositoryInterface {

    public function __construct(private \PDO $db) {}

    public function save(string $nombre, string $email, string $telefono, array $libros): int {
        $stmt = $this->db->prepare(
            'INSERT INTO reservations (nombre, email, telefono, libros)
             VALUES (:nombre, :email, :telefono, :libros)'
        );
        $stmt->bindValue(':nombre',   $nombre);
        $stmt->bindValue(':email',    $email);
        $stmt->bindValue(':telefono', $telefono);
        $stmt->bindValue(':libros',   json_encode($libros, JSON_UNESCAPED_UNICODE));
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function findAll(int $offset, int $limit): array {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, email, telefono, libros, created_at
             FROM reservations
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function (array $row): array {
            $row['libros'] = json_decode($row['libros'], true) ?? [];
            return $row;
        }, $stmt->fetchAll());
    }

    public function countAll(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
    }
}
