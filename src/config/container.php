<?php

    use Monolog\Handler\StreamHandler;
    use Monolog\Level;
    use Monolog\Logger;
    use Paw\Interfaces\BookRepositoryInterface;
    use Paw\Interfaces\PromotionRepositoryInterface;
    use Paw\Repositories\BookRepository;
    use Paw\Repositories\PromotionRepository;
    use Psr\Log\LoggerInterface;

    use function DI\autowire;
    use function DI\factory;

    return [

        PDO::class => factory(function () {
            $pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8",
                $_ENV['DB_USER'],
                $_ENV['DB_PASSWORD']
            );

            // Lanza excepciones ante errores de SQL, en lugar de retornar false
            // silenciosamente.
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Devuelve las filas como arrays asociativos (clave => valor)
            // en lugar de duplicar cada fila y devolver, además, un array indexado.
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            return $pdo;
        }),

        LoggerInterface::class => factory(function () {
            $logger = new Logger('AppLogger');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/../app.log', Level::Debug));
            return $logger;
        }),

        BookRepositoryInterface::class      => autowire(BookRepository::class),
        PromotionRepositoryInterface::class => autowire(PromotionRepository::class),

    ];
