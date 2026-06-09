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

            $id = $this->reservationRepository->save($nombre, $email, $telefono, $libros);
            $this->logger->info("Reserva guardada.", ['id' => $id, 'email' => $email]);

            $this->emailService->sendReservationNotification($nombre, $email, $telefono, $libros);

            header('Location: /reservation?enviada=1');
            exit;
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
