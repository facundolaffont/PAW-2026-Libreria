document.addEventListener("DOMContentLoaded", () => {

    /**
     * Carga el script de utilidades y luego ejecuta la función callback.
     * 
     * @param {function} onReadyFnCallback - Función a ejecutar una vez que
     * el script de utilidades se haya cargado.
     */
    const bootstrapUtilities = (onReadyFnCallback) => {
        
        // Si el script ya se ha cargado, ejecuta la función callback.
        if (typeof Utilities !== "undefined") {
            onReadyFnCallback();
            return;
        }

        // Crea y añade el script a <head>, disparando la ejecución de la
        // función callback una vez que el script se haya cargado.
        const script = document.createElement("script");
        script.src = "resources/js/components/Utilities.js";
        script.onload = onReadyFnCallback;
        document.head.appendChild(script);
    };

    /**
     * Inicializa los componentes de la página, cargando los scripts necesarios.
     */
    const initializeComponents = () => {
        
        // Crea el menú hamburguesa.
        Utilities.loadScript("HamburguerMenu", "resources/js/components/HamburgerMenu.js", () => {
            const hamburgerButton = document.querySelector("button.menu-hamburguesa");
            const menu = document.querySelector("nav.menu-navegacion");
            if (hamburgerButton && menu) {
                new HamburgerMenu(hamburgerButton, menu);
            }
        });

        /* Carga los scripts, dependiendo la página. */

        const page = document.body?.dataset?.page;
        
        switch(page) {

            case "home-page":
                Utilities.loadScript("Carousel", "resources/js/components/Carousel.js", () => {

                    // Obtiene los carruseles.
                    const carousels = document.querySelectorAll("section.carrusel");
                    
                    // Inicializa los carruseles únicamente con su contenedor.
                    // El efecto se define por argumento (tomado del data-effect de cada sección).
                    carousels.forEach((carrusel) => {
                        const container = carrusel.querySelector(".contenedor-carrusel");
                        if (container) {
                            new Carousel(
                                container,
                                {
                                    effect: carrusel.dataset.effect || "slide",
                                    autoPlayMs: 4000
                                }
                            );
                        }
                    });
                });
                break;

            case "catalog":
                Utilities.loadScript("Catalog", "resources/js/catalog.js");
                break;
        }
    };

    // Carga el script de utilidades y luego inicializa los componentes.
    bootstrapUtilities(initializeComponents);
});
