/**
 * Clase para manejar un carrusel de productos.
 * 
 * Mantiene el estado de la posición en #state y actualiza el DOM
 * exclusivamente a través de #render(), disparado por #setState().
 * 
 * @param {HTMLElement} container - El elemento div que contiene los artículos del carrusel.
 * @param {HTMLElement} leftButton - El botón para desplazarse a la izquierda.
 * @param {HTMLElement} rightButton - El botón para desplazarse a la derecha.
 * @param {number} scrollAmount - La cantidad de píxeles a desplazar por clic.
 */
class Carrusel {

    /*** Público ***/

    constructor(container, leftButton, rightButton, scrollAmount) {
        this.container = container;
        this.leftButton = leftButton;
        this.rightButton = rightButton;
        this.scrollAmount = scrollAmount;

        this.#state = { position: 0 };
        this.#render();

        this.container.addEventListener('wheel', (e) => {
            if (window.matchMedia('(min-width: 1000px)').matches) {
                if (e.shiftKey || e.deltaX !== 0) {
                    e.preventDefault();
                }
            }
        }, { passive: false });

        this.leftButton.addEventListener('click', () => {
            this.#setState({ position: this.#state.position - this.scrollAmount });
        });

        this.rightButton.addEventListener('click', () => {
            this.#setState({ position: this.#state.position + this.scrollAmount });
        });
    }


    /*** Privado ***/

    #state;
    
    #setState(newState) {
        const maxPosition = this.container.scrollWidth - this.container.clientWidth;
        const clamped = Math.max(0, Math.min(newState.position, maxPosition));
        this.#state = { ...this.#state, position: clamped };
        this.#render();
    }

    #render() {
        this.container.scrollTo({ left: this.#state.position, behavior: 'smooth' });
    }

}