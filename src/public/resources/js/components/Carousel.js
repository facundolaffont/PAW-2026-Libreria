/**
 * Libreria de carrusel responsive con:
 * - carga progresiva de imagenes,
 * - navegacion por botones, thumbs, swipe y teclado,
 * - efectos de desplazamiento configurables por constructor.
 */
class Carousel {

    /**
     * @param {HTMLElement} container - Contenedor que ya incluye los slides.
    * @param {{effect?: 'slide'|'block'|'disappear', autoPlayMs?: number}} options
     */
    constructor(container, options = {}) {
        if (!(container instanceof HTMLElement)) {
            throw new Error('Carousel requiere un contenedor valido.');
        }

        this.container = container;
        this.root = container.parentElement;
        this.effect = this.#normalizeEffect(options.effect || 'slide');
        this.options = {
            effect: this.effect,
            autoPlayMs: options.autoPlayMs || 3500,
        };

        this.currentIndex = 0;
        this.isDragging = false;
        this.touchStartX = 0;
        this.touchCurrentX = 0;
        this.autoPlayTimer = null;
        this.preloadedImages = 0;
        this.progressStartAt = 0;
        this.progressHideTimer = null;
        this.scrollAnimationFrame = null;

        this.items = Array.from(this.container.querySelectorAll('article'));
        this.totalItems = this.items.length;

        if (!this.root || this.totalItems === 0) {
            return;
        }

        if (!Carousel.activeInstance) {
            Carousel.activeInstance = this;
        }

        this.#buildUi();
        this.#setupAccessibility();
        this.#bindEvents();

        // Deja que el navegador pinte la UI inicial antes de arrancar la precarga,
        // para que la barra de progreso sea perceptible en conexiones lentas.
        requestAnimationFrame(() => {
            this.progressStartAt = performance.now();
            this.#activateMainImageSources();
            this.#preloadImagesAndStart();
        });
    }

    /**
     * Avanza al siguiente slide respetando el loop circular.
     * @returns {void}
     */
    next() {
        if (this.effect === 'block') {
            const anchors = this.#getBlockAnchors();
            const currentAnchor = this.#closestBlockAnchor(this.currentIndex, anchors);
            const currentAnchorPos = anchors.indexOf(currentAnchor);
            const nextAnchor = anchors[(currentAnchorPos + 1) % anchors.length];
            this.goTo(nextAnchor);
            return;
        }

        this.goTo(this.currentIndex + 1);
    }

    /**
     * Retrocede al slide anterior respetando el loop circular.
     * @returns {void}
     */
    prev() {
        if (this.effect === 'block') {
            const anchors = this.#getBlockAnchors();
            const currentAnchor = this.#closestBlockAnchor(this.currentIndex, anchors);
            const currentAnchorPos = anchors.indexOf(currentAnchor);
            const prevPos = (currentAnchorPos - 1 + anchors.length) % anchors.length;
            this.goTo(anchors[prevPos]);
            return;
        }

        this.goTo(this.currentIndex - 1);
    }

    /**
     * Navega a un índice de slide y aplica el efecto configurado.
     * @param {number} index - Indice destino (se normaliza en rango válido).
     * @returns {void}
     */
    goTo(index) {

        // Si no hay items, no hay qué mover.
        if (!this.totalItems) return;

        const normalized = (index + this.totalItems) % this.totalItems;
        this.currentIndex = normalized;

        const shouldApplyScrollEffect = this.#hasRealScrollMovement(this.currentIndex);

        if (shouldApplyScrollEffect) {
            if (this.effect === 'disappear') {
                this.#applyDisappearEffect();
            } else {
                this.#applySlideEffect();
            }
        }

        this.#updateThumbs();
        this.#restartAutoplay();
    }

    /**
     * Construye la UI interna del carrusel: flechas, progreso y thumbnails.
     * @returns {void}
     */
    #buildUi() {
        this.root.classList.add('carousel-root');
        this.container.classList.add('carousel-track');
        this.container.classList.add('carousel-effect-' + this.effect);

