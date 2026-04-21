<?php

    require '../bootstrap.php';

    use Paw\Controllers\PageController;

    # Obtiene ruta actual.
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    # Asigna rutas y rutea.
    $pageController = new PageController();
    $pageController
        ->addRoute('/', 'PAWPrints - Página principal', 'home-page')
        ->addRoute('/promotions', 'PAWPrints - Promociones', 'promotions')
        ->addRoute('/catalog', 'PAWPrints - Catálogo', 'catalog')
        ->addRoute('/book-detail', 'PAWPrints - Detalles de libro', 'book-detail')
        ->addRoute('/reservation', 'PAWPrints - Reserva', 'reservation')
        ->addRoute('/about-us', 'PAWPrints - Acerca de nosotros', 'about-us');
    $route = $pageController->route($path);
    $title = $route['title'];
    $page = $route['page'];
    $appLogger->info("Ruta accedida: {$path}");
    require '../components/html.php';