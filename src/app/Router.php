<?php

    namespace Paw;

    class Router {

        public function addRoute(string $path, string $title, string $page) {
            $this->routes[$path] = [
                'title' => $title,
                'page' => $page,
            ];

            return $this;
        }

        public function route(string $path): array {
            return $this->routes[$path] ?? [
                'title' => 'Página no encontrada',
                'page' => '404'
            ];
        }

        private array $routes = [];
    }