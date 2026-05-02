/**
 * Clase para manejar un carrusel de productos.
 * 
 * Mantiene la posición en #position y actualiza el DOM
 * exclusivamente a través de #render(), disparado por #setPosition().
 * 
 * @param {HTMLElement} container - El elemento div que contiene los artículos del carrusel.
 */
class Carousel {

    /*** Público ***/

    constructor(container) {

        // Renderiza la posición inicial.
        this.#render(container);

        this.#cancelWheel(container);

    }

    moveLeft(container, scrollAmount) {
        this.#setPosition(container, this.#position - scrollAmount);
    }
    
    moveRight(container, scrollAmount) {
        this.#setPosition(container, this.#position + scrollAmount);
    }

    handleTouchStart(container, e) {
        if (e.touches.length !== 1) return;
        this.#isDragging = true;
        this.#touchStartX = e.touches[0].clientX;
        this.#scrollStartX = container.scrollLeft;
    }

    handleTouchMove(container, e) {
        if (!this.#isDragging || e.touches.length !== 1) return;
        const deltaX = e.touches[0].clientX - this.#touchStartX;
        container.scrollLeft = this.#scrollStartX - deltaX;

        // Evita el scroll vertical si el movimiento es principalmente horizontal.
        if (Math.abs(deltaX) > 10) e.preventDefault();
    }

    handleTouchEnd(container) {
        this.#isDragging = false;

        // Actualiza el estado para mantener la posición.
        this.#setPosition(container, container.scrollLeft);
    }


    /*** Privado ***/

    #position = 0;
    #touchStartX = 0;
    #scrollStartX = 0;
    #isDragging = false;

    /**
     * Previene en pantalla grande el scroll horizontal con la rueda del mouse en el carrusel.
     */
    #cancelWheel(container) {
        container.addEventListener('wheel', (e) => {
            if (window.matchMedia('(min-width: 1000px)').matches) {
                if (e.shiftKey || e.deltaX !== 0) {
                    e.preventDefault();
                }
            }
        }, { passive: false });
    }

    #setPosition(container, newPosition) {
        const maxPosition = container.scrollWidth - container.clientWidth;
        this.#position = Math.max(0, Math.min(newPosition, maxPosition));
        this.#render(container);
    }

    #render(container) {
        container.scrollTo({ left: this.#position, behavior: 'smooth' });
    }

}