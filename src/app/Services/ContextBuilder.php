<?php

namespace Paw\Services;

use Paw\Services\UserSessionManager;
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

    /**
     * Enriquece el contexto con información transversal de sesión/request y
     * adapta imágenes responsivas para las vistas que lo requieran.
     * 
     * @param array $context El contexto específico de la página que se va a
     * renderizar.
     * @return array El contexto enriquecido con información común y adaptado
     * para imágenes responsivas, listo para ser pasado a la vista Twig. Este
     * contexto incluirá claves como 'currentUser', 'isAdmin', 'isClient',
     * 'requestPath', 'safeQueryParams', 'query' y, si el contexto original
     * contenía datos de libros o promociones, también incluirá claves como
     * 'booksByGenre', 'promotions', 'books' o 'book' con información adicional
     * para imágenes responsivas.
     */
    public static function addSharedContext(array $context): array {

        $currentSessionUser = UserSessionManager::getCurrentUser();
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $safeQueryParams = $_GET;
        unset($safeQueryParams['user']);

        // Añade al contexto la información común de sesión y request que se
        // necesita en la mayoría de las vistas.
        $context['currentUser'] = $currentSessionUser->toArray();
        $context['isAdmin'] = $currentSessionUser->isAdmin();
        $context['isClient'] = $currentSessionUser->isClient();
        $context['requestPath'] = $requestPath;
        $context['safeQueryParams'] = $safeQueryParams;
        $context['query'] = $_GET;

        return $context;
    }

    /**
     * Parsea un string de imagen responsiva en un arreglo con fuentes y una URL
     * de respaldo.
     *
     * El string de imagen responsiva tiene el formato:
     *      "maxWidth1:url1;maxWidth2:url2;...;fallbackUrl"
     * Donde cada parte representa una fuente de imagen para un ancho máximo
     * específico, y la última parte sin formato "maxWidth:url" representa la
     * URL de respaldo para casos donde no se cumpla ninguna de las condiciones
     * anteriores.
     * 
     * @param string $image El string de imagen responsiva a parsear.
     * @return array Un arreglo con dos claves:
     * - 'sources': un arreglo de fuentes de imagen, donde cada fuente es un
     *   arreglo con las claves 'maxWidth' (int) y 'url' (string).
     * - 'fallbackSrc': una cadena con la URL de respaldo para la imagen.
     */
    private static function parseResponsiveImage(string $image): array {

        // Convierte en arreglo las claves maxWidth:url del string que
        // contiene las fuentes de la imagen responsiva.
        $parts = explode(';', $image);

        $sources = [];
        $fallbackSrc = '';

        // Itera sobre cada parte del string para identificar las fuentes y
        // la URL de respaldo.
        foreach ($parts as $part) {
            if (preg_match('/^(\d+):(.+)$/', $part, $matches) === 1) {
                $sources[] = [
                    'maxWidth' => (int)$matches[1],
                    'url' => $matches[2],
                ];
                continue;
            }

            if ($part !== '') {
                $fallbackSrc = $part;
            }
        }

        return [
            'sources' => $sources,
            'fallbackSrc' => $fallbackSrc,
        ];
    }

    private function buildHomePageContext(
        BookRepositoryInterface $bookRepository,
        PromotionRepositoryInterface $promotionRepository
    ): array {
        return [
            'booksByGenre' => self::enrichBooksByGenreWithResponsiveImages(
                $bookRepository->findAllGroupedByGenre()
            ),
            'promotions'   => self::enrichPromotionsWithResponsiveImages(
                $promotionRepository->findAll()
            ),
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
            'books'       => self::enrichBooksWithResponsiveImages(
                $bookRepository->findAll($offset, $perPage, $filters)
            ),
            'allBooks'    => self::enrichBooksWithResponsiveImages(
                $bookRepository->findAll(0, PHP_INT_MAX, [])
            ),
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
            'book' => self::enrichBookWithResponsiveImage($book),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $books
     * @return array<int, array<string, mixed>>
     */
    private static function enrichBooksWithResponsiveImages(array $books): array {
        foreach ($books as $index => $book) {
            if (is_array($book) && isset($book['image'])) {
                $book['responsiveImage'] = self::parseResponsiveImage((string)$book['image']);
            }

            $books[$index] = $book;
        }

        return $books;
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $booksByGenre
     * @return array<string, array<int, array<string, mixed>>>
     */
    private static function enrichBooksByGenreWithResponsiveImages(array $booksByGenre): array {
        foreach ($booksByGenre as $genre => $books) {
            $booksByGenre[$genre] = self::enrichBooksWithResponsiveImages($books);
        }

        return $booksByGenre;
    }

    /**
     * @param array<int, array<string, mixed>> $promotions
     * @return array<int, array<string, mixed>>
     */
    private static function enrichPromotionsWithResponsiveImages(array $promotions): array {
        foreach ($promotions as $index => $promotion) {
            if (is_array($promotion) && isset($promotion['image'])) {
                $promotion['responsiveImage'] = self::parseResponsiveImage((string)$promotion['image']);
            }

            $promotions[$index] = $promotion;
        }

        return $promotions;
    }

    /**
     * @param array<string, mixed> $book
     * @return array<string, mixed>
     */
    private static function enrichBookWithResponsiveImage(array $book): array {
        if (isset($book['image'])) {
            $book['responsiveImage'] = self::parseResponsiveImage((string)$book['image']);
        }

        return $book;
    }
}
