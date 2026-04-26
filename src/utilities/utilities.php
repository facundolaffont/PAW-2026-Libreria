<?php

    function dump($data) {
        
        $text = print_r($data, true);
        throw new Exception("DUMP: " . $text);

    }