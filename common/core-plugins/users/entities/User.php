<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class User extends JsonActiveRecord {

    protected static string $filePath = DATA_PLUGIN . 'users/users.json';

    protected static string $primaryKey = 'id';

    public function save(): bool
    {
        $this->attributes['token'] = $this->attributes['token'] ?? UsersManager::generateToken();
        return parent::save();
    }

    /**
     * Checks if the user is authorized based on the request token matching the user's token.
     *
     * Checks if a 'token' parameter is present in the request (URL params or POST data).
     * If so, returns true if it matches the user's token, false otherwise.
     *
     * Also checks the $_REQUEST global for a 'token' value and compares it to the user's token.
     *
     * Returns false if no token is present.
     */
    public function isAuthorized(): bool
    {
        $matches = router::getInstance()->match();
        if (isset($matches['params']['token'])) {
            if ($matches['params']['token'] === $this->attributes['token']) {
                return true;
            }
            return false;
        }
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $data = json_decode($content, true);
            if (isset($data['token'])) {
                if ($data['token'] === $this->attributes['token']) {
                    return true;
                }
                return false;
            }
        }
        if (!isset($_REQUEST['token']))
            return false;
        if ($_REQUEST['token'] != $this->attributes['token'])
            return false;
        return true;
    }


}