class Utilities {

    /**
     * Carga un elemento script en <head>.
     * 
     * @param {string} name - El ID que se le asignará al script a cargar.
     * Si ya existe un script con ese ID, no se carga nada.
     * @param {string} url - La URL del script a cargar.
     * @param {function} onLoadFnCallback - Función a ejecutar una vez que el
     * script se haya cargado.
     */
    static loadScript(name, url, onLoadFnCallback) {
        let element = document.querySelector("script#" + name);
        if (!element) {

            // Crea el tag del script.
            element = this.newElement("script", "", {src: url, id: name});
            
            // Si existe función callback, la ejecutará una vez que el script
            // se haya cargado.
            if (onLoadFnCallback)
                element.addEventListener("load", onLoadFnCallback);

            // Añade el tag a <head> y carga el script.
            document.head.appendChild(element);

        } else if (typeof onLoadFnCallback === "function") {
            onLoadFnCallback();
        }

        return element;
    }

    /**
     * Crea un nuevo elemento HTML.
     * 
     * @param {string} tag - El nombre del tag HTML a crear.
     * @param {string} content="" - El contenido del elemento.
     * @param {Object} attributes={} - Un objeto con los atributos, y sus valores,
     * a asignar al elemento.
     * @returns {HTMLElement} El elemento creado.
     * 
     * Ejemplo:
     * newElement("script", "", {src: URL, name: "nombreDelScript"});
     */
    static newElement(tag, content = "", attributes = {}) {
        
        // Crea el elemento.
        let element = document.createElement(tag);

        for (const attribute in attributes) {
            element.setAttribute(attribute, attributes[attribute]);
        }

        // Agrega el contenido.
        element.appendChild(document.createTextNode(content));

        return element;
    }

}
