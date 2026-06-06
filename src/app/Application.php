<?php

    namespace Paw;

    use Paw\Controllers\NewBookController;
    use Paw\Controllers\PageController;
    use Paw\Controllers\ReservationController;
    use Paw\Enums\HttpCodeError;
    use Paw\Errors\Exceptions\HttpErrorException;
    use Paw\Interfaces\BookRepositoryInterface;
    use Paw\Services\ContextBuilder;
    use Paw\Services\UserSessionManager;
    use Psr\Container\ContainerInterface;
    use Psr\Log\LoggerInterface;

    class Application {

        public function __construct(private ContainerInterface $dependencyContainer) {}

        public function run(): void {
            
            $logger = $this->dependencyContainer->get(LoggerInterface::class);

            $method = $_SERVER['REQUEST_METHOD'];
            $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $logger->info("{$method} {$path}");

            // Inicializa el usuario actual en sesión y permite alternar con ?user=admin|client.
            UserSessionManager::bootstrapFromRequest($_GET);

            // Si el rol actual es administrador, la sección de reservas no aplica.
            // Se redirige a home para mantener una UX coherente al cambiar de rol.
            if ($path === '/reservation' && UserSessionManager::isAdmin()) {
                header('Location: /');
                exit;
            }

            /*
             * Las solicitudes POST corresponden al envío de formularios.
             * Los datos viajan en el cuerpo de la petición, codificados como
             * application/x-www-form-urlencoded (pares clave=valor separados por &,
             * con caracteres especiales en formato URL-encoding).
             * PHP los decodifica y los expone en el superglobal $_POST.
             */
            if ($method === 'POST') {
                match ($path) {
                    '/reservation' => $this->dependencyContainer->get(ReservationController::class)->handle(),
                    '/new-book'    => UserSessionManager::isAdmin()
                        ? $this->dependencyContainer->get(NewBookController::class)->handle()
                        : throw new HttpErrorException(HttpCodeError::FORBIDDEN),
                    default        => (function () {
                        http_response_code(405);
                        header('Allow: POST /reservation, POST /new-book');
                        echo "Método no permitido.";
                    })(),
                };
                return;
            }

            // API JSON del catálogo (búsqueda y filtrado server-side).
            if ($path === '/api/catalog') {
                $this->emitCatalogJson($logger);
                return;
            }

            // Configura las rutas.
            $router = new Router($logger);
            $router
                ->addRoute('/', 'home-page', 'Página principal')
                ->addRoute('/promotions', 'promotions', 'Promociones')
                ->addRoute('/catalog', 'catalog', 'Catálogo')
                ->addRoute('/book-detail', 'book-detail', 'Detalles de libro')
                ->addRoute('/reservation', 'reservation', 'Reserva')
                ->addRoute('/about-us', 'about-us', 'Acerca de nosotros')
                ->addRoute('/new-book', 'new-book', 'Nuevo libro')
                ->addRoute('/orders',   'orders',   'Pedidos');
            $route = $router->route($path);
            $logger->debug("Resultado del ruteo: " . print_r($route, true));

            if ($path === '/new-book' && !UserSessionManager::isAdmin()) {
                throw new HttpErrorException(HttpCodeError::FORBIDDEN);
            }

            if ($path === '/orders' && !UserSessionManager::isAdmin()) {
                throw new HttpErrorException(HttpCodeError::FORBIDDEN);
            }
            
            // Si route está vacío (lo que indica que no se encontró una ruta coincidente),
            // lanza una excepción.
            if (empty($route)) throw new HttpErrorException(HttpCodeError::NOT_FOUND);

            // Descarga del catálogo en formato CSV (vista actual).
            if ($path === '/catalog' && ($_GET['format'] ?? '') === 'csv') {
                $this->emitCatalogCsv($logger);
                return;
            }

            // Si route no está vacío, extrae el título y la página, y llama
            // al controlador de páginas para mostrar la vista correspondiente.
            $title = $route['title'];
            $page  = $route['page'];
            $this->dependencyContainer
                ->get(PageController::class)
                ->show(
                    $title,
                    $page
                );
        }

        private function emitCatalogJson(LoggerInterface $logger): void {
            $logger->info("API catálogo JSON.", ['query' => $_GET]);

            $repo        = $this->dependencyContainer->get(BookRepositoryInterface::class);
            $filters     = ContextBuilder::buildCatalogFilters($_GET);
            $perPage     = max(1, min(48, (int)($_GET['por_pagina'] ?? 12)));
            $currentPage = max(1, (int)($_GET['pagina'] ?? 1));
            $total       = $repo->countAll($filters);
            $totalPages  = max(1, (int) ceil($total / $perPage));
            $currentPage = min($currentPage, $totalPages);
            $offset      = ($currentPage - 1) * $perPage;

            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');

            echo json_encode([
                'books'       => $repo->findAll($offset, $perPage, $filters),
                'currentPage' => $currentPage,
                'totalPages'  => $totalPages,
                'total'       => $total,
            ], JSON_HEX_TAG | JSON_HEX_AMP);
        }

        /**
         * Emite la vista actual del catálogo (filtros + búsqueda + orden, sin paginación)
         * como un archivo CSV descargable. Se incluye un BOM UTF-8 para que Excel
         * interprete correctamente los acentos.
         */
        private function emitCatalogCsv(LoggerInterface $logger): void {
            $logger->info("Exportando catálogo a CSV.", ['query' => $_GET]);

            $repo    = $this->dependencyContainer->get(BookRepositoryInterface::class);
            $filters = ContextBuilder::buildCatalogFilters($_GET);
            $books   = $repo->findAll(0, PHP_INT_MAX, $filters);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="catalogo.csv"');
            header('Cache-Control: no-store');

            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Título', 'Autor', 'Género', 'Precio']);
            foreach ($books as $b) {
                fputcsv($out, [
                    $b['id'],
                    $b['title'],
                    $b['author'],
                    $b['genre'],
                    $b['price'],
                ]);
            }
            fclose($out);
        }
    }
