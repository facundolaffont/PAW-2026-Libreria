<?php

    namespace Paw;

    use Paw\Services\ContextBuilder;
    use Psr\Log\LoggerInterface;
    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    class View {

        /**
         * Renderiza una plantilla Twig con el contexto proporcionado.
         *
         * @param string $page El nombre de la plantilla Twig a renderizar (sin
         * extensión).
         * @param string $title El título de la página que se mostrará en el
         * navegador.
         * @param LoggerInterface $logger El logger para registrar información
         * de depuración.
         * @param array $context Un arreglo asociativo con los datos de
         * contexto que se pasarán a la plantilla Twig. Según la vista, puede
         * incluir claves como:
         * - 'booksByGenre': arreglo de libros agrupados por género.
         * - 'promotions': arreglo de promociones.
         * - 'books': arreglo de libros.
         * - 'book': arreglo con los datos de un libro individual.
         *
         * Además, este método agrega automáticamente al contexto las claves:
         * 'currentUser', 'isAdmin', 'isClient', 'requestPath',
         * 'safeQueryParams' y 'query'.
         * @return void
         */
        public static function render(
            string $page,
            string $title,
            LoggerInterface $logger,
            array $context = []
        ): void {
            $logger->debug("",
                compact('page', 'title', 'context')
            );

            $context = ContextBuilder::addSharedContext($context);

            // Configura Twig para que cargue las plantillas desde el directorio de
            // vistas.
            $loader = new FilesystemLoader(__DIR__ . '/../views');
            $twig = new Environment($loader, [
                'cache' => false,
            ]);

            echo $twig->render("{$page}.twig", [
                'page' => $page,
                'title' => "PAWPrints - $title",
                'context' => $context,
            ]);
        }
    }
