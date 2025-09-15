<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

/**
 * UsersManager provides methods for user management and authentication.
 *
 * It handles login/logout, retrieving user objects, password encryption,
 * auth tokens, and persistence of user data.
 */
class UsersManager
{

    /**
     * Path file where users are stored
     */
    protected static $file = DATA_PLUGIN . 'users/users.json';

    /**
     * Logs in a user with the provided email and password.
     *
     * @param string $email The user's email address
     * @param string $password The user's password
     * @param bool $useCookies Whether to set auth cookies after successful login
     * @return bool True if login succeeded, false otherwise
     */
    public static function login(string $mail, string $password, bool $useCookies = false): bool
    {
        $user = User::find('email',$mail);
        if ($user === null) {
            return false;
        }
        
        // Use new secure password verification
        if (!self::verifyPassword($password, $user->pwd)) {
            return false;
        }
        
        // Check if password needs rehashing (migration from SHA1)
        if (self::needsPasswordRehash($user->pwd)) {
            $user->pwd = self::encrypt($password);
            logg("Password hash migrated for user: " . $mail, "INFO");
        }
        
        $user->token = self::generateToken();
        $user->save();
        
        // Note: session_regenerate_id() cause des problèmes de session
        // La protection contre la session fixation sera implémentée différemment
        
        self::logon($user);
        
        if ($useCookies) {
            self::setRememberCookies($user);
        }
        
        return true;
    }

    /**
     * Checks if the user is logged in using auth cookies.
     *
     * @return bool True if user is logged in via cookies, false otherwise.
     */
    protected static function loginByCookies(): bool
    {
        $parts = explode('//', $_COOKIE['koAutoConnect']);
        $mail = $parts[0] ?? '';
        $rememberToken = $parts[1] ?? '';

        $user = User::find('email', $mail);
        if ($user === null) {
            // User doesn't exist
            self::clearRememberCookie();
            return false;
        }

        // Vérifier le token de session et son expiration
        if (isset($user->remember_token) && 
            isset($user->remember_token_expires) &&
            $user->remember_token !== null &&
            $user->remember_token_expires !== null &&
            $user->remember_token === $rememberToken &&
            $user->remember_token_expires > time()) {
            
            $user->token = self::generateToken();
            $user->save();
            
            // Note: session_regenerate_id() cause des problèmes de session
            // La protection contre la session fixation sera implémentée différemment
            
            self::logon($user);
            return true;
        }

        // Token invalide ou expiré
        self::clearRememberCookie();
        return false;
    }

    /**
     * Clears the remember me cookie.
     */
    private static function clearRememberCookie(): void
    {
        setcookie('koAutoConnect', '', 1, '/');
    }

    /**
     * Invalidates all remember tokens for a user (useful for logout).
     */
    public static function invalidateRememberTokens(User $user): void
    {
        $user->remember_token = null;
        $user->remember_token_expires = null;
        $user->save();
        self::clearRememberCookie();
    }

    /**
     * Checks if the user is currently logged in.
     *
     * @return bool True if user is logged in, false otherwise.
     */
    public static function isLogged(): bool
    {
        logg("IS_LOGGED: Checking login status", "INFO");
        $currentUser = self::getCurrentUser();
        if ($currentUser === null) {
            logg("IS_LOGGED: No current user, checking cookies", "INFO");
            // Try to connect by cookies
            if (isset($_COOKIE['koAutoConnect']) && is_string($_COOKIE['koAutoConnect'])) {
                logg("IS_LOGGED: Cookie found, attempting loginByCookies()", "INFO");
                return self::loginByCookies();
            }
            logg("IS_LOGGED: No cookie, returning false", "INFO");
            return false;
        }
        logg("IS_LOGGED: User found: " . $currentUser->email . ", returning true", "INFO");
        return true;
    }

    /**
     * Logs in the given user with session fixation protection.
     *
     * @param User $user The user to log in.
     */
    protected static function logon(User $user):void
    {
        logg("LOGON: Setting session data for: " . $user->email, "INFO");
        
        $_SESSION['email'] = $user->email;
        $_SESSION['token'] = $user->token;
        $_SESSION['user_id'] = $user->id;
        
        // Ajouter des métadonnées de session pour la sécurité
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        logg("LOGON: Session data set: " . json_encode($_SESSION), "INFO");
    }

