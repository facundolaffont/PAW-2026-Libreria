<?php

    namespace Paw;

    use Paw\Services\UserSessionManager;
    use Psr\Log\LoggerInterface;

    class View {
        public static function render(
            string $page,
            string $title,
            LoggerInterface $logger,
            array $context = []
        ): void {
            $currentUser = UserSessionManager::getCurrentUser();
            $context['currentUser'] = $currentUser->toArray();
            $context['isAdmin'] = $currentUser->isAdmin();
            $context['isClient'] = $currentUser->isClient();

            $logger->debug(
                "",
                compact('page', 'title', 'context')
            );

            // Genera la vista.
            $title = "PAWPrints - $title";
            require __DIR__ . '../../components/html.php';
        }
    }