<body>
    
    <?php require 'components/header.php'; ?>

    <!-- Contenido principal: los libros y su información de reserva -->
    <main>

        <?php foreach ($context['booksByGenre'] as $genre => $genreBooks): ?>

        <!-- Carrusel de libros de <?= htmlspecialchars($genre) ?> -->
        <section class="carrusel">

            <h2><?= htmlspecialchars($genre) ?></h2>

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
                                <img src="resources/images/placeholder-libro-chica.png" alt="<?= htmlspecialchars($book['title']) ?>">
                            </picture>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                        </a>
                        <p><?= htmlspecialchars($book['author']) ?></p>
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
                            srcset="resources/images/<?= htmlspecialchars($promotion['image']) ?>"
                            media="( min-width: 600px )"
                        >
                        <img src="resources/images/<?= htmlspecialchars($promotion['image']) ?>" alt="<?= htmlspecialchars($promotion['description']) ?>">
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
            new Carrusel(carrusel);
        });
    </script>

</body>