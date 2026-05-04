<?php

    namespace Paw\Services;

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception as MailException;
    use Psr\Log\LoggerInterface;

    class EmailService {

        public function __construct(private LoggerInterface $logger) {}

        public function sendReservationNotification(
            string $nombre,
            string $email, 
            string $telefono, 
            array $libros = []
        ): bool {

            $mailer = new PHPMailer(true);

            try {

                // Configura el mailer.
                $mailer->isSMTP();
                $mailer->Host       = $_ENV['MAIL_HOST'];
                $mailer->SMTPAuth   = true;
                $mailer->Username   = $_ENV['MAIL_USERNAME'];
                $mailer->Password   = $_ENV['MAIL_PASSWORD'];
                $mailer->SMTPSecure = strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl'
                    ? PHPMailer::ENCRYPTION_SMTPS
                    : PHPMailer::ENCRYPTION_STARTTLS;
                $mailer->Port    = (int) $_ENV['MAIL_PORT'];
                $mailer->CharSet = PHPMailer::CHARSET_UTF8;

                // Establece remitente y destinatarios.
                $mailer->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
                foreach (explode(',', $_ENV['MAIL_TO']) as $recipient) {
                    $mailer->addAddress(trim($recipient));
                }

                // Construye el correo.
                $mailer->isHTML(true);
                $mailer->Subject = 'Nueva solicitud de reserva - PAWPrints';
                $mailer->Body    = $this->buildHtmlBody($nombre, $email, $telefono, $libros);
                $mailer->AltBody = $this->buildTextBody($nombre, $email, $telefono, $libros);

                // Envía el correo.
                $mailer->send();
                $this->logger->info(
                    "Correo de reserva enviado.",
                    compact('email')
                );
                
                return true;

            } catch (MailException $e) {
                $this->logger->error(
                    "Error al enviar correo de reserva.",
                    ['error' => $mailer->ErrorInfo]
                );

                return false;
            }

        }

        private function buildHtmlBody(string $nombre, string $email, string $telefono, array $libros): string {
            $n = htmlspecialchars($nombre,   ENT_QUOTES, 'UTF-8');
            $e = htmlspecialchars($email,    ENT_QUOTES, 'UTF-8');
            $t = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');

            $librosHtml = '';
            foreach ($libros as $libro) {
                $titulo = htmlspecialchars($libro['titulo'] ?? '', ENT_QUOTES, 'UTF-8');
                $autor  = htmlspecialchars($libro['autor']  ?? '', ENT_QUOTES, 'UTF-8');
                $librosHtml .= "<li style='margin-bottom:4px'><strong>{$titulo}</strong> — {$autor}</li>";
            }

            return <<<HTML
                <h2 style="color:#5C068C;font-family:sans-serif">Nueva solicitud de reserva - PAWPrints</h2>

                <h3 style="font-family:sans-serif;margin-bottom:4px">Datos del solicitante</h3>
                <table cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-family:sans-serif">
                    <tr>
                        <th style="text-align:left;border-bottom:1px solid #ccc">Nombre</th>
                        <td style="border-bottom:1px solid #ccc">{$n}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left;border-bottom:1px solid #ccc">Email</th>
                        <td style="border-bottom:1px solid #ccc">{$e}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left">Teléfono</th>
                        <td>{$t}</td>
                    </tr>
                </table>

                <h3 style="font-family:sans-serif;margin-top:20px;margin-bottom:4px">Libros reservados</h3>
                <ul style="font-family:sans-serif">{$librosHtml}</ul>
            HTML;
        }

        private function buildTextBody(string $nombre, string $email, string $telefono, array $libros): string {
            $librosTexto = implode("\n", array_map(
                fn($l) => "  - {$l['titulo']} — {$l['autor']}",
                $libros
            ));

            return "Nueva solicitud de reserva - PAWPrints\n\n"
                . "Nombre: {$nombre}\nEmail: {$email}\nTeléfono: {$telefono}\n\n"
                . "Libros reservados:\n{$librosTexto}";
        }
    }