        this.prevButton = document.createElement('button');
        this.prevButton.type = 'button';
        this.prevButton.className = 'flecha flecha-izq';
        this.prevButton.setAttribute('aria-label', 'Imagen anterior');

        this.nextButton = document.createElement('button');
        this.nextButton.type = 'button';
        this.nextButton.className = 'flecha flecha-der';
        this.nextButton.setAttribute('aria-label', 'Imagen siguiente');

        this.root.appendChild(this.prevButton);
        this.root.appendChild(this.nextButton);

        this.progress = document.createElement('div');
        this.progress.className = 'carousel-progress';
        this.progress.innerHTML =
            '<div class="carousel-progress-bar"></div><span class="carousel-progress-label">0%</span>';
        this.progressBar = this.progress.querySelector('.carousel-progress-bar');
        this.progressLabel = this.progress.querySelector('.carousel-progress-label');
        this.root.appendChild(this.progress);

        this.thumbs = document.createElement('div');
        this.thumbs.className = 'carousel-thumbs';

        this.thumbButtons = this.items.map((item, index) => {
            // Wrapper para thumb y guión
            const wrapper = document.createElement('div');
            wrapper.className = 'carousel-thumb-wrapper';

            const thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.className = 'carousel-thumb is-loading';
            thumb.setAttribute('aria-label', 'Ir a imagen ' + (index + 1));
            thumb.dataset.index = String(index);

            // Indicador visual (guión)
            const indicator = document.createElement('span');
            indicator.className = 'carousel-thumb-indicator';

            wrapper.appendChild(thumb);
            wrapper.appendChild(indicator);
            this.thumbs.appendChild(wrapper);
            return thumb;
        });

