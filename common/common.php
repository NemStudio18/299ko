<?php
namespace Common;

/**
 * @copyright (C) 2024, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
session_start();
defined('ROOT') or exit('Access denied!');

define('BASE_PATH', rtrim(dirname($_SERVER['SCRIPT_NAME']), DIRECTORY_SEPARATOR));

include_once(ROOT . 'common/config.php');

// Définir les préfixes pour l'autoloader
$prefixes = [
    'Common\\' => ROOT . '/Common/',
    'Plugins\\' => ROOT . '/Plugins/',
];

spl_autoload_register(function ($class) use ($prefixes) {
    // Gestion des namespaces PSR-4
    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }

    // Gestion des classes sans namespace dans COMMON
/*     $className = strtolower($class);
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(COMMON));
    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $fileName = strtolower($file->getFilename());
            if ($fileName === $className . '.php' || $fileName === $className . '.class.php') {
                require_once $file;
                return;
            }
        }
    } */
});

use Common\Router\Router;
use function Common\Router\generate;
use Common\{Core, PluginsManager, Lang, Template};

$router = Router::getInstance();
$core = Core::getInstance();

$pluginsManager = pluginsManager::getInstance();
foreach ($pluginsManager->getPlugins() as $plugin) {
	if ($plugin->getConfigVal('activate')) {
		$plugin->loadLangFile();
		$plugin->loadRoutes();
		if ($plugin->getLibFile() !== false) {
			include_once($plugin->getLibFile());
		}
		foreach ($plugin->getHooks() as $name => $function) {
			$core->addHook($name, $function);
		}
	}
}

lang::loadLanguageFile(THEMES . $core->getConfigVal('theme') . '/langs/');
$themeFunctionsFile = THEMES . $core->getConfigVal('theme') . '/functions.php';
if (file_exists($themeFunctionsFile)) {
	include_once($themeFunctionsFile);
}

## $runPLugin représente le plugin en cours d'execution et s'utilise avec la classe plugin & pluginsManager
$runPlugin = $pluginsManager->getPlugin($core->getPluginToCall());

Template::addGlobal('COMMON', COMMON);
Template::addGlobal('DATA', DATA);
Template::addGlobal('UPLOAD', UPLOAD);
Template::addGlobal('DATA_PLUGIN', DATA_PLUGIN);
Template::addGlobal('THEMES', THEMES);
Template::addGlobal('PLUGINS', PLUGINS);
Template::addGlobal('THEME_PATH', THEMES . $core->getConfigVal('theme') . '/');
Template::addGlobal('SITE_URL', $core->getConfigVal('siteUrl'));
Template::addGlobal('ADMIN_URL', $router->generate('admin'));
Template::addGlobal('VERSION', VERSION);
Template::addGlobal('runPlugin', $runPlugin);
Template::addGlobal('ROUTER', $router);
Template::addGlobal('pluginsManager', $pluginsManager);
Template::addGlobal('CORE', $core);
Template::addGlobal('ADMIN_PATH', ADMIN_PATH);
Template::addGlobal('Lang', new Lang());
