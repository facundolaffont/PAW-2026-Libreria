/**
 * Componente de drag & drop para subir imagen de portada de libro.
 * Soporta click, arrastre, preview, validación client-side y
 * feedback visual durante todas las interacciones.
 */
class DragDropImage {

    static MAX_FILE_SIZE = 2 * 1024 * 1024;
    static ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @param {string} dropZoneId - ID del contenedor de la zona de drop
     * @param {string} fileInputId - ID del input[type=file] oculto
     * @param {string} errorSpanId - ID del span donde mostrar errores
     */
    constructor(dropZoneId, fileInputId, errorSpanId) {
        this.dropZone = document.getElementById(dropZoneId);
        this.fileInput = document.getElementById(fileInputId);
        this.errorSpan = document.getElementById(errorSpanId);

        if (!this.dropZone || !this.fileInput || !this.errorSpan) {
            return;
        }

        this.currentFile = null;
        this.previewEl = null;

        this.#bindEvents();
    }

    #bindEvents() {
        this.dropZone.addEventListener('click', () => {
            this.fileInput.value = '';
            this.fileInput.click();
        });

        this.fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                this.#handleFile(file);
            }
        });

        this.dropZone.addEventListener('dragenter', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.dropZone.classList.add('drag-over');
        });

        this.dropZone.addEventListener('dragover', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.dropZone.classList.add('drag-over');
        });

        this.dropZone.addEventListener('dragleave', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.dropZone.classList.remove('drag-over');
        });

        this.dropZone.addEventListener('drop', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.dropZone.classList.remove('drag-over');

            const file = event.dataTransfer.files[0];
            if (file) {
                this.fileInput.files = event.dataTransfer.files;
                this.#handleFile(file);
            }
        });
    }

    #handleFile(file) {
        const error = this.#validate(file);
        if (error) {
            this.#showError(error);
            this.#clearPreview();
            this.currentFile = null;
            this.fileInput.value = '';
            return;
        }

        this.#clearError();
        this.currentFile = file;
        this.#showPreview(file);
    }

    #validate(file) {
        if (!file) {
            return 'Debe seleccionar un archivo.';
        }

        if (!DragDropImage.ALLOWED_TYPES.includes(file.type)) {
            return 'Formato no permitido. Solo JPG, PNG y WebP.';
        }

        if (file.size > DragDropImage.MAX_FILE_SIZE) {
            return 'El archivo supera los 2 MB.';
        }

        return null;
    }

    #showPreview(file) {
        this.#clearPreview();

        const reader = new FileReader();
        reader.addEventListener('load', () => {
            const img = document.createElement('img');
            img.src = reader.result;
            img.alt = 'Vista previa de la portada';
            img.className = 'drop-preview';

            const text = this.dropZone.querySelector('p');
            if (text) {
                text.style.display = 'none';
            }

            this.dropZone.appendChild(img);
            this.previewEl = img;
            this.dropZone.classList.add('has-image');
        });
        reader.readAsDataURL(file);
    }

    #clearPreview() {
        if (this.previewEl) {
            this.previewEl.remove();
            this.previewEl = null;
        }

        const text = this.dropZone.querySelector('p');
        if (text) {
            text.style.display = '';
        }

        this.dropZone.classList.remove('has-image');
    }

    #showError(message) {
        if (this.errorSpan) {
            this.errorSpan.textContent = message;
        }
        this.dropZone.setAttribute('aria-invalid', 'true');
    }

    #clearError() {
        if (this.errorSpan) {
            this.errorSpan.textContent = '';
        }
        this.dropZone.removeAttribute('aria-invalid');
    }

    /**
     * Devuelve el archivo actual seleccionado, o null si no hay.
     * @returns {File|null}
     */
    getFile() {
        return this.currentFile;
    }

    /**
     * Valida el estado actual del componente.
     * @returns {string} Mensaje de error o cadena vacía si es válido.
     */
    validate() {
        if (!this.currentFile) {
            return 'La imagen de portada es obligatoria.';
        }
        const error = this.#validate(this.currentFile);
        return error || '';
    }
}
