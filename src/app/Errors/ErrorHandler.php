<?php

namespace Paw\Errors;

use Throwable;
use Paw\View;
use Paw\Enums\HttpCodeError;
use Paw\Errors\Exceptions\HttpErrorException;
use Psr\Log\LoggerInterface;

class ErrorHandler {

    /**
     * Maneja errores HTTP (4xx y 5xx) en la aplicación.
     * 
     * @param HttpErrorException $e - La excepción HTTP.
     * @param LoggerInterface $logger - Instancia de Logger para registrar el error.
     */
    static public function handleHttpError(
        HttpErrorException $e,
        LoggerInterface $logger
    ): void {
        $logger->error("",
        [
            'callerFile' => $e->getFile() . ":" . $e->getLine(),
            'exception' => get_class($e),
            'httpCode' => $e->getHttpCode()->toInt(),
            'httpCodeMessage' => $e->getHttpCode()->getMessage(),
        ]);

        // Establece el código de respuesta.
        http_response_code($e->getHttpCode()->toInt());
        
        // En producción, genera la vista de error.
        View::render(
            'error',
            'Error',
            $logger,
            [
                'httpCode'      => $e->getHttpCode()->toInt(),
                'clientMessage' => $e->getHttpCode()->getMessage(),
            ]
        );
    }

    /**
     * Maneja el resto de los errores en la aplicación.
     * 
     * En este caso, se asume que el error es un error de servidor (5xx).
     * 
     * @param Throwable $e - La excepción o error capturado.
     * @param string $loggingMessage - Mensaje de registro para el logger.
     * @param LoggerInterface $logger - Instancia de Logger para registrar el error.
     */
    static public function handleAppError(
        Throwable $e,
        string $loggingMessage,
        LoggerInterface $logger
    ): void {
        $logger->error($loggingMessage,
        [
            'callerFile' => $e->getFile() . ":" . $e->getLine(),
            'exception' => get_class($e),
            'exceptionMessage' => $e->getMessage(),
            'stackTrace' => $e->getTrace(),
        ]);

        // Establece el código de respuesta.
        $httpResponseEnum = HttpCodeError::INTERNAL_SERVER_ERROR;
        http_response_code($httpResponseEnum->toInt());
        
        // En producción, genera la vista de error.
        View::render(
            'error',
            'Error',
            $logger,
            [
                'httpCode'      => $httpResponseEnum->toInt(),
                'clientMessage' => $httpResponseEnum->getMessage(),
            ]
        );
    }
}
