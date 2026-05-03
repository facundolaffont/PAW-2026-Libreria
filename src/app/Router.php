<?php

    namespace Paw;

    class Router {

        public function addRoute(string $path, string $page, string $title) {
            $this->routes[$path] = [
                'page' => $page,
                'title' => $title,
            ];

            return $this;
        }

        public function route(string $path): array {
            return $this->routes[$path] ?? [
                'page' => '404',
                'title' => 'Página no encontrada'
            ];
        }

        private array $routes = [];
    }