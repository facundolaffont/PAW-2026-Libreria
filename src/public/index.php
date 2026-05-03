<?php

    use Paw\Bootstrap;
    use Paw\Application;
    
    require '../vendor/autoload.php';

    Bootstrap::boot();
    $container = Bootstrap::buildContainer();

    (new Application($container))->run();