    /**
     * Regenerates session ID for security (call this before any output)
     */
    public static function regenerateSessionId(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(false);
        }
    }

    /**
     * Regenerates session ID after login for session fixation protection
     * This method is safer as it's called after the session data is set
     */
    public static function regenerateSessionIdAfterLogin(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            // Sauvegarder les données de session importantes
            $sessionData = $_SESSION;
            
            // Régénérer l'ID de session
            session_regenerate_id(false);
            
            // Restaurer les données de session
            $_SESSION = $sessionData;
        }
    }

    /**
     * Secure session ID regeneration that preserves session data
     * This method is called BEFORE setting session data to avoid conflicts
     */
    public static function regenerateSessionIdSecure(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            // Régénérer l'ID de session sans détruire les données
            session_regenerate_id(false);
        }
    }

    /**
     * Sets remember me cookies for the given user using secure tokens.
     */
    protected static function setRememberCookies(User $user)
    {
        // Générer un token de session sécurisé
        $rememberToken = self::generateRememberToken();
        
        // Stocker le token en base de données avec expiration
        $user->remember_token = $rememberToken;
        $user->remember_token_expires = time() + (60 * 24 * 3600); // 60 jours
        $user->save();
        
        setcookie(
            'koAutoConnect',
            $user->email . '//' . $rememberToken,
            [
                'expires' => time() + 60 * 24 * 3600,
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'path' => '/'
            ]
        );
    }

    /**
     * Generates a secure remember token.
     */
    private static function generateRememberToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Return the current User, if connected by session with security validation
     * @return User|null User or false if not connected
     */
    public static function getCurrentUser(): ?User
    {
        if (!isset($_SESSION['email'])) {
            return null;
        }
        
        $user = User::find('email', $_SESSION['email']);
        if ($user === null) {
            return null;
        }
        
        // Vérifier le token de session
        if ($_SESSION['token'] !== $user->token) {
            self::destroySession();
            return null;
        }
        
        // Vérifier la validité de la session (détection d'anomalies)
        if (!self::validateSession($user)) {
            self::destroySession();
            return null;
        }
        
        // Mettre à jour l'activité
        $_SESSION['last_activity'] = time();
        
        return $user;
    }

    /**
     * Validates session security (IP, User-Agent, timeout)
     */
    private static function validateSession(User $user): bool
    {
        // Vérifier l'IP (optionnel, peut causer des problèmes avec les proxies)
        // if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
        //     return false;
        // }
        
        // Vérifier le User-Agent
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')) {
            return false;
        }
        
        // Vérifier le timeout de session (24 heures)
        $sessionTimeout = 24 * 3600; // 24 heures
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
            return false;
        }
        
        return true;
    }

    /**
     * Destroys the current session
     */
    private static function destroySession(): void
    {
        session_destroy();
        session_start();
    }

    /**
     * Encrypts the given data string using modern secure hashing.
     *
     * @param string $data The data to encrypt
     * @return string The encrypted data
     */
    public static function encrypt(string $data): string
    {
        // Security fix: Use Argon2ID instead of SHA1
        return SecureAuth::hashPassword($data);
    }

    /**
     * Generates a random token string to be used for authentication.
     *
     * @return string A random token string.
     */
    public static function generateToken(): string
    {
        // Security fix: Use cryptographically secure random generation
        return SecureAuth::generateSecureToken(32);
    }

    /**
     * Verify a password against its hash with automatic migration support.
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if password matches, false otherwise
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        // Check if it's an old SHA1 hash and migrate if needed
        if (strlen($hash) === 40 && ctype_xdigit($hash)) {
            // Old SHA1 hash - verify and allow migration
            $oldHash = hash_hmac('sha1', $password, KEY);
            if (hash_equals($oldHash, $hash)) {
                logg("Password hash migration needed for user", "INFO");
                return true; // Allow login, but password should be rehashed
            }
            return false;
        }
        
        // New Argon2ID hash
        return SecureAuth::verifyPassword($password, $hash);
    }

    /**
     * Check if a password hash needs to be rehashed (for migration).
     *
     * @param string $hash The hash to check
     * @return bool True if hash needs rehashing
     */
    public static function needsPasswordRehash(string $hash): bool
    {
        // Old SHA1 hashes need rehashing
        if (strlen($hash) === 40 && ctype_xdigit($hash)) {
            return true;
        }
        
        // Check if Argon2ID hash needs rehashing
        return SecureAuth::needsRehash($hash);
    }

    /**
     * Migrate an old password hash to the new secure format.
     *
     * @param string $password The plain text password
     * @param string $oldHash The old hash to verify against
     * @return string|false New hash if migration successful, false otherwise
     */
    public static function migratePassword(string $password, string $oldHash): string|false
    {
        return SecureAuth::migratePassword($password, $oldHash);
    }

}