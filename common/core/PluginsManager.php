<?php

/**
 * @copyright (C) 2025, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

class pluginsManager {

    private $plugins;
    private static $instance = null;

    ## Constructeur

    public function __construct() {
        $this->plugins = $this->listPlugins();
    }

    /**
     * Returns the list of plugins
     * 
     * @return array
     */
    public function getPlugins() {
        return $this->plugins;
    }

    /**
     * Return a plugin by its name
     * Return false if not found
     * 
     * @param string $name
     * @return plugin|false
     */
    public function getPlugin($name) {
        foreach ($this->plugins as $plugin) {
            if ($plugin->getName() == $name)
                return $plugin;
        }
        return false;
    }

    ## Sauvegarde la configuration d'un objet plugin

    public function savePluginConfig($obj) {
        if ($obj->getIsValid() && $path = $obj->getDataPath()) {
            return util::writeJsonFile($path . 'config.json', $obj->getConfig());
        }
    }

    /**
     * Installs a plugin
     * 
     * @param string $name Plugin name
     * @param bool $activate true to activate the plugin, false otherwise
     * 
     * @return bool true if the plugin has been successfully installed, false otherwise
     */
    public function installPlugin($name, $activate = false) {
        if (!is_dir(DATA_PLUGIN . $name)) {
            mkdir(DATA_PLUGIN . $name . DS, 0755);
        }
        chmod(DATA_PLUGIN . $name . DS, 0755);
        
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $name . DS;
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $name . DS;
        }
        
        // Read original plugin config
        $config = util::readJsonFile($pluginPath . 'param' . DS . 'config.json');
        // By default, the plugin is not activated
        if ($activate)
            $config['activate'] = 1;
        else
            $config['activate'] = 0;
        // Write plugin config
        util::writeJsonFile(DATA_PLUGIN . $name . DS . 'config.json', $config);
        chmod(DATA_PLUGIN . $name . DS .'config.json', 0644);
        // Call install function if exists
        if (file_exists($pluginPath . $name . '.php')) {
            require_once ($pluginPath . $name . '.php');
            if (function_exists($name . 'Install')) {
                logg("Call function '" . $name . "Install'", "info");
                call_user_func($name . 'Install');
            }
        }
        // Check if plugin is installed
        if (!file_exists(DATA_PLUGIN . $name . DS . 'config.json')) {
            logg("Plugin $name can't be installed", "error");
            return false;
        }
        logg("Plugin $name successfully installed", "info");
        return true;
    }

    /**
     * Uninstalls a plugin by its name.
     * 
     * This method performs the following actions:
     * - Calls the plugin's uninstall function, if it exists.
     * - Removes the plugin's data directory.
     * - Removes the plugin's directory.
     * - Logs the uninstallation process.
     *
     * @param string $name The name of the plugin to uninstall.
     * @return bool Returns true if the plugin was successfully uninstalled.
     */
    public function uninstallPlugin(string $name) {
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $name . DS;
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $name . DS;
        }
        
        // Call uninstall function if exists
        if (file_exists($pluginPath . $name . '.php')) {
            require_once ($pluginPath . $name . '.php');
            if (function_exists($name . 'Uninstall')) {
                logg("Call function '" . $name . "Uninstall'", "info");
                call_user_func($name . 'Uninstall');
            }
        }
        // Remove plugin data
        if (is_dir(DATA_PLUGIN . $name)) {
            util::delTree(DATA_PLUGIN . $name);
        }
        // Only remove plugin directory if it's in regular plugins (not core-plugins)
        if (is_dir(PLUGINS . $name)) {
            util::delTree(PLUGINS . $name);
        }
        logg("Plugin $name successfully uninstalled", "info");
        return true;
    }
        

    /**
     * Return the singleton instance
     * 
     * @return \self
     */
    public static function getInstance() {
        if (is_null(self::$instance))
            self::$instance = new pluginsManager();
        return self::$instance;
    }

    /**
     * Retrieves a specific configuration value from a plugin.
     *
     * This function accesses a plugin's configuration by its name and 
     * returns the value of the specified configuration key.
     *
     * @param string $pluginName The name of the plugin.
     * @param string $kConf The configuration key to retrieve the value for.
     * @return mixed The value of the configuration key, or false if not found.
     */
    public static function getPluginConfVal($pluginName, $kConf) {
        $instance = self::getInstance();
        $plugin = $instance->getPlugin($pluginName);
        return $plugin->getConfigVal($kConf);
    }

    ## Détermine si le plugin ciblé existe et s'il est actif

    public static function isActivePlugin($pluginName) {
        $instance = self::getInstance();
        $plugin = $instance->getPlugin($pluginName);
        if ($plugin && $plugin->isInstalled() && $plugin->getConfigval('activate'))
            return true;
        return false;
    }

    /**
     * Creates a list of plugin objects.
     *
     * This function creates a list of all active plugins and their respective
     * configurations. It first scans both the plugins directory and core-plugins
     * directory for directories and then checks if a configuration file exists 
     * for each plugin. If a configuration file exists, it reads the file and 
     * adds the plugin to the list. If not, it adds the plugin to the list with 
     * a low priority.
     *
     * @return array An array of plugin objects.
     */
    private function listPlugins() {
        $data = [];
        $dataNotSorted = [];
        
        // Scan core plugins first (they have higher priority)
        $coreItems = util::scanDir(CORE_PLUGINS);
        foreach ($coreItems['dir'] as $dir) {
            // If the plugin is installed, get its configuration
            if (file_exists(DATA_PLUGIN . $dir . '/config.json')) {
                $dataNotSorted[$dir] = util::readJsonFile(DATA_PLUGIN . $dir . '/config.json', true);
            }
            // Otherwise, give it a low priority
            else {
                $dataNotSorted[$dir]['priority'] = '9';
            }
        }
        
        // Then scan regular plugins
        $items = util::scanDir(PLUGINS);
        foreach ($items['dir'] as $dir) {
            // If the plugin is installed, get its configuration
            if (file_exists(DATA_PLUGIN . $dir . '/config.json')) {
                $dataNotSorted[$dir] = util::readJsonFile(DATA_PLUGIN . $dir . '/config.json', true);
            }
            // Otherwise, give it a low priority
            else {
                $dataNotSorted[$dir]['priority'] = '9';
            }
        }
        
        // Sort the plugins by priority
        $dataSorted = @util::sort2DimArray($dataNotSorted, 'priority', 'num');
        foreach ($dataSorted as $plugin => $config) {
            $data[] = $this->createPlugin($plugin);
        }
        return $data;
    }

    ## Créée un objet plugin

    private function createPlugin($name) {
        // Instance du core
        $core = core::getInstance();
        
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $name . '/';
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $name . '/';
        }
        
        // Infos du plugin
        $infos = util::readJsonFile($pluginPath . 'param/infos.json');
        // Configuration du plugin
        $config = util::readJsonFile(DATA_PLUGIN . $name . '/config.json');
        // Hooks du plugin
        $hooks = util::readJsonFile($pluginPath . 'param/hooks.json');
        // Config usine
        $initConfig = util::readJsonFile($pluginPath . 'param/config.json');
        // Derniers checks
        if (!is_array($config))
            $config = [];
        if (!is_array($hooks))
            $hooks = [];
        // Création de l'objet
        $plugin = new plugin($name, $config, $infos, $hooks, $initConfig, $pluginPath);
        return $plugin;
    }

}