<body>

    <?php require 'components/header.php'; ?>

    <main>

        <?php
            // Garantiza que el contexto, el cual se debería construir dinámicamente en el
            // controlador de página según la vista solicitada, siempre estará definido.
            $context = $context ?? [];
        ?>

        <!-- Barra de acciones móvil (ordenar / filtrar) -->
        <div>
            <button type="button">Ordenar</button>
            <button type="button">Filtrar</button>
        </div>

        <div>

            <aside aria-label="Filtros de búsqueda">
                <h2>Filtros</h2>

                <form action="/catalog" method="get">
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
                                            <?= in_array($genre, $context['filters']['generos']) ? 'checked' : '' ?>
                                        >
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
                            <input type="number" id="precio-min" name="precio_min" min=0 placeholder="$ 0" value="<?= htmlspecialchars($context['filters']['precio_min']) ?>">
                        </div>
                        <div>
                            <label for="precio-max">Hasta</label>
                            <input type="number" id="precio-max" name="precio_max" min=0 placeholder="$ 99999" value="<?= htmlspecialchars($context['filters']['precio_max']) ?>">
                        </div>
                    </details>

                    <details>
                        <summary>Editorial</summary>
                        <ul>
                            <!-- Las editoriales se cargan dinámicamente desde la base de datos. -->
                            <li>
                                <label>
                                    <input type="checkbox" name="editorial" value="ID" />
                                    Nombre editorial
                                </label>
                            </li>
                        </ul>
                    </details>

                    <details>
                        <summary>Idioma</summary>
                        <ul>
                            <!-- Los idiomas se cargan dinámicamente desde la base de datos. -->
                            <li>
                            <label>
                                <input type="checkbox" name="idioma" value="ID" />
                                Nombre idioma
                            </label>
                            </li>
                        </ul>
                    </details>

                    <details>
                        <summary>Autor</summary>
                        <label for="filtro-autor">Buscar autor</label>
                        <input type="text" id="filtro-autor" name="autor" placeholder="Nombre del autor" value="<?= htmlspecialchars($context['filters']['autor']) ?>">
                    </details>

                    <button type="submit">Aplicar filtros</button>
                    <a href="/catalog">Limpiar filtros</a>

                </form>
            </aside>

            <section aria-label="Listado de libros">
                <h2>Libros</h2>

                <div>
                    <label for="ordenar">Ordenar por:</label>
                    <select id="ordenar" name="orden">
                        <option value="precio-asc">Precio: menor a mayor</option>
                        <option value="precio-desc">Precio: mayor a menor</option>
                        <option value="titulo-asc">Título A–Z</option>
                        <option value="titulo-desc">Título Z–A</option>
                        <option value="autor-asc">Autor A–Z</option>
                        <option value="recientes">Más recientes</option>
                    </select>
                </div>

                <ul>

                    <?php foreach ($context['books'] as $book): ?>
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
                                        media="( max-width: <?= $source['maxWidth'] ?>px )"
                                    >
                                    <?php endforeach; ?>
                                    <img src="resources/images/<?= htmlspecialchars($fallbackSrc) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                                </picture>
                                <h3><?= htmlspecialchars($book['title']) ?></h3>
                            </a>
                            <p><?= htmlspecialchars($book['author']) ?></p>
                            <p><strong>$ <?= number_format($book['price'], 2, ',', '.') ?></strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>
                    <?php endforeach; ?>

                </ul>

                    <nav aria-label="Paginación del catálogo">
                        <ol>
                            <?php
                                $fq = $context['filterQuery'] ? '&' . $context['filterQuery'] : '';
                            ?>

                            <?php if ($context['currentPage'] > 1): ?>
                            <li>
                                <a href="catalog?pagina=<?= $context['currentPage'] - 1 ?><?= $fq ?>" aria-label="Página anterior">Anterior</a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $context['totalPages']; $i++): ?>
                            <li>
                                <a
                                    href="catalog?pagina=<?= $i ?><?= $fq ?>"
                                    aria-label="Página <?= $i ?>"
                                    <?= $i === $context['currentPage'] ? 'aria-current="page"' : '' ?>
                                ><?= $i ?></a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($context['currentPage'] < $context['totalPages']): ?>
                            <li>
                                <a href="catalog?pagina=<?= $context['currentPage'] + 1 ?><?= $fq ?>" aria-label="Página siguiente">Siguiente</a>
                            </li>
                            <?php endif; ?>
                        </ol>
                    </nav>

            </section>

        </div>

    </main>

    <?php require 'components/footer.php'; ?>

</body>