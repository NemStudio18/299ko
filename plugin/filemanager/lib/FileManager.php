<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

require_once(PLUGINS . 'filemanager/lib/File.php');
require_once(PLUGINS . 'filemanager/lib/Folder.php');

class FileManager {
    
    /**
     * @var string Current directory
     */
    protected string $directory = '';
    
    /**
     * @var array Children directories
     */
    protected array $subDir = [];

    /**
     * @var array Children files
     */
    protected array $subFiles = [];
    
    public function __construct($directory) {
        $this->directory = trim($directory, '/') . '/';
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755);
        }
        $this->hydrateChildren();
    }
    
    protected function hydrateChildren() {
        $fileList = glob($this->directory . "*");
        for ($v = 0; $v < sizeof($fileList); $v++) {
            $name = str_replace($this->directory, "", $fileList[$v]);
            if (is_dir($fileList[$v])) {
                $this->subDir[$name] = new Folder($name, $this->directory);
            } else {
                $this->subFiles[$name] = new File($name, $this->directory);
            }
        }
    }
    
    public function getFolders() {
        return $this->subDir;
    }
    
    public function getFiles() {
        return $this->subFiles;
    }
    
    public function uploadFile($arrayName) {
        $file = $_FILES[$arrayName];
        
        // Security fix for CVE-2025-8265: Comprehensive file validation
        if (!$this->validateUploadedFile($file)) {
            logg("Invalid file upload attempt: " . ($file['name'] ?? 'unknown'), "WARNING");
            return false;
        }
        
        $fileName = util::strToUrl(pathinfo($file['name'], PATHINFO_FILENAME));
        $fileExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Generate secure filename with random component
        $secureFileName = $this->generateSecureFileName($fileName, $fileExt);
        
        if (move_uploaded_file($file['tmp_name'], $this->directory . $secureFileName)) {
            $file = new File($secureFileName, $this->directory);
            return $file->getUrl();
        } else {
            logg("Failed to move uploaded file: " . $file['name'], "ERROR");
            return false;
        }
    }
    
    /**
     * Comprehensive file validation to prevent malicious uploads
     * 
     * @param array $file The $_FILES array element
     * @return bool True if file is valid, false otherwise
     */
    private function validateUploadedFile($file) {
        // Check if file was uploaded via HTTP POST
        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size (max 10MB)
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxFileSize) {
            logg("File too large: " . $file['name'] . " (" . $file['size'] . " bytes)", "WARNING");
            return false;
        }
        
        // Check file name
        if (empty($file['name']) || strlen($file['name']) > 255) {
            return false;
        }
        
        // Sanitize filename
        $fileName = $file['name'];
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        if (empty($fileName)) {
            return false;
        }
        
        // Get file extension and MIME type
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeType = $this->getMimeType($file['tmp_name']);
        
        // Allowed file types (whitelist approach)
        $allowedTypes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'bmp' => ['image/bmp'],
            'ico' => ['image/x-icon', 'image/vnd.microsoft.icon'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml'],
            'pdf' => ['application/pdf'],
            'txt' => ['text/plain'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'zip' => ['application/zip'],
            'rar' => ['application/x-rar-compressed']
        ];
        
        // Check if extension is allowed
        if (!isset($allowedTypes[$extension])) {
            logg("Disallowed file extension: " . $extension . " for file: " . $fileName, "WARNING");
            return false;
        }
        
        // Check MIME type
        if (!in_array($mimeType, $allowedTypes[$extension])) {
            logg("MIME type mismatch: " . $mimeType . " for extension: " . $extension . " file: " . $fileName, "WARNING");
            return false;
        }
        
        // Additional content validation for images
        if (strpos($mimeType, 'image/') === 0) {
            if (!$this->validateImageContent($file['tmp_name'], $extension)) {
                logg("Invalid image content: " . $fileName, "WARNING");
                return false;
            }
        }
        
        // Check for dangerous file patterns
        if ($this->containsDangerousPatterns($file['tmp_name'])) {
            logg("Dangerous file pattern detected: " . $fileName, "WARNING");
            return false;
        }
        
        return true;
    }
    
    /**
     * Get MIME type of a file
     * 
     * @param string $filePath Path to the file
     * @return string MIME type
     */
    private function getMimeType($filePath) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        } elseif (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        } else {
            // Fallback to extension-based detection
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/x-icon',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'pdf' => 'application/pdf',
                'txt' => 'text/plain',
                'zip' => 'application/zip'
            ];
            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }
    }
    
    /**
     * Validate image content to ensure it's a real image
     * 
     * @param string $filePath Path to the file
     * @param string $extension File extension
     * @return bool True if valid image, false otherwise
     */
    private function validateImageContent($filePath, $extension) {
        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }
        
        // Check if the detected type matches the extension
        $detectedType = $imageInfo[2];
        $expectedTypes = [
            'jpg' => IMAGETYPE_JPEG,
            'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'gif' => IMAGETYPE_GIF,
            'bmp' => IMAGETYPE_BMP,
            'webp' => IMAGETYPE_WEBP
        ];
        
        if (isset($expectedTypes[$extension]) && $detectedType !== $expectedTypes[$extension]) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for dangerous patterns in file content
     * 
     * @param string $filePath Path to the file
     * @return bool True if dangerous patterns found, false otherwise
     */
    private function containsDangerousPatterns($filePath) {
        $content = file_get_contents($filePath, false, null, 0, 1024); // Read first 1KB
        if ($content === false) {
            return true; // Consider unreadable files as dangerous
        }
        
        $dangerousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/eval\(/i',
            '/base64_decode\(/i',
            '/system\(/i',
            '/exec\(/i',
            '/shell_exec\(/i',
            '/passthru\(/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate a secure filename with random component
     * 
     * @param string $originalName Original filename
     * @param string $extension File extension
     * @return string Secure filename
     */
    private function generateSecureFileName($originalName, $extension) {
        // Generate random component
        $randomComponent = bin2hex(random_bytes(16));
        $timestamp = time();
        
        // Create secure filename
        $secureName = substr($originalName, 0, 50) . '_' . $timestamp . '_' . $randomComponent . '.' . $extension;
        
        return $secureName;
    }
    
    public function deleteFile($filename) {
        if (isset($this->subFiles[$filename])) {
            $error = $this->subFiles[$filename]->delete();
            if (!$error) {
                unset($this->subFiles[$filename]);
                return true;
            }
        }
        return false;
    }
    
    public function deleteFolder($foldername) {
        if (isset($this->subDir[$foldername])) {
            $error = $this->subDir[$foldername]->delete();
            if (!$error) {
                unset($this->subDir[$foldername]);
                return true;
            }
        }
        return false;
    }
    
    public function deleteAllFiles() {
        $error = false;
        foreach ($this->subFiles as $file) {
            if (!$file->delete()) {
                $error = true;
            }
        }
        return $error;
    }
    
    public function deleteAllFolders() {
        $error = false;
        foreach ($this->subDir as $folder) {
            if (!$folder->delete()) {
                $error = true;
            }
        }
        return $error;
    }
    
    public function createFolder($name) {
        return mkdir($this->directory . $name, 0755);
    }
}
