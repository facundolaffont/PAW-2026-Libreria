<?php

    namespace Paw\Controllers;

    use Paw\Interfaces\BookRepositoryInterface;
    use Paw\View;
    use Psr\Log\LoggerInterface;

    class NewBookController {

        /*
         * Reglas de validación: deben mantenerse sincronizadas con las reglas
         * del archivo new-book-validation.js y los atributos del formulario HTML.
         */
        private const TITULO_MAX      = 255;
        private const AUTOR_MIN       = 2;
        private const AUTOR_MAX       = 255;
        private const GENERO_MAX      = 100;
        private const PRECIO_MIN      = 0.01;
        private const ISBN_LENGTH     = 13;
        private const DESCRIPCION_MAX = 2000;

        public function __construct(
            private BookRepositoryInterface $bookRepository,
            private LoggerInterface         $logger
        ) {}

        public function show(): void {
            View::render('new-book', 'Nuevo libro', $this->logger);
        }

        /**
         * Procesa el formulario de alta de libro por POST.
         * Flujo:
         *   - Validación inválida → re-renderiza el formulario con errores.
         *   - Validación exitosa  → inserta en BD → redirige (patrón PRG).
         */
        public function handle(): void {
            $titulo      = trim($_POST['titulo']      ?? '');
            $autor       = trim($_POST['autor']       ?? '');
            $genero      = trim($_POST['genero']      ?? '');
            $precio      = trim($_POST['precio']      ?? '');
            $stock       = trim($_POST['stock']       ?? '');
            $isbn        = trim($_POST['isbn']        ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            $errors = $this->validate($titulo, $autor, $genero, $precio, $stock, $isbn, $descripcion);

            if (!empty($errors)) {
                View::render('new-book', 'Nuevo libro', $this->logger, [
                    'errors'      => $errors,
                    'titulo'      => $titulo,
                    'autor'       => $autor,
                    'genero'      => $genero,
                    'precio'      => $precio,
                    'stock'       => $stock,
                    'isbn'        => $isbn,
                    'descripcion' => $descripcion,
                ]);
                return;
            }

            $id = $this->bookRepository->create([
                'title'       => $titulo,
                'author'      => $autor,
                'genre'       => $genero      ?: null,
                'price'       => (float) $precio,
                'stock'       => (int)   $stock,
                'isbn'        => $isbn         ?: null,
                'description' => $descripcion  ?: null,
            ]);

            $this->logger->info("Libro creado.", ['id' => $id, 'titulo' => $titulo]);

            // PRG: redirige en GET para evitar reenvíos al refrescar la página.
            header('Location: /new-book?creado=1');
            exit;
        }

        private function validate(
            string $titulo,
            string $autor,
            string $genero,
            string $precio,
            string $stock,
            string $isbn,
            string $descripcion
        ): array {
            $errors = [];

            if ($titulo === '') {
                $errors['titulo'] = 'El título es obligatorio.';
            } elseif (mb_strlen($titulo) > self::TITULO_MAX) {
                $errors['titulo'] = 'El título no puede superar los ' . self::TITULO_MAX . ' caracteres.';
            }

            if ($autor === '') {
                $errors['autor'] = 'El autor es obligatorio.';
            } elseif (mb_strlen($autor) < self::AUTOR_MIN) {
                $errors['autor'] = 'El autor debe tener al menos ' . self::AUTOR_MIN . ' caracteres.';
            } elseif (mb_strlen($autor) > self::AUTOR_MAX) {
                $errors['autor'] = 'El autor no puede superar los ' . self::AUTOR_MAX . ' caracteres.';
            }

            if ($genero !== '' && mb_strlen($genero) > self::GENERO_MAX) {
                $errors['genero'] = 'El género no puede superar los ' . self::GENERO_MAX . ' caracteres.';
            }

            if ($precio === '') {
                $errors['precio'] = 'El precio es obligatorio.';
            } elseif (!is_numeric($precio) || (float) $precio < self::PRECIO_MIN) {
                $errors['precio'] = 'El precio debe ser un número mayor a 0.';
            } elseif (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $precio)) {
                $errors['precio'] = 'El precio debe tener como máximo 2 decimales y 7 dígitos enteros.';
            }

            if ($stock === '') {
                $errors['stock'] = 'El stock es obligatorio.';
            } elseif (!ctype_digit($stock)) {
                $errors['stock'] = 'El stock debe ser un número entero positivo.';
            }

            if ($isbn !== '') {
                if (!ctype_digit($isbn)) {
                    $errors['isbn'] = 'El ISBN debe contener solo dígitos.';
                } elseif (strlen($isbn) !== self::ISBN_LENGTH) {
                    $errors['isbn'] = 'El ISBN debe tener exactamente ' . self::ISBN_LENGTH . ' dígitos.';
                } elseif ($this->bookRepository->existsByIsbn($isbn)) {
                    $errors['isbn'] = 'El ISBN ingresado ya está registrado.';
                }
            }

            if ($descripcion !== '' && mb_strlen($descripcion) > self::DESCRIPCION_MAX) {
                $errors['descripcion'] = 'La descripción no puede superar los ' . self::DESCRIPCION_MAX . ' caracteres.';
            }

            return $errors;
        }
    }
