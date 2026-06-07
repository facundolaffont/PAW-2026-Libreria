## ¿Qué es un ataque XSS?

**Cross-Site Scripting (XSS)** es una vulnerabilidad que permite a un atacante inyectar código JavaScript malicioso en una página web que otros usuarios visitan. Cuando el navegador renderiza ese contenido sin sanitizar, ejecuta el script del atacante en el contexto de la aplicación legítima. Esto puede:

- Robar cookies de sesión y suplantar usuarios.
- Redirigir a sitios de phishing.
- Modificar el DOM para capturar datos ingresados en formularios.
- Ejecutar acciones en nombre del usuario (CSRF).

XSS se clasifica en tres tipos:

| Tipo | Descripción |
|------|-------------|
| **Reflejado** | El payload viaja en la URL/query string y se refleja inmediatamente en la respuesta. |
| **Almacenado** | El payload se persiste en la DB y se sirve a todos los visitantes de la página. |
| **DOM-based** | La vulnerabilidad está en el JavaScript del lado cliente, no en el HTML del servidor. |

## ¿Cómo se evita?

La regla de oro: **nunca confíes en entrada del usuario ni en datos provenientes de DB**. Las principales defensas:

1. **Escape de salida (`htmlspecialchars`)** — Convierte caracteres HTML especiales (`<`, `>`, `"`, `'`, `&`) en sus entidades HTML (`&lt;`, `&gt;`, etc.), evitando que el navegador los interprete como código.
2. **Content-Security-Policy (CSP)** — Header HTTP que restringe qué recursos (scripts, estilos, imágenes) puede cargar el navegador, mitigando incluso si un payload se cuela.
3. **Validación de entrada** — Rechazar datos malformados en el servidor antes de persistirlos.
4. **Uso de `json_encode` con flags** — `JSON_HEX_TAG | JSON_HEX_AMP` para embeber datos JSON en `<script>` sin riesgo.

## Implementación en el proyecto

### 1. Escape sistemático con `htmlspecialchars`

Se auditaron todos los archivos de vista (`src/views/`, `src/components/`) buscando outputs sin escape. Donde faltaba, se agregó:

```php
<?= htmlspecialchars($variable, ENT_QUOTES, 'UTF-8') ?>
```

Archivos corregidos:
- **`home-page.php`** — `$genre`, `$source['url']`, `$fallbackSrc`, `$book['title']`, `$book['author']`, `$promotion['description']`
- **`head.php:5`** — `$title`

Los archivos que ya estaban correctamente escapados (no se modificaron): `catalog.php`, `book-detail.php`, `reservation.php`, `new-book.php`, `error.php`, `header.php`.

### 2. Headers de seguridad en `Application.php:21-24`

```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; frame-ancestors 'none'");
```

- **`X-Content-Type-Options: nosniff`** — Evita MIME-sniffing (el navegador no intenta adivinar el tipo de contenido).
- **`X-Frame-Options: DENY`** — Previene clickjacking al impedir que el sitio se cargue en un `<iframe>`.
- **`CSP`** — Restringe scripts y estilos solo al mismo origen. Se permite `unsafe-inline` porque el proyecto usa inline scripts (JSON island en catalog) y estilos inline.

### 3. JSON seguro en `catalog.php:30-45`

Ya usaba `json_encode` con `JSON_HEX_TAG | JSON_HEX_AMP`, que convierte `<` → `\u003C`, `>` → `\u003E`, `&` → `\u0026`, evitando que datos de la DB se interpreten como HTML dentro del bloque `<script>`.