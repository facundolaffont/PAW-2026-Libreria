<body>

    <?php require 'components/header.php'; ?>

    <main>

        <nav aria-label="Migas de pan">
            <ol>
                <li><a href="#">Reserva de libros</a></li>
            </ol>
        </nav>

        <h1>Mi reserva</h1>

        <section>
            <h2>Libros seleccionados</h2>

            <ul>
                <li>
                    <article>
                        <img src="resources/images/portada-harry-potter.jpg" alt="Portada de Harry potter y la piedra filosofal">
                        <div>
                            <h3>Harry potter y la piedra filosofal</h3>
                            <p>J. K. Rowling</p>
                        </div>
                        <button type="button" aria-label="Eliminar Harry potter y la piedra filosofal de la reserva"><img class="boton-eliminar" src="resources/images/ícono-eliminar.png" alt=""></button>
                    </article>
                </li>
                <li>
                    <article>
                        <img src="resources/images/portada-harry-potter.jpg" alt="Portada de Harry potter y la piedra filosofal">
                        <div>
                            <h3>Harry potter y la piedra filosofal</h3>
                            <p>J. K. Rowling</p>
                        </div>
                        <button type="button" aria-label="Eliminar Harry potter y la piedra filosofal de la reserva"><img class="boton-eliminar" src="resources/images/ícono-eliminar.png" alt=""></button>
                    </article>
                </li>
                <li>
                    <article>
                        <img src="resources/images/portada-harry-potter.jpg" alt="Portada de Harry potter y la piedra filosofal">
                        <div>
                            <h3>Harry potter y la piedra filosofal</h3>
                            <p>J. K. Rowling</p>
                        </div>
                        <button type="button" aria-label="Eliminar Harry potter y la piedra filosofal de la reserva"><img class="boton-eliminar" src="resources/images/ícono-eliminar.png" alt=""></button>
                    </article>
                </li>
            </ul>
        </section>

        <section>
            <h2>Datos personales</h2>

            <form>
                <div>
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan garcia">
                </div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Ej: mi_correo@gmail.com">
                </div>

                <div>
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Ej: 1126660206">
                </div>

                <button type="submit">Confirmar reserva</button>
            </form>
        </section>

    </main>

    <?php require 'components/footer.php'; ?>

</body>