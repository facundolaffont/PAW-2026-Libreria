<?php

    namespace Paw\Interfaces;

    interface BookRepositoryInterface {
        public function findAllGroupedByGenre(): array;
        public function findAllGenres(): array;
        public function findAllAuthors(): array;
        public function findAll(int $offset, int $limit, array $filters = []): array;
        public function findById(int $id): ?array;
        public function countAll(array $filters = []): int;
        public function create(array $data): int;
        public function existsByIsbn(string $isbn): bool;
        public function findPriceMin(): float;
        public function findPriceMax(): float;
    }