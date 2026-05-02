<?php

    namespace Paw;

    use Psr\Log\LoggerInterface;

    class View {
        public static function render(string $title, string $page, array $context = []): void {
            global $container;
            $container
                ->get(LoggerInterface::class)
                ->debug("View.render. [\$title=$title; \$page=$page]");

            $title = "PAWPrints - $title";
            require __DIR__ . '../../components/html.php';
        }
    }