        this.root.appendChild(this.thumbs);
    }

    /**
     * Configura atributos de accesibilidad para navegacion y anuncios de cambios.
     * @returns {void}
     */
    #setupAccessibility() {
        this.root.setAttribute('tabindex', '0');
        this.container.setAttribute('tabindex', '0');
        this.container.setAttribute('aria-live', 'polite');
    }

    /**
     * Registra los listeners de interaccion: click, touch, teclado y wheel.
     * @returns {void}
     */
    #bindEvents() {
        const setAsActive = () => {
            Carousel.activeInstance = this;
        };

        this.root.addEventListener('mouseenter', setAsActive);
        this.root.addEventListener('pointerdown', setAsActive);
        this.root.addEventListener('touchstart', setAsActive, { passive: true });
        this.root.addEventListener('focusin', setAsActive);

        this.prevButton.addEventListener('click', () => this.prev());
        this.nextButton.addEventListener('click', () => this.next());

        this.thumbButtons.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                this.goTo(Number(thumb.dataset.index || 0));
            });
        });

        this.container.addEventListener(
            'touchstart',
            (event) => {
                if (event.touches.length !== 1) return;
                this.isDragging = true;
                this.touchStartX = event.touches[0].clientX;
                this.touchCurrentX = this.touchStartX;
            },

            // Permite que el navegador optimize el scroll nativo
            // y solo prevenga el comportamiento en touchmove si se
            // detecta un swipe horizontal significativo.
            { passive: true }

        );

        this.container.addEventListener(
            'touchmove',
            (event) => {
                if (!this.isDragging || event.touches.length !== 1) return;
                this.touchCurrentX = event.touches[0].clientX;
                const deltaX = this.touchCurrentX - this.touchStartX;
                if (Math.abs(deltaX) > 10) {
                    event.preventDefault();
                }
            },
            
            // Le indica al navegador que este manejador podría cancelar el
            // evento para evitar el scroll nativo, lo cual es necesario para
            // una experiencia de swipe fluida.
            { passive: false }
        );

        this.container.addEventListener('touchend', () => {
            if (!this.isDragging) return;
            const deltaX = this.touchCurrentX - this.touchStartX;
            this.isDragging = false;

            if (Math.abs(deltaX) < 35) return;
            if (deltaX < 0) this.next();
            if (deltaX > 0) this.prev();
        });

        document.addEventListener('keydown', (event) => {
            if (Carousel.activeInstance !== this) return;

            const target = event.target;
            if (target instanceof HTMLElement) {
                const tag = target.tagName;
                if (
                    tag === 'INPUT' ||
                    tag === 'TEXTAREA' ||
                    tag === 'SELECT' ||
                    target.isContentEditable
                ) {
                    return;
                }
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.prev();
            }
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.next();
            }
        });

        this.container.addEventListener('wheel', (event) => {
            if (window.matchMedia('(min-width: 1000px)').matches && (event.shiftKey || event.deltaX !== 0)) {
                event.preventDefault();
            }
        }, { passive: false });
    }

    /**
     * Precarga imagenes del carrusel y actualiza la barra de progreso.
     * Al completar el 100%, habilita el inicio de la animacion.
     * @returns {void}
     */
    #preloadImagesAndStart() {
        // Solo cuenta las imagenes principales de cada slide (una por article).
        const images = this.items
            .map((item) => item.querySelector('img'))
            .filter((img) => img instanceof HTMLImageElement);
        const totalImages = images.length;

        if (totalImages === 0) {
            this.#setProgress(100);
            this.#finishProgressAndStart();
            return;
        }

        const onImageReady = (img, index) => {
            this.preloadedImages += 1;
            this.#syncThumbFromMainImage(img, index);
            const pct = Math.min(100, Math.round((this.preloadedImages / totalImages) * 100));
            this.#setProgress(pct);

            if (this.preloadedImages >= totalImages) {
                this.#finishProgressAndStart();
            }
        };

        images.forEach((img, index) => {
            if (img.complete && img.naturalWidth > 0) {
                onImageReady(img, index);
                return;
            }

            img.addEventListener('load', () => onImageReady(img, index), { once: true });
            img.addEventListener('error', () => onImageReady(img, index), { once: true });
        });
    }

    /**
     * Activa la carga real de imagenes principales seteando src y srcset desde data attributes.
     * @returns {void}
     */
    #activateMainImageSources() {
        this.items.forEach((item) => {
            const picture = item.querySelector('picture');
            const img = item.querySelector('img');

            if (!img) return;

            if (picture) {
                const sources = Array.from(picture.querySelectorAll('source[data-carousel-srcset]'));
                sources.forEach((source) => {
                    const srcset = source.dataset.carouselSrcset;
                    if (srcset) {
                        source.setAttribute('srcset', srcset);
                    }
                });
            }

            const mainSrc = img.dataset.carouselSrc;
            if (mainSrc) {
                img.setAttribute('src', mainSrc);
            }
        });
    }

    /**
     * Copia la fuente de la imagen principal al thumbnail correspondiente.
     * @param {HTMLImageElement} img - Imagen principal del slide.
     * @param {number} index - Indice del slide asociado.
     * @returns {void}
     */
    #syncThumbFromMainImage(img, index) {
        const thumb = this.thumbButtons[index];
        if (!thumb || !img) return;

        if (img.currentSrc) {
            thumb.style.backgroundImage = 'url("' + img.currentSrc + '")';
            thumb.classList.remove('is-loading');
            return;
        }

        const src = img.getAttribute('src');
        if (src) {
            thumb.style.backgroundImage = 'url("' + src + '")';
            thumb.classList.remove('is-loading');
        }
    }

    /**
     * Actualiza visualmente el porcentaje de progreso de carga.
     * @param {number} percent - Porcentaje en rango 0-100.
     * @returns {void}
     */
    #setProgress(percent) {
        this.progressBar.style.width = percent + '%';
        this.progressLabel.textContent = percent + '%';
    }

    /**
     * Marca el carrusel como listo, oculta progreso y posiciona el primer slide.
     * @returns {void}
     */
    #finishProgressAndStart() {
        const elapsed = performance.now() - this.progressStartAt;
        const minimumVisibleMs = 700;

        if (this.progressHideTimer) {
            clearTimeout(this.progressHideTimer);
            this.progressHideTimer = null;
        }

        const finalize = () => {
            this.root.classList.add('carousel-ready');
            this.goTo(0);
        };

        if (elapsed < minimumVisibleMs) {
            this.progressHideTimer = setTimeout(finalize, minimumVisibleMs - elapsed);
            return;
        }

        finalize();
    }

    /**
     * Sincroniza el estado visual de thumbnails segun el slide activo.
     * @returns {void}
     */
    #updateThumbs() {
        this.thumbButtons.forEach((thumb, index) => {
            // Buscar el indicador en el wrapper padre
            const wrapper = thumb.parentElement;
            const indicator = wrapper.querySelector('.carousel-thumb-indicator');
            if (index === this.currentIndex) {
                thumb.classList.add('is-active');
                if (indicator) indicator.classList.add('is-active');
            } else {
                thumb.classList.remove('is-active');
                if (indicator) indicator.classList.remove('is-active');
            }
        });
    }

    /**
     * Aplica transicion por desplazamiento horizontal suave.
     * @returns {void}
     */
    #applySlideEffect() {
        const target = this.items[this.currentIndex];
        if (!target) return;

        if (this.scrollAnimationFrame) {
            cancelAnimationFrame(this.scrollAnimationFrame);
            this.scrollAnimationFrame = null;
        }

        const left = this.#getTargetScrollLeft(this.currentIndex);
        this.container.scrollTo({ left: left, behavior: 'smooth' });
    }

    /**
     * Aplica efecto de desaparicion: fade out, scroll, fade in.
     * Solo aplica si hay movimiento real de scroll.
     * @returns {void}
     */
    #applyDisappearEffect() {
        const target = this.items[this.currentIndex];
        if (!target) return;

        const targetLeft = this.#getTargetScrollLeft(this.currentIndex);
        const currentLeft = this.container.scrollLeft;

        if (Math.abs(targetLeft - currentLeft) < 1) {
            return;
        }

        const previousSnapType = this.container.style.scrollSnapType;
        this.container.style.scrollSnapType = 'none';
        this.container.style.transition = 'opacity 0.35s ease';

        this.container.style.opacity = '0';
        setTimeout(() => {
            this.container.scrollLeft = targetLeft;
            setTimeout(() => {
                this.container.style.opacity = '1';
                setTimeout(() => {
                    this.container.style.transition = '';
                    this.container.style.scrollSnapType = previousSnapType;
                }, 350);
            }, 50);
        }, 350);
    }

    /**
     * Determina si un indice objetivo implica movimiento real de scroll.
     * @param {number} index - Indice del item objetivo.
     * @returns {boolean}
     */
    #hasRealScrollMovement(index) {
        const targetLeft = this.#getTargetScrollLeft(index);
        const currentLeft = this.container.scrollLeft;
        return Math.abs(targetLeft - currentLeft) >= 1;
    }

    /**
     * Calcula el scrollLeft efectivo para un indice, limitado al rango valido.
     * @param {number} index - Indice del item objetivo.
     * @returns {number}
     */
    #getTargetScrollLeft(index) {
        const target = this.items[index];
        if (!target) return this.container.scrollLeft;

        const rawLeft = target.offsetLeft;
        const maxLeft = Math.max(0, this.container.scrollWidth - this.container.clientWidth);
        return Math.min(Math.max(0, rawLeft), maxLeft);
    }

    /**
     * Calcula los anclajes de inicio para navegacion por bloques.
     * @returns {number[]}
     */
    #getBlockAnchors() {
        const anchors = [];
        let start = 0;

        while (start < this.totalItems) {
            anchors.push(start);
            start += this.#countVisibleFromIndex(start);
        }

        return anchors.length ? anchors : [0];
    }

    /**
     * Obtiene el anclaje de bloque que contiene al indice actual.
     * @param {number} index - Indice de referencia actual.
     * @param {number[]} anchors - Lista de inicios de bloque.
     * @returns {number}
     */
    #closestBlockAnchor(index, anchors) {
        let closest = anchors[0];

        for (const anchor of anchors) {
            if (anchor <= index) {
                closest = anchor;
                continue;
            }
            break;
        }

        return closest;
    }

    /**
     * Cuenta cuantos items entran en viewport desde un indice de inicio.
     * @param {number} startIndex - Indice desde donde comienza el bloque.
     * @returns {number}
     */
    #countVisibleFromIndex(startIndex) {
        const startItem = this.items[startIndex];
        if (!startItem) return 1;

        const viewportWidth = this.container.clientWidth;
        if (viewportWidth <= 0) return 1;

        const startLeft = startItem.offsetLeft;
        let count = 0;

        for (let i = startIndex; i < this.totalItems; i += 1) {
            const item = this.items[i];
            if (!item) break;

            const relativeEnd = item.offsetLeft + item.offsetWidth - startLeft;
            if (count > 0 && relativeEnd > viewportWidth + 1) {
                break;
            }

            count += 1;
        }

        return Math.max(1, count);
    }

    /**
     * Realiza una animacion manual de scroll horizontal con easing custom.
     * @param {number} targetLeft - Posicion final de scrollLeft.
     * @param {number} durationMs - Duracion de animacion en milisegundos.
     * @param {(t:number) => number} easingFn - Funcion de easing, con t en [0,1].
     * @returns {void}
     */
    #animateScrollTo(targetLeft, durationMs, easingFn) {
        if (this.scrollAnimationFrame) {
            cancelAnimationFrame(this.scrollAnimationFrame);
            this.scrollAnimationFrame = null;
        }

        const previousSnapType = this.container.style.scrollSnapType;
        this.container.style.scrollSnapType = 'none';

        const startLeft = this.container.scrollLeft;
        const delta = targetLeft - startLeft;

        if (Math.abs(delta) < 1) {
            this.container.scrollLeft = targetLeft;
            this.container.style.scrollSnapType = previousSnapType;
            return;
        }

        const startAt = performance.now();
        const tick = (now) => {
            const elapsed = now - startAt;
            const progress = Math.min(1, elapsed / durationMs);
            const eased = easingFn(progress);
            this.container.scrollLeft = startLeft + (delta * eased);

            if (progress < 1) {
                this.scrollAnimationFrame = requestAnimationFrame(tick);
                return;
            }

            this.container.scrollLeft = targetLeft;
            this.container.style.scrollSnapType = previousSnapType;
            this.scrollAnimationFrame = null;
        };

        this.scrollAnimationFrame = requestAnimationFrame(tick);
    }

    /**
     * Normaliza nombres de efectos para mantener compatibilidad hacia atras.
     * @param {string} effect - Nombre solicitado para el efecto.
     * @returns {'slide'|'block'|'disappear'}
     */
    #normalizeEffect(effect) {
        if (effect === 'block' || effect === 'disappear') return effect;
        return 'slide';
    }

    /**
     * Reinicia el autoplay con el intervalo configurado.
     * No activa temporizador si el valor es invalido o demasiado bajo.
     * @returns {void}
     */
    #restartAutoplay() {
        if (!this.options.autoPlayMs || this.options.autoPlayMs < 1000) {
            return;
        }

        if (this.autoPlayTimer) {
            clearTimeout(this.autoPlayTimer);
        }

        this.autoPlayTimer = setTimeout(() => this.next(), this.options.autoPlayMs);
    }

}
