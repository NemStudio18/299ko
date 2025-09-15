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
 * SecureAuth provides secure authentication methods using modern cryptographic standards.
 * 
 * This class handles password hashing with Argon2ID, token generation,
 * and password strength validation.
 */
class SecureAuth
{
    /**
     * Hash a password using Argon2ID algorithm.
     * 
     * @param string $password The password to hash
     * @return string The hashed password
     */
    public static function hashPassword(string $password): string
    {
        $options = [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,        // 4 iterations
            'threads' => 3           // 3 threads
        ];
        
        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }
    
    /**
     * Verify a password against its hash.
     * 
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if password matches, false otherwise
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if a password hash needs to be rehashed (for migration purposes).
     * 
     * @param string $hash The hash to check
     * @return bool True if hash needs rehashing, false otherwise
     */
    public static function needsRehash(string $hash): bool
    {
        $options = [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ];
        
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, $options);
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
        if (self::verifyOldPassword($password, $oldHash)) {
            return self::hashPassword($password);
        }
        return false;
    }
    
    /**
     * Verify a password against an old SHA1 hash (for migration).
     * 
     * @param string $password The password to verify
     * @param string $oldHash The old SHA1 hash
     * @return bool True if password matches old hash
     */
    public static function verifyOldPassword(string $password, string $oldHash): bool
    {
        // Check if it's an old SHA1 hash (40 hex characters)
        if (strlen($oldHash) === 40 && ctype_xdigit($oldHash)) {
            $expectedHash = hash_hmac('sha1', $password, KEY);
            return hash_equals($expectedHash, $oldHash);
        }
        
        return false;
    }
    
    /**
     * Generate a cryptographically secure token.
     * 
     * @param int $length Token length in bytes (default: 32)
     * @return string The generated token
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Generate a secure remember token for "remember me" functionality.
     * 
     * @return string The generated remember token
     */
    public static function generateRememberToken(): string
    {
        return self::generateSecureToken(32);
    }
    
    /**
     * Validate password strength.
     * 
     * @param string $password The password to validate
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        // Maximum length (prevent DoS attacks)
        if (strlen($password) > 128) {
            $errors[] = 'Password must be less than 128 characters';
        }
        
        // Lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        // Uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        // Digit
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit';
        }
        
        // Special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        // Check for common patterns
        if (preg_match('/(.)\1{2,}/', $password)) {
            $errors[] = 'Password must not contain more than 2 consecutive identical characters';
        }
        
        // Check for common passwords
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey'
        ];
        
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'Password is too common, please choose a more unique password';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate a secure random string for various purposes.
     * 
     * @param int $length Desired length of the string
     * @param string $characters Character set to use (default: alphanumeric)
     * @return string The generated random string
     */
    public static function generateRandomString(int $length = 16, string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate a secure session ID.
     * 
     * @return string The generated session ID
     */
    public static function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Check if a string is a valid hash format.
     * 
     * @param string $hash The hash to check
     * @return bool True if valid hash format
     */
    public static function isValidHash(string $hash): bool
    {
        // Check for Argon2ID hash format (starts with $argon2id$)
        if (strpos($hash, '$argon2id$') === 0) {
            return true;
        }
        
        // Check for old SHA1 hash format (40 hex characters)
        if (strlen($hash) === 40 && ctype_xdigit($hash)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get password strength score (0-100).
     * 
     * @param string $password The password to score
     * @return int Password strength score (0-100)
     */
    public static function getPasswordStrength(string $password): int
    {
        $score = 0;
        $length = strlen($password);
        
        // Length scoring
        if ($length >= 8) $score += 10;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        
        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 10;
        
        // Pattern penalties
        if (preg_match('/(.)\1{2,}/', $password)) $score -= 20;
        if (preg_match('/123|abc|qwe/i', $password)) $score -= 15;
        
        // Bonus for very long passwords
        if ($length >= 20) $score += 10;
        
        return max(0, min(100, $score));
    }
}
