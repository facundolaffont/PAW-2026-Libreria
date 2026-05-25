<?php

    namespace Paw\Controllers;

    use Paw\Services\ContextBuilder;
    use Paw\View;
    use Psr\Log\LoggerInterface;

    class PageController {
        
        public function __construct(
            private ContextBuilder $contextBuilder, 
            private LoggerInterface $logger
        ) {}

        public function show(
            string $title, 
            string $page
        ): void {
            $this->logger->debug(
                "",
                compact('title', 'page')
            );

            // Construye el contexto específico para la página solicitada.
            $context = $this->contextBuilder->build($title, $page, $_GET);

            // Renderiza la vista correspondiente, pasando el contexto construido.
            View::render($page, $title, $this->logger, $context);
        }
    }