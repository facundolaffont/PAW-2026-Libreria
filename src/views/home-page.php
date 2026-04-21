<body>
    
    <?php require 'components/header.php'; ?>

    <!-- Contenido principal: los libros y su información de reserva -->
    <main>

        <?php foreach ($booksByGenre as $genre => $genreBooks): ?>
        <!-- Carrusel de libros de <?= htmlspecialchars($genre) ?> -->
        <section class="carrusel">

            <h2><?= htmlspecialchars($genre) ?></h2>

            <!-- Flecha izquierda del carrusel -->
            <button class="flecha flecha-izq"></button>

            <!-- Flecha derecha del carrusel -->
            <button class="flecha flecha-der"></button>

            <div class="contenedor-carrusel">

                <div class="libros-container">

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

            <div class="libros-container">

                <!-- Promoción -->
                <article class="promocion">
                    <a href="promotions">
                        <picture>
                            <source
                                srcset="resources/images/placeholder-promoción-grande.png"
                                media="( min-width: 600px )"
                            >
                            <img src="resources/images/placeholder-promoción-chica.png" alt="Descripción de la promoción.">
                        </picture>
                    </a>
                </article>

                <!-- Promoción -->
                <article class="promocion">
                    <a href="promotions">
                        <picture>
                            <source
                                srcset="resources/images/placeholder-promoción-grande.png"
                                media="( min-width: 600px )"
                            >
                            <img src="resources/images/placeholder-promoción-chica.png" alt="Descripción de la promoción.">
                        </picture>
                    </a>
                </article>

                <!-- Promoción -->
                <article class="promocion">
                    <a href="promotions">
                        <picture>
                            <source
                                srcset="resources/images/placeholder-promoción-grande.png"
                                media="( min-width: 600px )"
                            >
                            <img src="resources/images/placeholder-promoción-chica.png" alt="Descripción de la promoción.">
                        </picture>
                    </a>
                </article>

                <!-- Se repite el artículo anterior tantas veces como promociones haya -->

            </div>

        </div>

    </section>

    <?php require 'components/footer.php'; ?>

</body>