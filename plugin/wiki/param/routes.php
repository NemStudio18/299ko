<?php
defined('ROOT') or exit('Access denied!');

$router = ROUTER::getInstance();

// Route pour afficher une page Wiki côté public
$router->map('GET', '/wiki[/?]', 'WikiPageController#view', 'wiki-view');

// Routes pour l'administration du Wiki
$router->map('GET', '/admin/wiki[/?]', 'AdminWikiController#index', 'admin-wiki');
$router->map('GET', '/admin/wiki/edit[/?]', 'AdminWikiController#edit', 'admin-wiki-edit');
$router->map('POST', '/admin/wiki/save[/?]', 'AdminWikiController#save', 'admin-wiki-save');
