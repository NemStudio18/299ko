<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

abstract class Controller {
    
    /**
     * Core instance
     * @var core
     */
    protected core $core;
    
    /**
     * Router instance
     * @var router
     */
    protected router $router;

    /**
     * pluginsManager instance
     * @var pluginsManager
     */
    protected pluginsManager $pluginsManager;

    /**
     * Request instance
     * @var Request
     */
    protected Request $request;

    /**
     * SLogger instance
     * @var Logger
     */
    protected Logger $logger;

    /**
     * JSON data sent by fetch, used for API
     * @var array
     */
    protected array $jsonData = [];
    
    /**
     * CSRF protection enabled for this controller
     * @var bool
     */
    protected bool $csrfProtectionEnabled = true;
    
    public function __construct() {
        $this->core = core::getInstance();
        $this->router = router::getInstance();
        $this->pluginsManager = pluginsManager::getInstance();
        $this->request = new Request();
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $this->jsonData = json_decode($content, true);
        }
        $this->logger = $this->core->getLogger();
        
        // Initialize CSRF protection
        $this->initializeCSRFProtection();
    }
    
    /**
     * Initialize CSRF protection for this controller.
     */
    protected function initializeCSRFProtection(): void
    {
        if ($this->csrfProtectionEnabled && CSRFProtection::isProtectedRequest()) {
            try {
                CSRFProtection::requireValidToken();
            } catch (SecurityException $e) {
                $this->handleCSRFViolation($e);
            }
        }
    }
    
    /**
     * Handle CSRF violation by logging and responding appropriately.
     * 
     * @param SecurityException $e The security exception
     */
    protected function handleCSRFViolation(SecurityException $e): void
    {
        // Log the security violation
        $this->logger->log("CSRF violation detected: " . $e->getMessage(), "WARNING");
        
        // Return appropriate response based on request type
        if ($this->isAjaxRequest()) {
            $this->sendJsonError('Invalid CSRF token', 403);
        } else {
            $this->sendErrorResponse('Invalid CSRF token', 403);
        }
    }
    
    /**
     * Check if the current request is an AJAX request.
     * 
     * @return bool True if AJAX request
     */
    protected function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Send JSON error response.
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     */
    protected function sendJsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
    
    /**
     * Send error response.
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     */
    protected function sendErrorResponse(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo $message;
        exit;
    }
    
    /**
     * Get CSRF token for forms.
     * 
     * @return string The CSRF token
     */
    protected function getCSRFToken(): string
    {
        return CSRFProtection::getToken();
    }
    
    /**
     * Get CSRF token field for forms.
     * 
     * @return string HTML input field for CSRF token
     */
    protected function getCSRFTokenField(): string
    {
        return CSRFProtection::getTokenField();
    }
    
    /**
     * Get CSRF token meta tag for AJAX.
     * 
     * @return string HTML meta tag for CSRF token
     */
    protected function getCSRFTokenMeta(): string
    {
        return CSRFProtection::getTokenMeta();
    }
}