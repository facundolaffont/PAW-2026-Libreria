<body data-page="book-detail">

    <!-- Inclusión del encabezado global del sitio -->
    <?php require 'components/header.php'; ?>

    <main>

        <?php
            // Garantiza que el contexto, el cual se debería construir dinámicamente en el
            // controlador de página según la vista solicitada, siempre estará definido.
            $context = $context ?? [];
        ?>

        <!-- Breadcrumbs -->
        <nav aria-label="Migas de pan">
            <ol>
                <li><a href="#">Detalle de libro</a></li>
            </ol>
        </nav>

        <section class="contenido-detalle">
            <h2>Detalle del libro</h2>

            <!-- Verifica si existe la información del libro en el contexto -->
            <?php

            if ($context['book'] ?? false):

                /*
                 * Procesa imágenes adaptables. Se asume que
                 * $context['book']['image'] es un string formateado
                 * (p.e., "400:small.jpg;800:medium.jpg;fallback.jpg").
                 */

                $imageParts = explode(';', $context['book']['image'] ?? '');
                $sources = [];
                $fallbackSrc = 'default.jpg'; // Imagen por defecto.

                foreach ($imageParts as $part) {

                    // Si la parte tiene el formato 'ancho:url', se añade como
                    // fuente para <source>.
                    if (preg_match('/^(\d+):(.+)$/', $part, $m)) {
                        $sources[] = ['maxWidth' => (int)$m[1], 'url' => $m[2]];

                    // Si es solo una URL, se toma como la imagen de respaldo.
                    } elseif ($part) {
                        $fallbackSrc = $part;
                    }
                }
            ?>

                <!-- Contenedor visual de la portada. -->
                <figure class="portada">
                    <picture>
                        <?php foreach ($sources as $source): ?>

                            <!-- Definición de imágenes para diferentes anchos de pantalla. -->
                            <source
                                srcset="resources/images/<?= htmlspecialchars($source['url']) ?>"
                                media="(max-width: <?= (int)$source['maxWidth'] ?>px)"
                            >

                        <?php endforeach; ?>
                        
                        <!-- Imagen fallback. -->
                        <img
                            src="resources/images/<?= htmlspecialchars($fallbackSrc) ?>" 
                            alt="Portada del libro <?= htmlspecialchars($context['book']['title']) ?>"
                        >

                    </picture>
                </figure>

                <!-- Bloque de información textual del libro. -->
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

                    <?php if (!($context['isAdmin'] ?? false)): ?>
                        <button
                            type="button"
                            class="boton-reservar"
                            data-book-id="<?= (int)($context['book']['id'] ?? 0) ?>"
                            data-title="<?= htmlspecialchars($context['book']['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-author="<?= htmlspecialchars($context['book']['author'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-image="<?= htmlspecialchars($context['book']['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-price="<?= htmlspecialchars((string)($context['book']['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >Agregar a mis reservas</button>
                    <?php endif; ?>

                </article>

            <?php else: ?>

                <!-- Estado de error: mensaje cuando no hay libro disponible -->
                <p class="error-libro-no-encontrado" role="alert">
                    El libro solicitado no existe o no se encuentra disponible.
                </p>

            <?php endif; ?>
        </section>
    </main>

    <!-- Inclusión del pie de página global del sitio. -->
    <?php require 'components/footer.php'; ?>

</body>