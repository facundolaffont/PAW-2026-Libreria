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

                    <article class="libro">
                        <a href="book-detail?id=<?= (int)$book['id'] ?>">
                            <picture>
                                <source
                                    srcset="resources/images/placeholder-libro-grande.png"
                                    media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="<?= $book['title'] ?>">
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

            <article class="promocion">
                <a href="promotions">
                    <picture>
                        <source
                            srcset="resources/images/<?= $promotion['image'] ?>"
                            media="( min-width: 600px )"
                        >
                        <img src="resources/images/<?= $promotion['image'] ?>" alt="<?= $promotion['description'] ?>">
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