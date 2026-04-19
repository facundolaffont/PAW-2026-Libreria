<body>

    <?php require 'components/header.php'; ?>

    <main>


        <!-- Barra de acciones móvil (ordenar / filtrar) -->
        <div>
            <button type="button">Ordenar</button>
            <button type="button">Filtrar</button>
        </div>

        <div>

            <aside aria-label="Filtros de búsqueda">
                <h2>Filtros</h2>

                <form action="/catalogo" method="get">
                    <details class="color-blanco">
                        <summary>Categorías</summary>
                        <nav aria-label="Filtrar por categoría">
                            <ul>
                                <!-- Las categorías se cargan dinámicamente desde la base de datos. -->
                                <li>
                                    <label>
                                        <input type="checkbox" name="categoria" value="ID">
                                        Categoría A
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="checkbox" name="categoria" value="ID">
                                        Categoría B
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="checkbox" name="categoria" value="ID">
                                        Categoría C
                                    </label>
                                </li>
                            </ul>
                        </nav>
                    </details>

                    <details>
                        <summary>Precio</summary>
                        <div>
                            <label for="precio-min">Desde</label>
                            <input type="number" id="precio-min" name="precio_min" min=0 placeholder="$ 0" >
                        </div>
                        <div>
                            <label for="precio-max">Hasta</label>
                            <input type="number" id="precio-max" name="precio_max" min=0 placeholder="$ 99999" >
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
                        <input type="text" id="filtro-autor" name="autor" placeholder="Nombre del autor" >
                    </details>

                    <button type="submit" disabled>Aplicar filtros</button>
                    <a href="#">Limpiar filtros</a>

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

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro1">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 1]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro2">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 2]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro3">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 3]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro4">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 4]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro5">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 5]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro6">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 6]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro7">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 7]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro8">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 8]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro9">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 9]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                    <li>
                        <article>
                            <a href="book-detail.php?id=libroN">
                                <picture id="libro10">
                                    <source
                                        srcset="resources/images/placeholder-libro-grande.png"
                                        media="( min-width: 600px )"
                                >
                                <img src="resources/images/placeholder-libro-chica.png" alt="Descripción del libro.">
                                </picture>
                                <h3>[Título del libro 10]</h3>
                            </a>
                            <p>[Autor/a]</p>
                            <p><strong>$[Precio]</strong></p>
                            <button type="button">Reservar</button>
                        </article>
                    </li>

                </ul>

                    <nav aria-label="Paginación del catálogo">
                        <ol>
                            <li>
                                <a href="catalog.php?pagina=1" aria-label="Página 1" aria-current="page">1</a>
                            </li>
                            <li>
                                <a href="catalog.php?pagina=2" aria-label="Página 2">2</a>
                            </li>
                            <li>
                                <a href="catalog.php?pagina=3" aria-label="Página 3">3</a>
                            </li>
                            <li>
                                <a href="catalog.php?pagina=2" aria-label="Página siguiente">Siguiente</a>
                            </li>
                        </ol>
                    </nav>

            </section>

        </div>

    </main>

    <?php require 'components/footer.php'; ?>

</body>