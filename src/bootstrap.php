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

    # Conexión a la base de datos.
    $db = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return [
        'db'        => $db,
        'appLogger' => $appLogger,
    ];