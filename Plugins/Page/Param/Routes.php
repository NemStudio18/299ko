<?php

Use Common\Router\Router;

/**
 * @copyright (C) 2023, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */

defined('ROOT') OR exit('Access denied!');

$router = router::getInstance();

$router->map('GET|POST', '/page[/?]', '\Plugins\Page\Controllers\PageController#home', 'page-home');
$router->map('GET|POST', '/page/[*:name]-[i:id][/?]', '\Plugins\Page\Controllers\PageController#read', 'page-read');

$router->map('GET', '/admin/page[/?]', '\Plugins\Page\Controllers\PageAdminController#list', 'page-admin-home');
$router->map('GET', '/admin/page/new', '\Plugins\Page\Controllers\PageAdminController#new', 'page-admin-new');
$router->map('POST', '/admin/page/save', '\Plugins\Page\Controllers\PageAdminController#save', 'page-admin-save');
$router->map('GET', '/admin/page/edit/[a:id]', '\Plugins\Page\Controllers\PageAdminController#edit', 'page-admin-edit');
$router->map('GET', '/admin/page/new-parent', '\Plugins\Page\Controllers\PageAdminController#newParent', 'page-admin-new-parent');
$router->map('GET', '/admin/page/new-link', '\Plugins\Page\Controllers\PageAdminController#newLink', 'page-admin-new-link');
$router->map('GET', '/admin/page/maintenance/[a:id]/[a:token]', '\Plugins\Page\Controllers\PageAdminController#maintenance', 'page-admin-maintenance');
$router->map('GET', '/admin/page/delete/[a:id]/[a:token]', '\Plugins\Page\Controllers\PageAdminController#delete', 'page-admin-delete');
$router->map('GET', '/admin/page/page-up/[a:id]/[a:token]', '\Plugins\Page\Controllers\PageAdminController#pageUp', 'page-admin-page-up');
$router->map('GET', '/admin/page/page-down/[a:id]/[a:token]', '\Plugins\Page\Controllers\PageAdminController#pageDown', 'page-admin-page-down');