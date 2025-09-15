<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class ConfigManagerBackupsManager {

    public static function getAll():array {
        $backups = [];
        $backupFiles = [];
        $files = scandir(DATA_PLUGIN . 'configmanager' . DS);
        foreach ($files as $file) {
            if (preg_match('/backup-.*\.zip/i', $file)) {
                $backupFiles[] = $file;
            }
        }
        foreach ($backupFiles as $file) {
            $obj = new ConfigManagerBackup($file);
            $timestamp = util::getTimestampFromDate($obj->date);
            $backups[$timestamp] = $obj;
        }
        krsort($backups, SORT_NUMERIC);
        return $backups;
    }
}