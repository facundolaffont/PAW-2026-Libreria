<?php

    namespace Paw\Repositories;

    use Paw\Interfaces\PromotionRepositoryInterface;
    
    class PromotionRepository implements PromotionRepositoryInterface {
        public function __construct(private \PDO $db) {}

        public function findAll(): array {
            $stmt = $this->db->query('SELECT id, description, image FROM promotions ORDER BY id');
            return $stmt->fetchAll();
        }
    }
