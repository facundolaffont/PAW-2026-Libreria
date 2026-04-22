<?php

    namespace Paw\Repositories;

    class PromotionRepository {
        public function __construct(private \PDO $db) {}

        public function findAll(): array {
            $stmt = $this->db->query('SELECT id, description, image FROM promotions ORDER BY id');
            return $stmt->fetchAll();
        }
    }
