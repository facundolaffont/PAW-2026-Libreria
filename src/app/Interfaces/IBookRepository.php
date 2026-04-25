<?php

    namespace Paw\Interfaces;

    interface IBookRepository {
        public function findAllGroupedByGenre(): array;
    }