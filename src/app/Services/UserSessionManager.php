<?php

namespace Paw\Services;

use Paw\Enums\UserRole;
use Paw\Models\User;

class UserSessionManager {

    private const SESSION_KEY = 'current_user';

    /**
     * Usuarios iniciales de la aplicación.
     * Se usa una clave corta para poder alternar fácilmente con ?user=admin|client.
     */
    private const AVAILABLE_USERS = [
        'admin' => ['id' => 1, 'name' => 'Administrador', 'role' => UserRole::ADMIN],
        'client' => ['id' => 2, 'name' => 'Cliente', 'role' => UserRole::CLIENT],
    ];

    public static function bootstrapFromRequest(array $queryParams = []): void {
        self::ensureSessionStarted();

        $requestedUser = strtolower((string)($queryParams['user'] ?? ''));
        if ($requestedUser !== '') {
            self::setCurrentUserByKey($requestedUser);
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            self::setCurrentUserByKey('client');
        }
    }

    public static function getCurrentUser(): User {
        self::bootstrapFromRequest();

        $userData = $_SESSION[self::SESSION_KEY] ?? self::AVAILABLE_USERS['client'];

        return new User(
            (int)$userData['id'],
            (string)$userData['name'],
            $userData['role'] instanceof UserRole
                ? $userData['role']
                : UserRole::from((string)$userData['role'])
        );
    }

    public static function isAdmin(): bool {
        return self::getCurrentUser()->isAdmin();
    }

    public static function isClient(): bool {
        return self::getCurrentUser()->isClient();
    }

    private static function setCurrentUserByKey(string $key): void {
        if (!array_key_exists($key, self::AVAILABLE_USERS)) {
            return;
        }

        $user = self::AVAILABLE_USERS[$key];
        $_SESSION[self::SESSION_KEY] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']->value,
        ];
    }

    private static function ensureSessionStarted(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_start();
    }
}