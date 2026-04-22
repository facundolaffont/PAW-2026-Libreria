<?php

    namespace Paw;

    class View {
        public static function render(string $title, string $page, array $context = []): void {
            require __DIR__ . '../../components/html.php';
        }
    }