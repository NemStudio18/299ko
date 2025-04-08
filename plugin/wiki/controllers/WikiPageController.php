<?php
// plugin/wiki/controllers/WikiPageController.php
defined('ROOT') or exit('Access denied!');
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiPageManager.php';
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiCategoryManager.php';

class WikiPageController {
    protected $pageManager;
    protected $categoryManager;

    public function __construct() {
        $this->pageManager = new WikiPageManager(DATA_PLUGIN.'wiki'.DS.'pages'.DS);
        $this->categoryManager = WikiCategoryManager::getInstance();
    }

    public function view() {
        $router = ROUTER::getInstance();
        $pageId = $_GET['page'] ?? '';
        $catId  = $_GET['cat'] ?? '';
        $q      = isset($_GET['q']) ? trim($_GET['q']) : '';

        // Construction du menu de navigation
        $navMenu = $this->buildNavigationMenu();

        if ($pageId) {
            $page = $this->pageManager->getPage($pageId);
            if (!$page) {
                die("Page non trouvée.");
            }
            if (isset($page['draft']) && $page['draft'] === true) {
                die("Page non publiée.");
            }
            $content = $page['content'];
            $response = new PublicResponse();
            $tpl = $response->createPluginTemplate('wiki', 'public/wiki-view');
            $response->setTitle($page['title']);
            $tpl->set('pageTitle', $page['title']);
            $tpl->set('content', $content);
            $tpl->set('menu', $navMenu);
            $tpl->set('router', $router);
            $response->addTemplate($tpl);
            return $response;
        } else {
            if ($catId) {
                $pages = $this->pageManager->getPagesByCategory($catId);
            } elseif ($q) {
                $pages = $this->pageManager->searchPages($q);
            } else {
                $pages = $this->pageManager->getPagesTree();
            }
            $response = new PublicResponse();
            $tpl = $response->createPluginTemplate('wiki', 'public/wiki-list');
            $response->setTitle("Wiki");
            $tpl->set('pages', $pages);
            $tpl->set('menu', $navMenu);
            $tpl->set('router', $router);
            $tpl->set('searchQuery', $q);
            $response->addTemplate($tpl);
            return $response;
        }
    }

protected function buildNavigationMenu() {
    // Récupère l'arborescence des catégories
    $categoriesTree = $this->categoryManager->getCategoriesTree();

    // Compléter chaque catégorie avec les pages associées
    foreach ($categoriesTree as &$cat) {
        // On récupère les pages affectées à la catégorie
        $pages = $this->pageManager->getPagesByCategory($cat['id']);
        // On aplatit l'arborescence des pages pour les afficher dans la navigation
        $cat['pages'] = $this->flattenPagesTree($pages);
    }
    unset($cat); // libérer la référence

    // Récupère les pages orphelines
    $orphanPages = $this->pageManager->getOrphanPages();
    $router = ROUTER::getInstance();
    // On s'assure que l'URL générée se termine par un slash
    $baseUrl = rtrim($router->generate('wiki-view'), '/') . '/';

    $html = '<ul>';
    foreach ($categoriesTree as $cat) {
        $html .= $this->renderCategoryMenu($cat, $baseUrl);
    }
    if (!empty($orphanPages)) {
        $html .= '<li><strong>Pages</strong><ul>';
        foreach ($orphanPages as $page) {
            $html .= '<li><a href="'.$baseUrl.'?page='.$page['filename'].'">'.$page['title'].'</a></li>';
        }
        $html .= '</ul></li>';
    }
    $html .= '</ul>';
    return $html;
}

/**
 * Fonction récursive pour aplatir un arbre de pages.
 *
 * @param array $pages
 * @return array
 */
private function flattenPagesTree($pages) {
    $flat = [];
    foreach ($pages as $page) {
        $flat[] = $page;
        if (!empty($page['children'])) {
            $flat = array_merge($flat, $this->flattenPagesTree($page['children']));
        }
    }
    return $flat;
}


protected function renderCategoryMenu($cat, $baseUrl) {
    $html = '<li><a href="'.$baseUrl.'?cat='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a>';
    if (!empty($cat['pages'])) {
        $html .= '<ul>';
        foreach ($cat['pages'] as $page) {
            $html .= '<li><a href="'.$baseUrl.'?page='.$page['filename'].'">'.htmlspecialchars($page['title']).'</a></li>';
        }
        $html .= '</ul>';
    }
    if (!empty($cat['children'])) {
        $html .= '<ul>';
        foreach ($cat['children'] as $subcat) {
            $html .= $this->renderCategoryMenu($subcat, $baseUrl);
        }
        $html .= '</ul>';
    }
    $html .= '</li>';
    return $html;
}

}