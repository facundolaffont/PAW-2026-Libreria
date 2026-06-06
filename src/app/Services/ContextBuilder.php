<?php

namespace Paw\Services;

use Paw\Enums\HttpCodeError;
use Paw\Errors\Exceptions\HttpErrorException;
use Paw\Interfaces\BookRepositoryInterface;
use Paw\Interfaces\PromotionRepositoryInterface;
use Paw\Interfaces\ReservationRepositoryInterface;
use Psr\Log\LoggerInterface;

class ContextBuilder {

    public function __construct(
        private BookRepositoryInterface        $bookRepository,
        private PromotionRepositoryInterface   $promotionRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private LoggerInterface                $logger
    ) {}

    public function build(string $title, string $page, array $queryParams = []): array {
        $context = [];
        $context['currentPageName'] = $title;
        $context['page'] = $page;

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
            'orders' => $context += $this->buildOrdersContext(
                $this->reservationRepository,
                $queryParams
            ),
            default => [],
        };

        $this->logger->debug(
            "Contexto construido para la página '{$page}' con título '{$title}'",
            ['context' => $context]
        );
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
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'genres'      => $bookRepository->findAllGenres(),
            'authors'     => $bookRepository->findAllAuthors(),
            'filters'     => $filters,
            'filterQuery' => $filterQuery,
        ];
    }

    private function buildOrdersContext(
        ReservationRepositoryInterface $reservationRepository,
        array $queryParams
    ): array {
        $perPage     = 20;
        $currentPage = max(1, (int)($queryParams['pagina'] ?? 1));
        $total       = $reservationRepository->countAll();
        $totalPages  = max(1, (int) ceil($total / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset      = ($currentPage - 1) * $perPage;

        return [
            'orders'      => $reservationRepository->findAll($offset, $perPage),
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'total'       => $total,
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
