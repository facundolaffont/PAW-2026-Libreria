<?php

    use Paw\Interfaces\BookRepositoryInterface;
    use Paw\Interfaces\PromotionRepositoryInterface;
    use Paw\Logging\AppLogFormatter;
    use Paw\Repositories\BookRepository;
    use Paw\Repositories\PromotionRepository;
    use Paw\Services\EmailService;
    use Monolog\Handler\StreamHandler;
    use Monolog\Level;
    use Monolog\Logger;
    use Monolog\Processor\IntrospectionProcessor;
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
            $streamHandler = new StreamHandler(__DIR__ . '/../app.log', Level::Debug);

            // Configura un formateador personalizado para el log.
            $formatter = new AppLogFormatter();
            $formatter->setJsonPrettyPrint(true);
            $streamHandler->setFormatter($formatter);
            $logger->pushHandler($streamHandler);

            // Fusiona file y line en un único campo "file:line".
            // Se agrega antes de IntrospectionProcessor porque Monolog ejecuta
            // los processors en orden LIFO (el último en agregarse se ejecuta primero).
            $logger->pushProcessor(function ($record) {
                if (isset($record->extra['file'], $record->extra['line'])) {
                    $record->extra['file'] = $record->extra['file'] . ':' . $record->extra['line'];
                    unset($record->extra['line']);
                }
                return $record;
            });

            // Agrega información de contexto de ejecución (archivo, línea, clase, método).
            $logger->pushProcessor(new IntrospectionProcessor());

            return $logger;
        }),

        BookRepositoryInterface::class      => autowire(BookRepository::class),
        PromotionRepositoryInterface::class => autowire(PromotionRepository::class),
        EmailService::class                 => autowire(EmailService::class),

    ];
