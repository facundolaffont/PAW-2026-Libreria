<body>
    
    <?php require 'components/header.php'; ?>

    <!-- Contenido principal: los libros y su información de reserva -->
    <main>

        <?php
            // Garantiza que el contexto, el cual se debería construir dinámicamente en el
            // controlador de página según la vista solicitada, siempre estará definido.
            $context = $context ?? [];

            // Define una rotacion de efectos para asignar uno distinto a cada carrusel de libros.
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
                                <?php foreach ($sources as $source): ?>
                                <source
                                    data-carousel-srcset="resources/images/<?= $source['url'] ?>"
                                    media="( max-width: <?= $source['maxWidth'] ?>px )"
                                >
                                <?php endforeach; ?>
                                <img
                                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                    data-carousel-src="resources/images/<?= $fallbackSrc ?>"
                                    alt="<?= $book['title'] ?>"
                                >
                            </picture>
                            <h3><?= $book['title'] ?></h3>
                        </a>
                        <p><?= $book['author'] ?></p>
                        <p>$ <?= number_format($book['price'], 2, ',', '.') ?></p>
                        <button>Reservar</button>
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
                        <?php foreach ($sources as $source): ?>
                        <source
                            data-carousel-srcset="resources/images/<?= $source['url'] ?>"
                            media="( max-width: <?= $source['maxWidth'] ?>px )"
                        >
                        <?php endforeach; ?>
                        <img
                            src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
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