<?php

    # Configuraciones iniciales.
    require '../bootstrap.php';

    # Ruteador.
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    switch ($path) {
        case '/':
            $title = 'PAWPrints - Página principal';
            $pageStyle = 'home-page.css';
            $bodyContent = 'home-page';
            break;
        case '/promotions':
            $title = 'PAWPrints - Promociones';
            $pageStyle = 'promotions.css';
            $bodyContent = 'promotions';
            break;
        case '/catalog':
            $title = 'PAWPrints - Catálogo';
            $pageStyle = 'catalog.css';
            $bodyContent = 'catalog';
            break;
        case '/book-detail':
            $title = 'PAWPrints - Detalles de libro';
            $pageStyle = 'book-detail.css';
            $bodyContent = 'book-detail';
            break;
        case '/reservation':
            $title = 'PAWPrints - Reserva';
            $pageStyle = 'reservation.css';
            $bodyContent = 'reservation';
            break;
        case '/about-us':
            $title = 'PAWPrints - Acerca de nosotros';
            $pageStyle = 'about-us.css';
            $bodyContent = 'about-us';
            break;
        default:
            $title = 'PAWPrints - Página no encontrada';
            $pageStyle = '404.css';
            $bodyContent = '404';
    }

    require '../components/html.php';
?>