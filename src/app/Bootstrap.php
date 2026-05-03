<?php

namespace Paw;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Bootstrap
{
    public static function boot(): void
    {
        require __DIR__ . '/../utilities/utilities.php';
        self::configureWhoops();
    }

    public static function buildContainer(): ContainerInterface
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(require __DIR__ . '/../config/container.php');
        return $builder->build();
    }

    private static function configureWhoops(): void
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
    }
}
