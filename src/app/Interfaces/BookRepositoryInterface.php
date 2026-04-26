<?php

    namespace Paw\Interfaces;

    interface BookRepositoryInterface {
        public function findAllGroupedByGenre(): array;
    }