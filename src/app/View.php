<?php

    namespace Paw;

    use Psr\Log\LoggerInterface;

    class View {
        public static function render(
            string $title,
            string $page,
            LoggerInterface $logger,
            array $context = []
        ): void {
            $logger->debug("View.render. [\$title=$title; \$page=$page]");

            // Genera la vista.
            $title = "PAWPrints - $title";
            require __DIR__ . '../../components/html.php';
        }
    }