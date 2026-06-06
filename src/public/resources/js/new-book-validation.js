/*
 * Validación inline del formulario de alta de libros.
 *
 * Objetivo: reemplazar la validación nativa del navegador (cuyos mensajes y
 * estilos difieren entre Chrome, Firefox y Safari) por mensajes uniformes
 * renderizados en el DOM con el mismo aspecto en todos los browsers.
 *
 * Estrategia:
 *   - El formulario lleva el atributo `novalidate`, que suprime los popups
 *     nativos.
 *   - Cada campo se valida al perder el foco (blur) para feedback inmediato.
 *   - Al intentar enviar (submit) se validan todos los campos; si hay errores
 *     se cancela el envío y se enfoca el primer campo inválido.
 *
 * Sincronización: las reglas deben mantenerse alineadas con las constantes
 * de NewBookController.php (TITULO_MAX, AUTOR_MIN, etc.).
 */

(function () {

    'use strict';

    /* ------------------------------------------------------------------ */
    /* Reglas (sincronizadas con NewBookController.php)                    */
    /* ------------------------------------------------------------------ */

    var TITULO_MAX      = 255;
    var AUTOR_MIN       = 2;
    var AUTOR_MAX       = 255;
    var GENERO_MAX      = 100;
    var PRECIO_MIN      = 0.01;
    var ISBN_LENGTH     = 13;
    var DESCRIPCION_MAX = 2000;

    /* ------------------------------------------------------------------ */
    /* Lógica de validación por campo                                      */
    /* ------------------------------------------------------------------ */

    function validateField(name, value) {
        switch (name) {

            case 'titulo':
                if (value === '') return 'El título es obligatorio.';
                if (value.length > TITULO_MAX)
                    return 'El título no puede superar los ' + TITULO_MAX + ' caracteres.';
                return '';

            case 'autor':
                if (value === '') return 'El autor es obligatorio.';
                if (value.length < AUTOR_MIN)
                    return 'El autor debe tener al menos ' + AUTOR_MIN + ' caracteres.';
                if (value.length > AUTOR_MAX)
                    return 'El autor no puede superar los ' + AUTOR_MAX + ' caracteres.';
                return '';

            case 'genero':
                if (value.length > GENERO_MAX)
                    return 'El género no puede superar los ' + GENERO_MAX + ' caracteres.';
                return '';

            case 'precio':
                if (value === '') return 'El precio es obligatorio.';
                if (!/^\d{1,7}(\.\d{1,2})?$/.test(value) || parseFloat(value) < PRECIO_MIN)
                    return 'El precio debe ser un número mayor a 0 (máx. 2 decimales).';
                return '';

            case 'stock':
                if (value === '') return 'El stock es obligatorio.';
                if (!/^\d+$/.test(value)) return 'El stock debe ser un número entero positivo.';
                return '';

            case 'isbn':
                if (value === '') return '';
                if (!/^\d+$/.test(value)) return 'El ISBN debe contener solo dígitos.';
                if (value.length !== ISBN_LENGTH)
                    return 'El ISBN debe tener exactamente ' + ISBN_LENGTH + ' dígitos.';
                return '';

            case 'imagen':
                // Imagen opcional: el servidor usa Open Library si hay ISBN.
                // Solo validar tipo/tamaño si el usuario eligió un archivo.
                if (!dragDrop || !dragDrop.getFile()) return '';
                return dragDrop.validate();

            case 'descripcion':
                if (value.length > DESCRIPCION_MAX)
                    return 'La descripción no puede superar los ' + DESCRIPCION_MAX + ' caracteres.';
                return '';

            default:
                return '';
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helpers de DOM                                                       */
    /* ------------------------------------------------------------------ */

    function showError(input, message) {
        var errorSpan = document.getElementById(input.id + '-error');
        if (!errorSpan) return;
        errorSpan.textContent = message;
        if (message) {
            input.setAttribute('aria-invalid', 'true');
        } else {
            input.removeAttribute('aria-invalid');
        }
    }

    function clearError(input) {
        showError(input, '');
    }

    /* ------------------------------------------------------------------ */
    /* Actualiza el contador de caracteres del textarea de descripción     */
    /* ------------------------------------------------------------------ */

    function updateCounter(textarea) {
        var counter = document.getElementById('descripcion-contador');
        if (!counter) return;
        var remaining = DESCRIPCION_MAX - textarea.value.length;
        counter.textContent = remaining + ' caracteres restantes';
    }

    /* ------------------------------------------------------------------ */
    /* Inicialización                                                       */
    /* ------------------------------------------------------------------ */

    var dragDrop = null;

    document.addEventListener('DOMContentLoaded', function () {

        var form = document.getElementById('form-nuevo-libro');
        if (!form) return;

        dragDrop = new DragDropImage('zona-drop', 'imagen', 'imagen-error');

        var fields = ['titulo', 'autor', 'genero', 'precio', 'stock', 'isbn', 'imagen', 'descripcion'];

        /* Attach blur listeners for real-time per-field validation */
        fields.forEach(function (name) {
            var el = document.getElementById(name);
            if (!el) return;

            el.addEventListener('blur', function () {
                var error = validateField(name, el.value.trim());
                showError(el, error);
            });

            /* Remove error as soon as the user starts correcting */
            el.addEventListener('input', function () {
                if (el.getAttribute('aria-invalid') === 'true') {
                    var error = validateField(name, el.value.trim());
                    showError(el, error);
                }
                if (name === 'descripcion') updateCounter(el);
            });
        });

        /* Initialize counter */
        var textarea = document.getElementById('descripcion');
        if (textarea) updateCounter(textarea);

        /* Validate all fields on submit */
        form.addEventListener('submit', function (event) {
            var firstInvalid = null;

            fields.forEach(function (name) {
                var el = document.getElementById(name);
                if (!el) return;

                var value;
                if (name === 'imagen') {
                    value = '';
                } else {
                    value = el.value.trim();
                }

                var error = validateField(name, value);
                showError(el, error);
                if (error && !firstInvalid) firstInvalid = el;
            });

            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.focus();
            }
        });

    });

}());
