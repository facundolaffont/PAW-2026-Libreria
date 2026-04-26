<?php

    namespace Paw\Controllers;

    use Paw\Interfaces\BookRepositoryInterface;
    use Paw\Interfaces\PageControllerInterface;
    use Paw\Interfaces\PromotionRepositoryInterface;
    use Paw\View;

    class PageController {
        
        public function __construct(
            private BookRepositoryInterface $bookRepository,
            private PromotionRepositoryInterface $promotionRepository
        ) {}

        public function show(string $title, string $page): void {
            View::render($title, $page, $this->buildContext($page));
        }


        /*** Métodos privados ***/

        private function buildContext(string $page): array {
            return match($page) {
                'home-page' => [
                    'booksByGenre' => $this->bookRepository->findAllGroupedByGenre(),
                    'promotions'   => $this->promotionRepository->findAll(),
                ],
                default => [],
            };
        }
    }