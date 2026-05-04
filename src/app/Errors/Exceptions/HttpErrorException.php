<?php

namespace Paw\Errors\Exceptions;

use Throwable;
use Paw\Enums\HttpCodeError;

class HttpErrorException extends AppException {

    public function __construct(
        private HttpCodeError $httpCodeEnum,
        ?string $message = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message ?? $httpCodeEnum->getMessage(), $httpCodeEnum->toInt(), $previous);
    }

    public function getHttpCode(): HttpCodeError {
        return $this->httpCodeEnum;
    }
}
