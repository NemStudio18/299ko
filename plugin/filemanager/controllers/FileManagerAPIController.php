<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class FileManagerAPIController extends AdminController {

    protected ?string $dir = null;

    protected array $dirParts = [];

    protected string $fullDir = '';

    protected bool $ajaxView = false;

    protected bool $api = false;

    protected FileManager $filemanager;

    protected $editor = false;

    public function home() {
        return $this->render();
    }

    public function view() {
        return $this->render();
    }

    public function upload($token) {
        if (!$this->user->isAuthorized()) {
            echo json_encode(['success' => 0, 'error' => 'Unauthorized']);
            die();
        }
        
        // Security fix for CVE-2025-8265: Additional validation before upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => 0, 'error' => 'No valid file uploaded']);
            die();
        }
        
        $this->getSentDir();
        $this->filemanager = new FileManager($this->fullDir);
        
        if (isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
            $result = $this->filemanager->uploadFile('image');
            if ($result !== false) {
                echo json_encode(['success' => 1, 'url' => $result]);
                die();
            } else {
                echo json_encode(['success' => 0, 'error' => 'Upload failed - file validation failed']);
                die();
            }
        } else {
            echo json_encode(['success' => 0, 'error' => 'No file specified']);
            die();
        }
    }

    public function uploadAPI($token) {
        if (!$this->user->isAuthorized()) {
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(['error' => 'Unauthorized']);
            die();
        }
        
        $temp = current($_FILES);
        if (!is_uploaded_file($temp['tmp_name'])) {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'No valid file uploaded']);
            die();
        }
        
        // Security fix for CVE-2025-8265: Use secure FileManager for all uploads
        $tinyManager = new FileManager(UPLOAD . 'files/API');
        
        // Create a temporary $_FILES array for the secure upload method
        $_FILES['file'] = $temp;
        
        $uploaded = $tinyManager->uploadFile('file');
        if ($uploaded !== false) {
            echo json_encode(['location' => $uploaded]);
            die();
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'File validation failed']);
            die();
        }
    }

    public function delete($token) {
        if (!$this->user->isAuthorized()) {
            echo json_encode(['success' => 0]);
            die();
        }
        $this->getSentDir();
        $this->filemanager = new FileManager($this->fullDir);
        if (isset($_POST['filename'])) {
            // Delete File
            $deleted = $this->filemanager->deleteFile($_POST['filename']);
            echo json_encode(['success' => $deleted]);
            die();
        } elseif (isset($_POST['foldername'])) {
            $deleted = $this->filemanager->deleteFolder($_POST['foldername']);
            echo json_encode(['success' => $deleted]);
            die();
        }
    }

    public function create($token) {
        if (!$this->user->isAuthorized()) {
            echo json_encode(['success' => 0]);
            die();
        }
        $this->getSentDir();
        $this->filemanager = new FileManager($this->fullDir);
        $created = $this->filemanager->createFolder($_POST['folderName']);
        echo json_encode(['success' => $created]);
        die();
    }

    public function viewAjax() {
        if (!$this->user->isAuthorized()) {
            echo json_encode(['success' => 0]);
            die();
        }
        $this->ajaxView = true;
        $this->editor = $_POST['editor'];

        return $this->render();
    }

    public function viewAjaxHome($token, $editor = false) {
        if (!$this->user->isAuthorized()) {
            echo json_encode(['success' => 0]);
            die();
        }
        if ($editor === ''){
            $editor = false;
        }
        $this->editor = $editor;
        $this->ajaxView = true;
        $this->dir = '';
        return $this->render();
    }

    protected function render() {
        $this->getSentDir();
        $this->filemanager = new FileManager($this->fullDir);
        if ($this->ajaxView) {
            $response = new StringResponse();
        } else {
            $response = new AdminResponse();
        }
        $tpl = $response->createPluginTemplate('filemanager', 'listview');

        $tpl->set('token', $this->user->token);
        $tpl->set('dir', $this->dir);
        $tpl->set('dirParts', $this->dirParts);
        $tpl->set('manager', $this->filemanager);
        $tpl->set('ajaxView', $this->ajaxView);
        $tpl->set('uploadUrl', $this->router->generate('filemanager-upload', ['token' => $this->user->token]));
        $tpl->set('deleteUrl', $this->router->generate('filemanager-delete', ['token' => $this->user->token]));
        $tpl->set('createUrl', $this->router->generate('filemanager-create', ['token' => $this->user->token]));
        $tpl->set('redirectUrl', $this->router->generate('filemanager-view'));
        $tpl->set('redirectAjaxUrl', $this->router->generate('filemanager-view-ajax'));
        $tpl->set('editor', $this->editor);

        $response->addTemplate($tpl);
        return $response;
    }

    protected function getSentDir() {
        if (!isset($this->dir)) {
            $this->dir = $_POST['fmFolderToSee'] ?? '';
        }
        if ($this->dir === 'Back%To%Home%') {
            $this->dir = '';
        }
        
        // Security fix for CVE-2025-10232: Path traversal prevention
        $this->dir = $this->sanitizeDirectoryPath($this->dir);
        
        $this->dir = trim($this->dir, '/');
        if ($this->dir !== '') {
            $this->dirParts = explode('/', $this->dir);
            if (end($this->dirParts) === '..') {
                // Up to parent folder
                array_pop($this->dirParts);
                array_pop($this->dirParts);
            }

            $this->dir = implode('/', $this->dirParts);
        } else {
            $this->dirParts = [];
        }
        
        // Additional security: ensure the final path is within allowed directory
        $this->fullDir = $this->validateAndBuildFullPath($this->dir);
    }
    
    /**
     * Sanitize directory path to prevent path traversal attacks
     * 
     * @param string $path The input path to sanitize
     * @return string Sanitized path
     */
    private function sanitizeDirectoryPath($path) {
        // Remove any path traversal attempts
        $path = str_replace(['../', '..\\', '..%2F', '..%5C'], '', $path);
        $path = str_replace(['%2E%2E%2F', '%2E%2E%5C'], '', $path);
        
        // Decode URL encoding
        $path = urldecode($path);
        
        // Remove any remaining path traversal attempts after decoding
        $path = str_replace(['../', '..\\'], '', $path);
        
        // Remove any null bytes or other dangerous characters
        $path = str_replace(["\0", "\x00"], '', $path);
        
        // Remove leading/trailing slashes and normalize
        $path = trim($path, '/\\');
        
        return $path;
    }
    
    /**
     * Validate and build the full directory path ensuring it stays within allowed bounds
     * 
     * @param string $dir The directory path to validate
     * @return string The validated full directory path
     */
    private function validateAndBuildFullPath($dir) {
        $baseDir = UPLOAD . 'files/';
        $fullPath = $baseDir . $dir;
        
        // Resolve the real path to prevent any remaining traversal
        $realBaseDir = realpath($baseDir);
        $realFullPath = realpath($fullPath);
        
        // If realpath fails or the resolved path is not within the base directory, use base directory
        if ($realFullPath === false || strpos($realFullPath, $realBaseDir) !== 0) {
            logg("Path traversal attempt detected in FileManager: " . $dir, "WARNING");
            return $baseDir;
        }
        
        return $realFullPath . '/';
    }

}