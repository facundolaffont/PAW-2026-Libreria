# PAW-2026-Libreria

Repositorio del trabajo práctico que se desarrolla en la cursada de la materia Programación en Ambiente Web (código 11086), de la carrera Licenciatura en Sistemas de Información, de la Universidad de Luján (UNLu).

Temática: Librería.

Integrantes: ver [authors.txt](https://raw.githubusercontent.com/facundolaffont/PAW-2026-Libreria/refs/heads/main/authors.txt).

## Instalación del proyecto

Situarse vía terminal en la carpeta donde se va a descargar la carpeta del proyecto y ejectutar:

```shell
git clone https://github.com/facundolaffont/PAW-2026-Libreria.git
```

## Configuración del proyecto

Ingresar en la carpeta creada durante la instalación, crear el archivo `.env` en la raíz del proyecto y completarlo según las indicaciones del archivo `.env.example`.

## Ejecución del proyecto

Una vez configurado el proyecto, situarse vía terminal en la carpeta raíz y ejecutar:

```shell
docker compose up -d
```

**Nota**: la opción `-d` es para que el proceso corra en segundo plano; si se desea ver la salida del docker compose, no utilizar dicha opción.

Luego, ingresar a través del navegador web al sitio `localhost:8000`.

## Entregas de trabajos prácticos

A continuación se detallará la información sobre cada entrega que se va solicitando.

### 1era entrega (30/03/26)

[Consigas del trabajo práctico 1](https://raw.githubusercontent.com/facundolaffont/PAW-2026-Libreria/refs/tags/entrega-1/tps/tp1.pdf).

**Entregables**:

* [Sitemap](https://raw.githubusercontent.com/facundolaffont/PAW-2026-Libreria/refs/tags/entrega-1/Sitemap.png).
* [Wireframes](https://raw.githubusercontent.com/facundolaffont/PAW-2026-Libreria/refs/tags/entrega-1/Librer%C3%ADa%20-%20Wireframes.pdf).
* [Maquetado](https://github.com/facundolaffont/PAW-2026-Libreria/tree/entrega-1/maquetado-html).

#### Cómo visualizar las páginas

1. Descargar el archivo comprimido que figura en el siguiente enlace: [https://github.com/facundolaffont/PAW-2026-Libreria/releases/tag/entrega-1](https://github.com/facundolaffont/PAW-2026-Libreria/releases/tag/entrega-1).
2. Descomprimir el archivo descargado, ingresar a la carpeta descomprimida y luego a la carpeta maquetado-html.
3. Abrir index.html y navegar por los enlaces (opcionalmente se puede abrir cualquiera de las otras páginas).

### 2da entrega (13/04/26)

[Consigas del trabajo práctico 2](https://raw.githubusercontent.com/facundolaffont/PAW-2026-Libreria/refs/tags/entrega2/tps/tp2/Trabajo%20Pr%C3%A1ctico%20N%C2%BA%202.pdf).

**Entregables**:

* Wireframes: [carpeta wireframes](https://github.com/facundolaffont/PAW-2026-Libreria/tree/entrega2/wireframes).
* Maquetado: [maquetado-html](https://github.com/facundolaffont/PAW-2026-Libreria/tree/entrega2/maquetado-html).

**Prueba del sistema**: ver sección "Ejecución del proyecto".

### 3ra entrega (04/05/26)

[Consignas del trabajo práctico 3](https://github.com/facundolaffont/PAW-2026-Libreria/raw/0110cfd355932f3ea84a994bdca501e0e7ccc57f/tps/tp3.pdf).

**Entregables**:

* [Informe de arquitectura](https://github.com/facundolaffont/PAW-2026-Libreria/raw/0110cfd355932f3ea84a994bdca501e0e7ccc57f/site-info/Informe%20de%20arquitectura.pdf).

**Prueba del sistema**: ver sección "Ejecución del proyecto".

### 4ta entrega (26/05/26)

[Consignas del trabajo práctico 4](https://github.com/facundolaffont/PAW-2026-Libreria/raw/13fe4dcbe12440979d149204a5fa71095ff6ed01/tps/tp4.pdf).

**Prueba del sistema**: ver sección "Ejecución del proyecto".

### 5ta entrega (08/06/26)

[Consignas del trabajo práctico 5]

**Prueba del sistema**: ver sección "Ejecución del proyecto".
**Preguntas teóricas**: ver sección "Preguntas teóricas".

## Preguntas teóricas

### ¿Qué es un ataque de inyección SQL?

Un ataque de inyección SQL (SQL Injection) es la explotación de una vulnerabilidad que consiste en que un atacante logre inyectar código SQL malicioso en una consulta de la aplicación. Esto puede ocurrir cuando la aplicación arma consultas concatenando texto con datos ingresados por el usuario sin validación ni parametrización adecuada. En ese caso, la entrada maliciosa se interpreta como parte del SQL y altera la consulta original.

Impactos posibles:

- Saltar autenticación.
- Leer información sensible de la base de datos.
- Modificar o eliminar registros.
- En escenarios graves, ejecutar acciones administrativas sobre la DB.

La causa raíz es mezclar código SQL con entrada no confiable.

### ¿Cómo puede evitarse un ataque de inyección SQL?

La defensa principal es usar **consultas preparadas con parámetros** (*prepared statements*) y nunca concatenar datos de usuario dentro del SQL.

Medidas recomendadas:

1. **Parametrización obligatoria**: En PHP, por ejemplo, se debe utilizar `PDO::prepare()` + `bindValue()/execute()` para separar estructura SQL de datos. El funcionamiento es el siguiente: primero se define la consulta con marcadores de posición, luego los valores reales se envían aparte. Así, la base de datos interpreta esos valores solo como datos y no como instrucciones SQL, aunque contengan comillas, operadores u otros caracteres especiales. En ese proceso, el driver de acceso a datos aplica internamente el tratamiento seguro necesario para esos caracteres, sin que el desarrollador tenga que concatenarlos o escaparlos manualmente dentro del SQL.

2. **Validación del lado servidor**: Verificar tipo, formato, longitud y rango de los datos antes de consultar o persistir. Esto es importante porque permite rechazar entradas inválidas o anómalas antes de que lleguen a la capa de acceso a datos. No reemplaza el uso de consultas preparadas, pero agrega una defensa adicional al reducir errores, comportamientos inesperados y superficie de ataque.

3. **Mínimo privilegio en la base de datos**: El usuario de la app debe tener solo los permisos estrictamente necesarios para su función (por ejemplo, `SELECT`, `INSERT` y, solo si corresponde, `UPDATE/DELETE` sobre tablas específicas). No debe usarse una cuenta con privilegios administrativos ni permisos globales como crear o eliminar tablas/usuarios. De esta forma, si ocurre una inyección SQL, el alcance del daño queda limitado por los permisos de esa cuenta.

4. **Manejo seguro de errores**: No exponer mensajes SQL internos al cliente ni detalles de infraestructura (consulta completa, nombre de tablas, stack trace, credenciales o versiones). Hacia el usuario final conviene responder con mensajes genéricos (por ejemplo, "Ocurrió un error al procesar la solicitud") y códigos HTTP adecuados. Esto evita brindar información a potenciales atacantes para que puedan determinar cómo realizar un ataque de este tipo.

5. **Listas permitidas para SQL dinámico**: Si hay `ORDER BY`, nombres de columnas o tablas dinámicas, resolverlos con whitelist, no con input libre. Esto se debe a que los parámetros preparados protegen los valores de los datos, pero no suelen servir para reemplazar identificadores SQL como nombres de columnas, tablas o criterios de ordenamiento. Si esa parte de la consulta se arma con texto ingresado por el usuario, el usuario pasa a influir sobre la estructura del SQL. La forma segura es definir previamente qué opciones son válidas y mapear la elección del usuario a una de esas alternativas permitidas.

6. **Monitoreo y auditoría**: Registrar intentos anómalos para detectar patrones de ataque y responder temprano.

### ¿Qué es un ataque XSS?

**Cross-Site Scripting (XSS)** es una explotación de una vulnerabilidad que permite a un atacante inyectar código JavaScript malicioso en una página web que otros usuarios visitan. Cuando el navegador renderiza ese contenido sin sanitizarlo, ejecuta el script del atacante en el contexto de la aplicación legítima. Esto puede:

- Robar cookies de sesión y suplantar la identidad de usuarios.
- Redirigir al navegador de la víctima a sitios de phishing.
- Modificar el DOM para capturar datos ingresados en formularios.
- Ejecutar acciones en nombre del usuario.

XSS se clasifica en tres tipos:

| Tipo | Descripción |
|------|-------------|
| **Reflejado** | El payload viaja en la URL o en parámetros de la request y la aplicación lo devuelve inmediatamente en la respuesta sin escapar. No queda guardado en el servidor: el ataque se dispara cuando la víctima abre un enlace manipulado o envía un formulario especialmente construido. |
| **Almacenado** | El payload se persiste en la base de datos u otro almacenamiento del sistema y luego se sirve a los usuarios cada vez que visitan la página afectada. Es más peligroso porque no depende de un enlace puntual: alcanza con que la víctima consulte contenido previamente contaminado, como comentarios, reseñas o perfiles. |
| **Basado en el DOM** | La vulnerabilidad está en el JavaScript del lado cliente, no en el HTML generado por el servidor. El script toma datos no confiables, por ejemplo desde `location`, `document.URL` o `localStorage`, y los inserta en el DOM de forma insegura, haciendo que el navegador ejecute el payload. </br></br> La idea general que el atacante consigue que un dato controlado por él llegue a una fuente que el JavaScript de la página lee, por ejemplo la URL, parámetros de búsqueda o algún valor guardado en el navegador (por ejemplo, en `localStorage`). Después, ese script inserta ese dato en el DOM de forma insegura, por ejemplo interpretándolo como HTML en vez de tratarlo como texto plano. En ese momento, el navegador termina ejecutando el contenido malicioso. |

### ¿Cómo se evita un ataque XSS?

La regla de oro: **nunca confíes en entrada del usuario ni en datos provenientes de DB**. Las principales defensas:

1. **Escape de salida (`htmlspecialchars`)** — Convierte caracteres HTML especiales (`<`, `>`, `"`, `'`, `&`) en sus entidades HTML (`&lt;`, `&gt;`, etc.), evitando que el navegador los interprete como código.
2. **Content-Security-Policy (CSP)** — Header HTTP que restringe qué recursos (scripts, estilos, imágenes) puede cargar el navegador, mitigando incluso si un payload se cuela.
3. **Validación de entrada** — Rechazar datos malformados en el servidor antes de persistirlos.
4. **Uso de `json_encode` con flags** — `JSON_HEX_TAG | JSON_HEX_AMP` para embeber datos JSON en `<script>` sin riesgo.

### ¿Toda la microdata es estática?

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

### ¿Cómo decidimos en qué página es importante la microdata?

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

#### En los listados de libros (home y catalog), ¿qué tipo de objetos son?

Son **`schema.org/Book`** dentro de un **`schema.org/ItemList`**. No son publicidades ni tarjetas promocionales. Cada elemento de la lista representa un libro real del catálogo de la librería, con sus propiedades esenciales: `name` (título), `author` (autor), `image` (portada) y `url` (enlace al detalle). El `ItemList` organiza estos libros como una colección numerada (`ListItem.position`), permitiendo a los buscadores entender la estructura del catálogo.
