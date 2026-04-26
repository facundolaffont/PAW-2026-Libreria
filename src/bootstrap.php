<?php

    # Inclusión de funciones utilitarias.
    require '../utilities/utilities.php';

    # Inclusión de plugins.
    require '../vendor/autoload.php';
    use \Whoops\Run;
    use \Whoops\Handler\PrettyPageHandler;
    use DI\ContainerBuilder;

    # Configuración de Whoops.
    $whoops = new Run;
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();

    # Construcción del contenedor de dependencias.
    $builder = new ContainerBuilder();
    $builder->addDefinitions(require __DIR__ . '/config/container.php');
    return $builder->build();