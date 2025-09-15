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
 * CSRFProtection provides comprehensive CSRF (Cross-Site Request Forgery) protection.
 * 
 * This class handles token generation, validation, and provides utilities for
 * integrating CSRF protection into forms and AJAX requests.
 */
class CSRFProtection
{
    /**
     * Session key for storing CSRF token data
     */
    private const TOKEN_KEY = 'csrf_token';
    
    /**
     * Name of the CSRF token field in forms
     */
    private const TOKEN_NAME = 'csrf_token';
    
    /**
     * Token lifetime in seconds (1 hour)
     */
    private const TOKEN_LIFETIME = 3600;
    
    /**
     * Generate a new CSRF token and store it in the session.
     * 
     * @return string The generated CSRF token
     */
    public static function generateToken(): string
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate cryptographically secure token
        $token = bin2hex(random_bytes(32));
        
        // Store token in session with timestamp
        $_SESSION[self::TOKEN_KEY] = [
            'token' => $token,
            'timestamp' => time()
        ];
        
        return $token;
    }
    
    /**
     * Get the current CSRF token, generating a new one if none exists.
     * 
     * @return string The current CSRF token
     */
    public static function getToken(): string
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists and is not expired
        if (isset($_SESSION[self::TOKEN_KEY])) {
            $tokenData = $_SESSION[self::TOKEN_KEY];
            
            // Check if token is expired
            if (time() - $tokenData['timestamp'] < self::TOKEN_LIFETIME) {
                return $tokenData['token'];
            }
        }
        
        // Generate new token if none exists or expired
        return self::generateToken();
    }
    
    /**
     * Validate a CSRF token.
     * 
     * @param string $token The token to validate
     * @return bool True if token is valid, false otherwise
     */
    public static function validateToken(string $token): bool
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }
        
        // Check if token exists in session
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }
        
        $tokenData = $_SESSION[self::TOKEN_KEY];
        
        // Check if token is expired
        if (time() - $tokenData['timestamp'] >= self::TOKEN_LIFETIME) {
            // Token expired, remove it
            unset($_SESSION[self::TOKEN_KEY]);
            return false;
        }
        
        // Validate token using constant-time comparison
        return hash_equals($tokenData['token'], $token);
    }
    
    /**
     * Validate CSRF token from request (supports multiple sources).
     * 
     * @return bool True if valid token found, false otherwise
     */
    public static function validateRequest(): bool
    {
        $token = self::getTokenFromRequest();
        
        if ($token === null) {
            return false;
        }
        
        return self::validateToken($token);
    }
    
    /**
     * Require a valid CSRF token, throw exception if invalid.
     * 
     * @throws SecurityException If token is invalid or missing
     */
    public static function requireValidToken(): void
    {
        if (!self::validateRequest()) {
            throw new SecurityException('Invalid or missing CSRF token');
        }
    }
    
    /**
     * Check if the current request should be protected by CSRF.
     * 
     * @return bool True if request should be protected
     */
    public static function isProtectedRequest(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Protect POST, PUT, DELETE, PATCH requests
        return in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH']);
    }
    
    /**
     * Get CSRF token from various request sources.
     * 
     * @return string|null The token if found, null otherwise
     */
    private static function getTokenFromRequest(): ?string
    {
        // Check POST data first
        if (isset($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }
        
        // Check GET data
        if (isset($_GET[self::TOKEN_NAME])) {
            return $_GET[self::TOKEN_NAME];
        }
        
        // Check JSON request body
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (isset($data[self::TOKEN_NAME])) {
                return $data[self::TOKEN_NAME];
            }
        }
        
        // Check custom header
        $headers = getallheaders();
        if (isset($headers['X-CSRF-Token'])) {
            return $headers['X-CSRF-Token'];
        }
        
        return null;
    }
    
    /**
     * Generate HTML input field for CSRF token.
     * 
     * @return string HTML input field
     */
    public static function getTokenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Generate HTML meta tag for CSRF token (useful for AJAX).
     * 
     * @return string HTML meta tag
     */
    public static function getTokenMeta(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Get template data for CSRF protection.
     * 
     * @return array Template data
     */
    public static function getTemplateData(): array
    {
        return [
            'csrf_token' => self::getToken(),
            'csrf_token_field' => self::getTokenField(),
            'csrf_token_meta' => self::getTokenMeta(),
            'csrf_token_name' => self::TOKEN_NAME
        ];
    }
    
    /**
     * Clear the current CSRF token from session.
     */
    public static function clearToken(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[self::TOKEN_KEY]);
        }
    }
}

/**
 * SecurityException is thrown when security violations are detected.
 */
class SecurityException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
