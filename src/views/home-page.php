<body>
    
    <?php require 'components/header.php'; ?>

    <!-- Contenido principal: los libros y su información de reserva -->
    <main>

        <?php foreach ($context['booksByGenre'] as $genre => $genreBooks): ?>

        <!-- Carrusel de libros de <?= $genre ?> -->
        <section class="carrusel">

            <h2><?= $genre ?></h2>

            <!-- Flecha izquierda del carrusel -->
            <button class="flecha flecha-izq"></button>

            <!-- Flecha derecha del carrusel -->
            <button class="flecha flecha-der"></button>

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
                                    srcset="resources/images/<?= $source['url'] ?>"
                                    media="( max-width: <?= $source['maxWidth'] ?>px )"
                                >
                                <?php endforeach; ?>
                                <img src="resources/images/<?= $fallbackSrc ?>" alt="<?= $book['title'] ?>">
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

        <?php endforeach; ?>

    </main>

    <!-- Promociones -->
    <section class="carrusel">

        <h2>Promociones</h2>

        <!-- Flecha izquierda del carrusel -->
        <button class="flecha flecha-izq"></button>

        <!-- Flecha derecha del carrusel -->
        <button class="flecha flecha-der"></button>

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
                            srcset="resources/images/<?= $source['url'] ?>"
                            media="( max-width: <?= $source['maxWidth'] ?>px )"
                        >
                        <?php endforeach; ?>
                        <img src="resources/images/<?= $fallbackSrc ?>" alt="<?= $promotion['description'] ?>">
                    </picture>
                </a>
            </article>

            <?php endforeach; ?>

        </div>

    </section>

    <?php require 'components/footer.php'; ?>

    <script src="resources/js/components/Carrusel.js"></script>
    <script>
        document.querySelectorAll('section.carrusel').forEach(carrusel => {
            const container = carrusel.querySelector('div');
            const buttons = carrusel.querySelectorAll('button');
            const leftButton = buttons[0];
            const rightButton = buttons[1];
            const firstItem = container.querySelector('article');
            const gap = parseFloat(getComputedStyle(container).gap) || 0;
            const scrollAmount = firstItem.offsetWidth + gap;
            new Carrusel(container, leftButton, rightButton, scrollAmount);
        });
    </script>

</body>