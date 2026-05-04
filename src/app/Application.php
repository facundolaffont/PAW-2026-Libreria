<?php

    namespace Paw;

    use Paw\Controllers\PageController;
    use Paw\Controllers\ReservationController;
    use Paw\Enums\HttpCodeError;
    use Paw\Errors\Exceptions\HttpErrorException;
    use Paw\Services\ContextBuilder;
    use Psr\Container\ContainerInterface;
    use Psr\Log\LoggerInterface;

    class Application {

        public function __construct(private ContainerInterface $dependencyContainer) {}

        public function run(): void {
            
            $logger = $this->dependencyContainer->get(LoggerInterface::class);

            $method = $_SERVER['REQUEST_METHOD'];
            $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $logger->info("{$method} {$path}");

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
                    default        => (function () {
                        http_response_code(405);
                        header('Allow: POST /reservation');
                        echo "Método no permitido.";
                    })(),
                };
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
                ->addRoute('/about-us', 'about-us', 'Acerca de nosotros');
            $route = $router->route($path);
            $logger->debug("Resultado del ruteo: " . print_r($route, true));
            
            // Si route está vacío (lo que indica que no se encontró una ruta coincidente),
            // lanza una excepción.
            if (empty($route)) throw new HttpErrorException(HttpCodeError::NOT_FOUND);

            // Si route no está vacío, extrae el título y la página, y llama
            // al controlador de páginas para mostrar la vista correspondiente.
            $title = $route['title'];
            $page  = $route['page'];
            $this->dependencyContainer
                ->get(PageController::class)
                ->show(
                    $this->dependencyContainer->get(ContextBuilder::class),
                    $title,
                    $page,
                    $logger
                );
        }
    }
