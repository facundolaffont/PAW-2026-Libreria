<?php

    namespace Paw\Controllers;

    use Paw\Repositories\BookRepository;

    class PageController {
        public function __construct(private BookRepository $bookRepository) {}

        public function show(string $title, string $page): void {
            if ($page === 'home-page') {
                $booksByGenre = $this->bookRepository->findAllGroupedByGenre();
            }
            require __DIR__ . '/../../components/html.php';
        }
    }