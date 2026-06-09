<body>

    <?php require 'components/header.php'; ?>

    <main>

        <h1>Cargar nuevo libro</h1>

        <?php if (isset($_GET['creado']) && $_GET['creado'] === '1'): ?>
            <div class="alerta alerta-exito" role="alert">
                <p>¡El libro fue dado de alta correctamente!</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($context['errors']['general'])): ?>
            <div class="alerta alerta-error" role="alert">
                <p><?= htmlspecialchars($context['errors']['general'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <!--
            novalidate: deshabilita la validación nativa del navegador, cuya
            presentación y mensajes varían entre Chrome, Firefox y Safari.
            La validación inline se realiza mediante new-book-validation.js,
            logrando una experiencia uniforme en todos los navegadores.
        -->
        <form
            id="form-nuevo-libro"
            action="/new-book"
            method="post"
            enctype="multipart/form-data"
            novalidate
        >
            <div class="campo-grupo">
                <label for="titulo">Título <span class="obligatorio" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    placeholder="Ej: Cien años de soledad"
                    maxlength="255"
                    value="<?= htmlspecialchars($context['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="titulo-error"
                    aria-required="true"
                    <?= !empty($context['errors']['titulo']) ? 'aria-invalid="true"' : '' ?>
                >
                <span id="titulo-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="campo-grupo">
                <label for="autor">Autor <span class="obligatorio" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="autor"
                    name="autor"
                    placeholder="Ej: Gabriel García Márquez"
                    minlength="2"
                    maxlength="255"
                    value="<?= htmlspecialchars($context['autor'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="autor-error"
                    aria-required="true"
                    <?= !empty($context['errors']['autor']) ? 'aria-invalid="true"' : '' ?>
                >
                <span id="autor-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['autor'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="campo-grupo">
                <label for="genero">Género</label>
                <input
                    type="text"
                    id="genero"
                    name="genero"
                    placeholder="Ej: Realismo mágico"
                    maxlength="100"
                    value="<?= htmlspecialchars($context['genero'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="genero-error"
                    <?= !empty($context['errors']['genero']) ? 'aria-invalid="true"' : '' ?>
                >
                <span id="genero-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['genero'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="campo-grupo">
                <label for="precio">Precio <span class="obligatorio" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    inputmode="decimal"
                    id="precio"
                    name="precio"
                    placeholder="Ej: 1500.00"
                    value="<?= htmlspecialchars($context['precio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="precio-error"
                    aria-required="true"
                    <?= !empty($context['errors']['precio']) ? 'aria-invalid="true"' : '' ?>
                >
                <span id="precio-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['precio'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="campo-grupo">
                <label for="stock">Stock <span class="obligatorio" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    inputmode="numeric"
                    id="stock"
                    name="stock"
                    placeholder="Ej: 10"
                    value="<?= htmlspecialchars($context['stock'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="stock-error"
                    aria-required="true"
                    <?= !empty($context['errors']['stock']) ? 'aria-invalid="true"' : '' ?>
                >
                <span id="stock-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['stock'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="campo-grupo">
                <label for="isbn">ISBN</label>
                <input
                    type="text"
                    inputmode="numeric"
                    id="isbn"
                    name="isbn"
                    placeholder="Ej: 9789877358100"
                    maxlength="13"
                    value="<?= htmlspecialchars($context['isbn'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    aria-describedby="isbn-error ol-status"
                    <?= !empty($context['errors']['isbn']) ? 'aria-invalid="true"' : '' ?>
                >
                <span id="isbn-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['isbn'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span id="ol-status" class="ol-status" role="status" hidden></span>
            </div>

            <div class="campo-grupo campo-imagen">
                <label for="imagen">Imagen de portada</label>
                <p class="campo-ayuda">Opcional. Si ingresaste un ISBN, la tapa se buscará automáticamente en Open Library.</p>
                <div class="zona-drop" id="zona-drop" role="button" tabindex="0"
                     aria-label="Arrastrá o hacé clic para subir una imagen"
                     aria-describedby="imagen-error">
                    <p>Arrastrá una imagen aquí o hacé clic para seleccionar</p>
                </div>
                <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp" class="sr-only">
                <span id="imagen-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['imagen'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="campo-grupo campo-descripcion">
                <label for="descripcion">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="5"
                    maxlength="2000"
                    placeholder="Sinopsis o descripción del libro"
                    aria-describedby="descripcion-error descripcion-contador"
                    <?= !empty($context['errors']['descripcion']) ? 'aria-invalid="true"' : '' ?>
                ><?= htmlspecialchars($context['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <span id="descripcion-contador" class="campo-contador" aria-live="polite"></span>
                <span id="descripcion-error" class="campo-error" role="alert">
                    <?= htmlspecialchars($context['errors']['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <p class="leyenda-obligatorio"><span class="obligatorio" aria-hidden="true">*</span> Campos obligatorios</p>

            <button type="submit">Guardar libro</button>

        </form>

    </main>

    <?php require 'components/footer.php'; ?>

    <script src="resources/js/components/DragDropImage.js"></script>
    <script src="resources/js/new-book-validation.js"></script>
    <script src="resources/js/open-library.js"></script>

</body>
