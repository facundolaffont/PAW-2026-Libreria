<?php

    namespace Paw\Controllers;

    use Paw\Repositories\BookRepository;
    use Paw\Repositories\PromotionRepository;
    use Paw\View;

    class PageController {
        public function __construct(
            private BookRepository $bookRepository,
            private PromotionRepository $promotionRepository
        ) {}

        public function show(string $title, string $page): void {
            if ($page === 'home-page') {
                $context = [
                    'booksByGenre' => $this->bookRepository->findAllGroupedByGenre(),
                    'promotions'   => $this->promotionRepository->findAll(),
                ];
            }
            View::render($title, $page, $context ?? []);
        }
    }