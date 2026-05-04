<?php

namespace Paw\Enums;

enum HttpCodeError: int implements HttpCode {
    // 4xx - Errores del cliente.
    case BAD_REQUEST                     = 400;
    case UNAUTHORIZED                    = 401;
    case PAYMENT_REQUIRED                = 402;
    case FORBIDDEN                       = 403;
    case NOT_FOUND                       = 404;
    case METHOD_NOT_ALLOWED              = 405;
    case NOT_ACCEPTABLE                  = 406;
    case PROXY_AUTHENTICATION_REQUIRED   = 407;
    case REQUEST_TIMEOUT                 = 408;
    case CONFLICT                        = 409;
    case GONE                            = 410;
    case LENGTH_REQUIRED                 = 411;
    case PRECONDITION_FAILED             = 412;
    case CONTENT_TOO_LARGE               = 413;
    case URI_TOO_LONG                    = 414;
    case UNSUPPORTED_MEDIA_TYPE          = 415;
    case RANGE_NOT_SATISFIABLE           = 416;
    case EXPECTATION_FAILED              = 417;
    case IM_A_TEAPOT                     = 418;
    case MISDIRECTED_REQUEST             = 421;
    case UNPROCESSABLE_ENTITY            = 422;
    case LOCKED                          = 423;
    case FAILED_DEPENDENCY               = 424;
    case TOO_EARLY                       = 425;
    case UPGRADE_REQUIRED                = 426;
    case PRECONDITION_REQUIRED           = 428;
    case TOO_MANY_REQUESTS               = 429;
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    case UNAVAILABLE_FOR_LEGAL_REASONS   = 451;

    // 5xx - Errores del servidor.
    case INTERNAL_SERVER_ERROR           = 500;
    case NOT_IMPLEMENTED                 = 501;
    case BAD_GATEWAY                     = 502;
    case SERVICE_UNAVAILABLE             = 503;
    case GATEWAY_TIMEOUT                 = 504;
    case HTTP_VERSION_NOT_SUPPORTED      = 505;
    case VARIANT_ALSO_NEGOTIATES         = 506;
    case INSUFFICIENT_STORAGE            = 507;
    case LOOP_DETECTED                   = 508;
    case NOT_EXTENDED                    = 510;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;

    public function toInt(): int {
        return $this->value;
    }

    public function getMessage(): string {
        return match ($this) {
            self::BAD_REQUEST => 'La solicitud es inválida.',
            self::UNAUTHORIZED => 'Debes iniciar sesión para continuar.',
            self::PAYMENT_REQUIRED => 'Se requiere pago para continuar.',
            self::FORBIDDEN => 'No tienes permisos para acceder.',
            self::NOT_FOUND => 'Página no encontrada.',
            self::METHOD_NOT_ALLOWED => 'Método no permitido para esta ruta.',
            self::NOT_ACCEPTABLE => 'No se puede generar una respuesta aceptable.',
            self::PROXY_AUTHENTICATION_REQUIRED => 'Se requiere autenticación del proxy.',
            self::REQUEST_TIMEOUT => 'La solicitud tardó demasiado.',
            self::CONFLICT => 'La operación entró en conflicto con el estado actual.',
            self::GONE => 'El recurso solicitado ya no está disponible.',
            self::LENGTH_REQUIRED => 'Falta el encabezado Content-Length.',
            self::PRECONDITION_FAILED => 'No se cumplió una precondición de la solicitud.',
            self::CONTENT_TOO_LARGE => 'El contenido enviado es demasiado grande.',
            self::URI_TOO_LONG => 'La URL solicitada es demasiado larga.',
            self::UNSUPPORTED_MEDIA_TYPE => 'El tipo de contenido no está soportado.',
            self::RANGE_NOT_SATISFIABLE => 'El rango solicitado no se puede satisfacer.',
            self::EXPECTATION_FAILED => 'No se pudo cumplir la expectativa de la solicitud.',
            self::IM_A_TEAPOT => 'No puedo procesar esta solicitud.',
            self::MISDIRECTED_REQUEST => 'La solicitud fue dirigida al servidor incorrecto.',
            self::UNPROCESSABLE_ENTITY => 'No se pudo procesar la entidad enviada.',
            self::LOCKED => 'El recurso está bloqueado.',
            self::FAILED_DEPENDENCY => 'Falló una dependencia necesaria para la operación.',
            self::TOO_EARLY => 'La solicitud llegó demasiado pronto.',
            self::UPGRADE_REQUIRED => 'Se requiere actualizar el protocolo para continuar.',
            self::PRECONDITION_REQUIRED => 'Se requiere una precondición para procesar la solicitud.',
            self::TOO_MANY_REQUESTS => 'Demasiadas solicitudes. Intenta nuevamente más tarde.',
            self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Los encabezados de la solicitud son demasiado grandes.',
            self::UNAVAILABLE_FOR_LEGAL_REASONS => 'El recurso no está disponible por razones legales.',
            self::INTERNAL_SERVER_ERROR => 'Error interno del servidor.',
            self::NOT_IMPLEMENTED => 'La funcionalidad solicitada no está implementada.',
            self::BAD_GATEWAY => 'El servidor recibió una respuesta inválida de otro servidor.',
            self::SERVICE_UNAVAILABLE => 'Servicio temporalmente no disponible.',
            self::GATEWAY_TIMEOUT => 'El tiempo de espera del servidor se agotó.',
            self::HTTP_VERSION_NOT_SUPPORTED => 'La versión HTTP no está soportada.',
            self::VARIANT_ALSO_NEGOTIATES => 'Error de negociación de contenido.',
            self::INSUFFICIENT_STORAGE => 'No hay almacenamiento suficiente para completar la operación.',
            self::LOOP_DETECTED => 'Se detectó un bucle infinito en el procesamiento.',
            self::NOT_EXTENDED => 'Se requieren extensiones adicionales para la solicitud.',
            self::NETWORK_AUTHENTICATION_REQUIRED => 'Se requiere autenticación de red.',
        };
    }
}
