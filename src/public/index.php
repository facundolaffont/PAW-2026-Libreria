<?php

    $container = require '../bootstrap.php';

    use Paw\Router;
    use Paw\Controllers\PageController;
    use Paw\Controllers\ReservationController;
    use Paw\Services\ContextBuilder;
    use Psr\Log\LoggerInterface;

    $method = $_SERVER['REQUEST_METHOD'];
    $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $container->get(LoggerInterface::class)->info("{$method} {$path}");

    /*
     * Las solicitudes POST corresponden al envío de formularios.
     * Los datos viajan en el cuerpo de la petición, codificados como
     * application/x-www-form-urlencoded (pares clave=valor separados por &,
     * con caracteres especiales en formato URL-encoding).
     * PHP los decodifica y los expone en el superglobal $_POST.
     */
    if ($method === 'POST') {
        match ($path) {
            '/reservation' => $container->get(ReservationController::class)->handle(),
            default        => (function () use ($path) {
                http_response_code(405);
                header('Allow: POST /reservation');
                echo "Método no permitido.";
            })(),
        };
        exit;
    }

    # Rutas GET: cada ruta mapea una URL a un título de página y una vista.
    $router = new Router();
    $router
        ->addRoute('/', 'Página principal', 'home-page')
        ->addRoute('/promotions', 'Promociones', 'promotions')
        ->addRoute('/catalog', 'Catálogo', 'catalog')
        ->addRoute('/book-detail', 'Detalles de libro', 'book-detail')
        ->addRoute('/reservation', 'Reserva', 'reservation')
        ->addRoute('/about-us', 'Acerca de nosotros', 'about-us');

    $route = $router->route($path);
    $title = $route['title'];
    $page  = $route['page'];

    $container
        ->get(PageController::class)
        ->show(
            $container->get(ContextBuilder::class),
            $title,
            $page
        );
