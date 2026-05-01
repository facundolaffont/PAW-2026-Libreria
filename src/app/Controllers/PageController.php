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
                'catalog' => (function () {
                    $filters = [
                        'generos'    => array_map('strval', (array)($_GET['genero'] ?? [])),
                        'precio_min' => $_GET['precio_min'] ?? '',
                        'precio_max' => $_GET['precio_max'] ?? '',
                        'autor'      => trim($_GET['autor'] ?? ''),
                    ];

                    $filterParams = $_GET;
                    unset($filterParams['pagina']);
                    $filterQuery = http_build_query($filterParams);

                    $perPage     = 12;
                    $currentPage = max(1, (int)($_GET['pagina'] ?? 1));
                    $totalBooks  = $this->bookRepository->countAll($filters);
                    $totalPages  = max(1, (int) ceil($totalBooks / $perPage));
                    $currentPage = min($currentPage, $totalPages);
                    $offset      = ($currentPage - 1) * $perPage;

                    return [
                        'books'       => $this->bookRepository->findAll($offset, $perPage, $filters),
                        'currentPage' => $currentPage,
                        'totalPages'  => $totalPages,
                        'genres'      => $this->bookRepository->findAllGenres(),
                        'filters'     => $filters,
                        'filterQuery' => $filterQuery,
                    ];
                })(),
                default => [],
            };
        }
    }