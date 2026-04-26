
// Clase para manejar el menú hamburguesa.
class HamburgerMenu {

    /**
     * @param {HTMLElement} buttonSelector - Elemento del botón hamburguesa.
     * @param {HTMLElement} navSelector - Elemento del menú de navegación.
     * @param {string} [visibleClass='menu-visible'] - Clase que se agrega para mostrar el menú.
     */
    constructor(button, nav, visibleClass = 'menu-visible') {
        this.button = button;
        this.nav = nav;
        this.visibleClass = visibleClass;

        this.button.addEventListener('click', () => this.#toggleNavVisibility());
    }

    #toggleNavVisibility() {
        this.nav.classList.toggle(this.visibleClass);
        this.button.classList.toggle('activo');

        // Deshabilita el scroll del body cuando el menú está visible.
        if (this.nav.classList.contains(this.visibleClass)) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
}