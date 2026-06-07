<body data-page="<?= htmlspecialchars($context['page'] ?? '') ?>">

    <?php require 'components/header.php'; ?>

    <!-- Backdrop para el drawer de filtros (mobile) -->
    <div class="catalog-backdrop" id="catalog-backdrop" aria-hidden="true"></div>

    <main>

        <?php $context = $context ?? []; ?>

        <!-- Barra de acciones móvil -->
        <div class="acciones-mobile">
            <button type="button" id="btn-abrir-filtros" aria-controls="filtrar-mobile" aria-expanded="false">
                Filtrar
                <span class="filtros-activos-badge" id="filtros-badge" hidden></span>
            </button>
            <select id="ordenar-mobile-select" aria-label="Ordenar por">
                <option value="titulo-asc">Título A–Z</option>
                <option value="titulo-desc">Título Z–A</option>
                <option value="precio-asc">Precio: menor a mayor</option>
                <option value="precio-desc">Precio: mayor a menor</option>
                <option value="autor-asc">Autor A–Z</option>
                <option value="recientes">Más recientes</option>
            </select>
        </div>

        <script id="catalog-data" type="application/json">
            <?php // JSON island: todos los libros para el motor JS.  ?>
            <?=
                json_encode([
                    'books'      => $context['allBooks'] ?? [],
                    'priceRange' => $context['priceRange'] ?? ['min' => 0, 'max' => 99999],
                    'initialQ'   => $context['filters']['q'] ?? '',
                ],
                
                // Transforma los símbolos '<' y '>' en sus representaciones hexadecimales de Unicode.
                // Evita que el navegador ejecute código JavaScript si viene embebido en los datos en un nuevo
                // <script>.
                JSON_HEX_TAG | 
                
                // Transforma el símbolo '&' en su representación hexadecimal de Unicode.
                // Evita que el navegador confunda un carácter ampersand con el inicio de una entidad HTML.
                JSON_HEX_AMP) 
            ?>
        </script>

        <div>

            <aside aria-label="Filtros de búsqueda" id="filtrar-mobile" role="dialog" aria-modal="true">
                <div class="aside-cabecera">
                    <h2>Filtros</h2>
                    <button type="button" id="btn-cerrar-filtros" aria-label="Cerrar filtros">&#x2715;</button>
                </div>

                <form action="/catalog" method="get" id="catalog-form">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($context['filters']['q'] ?? '') ?>">

                    <details class="color-blanco">
                        <summary>Categorías</summary>
                        <nav aria-label="Filtrar por categoría">
                            <ul>
                                <?php foreach ($context['genres'] ?? [] as $genre): ?>
                                    <li>
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="genero[]"
                                                value="<?= htmlspecialchars($genre) ?>"
                                                <?= in_array($genre, $context['filters']['generos'] ?? []) ? 'checked' : '' ?>>
                                            <?= htmlspecialchars($genre) ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </details>

                    <details>
                        <summary>Precio</summary>
                        <div>
                            <label for="precio-min">Desde</label>
                            <input type="number" id="precio-min" name="precio_min" min="0" placeholder="$ 0" value="<?= htmlspecialchars($context['filters']['precio_min'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="precio-max">Hasta</label>
                            <input type="number" id="precio-max" name="precio_max" min="0" placeholder="$ 99999" value="<?= htmlspecialchars($context['filters']['precio_max'] ?? '') ?>">
                        </div>
                    </details>

                    <details class="filtro-autor">
                        <summary>Autor</summary>
                        <ul class="autor-lista" id="autor-lista">
                            <?php
                            $autores = $context['authors'] ?? [];
                            $autoresVisibles = array_slice($autores, 0, 7);
                            $autoresOcultos = array_slice($autores, 7);
                            foreach ($autoresVisibles as $autor):
                            ?>
                                <li>
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="autor[]"
                                            value="<?= htmlspecialchars($autor) ?>"
                                            <?= in_array($autor, $context['filters']['autor'] ?? []) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($autor) ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (count($autoresOcultos) > 0): ?>
                            <ul class="autor-lista autor-lista-oculta" id="autor-lista-oculta" style="display: none;">
                                <?php foreach ($autoresOcultos as $autor): ?>
                                    <li>
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="autor[]"
                                                value="<?= htmlspecialchars($autor) ?>"
                                                <?= in_array($autor, $context['filters']['autor'] ?? []) ? 'checked' : '' ?>>
                                            <?= htmlspecialchars($autor) ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="autor-ver-todos" id="autor-ver-todos">Ver todos</button>
                        <?php endif; ?>
                    </details>

                    <button type="button" id="aplicar-filtros-mobile">Aplicar filtros</button>
                    <button type="button" id="limpiar-filtros">Limpiar filtros</button>

                </form>
            </aside>

            <section aria-label="Listado de libros">
                <h2>Libros</h2>

                <!-- Barra de controles JS -->
                <div class="catalog-controles-barra">
                    <div id="ordenar-desktop-wrap">
                        <label for="ordenar">Ordenar por:</label>
                        <select id="ordenar" name="orden">
                            <option value="titulo-asc">Título A–Z</option>
                            <option value="titulo-desc">Título Z–A</option>
                            <option value="precio-asc">Precio: menor a mayor</option>
                            <option value="precio-desc">Precio: mayor a menor</option>
                            <option value="autor-asc">Autor A–Z</option>
                            <option value="recientes">Más recientes</option>
                        </select>
                    </div>
                    <div class="catalog-vista-controles">
                        <label for="por-pagina">Por página:</label>
                        <select id="por-pagina">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
                        </select>
                        <label class="toggle-scroll-label">
                            <input type="checkbox" id="scroll-infinito"> Scroll infinito
                        </label>
                    </div>
                    <p class="catalog-resultado-count" id="resultado-count" aria-live="polite"></p>
                </div>

                <!-- Listado renderizado por JS -->
                <ul id="catalog-lista-js" hidden></ul>
                <div id="catalog-sentinel" class="catalog-sentinel" aria-hidden="true"></div>
                <nav id="catalog-paginacion-js" aria-label="Paginación del catálogo" hidden></nav>

                <!-- Fallback server-side (oculto cuando JS activo) -->
                <div class="catalog-server-render">
                    <ul>
                        <?php foreach ($context['books'] ?? [] as $book): ?>
                            <?php
                            $imageParts  = explode(';', $book['image']);
                            $sources     = [];
                            $fallbackSrc = '';
                            foreach ($imageParts as $part) {
                                if (preg_match('/^(\d+):(.+)$/', $part, $m)) {
                                    $sources[] = ['maxWidth' => (int)$m[1], 'url' => $m[2]];
                                } else {
                                    $fallbackSrc = $part;
                                }
                            }
                            ?>
                            <li>
                                <article>
                                    <a href="book-detail?id=<?= (int)$book['id'] ?>">
                                        <picture>
                                            <?php foreach ($sources as $source): ?>
                                                <source
                                                    srcset="resources/images/<?= htmlspecialchars($source['url']) ?>"
                                                    media="( max-width: <?= $source['maxWidth'] ?>px )">
                                            <?php endforeach; ?>
                                            <img src="resources/images/<?= htmlspecialchars($fallbackSrc) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                                        </picture>
                                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                                    </a>
                                    <p><?= htmlspecialchars($book['author']) ?></p>
                                    <p><strong>$ <?= number_format($book['price'], 2, ',', '.') ?></strong></p>
                                    <?php if (!($context['isAdmin'] ?? false)): ?>
                                        <button type="button">Reservar</button>
                                    <?php endif; ?>
                                </article>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                    <nav aria-label="Paginación del catálogo">
                        <ol>
                            <?php
                            $fq = ($context['filterQuery'] ?? '') ? '&' . $context['filterQuery'] : '';
                            ?>
                            <?php if (($context['currentPage'] ?? 1) > 1): ?>
                                <li>
                                    <a href="catalog?pagina=<?= $context['currentPage'] - 1 ?><?= $fq ?>" aria-label="Página anterior">Anterior</a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= ($context['totalPages'] ?? 1); $i++): ?>
                                <li>
                                    <a
                                        href="catalog?pagina=<?= $i ?><?= $fq ?>"
                                        aria-label="Página <?= $i ?>"
                                        <?= $i === ($context['currentPage'] ?? 1) ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if (($context['currentPage'] ?? 1) < ($context['totalPages'] ?? 1)): ?>
                                <li>
                                    <a href="catalog?pagina=<?= $context['currentPage'] + 1 ?><?= $fq ?>" aria-label="Página siguiente">Siguiente</a>
                                </li>
                            <?php endif; ?>
                        </ol>
                    </nav>

                    <p class="descargar-csv-wrapper">
                        <a
                            href="catalog?<?= htmlspecialchars($context['filterQuery'] ?? '') ?><?= ($context['filterQuery'] ?? '') ? '&' : '' ?>format=csv"
                            id="descargar-csv"
                            class="descargar-csv">Descargar vista actual en CSV</a>
                    </p>
                </div>

                <!-- CSV link accesible desde JS -->
                <p class="descargar-csv-wrapper catalog-csv-js" hidden>
                    <a href="/catalog?format=csv" id="descargar-csv-js" class="descargar-csv">Descargar vista actual en CSV</a>
                </p>

            </section>

        </div>

    </main>

    <?php require 'components/footer.php'; ?>

    <?php
    $listItems = [];
    foreach ($context['books'] ?? [] as $i => $book) {
        $imageParts = explode(';', $book['image'] ?? '');
        $fb = 'default.jpg';
        foreach ($imageParts as $part) {
            if (!preg_match('/^\d+:.+$/', $part)) {
                $fb = $part;
                break;
            }
        }

        $listItems[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => [
                '@type'  => 'Book',
                'name'   => $book['title'],
                'author' => $book['author'],
                'image'  => 'resources/images/' . $fb,
                'url'    => 'book-detail?id=' . (int)$book['id'],
            ],
        ];
    }
    ?>
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'ItemList',
        'name'     => 'Catálogo de libros',
        'itemListElement' => $listItems,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?></script>

</body>
