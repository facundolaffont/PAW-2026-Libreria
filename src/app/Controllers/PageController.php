<?php

    namespace Paw\Controllers;

    use Paw\Services\ContextBuilder;
    use Paw\View;
    use Psr\Log\LoggerInterface;

    class PageController {
        
        public function __construct() {}

        public function show(
            ContextBuilder $contextBuilder, 
            string $title, 
            string $page, 
            LoggerInterface $logger
        ): void {
            $logger->debug(
                "",
                compact('title', 'page')
            );

            // Construye el contexto específico para la página solicitada.
            $context = $contextBuilder->build($title, $page, $_GET);

            // Renderiza la vista correspondiente, pasando el contexto construido.
            View::render($page, $title, $logger, $context);
        }
    }