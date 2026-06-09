<?php

    namespace Paw\Controllers;

    use Paw\Interfaces\ReservationRepositoryInterface;
    use Paw\Services\EmailService;
    use Paw\View;
    use Psr\Log\LoggerInterface;

    class ReservationController {

        /*
         * Reglas de validación: deben mantenerse sincronizadas con los atributos
         * del formulario HTML (minlength, maxlength, pattern, required).
         */
        private const NOMBRE_MIN      = 2;
        private const NOMBRE_MAX      = 100;
        private const NOMBRE_PATTERN  = '/^[A-Za-záéíóúüñÁÉÍÓÚÜÑ\s]+$/u';
        private const EMAIL_MAX       = 254;
        private const TELEFONO_MIN    = 8;
        private const TELEFONO_MAX    = 20;
        private const TELEFONO_PATTERN = '/^[0-9+\-\s]+$/';

        public function __construct(
            private EmailService                  $emailService,
            private ReservationRepositoryInterface $reservationRepository,
            private LoggerInterface               $logger
        ) {}

        /**
         * Procesa el formulario de reserva enviado por POST con codificación
         * application/x-www-form-urlencoded. PHP decodifica automáticamente
         * los pares clave=valor y los expone en $_POST.
         *
         * Flujo:
         *   - Validación inválida → re-renderiza el formulario con errores.
         *   - Validación exitosa  → envía correo → redirige (patrón PRG).
         */
        public function handle(): void {
            $nombre   = trim($_POST['nombre']   ?? '');
            $email    = trim($_POST['email']    ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $libros   = $_POST['libros'] ?? [];

            $errors = $this->validate($nombre, $email, $telefono);

            $items = $this->normalizeItems($libros);
            if (empty($items)) {
                $errors['general'] = 'Tu reserva está vacía. Agregá libros desde el catálogo.';
            }

            if (!empty($errors)) {
                View::render('reservation', 'Reserva', $this->logger,
                    [
                        'errors'   => $errors,
                        'nombre'   => $nombre,
                        'email'    => $email,
                        'telefono' => $telefono,
                    ]
                );
                return;
            }

            // Persiste primero: la DB pasa a ser la fuente de verdad.
            try {
                $this->reservationRepository->create(
                    ['nombre' => $nombre, 'email' => $email, 'telefono' => $telefono],
                    $items
                );
            } catch (\Throwable $e) {
                View::render('reservation', 'Reserva', $this->logger, [
                    'errors'   => ['general' => 'Hubo un problema al guardar tu reserva. Por favor, intentá nuevamente.'],
                    'nombre'   => $nombre,
                    'email'    => $email,
                    'telefono' => $telefono,
                ]);
                return;
            }

            // Mail best-effort: si falla, la reserva ya quedó guardada.
            $enviado = $this->emailService->sendReservationNotification($nombre, $email, $telefono, $libros);
            if (!$enviado) {
                $this->logger->warning(
                    "Reserva persistida pero no se pudo enviar el correo de notificación.",
                    ['email' => $email]
                );
            }

            // PRG: redirige en GET para evitar reenvíos al refrescar la página.
            header('Location: /reservation?enviada=1');
            exit;
        }

        /**
         * Normaliza el array $_POST['libros'] a la forma que espera el repositorio,
         * descartando entradas inválidas (sin título).
         */
        private function normalizeItems(array $libros): array {
            $items = [];
            foreach ($libros as $libro) {
                if (!is_array($libro)) continue;
                $titulo = trim((string)($libro['titulo'] ?? ''));
                if ($titulo === '') continue;
                $items[] = [
                    'book_id'         => isset($libro['book_id']) && (int)$libro['book_id'] > 0 ? (int)$libro['book_id'] : null,
                    'titulo'          => $titulo,
                    'autor'           => trim((string)($libro['autor'] ?? '')),
                    'cantidad'        => max(1, (int)($libro['cantidad'] ?? 1)),
                    'precio_unitario' => max(0, (float)($libro['precio_unitario'] ?? 0)),
                ];
            }
            return $items;
        }

        private function validate(string $nombre, string $email, string $telefono): array {
            $errors = [];

            if ($nombre === '') {
                $errors['nombre'] = 'El nombre completo es obligatorio.';
            } elseif (mb_strlen($nombre) < self::NOMBRE_MIN) {
                $errors['nombre'] = 'El nombre debe tener al menos ' . self::NOMBRE_MIN . ' caracteres.';
            } elseif (mb_strlen($nombre) > self::NOMBRE_MAX) {
                $errors['nombre'] = 'El nombre no puede superar los ' . self::NOMBRE_MAX . ' caracteres.';
            } elseif (!preg_match(self::NOMBRE_PATTERN, $nombre)) {
                $errors['nombre'] = 'El nombre solo puede contener letras y espacios.';
            }

            if ($email === '') {
                $errors['email'] = 'El email es obligatorio.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'El email ingresado no es válido.';
            } elseif (strlen($email) > self::EMAIL_MAX) {
                $errors['email'] = 'El email no puede superar los ' . self::EMAIL_MAX . ' caracteres.';
            }

            if ($telefono === '') {
                $errors['telefono'] = 'El teléfono es obligatorio.';
            } elseif (mb_strlen($telefono) < self::TELEFONO_MIN) {
                $errors['telefono'] = 'El teléfono debe tener al menos ' . self::TELEFONO_MIN . ' dígitos.';
            } elseif (mb_strlen($telefono) > self::TELEFONO_MAX) {
                $errors['telefono'] = 'El teléfono no puede superar los ' . self::TELEFONO_MAX . ' caracteres.';
            } elseif (!preg_match(self::TELEFONO_PATTERN, $telefono)) {
                $errors['telefono'] = 'El teléfono solo puede contener dígitos, espacios, + y -.';
            }

            return $errors;
        }
    }
