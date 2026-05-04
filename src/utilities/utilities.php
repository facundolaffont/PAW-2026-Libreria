<?php

    use Paw\Errors\Exceptions\AppException;

    function dump($data) {
        
        $text = print_r($data, true);
        throw new AppException("DUMP: " . $text);

    }