<?php

namespace Paw\Services;

use Paw\Enums\UserRole;
use Paw\Models\User;

/**
 * Clase responsable de gestionar la sesión del usuario en la aplicación.
 */
class UserSessionManager {

    /** Nombre de la clave de sesión que contendrá información del usuario actual. */
    private const SESSION_KEY = 'current_user';

    /** Usuarios iniciales hardcodeados de la aplicación. */
    private const AVAILABLE_USERS = [
        'admin' => ['id' => 1, 'name' => 'Administrador', 'role' => UserRole::ADMIN],
        'client' => ['id' => 2, 'name' => 'Cliente', 'role' => UserRole::CLIENT],
    ];

    /**
     * Asegura que la sesión exista y que haya un usuario guardado.
     * 
     * Si se proporciona un parámetro 'user' válido, se sobreescribe ese usuario en
     * la sesión. Si no se proporciona o es inválido, se mantiene el usuario
     * actual o, en su defecto, se establece el usuario cliente.
     * 
     * @param array $queryParams Los parámetros de la solicitud HTTP.
     * @return void
     */
    public static function ensureUserSession(array $queryParams = []): void {
        
        // Inicializa la sesión, si no lo está ya, para poder manipularla a
        // través de $_SESSION.
        self::ensureSessionStarted();

        // Si se proporciona un parámetro 'user' en la consulta, establece el
        // usuario correspondiente en la sesión.
        $requestedUser = strtolower((string)($queryParams['user'] ?? ''));
        self::setCurrentUserByKey($requestedUser);

        // Si no hay un usuario válido en la sesión, establece el usuario
        // cliente por defecto.
        if (!isset($_SESSION[self::SESSION_KEY])) {
            self::setCurrentUserByKey('client');
        }
    }

    /**
     * Obtiene el usuario actual que está guardado en la sesión. Si no existe,
     * se establece por defecto que el usuario actual tiene rol de cliente.
     *
     * @return User El usuario actual de la sesión.
     */
    public static function getCurrentUser(): User {

        // Asegura que exista una sesión y que haya un usuario válido guardado.
        // Si no existe la sesión, la crea, y si no existe el usuario, se
        // establece el usuario cliente por defecto.
        self::ensureUserSession();

        // Obtiene los datos del usuario actual de la sesión.
        $userData = $_SESSION[self::SESSION_KEY];

        // Devuelve una instancia de User construida a partir de los datos de
        // usuario obtenidos de la sesión.
        return new User(
            (int)$userData['id'],
            (string)$userData['name'],
            UserRole::from((string)$userData['role'])
        );
    }

    /**
     * Verifica si el usuario actual tiene rol de administrador.
     *
     * @return bool Verdadero si el usuario es administrador, falso en caso
     * contrario.
     */
    public static function isAdmin(): bool {
        return self::getCurrentUser()->isAdmin();
    }

    /**
     * Verifica si el usuario actual tiene rol de cliente.
     *
     * @return bool Verdadero si el usuario es cliente, falso en caso contrario.
     */
    public static function isClient(): bool {
        return self::getCurrentUser()->isClient();
    }

    /**
     * Guarda los datos del usuario actual en la sesión. Si la clave que
     * identifica al usuario no es válida, no se realiza ningún cambio en la
     * sesión.
     *
     * @param string $userKey La clave que identifica al usuario que se va a
     * guardar en la sesión.
     * @return void
     */
    private static function setCurrentUserByKey(string $userKey): void {

        // Se asegura de que el rol exista. Si no, no hace nada.
        if (!array_key_exists($userKey, self::AVAILABLE_USERS)) {
            return;
        }

        // Guarda los datos del usuario en la sesión.
        $user = self::AVAILABLE_USERS[$userKey];
        $_SESSION[self::SESSION_KEY] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']->value,
        ];
    }

    /**
     * Asegura que la sesión de PHP esté iniciada. Si la sesión ya está activa,
     * no hace nada. Si no, inicia la sesión.
     */
    private static function ensureSessionStarted(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_start();
    }
}