<?php

    namespace Paw\Controllers;

    use Paw\Interfaces\IBookRepository;
    use Paw\Interfaces\IPromotionRepository;
    use Paw\View;

    class PageController {
        
        public function __construct(
            private IBookRepository $iBookRepository,
            private IPromotionRepository $iPromotionRepository
        ) {}

        public function show(string $title, string $page): void {
            View::render($title, $page, $this->buildContext($page));
        }


        /*** Métodos privados ***/

        private function buildContext(string $page): array {
            return match($page) {
                'home-page' => [
                    'booksByGenre' => $this->iBookRepository->findAllGroupedByGenre(),
                    'promotions'   => $this->iPromotionRepository->findAll(),
                ],
                default => [],
            };
        }
    }