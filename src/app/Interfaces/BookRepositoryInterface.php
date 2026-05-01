<?php

    namespace Paw\Interfaces;

    interface BookRepositoryInterface {
        public function findAllGroupedByGenre(): array;
        public function findAllGenres(): array;
        public function findAll(int $offset, int $limit, array $filters = []): array;
        public function countAll(array $filters = []): int;
    }