<?php

namespace Paw\Enums;

interface HttpCode {
    public function toInt(): int;

    public function getMessage(): string;
}
