<?php

    namespace Paw;

    use Psr\Log\LoggerInterface;

    class View {
        public static function render(
            string $page,
            string $title,
            LoggerInterface $logger,
            array $context = []
        ): void {
            $logger->debug(
                "",
                compact('page', 'title', 'context')
            );

            // Genera la vista.
            $title = "PAWPrints - $title";
            require __DIR__ . '../../components/html.php';
        }
    }