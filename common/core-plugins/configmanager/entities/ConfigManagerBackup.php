<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class ConfigManagerBackup extends Zip {

    public string $filename;

    public string $date;

    public int $timestamp = 0;

    public string $url;

    public function __construct($filename) {
        parent::__construct(DATA_PLUGIN . 'configmanager' . DS . $filename);
        if (preg_match('/backup-(\d{4})-(\d{2})-(\d{2})-(\d{2})-(\d{2})-(\d{2})/', $filename, $matches)) {
            $this->date = "{$matches[1]}-{$matches[2]}-{$matches[3]} {$matches[4]}:{$matches[5]}:{$matches[6]}";
            $this->timestamp = util::getTimestampFromDate($this->date);
        } else {
            throw new Exception("Invalid backup filename: $filename");
        }
        $this->url = router::getInstance()->generate('configmanager-dl-backup', [
            'token' => UsersManager::getCurrentUser()->token,
            'timestamp' => $this->timestamp
        ]);
    }

    public function delete() {
        return unlink( $this->filename);
    }
}