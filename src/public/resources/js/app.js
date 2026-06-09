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

                // Botones "Reservar" de los carruseles del home (delegado).
                document.querySelectorAll("main section.carrusel .contenedor-carrusel").forEach((cont) => {
                    cont.addEventListener("click", (e) => {
                        const btn = e.target.closest("button.boton-reservar-card[data-book-id]");
                        if (!btn || !window.Cart) return;
                        const id = btn.dataset.bookId;
                        if (!id || id === "0") return;

                        window.Cart.add({
                            id: id,
                            title:  btn.dataset.title  || "",
                            author: btn.dataset.author || "",
                            image:  btn.dataset.image  || "",
                            price:  btn.dataset.price  || ""
                        });

                        if (btn.dataset.flashing === "1") return;
                        const original = btn.textContent;
                        btn.dataset.flashing = "1";
                        btn.textContent = "Agregado";
                        btn.disabled = true;
                        setTimeout(() => {
                            btn.textContent = original;
                            btn.disabled = false;
                            delete btn.dataset.flashing;
                        }, 1200);
                    });
                });
                break;

            case "catalog":
                Utilities.loadScript("Catalog", "resources/js/catalog.js");
                break;

            case "book-detail":
                Utilities.loadScript("BookDetail", "resources/js/book-detail.js");
                break;

            case "reservation":
                Utilities.loadScript("Reservation", "resources/js/reservation.js");
                break;
        }
    };

    // Carga el script de utilidades y luego inicializa los componentes.
    bootstrapUtilities(initializeComponents);
});
