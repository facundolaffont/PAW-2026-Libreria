<body>

    <?php require 'components/header.php'; ?>

    <main>

        <?php
            // Garantiza que el contexto, el cual se debería construir dinámicamente en el
            // controlador de página según la vista solicitada, siempre estará definido.
            $context = $context ?? [];
        ?>

        <!-- Barra de acciones móvil (ordenar / filtrar) -->
        <div class="acciones-mobile">
            <button type="button" data-toggle="ordenar-opciones-mobile">Ordenar</button>
            <button type="button" data-toggle="filtrar-mobile">Filtrar</button>
        </div>

        <?php
            // Opciones de orden para la versión móvil (lista de links).
            // Reusa $ordenOpciones definido más abajo; lo declaramos acá para
            // poder iterarlo antes.
            $ordenOpcionesMobile = [
                'titulo-asc'  => 'Título A–Z',
                'titulo-desc' => 'Título Z–A',
                'precio-asc'  => 'Precio: menor a mayor',
                'precio-desc' => 'Precio: mayor a menor',
                'autor-asc'   => 'Autor A–Z',
                'recientes'   => 'Más recientes',
            ];
            $ordenActualMobile = $context['filters']['orden'] ?? '';
            // Tomamos el query actual (sin paginación). Si ya contiene `orden`,
            // PHP toma el último valor de query strings con clave repetida, así
            // que al concatenar &orden=... el nuevo gana.
            $ordenBase = $context['filterQuery'] ?? '';
        ?>
        <ul id="ordenar-opciones-mobile" class="ordenar-opciones-mobile">
            <?php foreach ($ordenOpcionesMobile as $value => $label): ?>
            <li>
                <a
                    href="catalog?<?= htmlspecialchars($ordenBase) ?><?= $ordenBase ? '&' : '' ?>orden=<?= $value ?>"
                    <?= $ordenActualMobile === $value ? 'aria-current="true"' : '' ?>
                ><?= $label ?></a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div>

            <aside aria-label="Filtros de búsqueda" id="filtrar-mobile">
                <h2>Filtros</h2>

                <form action="/catalog" method="get" id="catalog-form">
                    <!--
                        La búsqueda libre del header (parámetro `q`) y el orden
                        viajan como hidden/select dentro de este mismo form para
                        que se preserven al aplicar filtros.
                    -->
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
                                        <?= in_array($autor, $context['filters']['autor']) ? 'checked' : '' ?>
                                    >
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
                                        <?= in_array($autor, $context['filters']['autor']) ? 'checked' : '' ?>
                                    >
                                    <?= htmlspecialchars($autor) ?>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="autor-ver-todos" id="autor-ver-todos">Ver todos</button>
                        <?php endif; ?>
                    </details>

                    <button type="submit">Aplicar filtros</button>
                    <a href="/catalog">Limpiar filtros</a>

                </form>
            </aside>

            <section aria-label="Listado de libros">
                <h2>Libros</h2>

                <div id="ordenar-mobile">
                    <label for="ordenar">Ordenar por:</label>
                    <?php $ordenActual = $context['filters']['orden'] ?? ''; ?>
                    <select
                        id="ordenar"
                        name="orden"
                        form="catalog-form"
                        onchange="document.getElementById('catalog-form').submit()"
                    >
                        <?php
                            $ordenOpciones = [
                                'titulo-asc'  => 'Título A–Z',
                                'titulo-desc' => 'Título Z–A',
                                'precio-asc'  => 'Precio: menor a mayor',
                                'precio-desc' => 'Precio: mayor a menor',
                                'autor-asc'   => 'Autor A–Z',
                                'recientes'   => 'Más recientes',
                            ];
                            foreach ($ordenOpciones as $value => $label):
                        ?>
                        <option value="<?= $value ?>" <?= $ordenActual === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
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

                    <p class="descargar-csv-wrapper">
                        <a
                            href="catalog?<?= htmlspecialchars($context['filterQuery']) ?><?= $context['filterQuery'] ? '&' : '' ?>format=csv"
                            class="descargar-csv"
                        >Descargar vista actual en CSV</a>
                    </p>

            </section>

        </div>

    </main>

    <?php require 'components/footer.php'; ?>

    <script>
        // Toggle mobile de "Ordenar" / "Filtrar" — togglea la clase mobile-visible
        // sobre el bloque al que apunta data-toggle.
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.acciones-mobile [data-toggle]').forEach(button => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.dataset.toggle);
                    if (target) target.classList.toggle('mobile-visible');
                });
            });

            // Toggle "Ver todos" en filtro de autor
            const verTodosBtn = document.getElementById('autor-ver-todos');
            const listaOculta = document.getElementById('autor-lista-oculta');
            if (verTodosBtn && listaOculta) {
                verTodosBtn.addEventListener('click', () => {
                    const estaVisible = listaOculta.style.display !== 'none';
                    listaOculta.style.display = estaVisible ? 'none' : 'block';
                    verTodosBtn.textContent = estaVisible ? 'Ver todos' : 'Ver menos';
                });
            }
        });
    </script>

</body>