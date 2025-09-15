<?php
/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxime Blanc <nemstudio18@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

defined('ROOT') or exit('Access denied!');

$router = router::getInstance();

// Homepage
$router->map('GET', '/admin/marketplace[/?]', 'AdminMarketplaceController#index', 'admin-marketplace');

// Plugins list
$router->map('GET', '/admin/marketplace/plugins[/?]', 'PluginsMarketController#index', 'marketplace-plugins');

// Themes list
$router->map('GET', '/admin/marketplace/themes[/?]', 'ThemesMarketController#index', 'marketplace-themes');

// Ressource install
$router->map('GET', '/admin/marketplace/install/[a:type]/[a:slug]/[a:token][/?]', 'AdminMarketplaceController#installRelease', 'marketplace-install-release');

// Ressource uninstall
$router->map('GET', '/admin/marketplace/uninstall/[a:type]/[a:slug]/[a:token][/?]', 'AdminMarketplaceController#uninstallRessource', 'marketplace-uninstall-ressource');