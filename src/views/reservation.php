<body data-page="reservation">

    <?php require 'components/header.php'; ?>

    <main>

        <nav aria-label="Migas de pan">
            <ol>
                <li><a href="#">Reserva de libros</a></li>
            </ol>
        </nav>

        <h1>Mi reserva</h1>

        <?php if (isset($_GET['enviada']) && $_GET['enviada'] === '1'): ?>
            <div class="alerta alerta-exito" role="alert">
                <p>¡Tu reserva fue enviada con éxito! Nos comunicaremos contigo a la brevedad.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($context['errors']['general'])): ?>
            <div class="alerta alerta-error" role="alert">
                <p><?= htmlspecialchars($context['errors']['general'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <section>
            <h2>Libros seleccionados</h2>

            <p id="reservation-vacia" class="reservation-vacia" hidden>
                Tu reserva está vacía. Agregá libros desde el <a href="catalog">catálogo</a>.
            </p>

            <ul id="reservation-lista" aria-live="polite"></ul>

            <p id="reservation-total" class="reservation-total" hidden>
                <span>Total:</span>
                <span class="reservation-total-valor">$ 0,00</span>
            </p>
        </section>

        <section>
            <h2>Datos personales</h2>

            <!--
                method="post": los datos se envían en el cuerpo de la petición HTTP,
                no en la URL. Apropiado para operaciones que modifican estado.

                enctype="application/x-www-form-urlencoded": codificación por defecto
                de los formularios HTML. Los campos se codifican como pares clave=valor
                separados por & con espacios como + y caracteres especiales como %XX.
                Alternativa para archivos: multipart/form-data.
            -->
            <form
                id="reservation-form"
                action="/reservation"
                method="post"
                enctype="application/x-www-form-urlencoded"
            >
                <div id="reservation-libros-hidden"></div>
                <div>
                    <label for="nombre">Nombre completo</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Ej: Juan Garcia"
                        required
                        minlength="2"
                        maxlength="100"
                        pattern="[A-Za-záéíóúüñÁÉÍÓÚÜÑ\s]+"
                        value="<?= htmlspecialchars($context['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        aria-describedby="nombre-error"
                        <?= !empty($context['errors']['nombre']) ? 'aria-invalid="true"' : '' ?>
                    >
                    <?php if (!empty($context['errors']['nombre'])): ?>
                        <span id="nombre-error" class="campo-error" role="alert">
                            <?= htmlspecialchars($context['errors']['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Ej: mi_correo@gmail.com"
                        required
                        maxlength="254"
                        value="<?= htmlspecialchars($context['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        aria-describedby="email-error"
                        <?= !empty($context['errors']['email']) ? 'aria-invalid="true"' : '' ?>
                    >
                    <?php if (!empty($context['errors']['email'])): ?>
                        <span id="email-error" class="campo-error" role="alert">
                            <?= htmlspecialchars($context['errors']['email'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="telefono">Teléfono</label>
                    <input
                        type="tel"
                        id="telefono"
                        name="telefono"
                        placeholder="Ej: 1126660206"
                        required
                        minlength="8"
                        maxlength="20"
                        pattern="[0-9+\-\s]+"
                        value="<?= htmlspecialchars($context['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        aria-describedby="telefono-error"
                        <?= !empty($context['errors']['telefono']) ? 'aria-invalid="true"' : '' ?>
                    >
                    <?php if (!empty($context['errors']['telefono'])): ?>
                        <span id="telefono-error" class="campo-error" role="alert">
                            <?= htmlspecialchars($context['errors']['telefono'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <button type="submit" id="reservation-confirmar">Confirmar reserva</button>
            </form>
        </section>

    </main>

    <?php require 'components/footer.php'; ?>

</body>
