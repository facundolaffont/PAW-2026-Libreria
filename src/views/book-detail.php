<body>

    <?php require 'components/header.php'; ?>

    <main>

        <nav aria-label="Migas de pan">
            <ol>
                <li><a href="#">Detalle de libro</a></li>
            </ol>
        </nav>

        <section class="contenido-detalle">

            <h2>Detalle del libro</h2>

            <figure class="portada">
                <img src="resources/images/portada-harry-potter.jpg" alt="Portada del libro Harry Potter y la piedra filosofal">
            </figure>

            <article class="info-libro">
                <h3 class="titulo-libro">Harry Potter y la piedra filosofal</h3>
                <p class="autor">por J. K. Rowling</p>

                <section class="seccion-descripcion">
                    <h4>Descripcion</h4>
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                        when an unknown printer took a galley of type and scrambled it to make a type
                        specimen book. It has survived not only five centuries, but also the leap into
                        electronic typesetting, remaining essentially unchanged. It was popularised in
                        the 1960s with the release of Letraset sheets containing Lorem Ipsum passages,
                        and more recently with desktop publishing software like Aldus PageMaker
                        including versions of Lorem Ipsum.
                    </p>
                </section>

                <section class="seccion-generos">
                    <h4>Géneros</h4>
                    <p>Novela, Literatura infantil, Literatura fantástica, Baja fantasía</p>
                </section>

                <button type="button" class="boton-reservar">Agregar a mis reservas</button>
            </article>
        </section>

    </main>

    <?php require 'components/footer.php'; ?>

</body>