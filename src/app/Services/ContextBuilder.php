<?php

namespace Paw\Services;

use Paw\Enums\HttpCodeError;
use Paw\Errors\Exceptions\HttpErrorException;
use Paw\Interfaces\BookRepositoryInterface;
use Paw\Interfaces\PromotionRepositoryInterface;

class ContextBuilder {

    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private PromotionRepositoryInterface $promotionRepository
    ) {}

    public function build(string $title, string $page, array $queryParams = []): array {
        $context = [];
        $context['currentPageName'] = $title;

        match ($page) {
            'home-page' => $context += $this->buildHomePageContext(
                $this->bookRepository,
                $this->promotionRepository
            ),
            'catalog' => $context += $this->buildCatalogContext(
                $this->bookRepository,
                $queryParams
            ),
            'book-detail' => $context += $this->buildBookDetailContext(
                $this->bookRepository,
                $queryParams
            ),
            default => [],
        };

        return $context;
    }

    private function buildHomePageContext(
        BookRepositoryInterface $bookRepository,
        PromotionRepositoryInterface $promotionRepository
    ): array {
        return [
            'booksByGenre' => $bookRepository->findAllGroupedByGenre(),
            'promotions'   => $promotionRepository->findAll(),
        ];
    }

    public static function buildCatalogFilters(array $queryParams): array {
        return [
            'generos'    => array_map('strval', (array)($queryParams['genero'] ?? [])),
            'precio_min' => $queryParams['precio_min'] ?? '',
            'precio_max' => $queryParams['precio_max'] ?? '',
            'autor'      => array_map('strval', (array)($queryParams['autor'] ?? [])),
            'q'          => trim($queryParams['q'] ?? ''),
            'orden'      => (string)($queryParams['orden'] ?? ''),
        ];
    }

    private function buildCatalogContext(
        BookRepositoryInterface $bookRepository,
        array $queryParams
    ): array {
        $filters = self::buildCatalogFilters($queryParams);

        $filterParams = $queryParams;
        unset($filterParams['pagina']);
        $filterQuery = http_build_query($filterParams);

        $perPage     = 12;
        $currentPage = max(1, (int)($queryParams['pagina'] ?? 1));
        $totalBooks  = $bookRepository->countAll($filters);
        $totalPages  = max(1, (int) ceil($totalBooks / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset      = ($currentPage - 1) * $perPage;

        return [
            'books'       => $bookRepository->findAll($offset, $perPage, $filters),
            'allBooks'    => $bookRepository->findAll(0, PHP_INT_MAX, []),
            'priceRange'  => [
                'min' => $bookRepository->findPriceMin(),
                'max' => $bookRepository->findPriceMax(),
            ],
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'genres'      => $bookRepository->findAllGenres(),
            'authors'     => $bookRepository->findAllAuthors(),
            'filters'     => $filters,
            'filterQuery' => $filterQuery,
        ];
    }

    private function buildBookDetailContext(
        BookRepositoryInterface $bookRepository,
        array $queryParams
    ): array {
        $id   = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
        $book = $id > 0 ? $bookRepository->findById($id) : null;

        if ($book === null) {
            throw new HttpErrorException(HttpCodeError::NOT_FOUND);
        }

        return [
            'book' => $book,
        ];
    }
}
