<?php

    use Paw\Bootstrap;
    use Paw\Application;
    use Paw\Errors\ErrorHandler;
    use Paw\Errors\Exceptions\HttpErrorException;
    use Psr\Log\LoggerInterface;

    require '../vendor/autoload.php';

    Bootstrap::boot();
    $dependencyContainer = Bootstrap::buildContainer();
    $logger = $dependencyContainer->get(LoggerInterface::class);
    $logger->info("Aplicación iniciada.");

    try {
        (new Application($dependencyContainer))->run();

    } catch (HttpErrorException $e) {
        ErrorHandler::handleHttpError(
            $e,
            $logger
        );

    } catch (Throwable $e) {
        ErrorHandler::handleAppError(
            $e,
            "Error no controlado en la aplicación.",
            $logger
        );
        
    }
