<?php
// plugin/wiki/controllers/AdminWikiCategoriesController.php
defined('ROOT') or exit('Access denied!');
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiCategoryManager.php';

class AdminWikiCategoriesController extends AdminController {
    protected $categoryManager;

    public function __construct() {
        $this->categoryManager = WikiCategoryManager::getInstance();
    }

    public function index() {
        $categoriesTree = $this->categoryManager->getCategoriesTree();
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-categories');
        $response->setTitle("Gestion des catégories");
        $tpl->set('categoriesTree', $categoriesTree);
        $tpl->set('adminMenu', $this->getAdminMenu());
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    public function edit() {
        $catId = $_GET['id'] ?? '';
        $category = $catId ? $this->categoryManager->getCategory($catId) : null;
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-categories-edit');
        $response->setTitle($catId ? "Modifier la catégorie" : "Nouvelle catégorie");
        $tpl->set('category', $category);
        $tpl->set('allCategories', $this->categoryManager->getAllCategories());
        $tpl->set('adminMenu', $this->getAdminMenu());
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    public function save() {
        $data = $_POST;
        $this->categoryManager->saveCategory($data);
        header("Location: ".ROUTER::getInstance()->generate('admin-wiki-categories'));
        exit;
    }

    public function delete() {
        $catId = $_GET['id'] ?? '';
        if ($catId) {
            $this->categoryManager->deleteCategory($catId);
        }
        header("Location: ".ROUTER::getInstance()->generate('admin-wiki-categories'));
        exit;
    }

    protected function getAdminMenu() {
        // Même menu que dans AdminWikiController
        $router = ROUTER::getInstance();
        $menu = [
            ['label' => 'Pages',      'url' => $router->generate('admin-wiki')],
            ['label' => 'Catégories',  'url' => $router->generate('admin-wiki-categories')],
            ['label' => 'Paramètres',  'url' => $router->generate('admin-wiki-parameters')]
        ];
        $html = '<ul>';
        foreach ($menu as $item) {
            $html .= '<li><a href="'.$item['url'].'">'.$item['label'].'</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
}