/**
 * Clase para manejar un carrusel de productos.
 * 
 * Permite desplazarse horizontalmente a través de los productos utilizando botones de flecha.
 * El desplazamiento se realiza de manera suave y se calcula automáticamente según el parámetro pasado.
 * 
 * @param {HTMLElement} container - El elemento div que contiene los artículos del carrusel.
 * @param {HTMLElement} leftButton - El botón para desplazarse a la izquierda.
 * @param {HTMLElement} rightButton - El botón para desplazarse a la derecha.
 * @param {number} scrollAmount - La cantidad de píxeles a desplazar por clic.
 */
class Carrusel {
    constructor(container, leftButton, rightButton, scrollAmount) {
        this.container = container;
        this.leftButton = leftButton;
        this.rightButton = rightButton;
        this.scrollAmount = scrollAmount;

        this.leftButton.addEventListener('click', () => {
            this.container.scrollBy({ left: -this.scrollAmount, behavior: 'smooth' });
        });

        this.rightButton.addEventListener('click', () => {
            this.container.scrollBy({ left: this.scrollAmount, behavior: 'smooth' });
        });
    }
}