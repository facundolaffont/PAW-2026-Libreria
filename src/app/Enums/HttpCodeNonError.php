<?php

namespace Paw\Enums;

enum HttpCodeNonError: int implements HttpCode {
    // 1xx - Informativos.
    case CONTINUE                      = 100;
    case SWITCHING_PROTOCOLS           = 101;
    case PROCESSING                    = 102;
    case EARLY_HINTS                   = 103;

    // 2xx - Éxito.
    case OK                            = 200;
    case CREATED                       = 201;
    case ACCEPTED                      = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT                    = 204;
    case RESET_CONTENT                 = 205;
    case PARTIAL_CONTENT               = 206;
    case MULTI_STATUS                  = 207;
    case ALREADY_REPORTED              = 208;
    case IM_USED                       = 226;

    // 3xx - Redirecciones.
    case MULTIPLE_CHOICES              = 300;
    case MOVED_PERMANENTLY             = 301;
    case FOUND                         = 302;
    case SEE_OTHER                     = 303;
    case NOT_MODIFIED                  = 304;
    case USE_PROXY                     = 305;
    case TEMPORARY_REDIRECT            = 307;
    case PERMANENT_REDIRECT            = 308;

    public function toInt(): int {
        return $this->value;
    }

    public function getMessage(): string {
        return match ($this) {
            self::CONTINUE => 'Continúa con la solicitud.',
            self::SWITCHING_PROTOCOLS => 'Cambiando de protocolo.',
            self::PROCESSING => 'La solicitud está siendo procesada.',
            self::EARLY_HINTS => 'Pistas tempranas enviadas al cliente.',
            self::OK => 'Operación completada correctamente.',
            self::CREATED => 'Recurso creado correctamente.',
            self::ACCEPTED => 'Solicitud aceptada para procesamiento.',
            self::NON_AUTHORITATIVE_INFORMATION => 'Respuesta con información no autoritativa.',
            self::NO_CONTENT => 'Operación completada sin contenido para devolver.',
            self::RESET_CONTENT => 'Restablece el contenido de la vista cliente.',
            self::PARTIAL_CONTENT => 'Se devuelve contenido parcial.',
            self::MULTI_STATUS => 'Respuesta de estado múltiple.',
            self::ALREADY_REPORTED => 'El recurso ya fue reportado previamente.',
            self::IM_USED => 'La respuesta aplica manipulación de instancia.',
            self::MULTIPLE_CHOICES => 'Existen múltiples opciones para el recurso.',
            self::MOVED_PERMANENTLY => 'El recurso fue movido de forma permanente.',
            self::FOUND => 'El recurso se encuentra temporalmente en otra ubicación.',
            self::SEE_OTHER => 'Consulta el recurso en otra ubicación.',
            self::NOT_MODIFIED => 'El recurso no fue modificado.',
            self::USE_PROXY => 'Debe usarse un proxy para acceder al recurso.',
            self::TEMPORARY_REDIRECT => 'Redirección temporal.',
            self::PERMANENT_REDIRECT => 'Redirección permanente.',
        };
    }
}
