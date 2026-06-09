<?php

namespace Paw\Services;

use Psr\Log\LoggerInterface;

class OpenLibraryService {

    private const COVER_URL = 'https://covers.openlibrary.org/b/isbn/%s-L.jpg';
    private const TIMEOUT   = 10;

    public function __construct(private LoggerInterface $logger) {}

    /**
     * Descarga la imagen de tapa desde Open Library para el ISBN dado.
     * El ISBN ya fue validado como 13 dígitos por el llamador.
     * Devuelve los bytes de la imagen, o null si no se encontró/no es válida.
     */
    public function fetchCover(string $isbn): ?string {
        $url = sprintf(self::COVER_URL, $isbn);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'PAWLibreria/1.0',
        ]);

        $data    = curl_exec($ch);
        $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error   = curl_error($ch);
        curl_close($ch);

        if ($data === false || $status !== 200) {
            $this->logger->warning("Open Library Covers no respondió.", [
                'isbn'   => $isbn,
                'status' => $status,
                'error'  => $error,
            ]);
            return null;
        }

        // Open Library devuelve una imagen de 1×1 px cuando no tiene tapa.
        $info = @getimagesizefromstring($data);
        if ($info === false || $info[0] <= 1 || $info[1] <= 1) {
            $this->logger->info("Tapa no disponible en Open Library.", ['isbn' => $isbn]);
            return null;
        }

        return $data;
    }
}
