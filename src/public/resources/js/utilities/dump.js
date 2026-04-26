// Borra todo el contenido de la pantalla y muestra solo el HTML pasado por parámetro.
export function dump(data) {
    document.body.innerHTML = data;
}
