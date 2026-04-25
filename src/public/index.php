<?php

    $services  = require '../bootstrap.php';
    $db        = $services['db'];
    $appLogger = $services['appLogger'];

    use Paw\Router;
    use Paw\Controllers\PageController;
    use Paw\Repositories\BookRepository;
    use Paw\Repositories\PromotionRepository;

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
    $appLogger->info("Ruta accedida: {$path}");

    # Delega al controlador de página.
    $pageController = new PageController(new BookRepository($db), new PromotionRepository($db));
    $pageController->show($title, $page);