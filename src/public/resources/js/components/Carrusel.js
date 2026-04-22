class Carrusel {
    constructor(carruselElement) {
        this.container = carruselElement.querySelector('.contenedor-carrusel');
        this.leftButton = carruselElement.querySelector('.flecha-izq');
        this.rightButton = carruselElement.querySelector('.flecha-der');

        const firstItem = this.container.querySelector('article');
        const gap = parseFloat(getComputedStyle(this.container).gap) || 0;
        this.scrollAmount = firstItem.offsetWidth + gap;

        this.leftButton.addEventListener('click', () => {
            this.container.scrollBy({ left: -this.scrollAmount, behavior: 'smooth' });
        });

        this.rightButton.addEventListener('click', () => {
            this.container.scrollBy({ left: this.scrollAmount, behavior: 'smooth' });
        });
    }
}