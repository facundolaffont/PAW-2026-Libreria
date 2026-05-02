<?php

    namespace Paw\Controllers;

    use Paw\Services\ContextBuilder;
    use Paw\View;
    use Psr\Log\LoggerInterface;

    class PageController {
        
        public function __construct() {}

        public function show(ContextBuilder $contextBuilder, string $title, string $page): void {
            global $container;
            $container->get(LoggerInterface::class)->debug("PageController.show. [\$title=$title]");

            // Construye el contexto específico para la página solicitada.
            $context = $contextBuilder->build($title, $page, $_GET);

            // Renderiza la vista correspondiente, pasando el contexto construido.
            View::render($title, $page, $context);
        }
    }