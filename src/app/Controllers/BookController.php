<?php

    namespace Paw\Controllers;

    use Paw\Interfaces\IBookRepository;

    class BookController {

        public function __construct(private IBookRepository $iBookRepository) {}

        public function findAllGroupedByGenre() {
            return $this->iBookRepository->findAllGroupedByGenre();
        }
    }