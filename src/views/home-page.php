<body>
    
    <?php require 'components/header.php'; ?>

    <!-- Contenido principal: los libros y su información de reserva -->
    <main>

        <?php
            
            /**
             * Variable que aloja los datos de contexto para la vista,
             * construidos dinámicamente en el controlador de página.
             * 
             * @var array{
             *      currentPageName: string,
             *      booksByGenre: array<
             *          string,
             *          array<
             *              int,
             *              array{
             *                  id: int,
             *                  title: string,
             *                  author: string,
             *                  genre: string,
             *                  price: float,
             *                  image: string
             *              }
             *          >
             *      >
             * } $context
             */
            $context = $context ?? [];

            // Define una rotacion de efectos para asignar uno distinto a cada carrusel.
            $bookCarouselEffects = ['slide', 'block', 'disappear'];
            $bookCarouselIndex = 0;
        ?>

        <?php foreach ($context['booksByGenre'] as $genre => $genreBooks): ?>

        <!-- Carrusel de libros de <?= $genre ?> -->
        <section
            class="carrusel"
            data-effect="<?= $bookCarouselEffects[$bookCarouselIndex % count($bookCarouselEffects)] ?>"
        >

            <h2><?= $genre ?></h2>

            <div class="contenedor-carrusel">

                <?php foreach ($genreBooks as $book): ?>

                    <?php
                        // Obtiene las posibles URLs para el libro en cuestión.
                        $imageParts = explode(';', $book['image']);
                        $sources = [];
                        $fallbackSrc = '';
                        foreach ($imageParts as $part) {
                            if (preg_match('/^(\d+):(.+)$/', $part, $m)) {
                                $sources[] = ['maxWidth' => (int)$m[1], 'url' => $m[2]];
                            } else {
                                $fallbackSrc = $part;
                            }
                        }
                    ?>

                    <article class="libro">
                        <a href="book-detail?id=<?= (int)$book['id'] ?>">
                            <picture>
                                <?php foreach ($sources as $source):
                                    // Se reemplaza source.srcset por data-carousel-srcset
                                    // para evitar que el navegador cargue las imágenes antes de tiempo,
                                    // y permite que data-carousel-srcset sea administrado por
                                    // Carousel.
                                ?>
                                    <source
                                        data-carousel-srcset="resources/images/<?= $source['url'] ?>"
                                        media="( max-width: <?= $source['maxWidth'] ?>px )"
                                    >
                                <?php endforeach; ?>
                                <img
                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 2 3'%3E%3Crect width='2' height='3' fill='white'/%3E%3C/svg%3E"
                                    data-carousel-src="resources/images/<?= $fallbackSrc ?>"
                                    alt="<?= $book['title'] ?>"
                                >
                            </picture>
                            <h3><?= $book['title'] ?></h3>
                        </a>
                        <p><?= $book['author'] ?></p>
                        <p>$ <?= number_format($book['price'], 2, ',', '.') ?></p>
                        <?php if (!($context['isAdmin'] ?? false)): ?>
                            <button>Reservar</button>
                        <?php endif; ?>
                    </article>

                <?php endforeach; ?>

            </div>

        </section>

        <?php $bookCarouselIndex += 1; ?>

        <?php endforeach; ?>

    </main>

    <!-- Promociones -->
    <section class="carrusel" data-effect="block">

        <h2>Promociones</h2>

        <div class="contenedor-carrusel">
            
            <?php foreach ($context['promotions'] as $promotion): ?>

            <?php
                $imageParts = explode(';', $promotion['image']);
                $sources = [];
                $fallbackSrc = '';
                foreach ($imageParts as $part) {
                    if (preg_match('/^(\d+):(.+)$/', $part, $m)) {
                        $sources[] = ['maxWidth' => (int)$m[1], 'url' => $m[2]];
                    } else {
                        $fallbackSrc = $part;
                    }
                }
            ?>

            <article class="promocion">
                <a href="promotions">
                    <picture>
                        <?php foreach ($sources as $source):
                            // Se reemplaza source.srcset por data-carousel-srcset
                            // para evitar que el navegador cargue las imágenes antes de tiempo,
                            // y permite que data-carousel-srcset sea administrado por
                            // Carousel.
                        ?>
                            <source
                                data-carousel-srcset="resources/images/<?= $source['url'] ?>"
                                media="( max-width: <?= $source['maxWidth'] ?>px )"
                            >
                        <?php endforeach; ?>
                        <img
                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='white'/%3E%3C/svg%3E"
                            data-carousel-src="resources/images/<?= $fallbackSrc ?>"
                            alt="<?= $promotion['description'] ?>"
                        >
                    </picture>
                </a>
            </article>

            <?php endforeach; ?>

        </div>

    </section>

    <?php require 'components/footer.php'; ?>

    <script src="resources/js/components/Carousel.js"></script>
    <script>

        // Inicializa los carruseles con unicamente su contenedor.
        // El efecto se define por argumento (tomado del data-effect de cada seccion).
        document.querySelectorAll('section.carrusel').forEach(carrusel => {
            const container = carrusel.querySelector('.contenedor-carrusel');
            const effect = carrusel.dataset.effect || 'slide';
            new Carousel(container, { effect: effect, autoPlayMs: 4000 });
        });

    </script>

</body>
