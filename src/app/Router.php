<?php

    namespace Paw;

    class Router {

        private $routes = [];

        public function addRoute($path, $title, $page) {
            $this->routes[$path] = [
                'title' => $title,
                'page' => $page,
            ];

            return $this;
        }

        public function route($path) {
            return $this->routes[$path] ?? [
                'title' => 'PAWPrints - Página no encontrada',
                'page' => '404'
            ];
        }
    }