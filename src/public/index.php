<?php

    $container = require '../bootstrap.php';
    
    // Vuelve global la instancia del contenedor para que pueda ser accedida desde cualquier
    // parte de la aplicación.
    global $container;

    use Paw\Router;
    use Paw\Controllers\PageController;
    use Psr\Log\LoggerInterface;

    # Obtiene ruta actual.
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    # Asigna rutas y solicita al ruteador que ejecute el ruteo.
    $router = new Router();
    $router
        ->addRoute('/', 'PAWPrints - Página principal', 'home-page')
        ->addRoute('/promotions', 'PAWPrints - Promociones', 'promotions')
        ->addRoute('/catalog', 'PAWPrints - Catálogo', 'catalog')
        ->addRoute('/book-detail', 'PAWPrints - Detalles de libro', 'book-detail')
        ->addRoute('/reservation', 'PAWPrints - Reserva', 'reservation')
        ->addRoute('/about-us', 'PAWPrints - Acerca de nosotros', 'about-us');
    $route = $router->route($path);
    $title = $route['title'];
    $page = $route['page'];
    $container->get(LoggerInterface::class)->info("Ruta accedida: {$path}");

    # Delega al controlador de página.
    $container->get(PageController::class)->show($title, $page);