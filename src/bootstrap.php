<?php

    # Inclusión de funciones utilitarias.
    require '../utilities/utilities.php';
    
    # Inclusión de plugins.
    require '../vendor/autoload.php';
    use \Whoops\Run;
    use \Whoops\Handler\PrettyPageHandler;
    use \Monolog\Logger;
    use \Monolog\Handler\StreamHandler;
    use \Monolog\Level;

    # Configuración de Whoops.
    $whoops = new Run;
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();

    # Configuración de Monolog.
    $appLogger = new Logger('AppLogger');
    $appLogger->pushHandler(new StreamHandler(__DIR__ . '/app.log', Level::Debug));