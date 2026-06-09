## Respuestas conceptuales

### 1. ¿Toda la microdata es estática?

No. La microdata se divide en dos categorías según el origen de los datos:

| Tipo | Origen | Ejemplos |
|------|--------|----------|
| **Estática** | Hardcodeada en el template | `BookStore` con nombre, dirección, teléfono — son datos del negocio que no cambian entre requests |
| **Dinámica** | Base de datos (vía `$context`) | `Book.name`, `Book.author`, `Offer.price` — varían según el libro consultado |

En nuestro proyecto:
- **about-us.php**: 100% estática (datos del local).
- **book-detail.php**: 100% dinámica (título, autor, precio, etc. vienen de la DB).
- **catalog.php** y **home-page.php**: 100% dinámicas (listas de libros desde la DB).

No hay microdata semiestática. Cada vez que se renderiza una página con datos dinámicos, el bloque JSON-LD se genera con los valores actuales de la base de datos.

### 2. ¿Cómo decidimos en qué página es importante la microdata?

Aplicamos dos criterios:

1. **Relevancia semántica**: La microdata describe el objeto principal que da sentido a la página. Si la página existe para mostrar ese objeto, entonces merece microdata. Si la página es un formulario funcional o un mensaje de error, no tiene objeto semántico que describir.

2. **Utilidad para buscadores (SEO)**: Schema.org permite a Google generar *rich snippets* (fichas de producto, estrellas de precio, breadcrumbs). Solo ciertos tipos tienen impacto visible en resultados de búsqueda. Priorizamos `Book`, `BookStore`, `ItemList` y `Offer` porque son los que Google reconoce para librerías y catálogos.

Aplicación concreta:

| Página | Objeto principal | Tipo schema.org | ¿Microdata? |
|--------|------------------|-----------------|-------------|
| **book-detail** | Un libro específico que el usuario vino a ver | `Book` + `Offer` | Sí — alta relevancia semántica |
| **catalog** | Lista de libros resultado de filtros/búsqueda | `ItemList` → `Book` | Sí — permite que Google indexe el catálogo como una colección |
| **home-page** | Carruseles de libros agrupados por género | `ItemList` → `Book` | Sí — expone los libros destacados de cada sección |
| **about-us** | La librería como negocio | `BookStore` | Sí — dato estático que mejora la ficha de Google My Business |
| **promotions** | Promociones bancarias (contenido placeholder) | — | No — no hay datos reales que describir |
| **reservation** | Formulario de contacto/envío | — | No — página funcional, no informativa |
| **new-book** | Formulario de administración | — | No — no es contenido público |
| **error / 404** | Mensaje de error | — | No — irrelevante para buscadores |

### 3. En los listados de libros (home y catalog), ¿qué tipo de objetos son?

Son **`schema.org/Book`** dentro de un **`schema.org/ItemList`**. No son publicidades ni tarjetas promocionales. Cada elemento de la lista representa un libro real del catálogo de la librería, con sus propiedades esenciales: `name` (título), `author` (autor), `image` (portada) y `url` (enlace al detalle). El `ItemList` organiza estos libros como una colección numerada (`ListItem.position`), permitiendo a los buscadores entender la estructura del catálogo.

---

## Archivos modificados en el proyecto

A continuación, todos los archivos que se tocaron y qué se cambió en cada uno:

| Archivo | Cambio |
|---------|--------|
| `src/views/home-page.php` |  Microdata: bloque `ItemList` → `Book` por cada género. |
| `src/views/catalog.php` | Microdata: bloque `ItemList` → `Book` con los libros paginados. |
| `src/views/book-detail.php` | Microdata: bloque `Book` + `Offer` con precio y moneda ARS. |
| `src/views/about-us.php` | Microdata: bloque `BookStore` estático con nombre, dirección, teléfono, email, redes. |
| `src/app/Application.php` | Headers de seguridad: `X-Content-Type-Options`, `X-Frame-Options`, `Content-Security-Policy`. |
| `src/views/new-book.css` | Fix scroll horizontal: `box-sizing`, `overflow-x: hidden`, `img { max-width: 100% }`. |
| `src/app/Controllers/NewBookController.php` | Fix ruta de imagen: retorna `'libros/' . $filename` en lugar del filename solo. |