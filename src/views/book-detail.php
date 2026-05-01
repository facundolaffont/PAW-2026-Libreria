<body>

    <?php require 'components/header.php'; ?>


    <main>

        <?php
            // Garantiza que el contexto, el cual se debería construir dinámicamente en el
            // controlador de página según la vista solicitada, siempre estará definido.
            $context = $context ?? [];
        ?>

        <nav aria-label="Migas de pan">
            <ol>
                <li><a href="#">Detalle de libro</a></li>
            </ol>
        </nav>

        <section class="contenido-detalle">
            <h2>Detalle del libro</h2>


            <?php if ($context['book'] ?? false): ?>
                <?php
                    $imageParts = explode(';', $context['book']['image'] ?? '');
                    $sources = [];
                    $fallbackSrc = 'default.jpg';
                    foreach ($imageParts as $part) {
                        if (preg_match('/^(\d+):(.+)$/', $part, $m)) {
                            $sources[] = ['maxWidth' => (int)$m[1], 'url' => $m[2]];
                        } elseif ($part) {
                            $fallbackSrc = $part;
                        }
                    }
                ?>
                <figure class="portada">
                    <picture>
                        <?php foreach ($sources as $source): ?>
                        <source
                            srcset="resources/images/<?= htmlspecialchars($source['url']) ?>"
                            media="(max-width: <?= (int)$source['maxWidth'] ?>px)"
                        >
                        <?php endforeach; ?>
                        <img src="resources/images/<?= htmlspecialchars($fallbackSrc) ?>" alt="Portada del libro <?= htmlspecialchars($context['book']['title']) ?>">
                    </picture>
                </figure>

                <article class="info-libro">
                    <h3 class="titulo-libro"><?= htmlspecialchars($context['book']['title']) ?></h3>
                    <p class="autor">por <?= htmlspecialchars($context['book']['author']) ?></p>

                    <section class="seccion-descripcion">
                        <h4>Descripción</h4>
                        <p><?= nl2br(htmlspecialchars($context['book']['description'] ?? 'Sin descripción.')) ?></p>
                    </section>

                    <section class="seccion-generos">
                        <h4>Géneros</h4>
                        <p><?= htmlspecialchars($context['book']['genre'] ?? 'Sin género') ?></p>
                    </section>

                    <button type="button" class="boton-reservar">Agregar a mis reservas</button>
                </article>
            <?php else: ?>
                <div class="error-libro-no-encontrado">
                    <p>El libro solicitado no existe o no se encuentra disponible.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require 'components/footer.php'; ?>

</body>