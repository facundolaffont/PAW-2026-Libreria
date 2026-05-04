<?php

    namespace Paw;

    use \Psr\Log\LoggerInterface;

    class Router {

        public function __construct(private LoggerInterface $logger) {}

        private array $routes = [];

        public function addRoute(string $path, string $page, string $title) {
            $this->routes[$path] = [
                'page' => $page,
                'title' => $title,
            ];

            return $this;
        }

        public function route(string $path): array {
            $this->logger->debug("Ruteo: {$path}");

            return $this->routes[$path] ?? [];
        }

    }
