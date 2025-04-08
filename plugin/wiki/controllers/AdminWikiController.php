<?php
// plugin/wiki/controllers/AdminWikiController.php
defined('ROOT') or exit('Access denied!');
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiPageManager.php';
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiCategoryManager.php';

class AdminWikiController extends AdminController {
    protected $pageManager;

    public function __construct() {
        // Les pages sont stockées en fichiers JSON dans DATA_PLUGIN/wiki/pages/
        $this->pageManager = new WikiPageManager(DATA_PLUGIN.'wiki'.DS.'pages'.DS);
    }

    public function index() {
        // Récupère l'arborescence des pages (côté admin, on affiche toutes, même les brouillons)
        $pagesTree = $this->pageManager->getPagesTree();

        // Récupération de toutes les catégories depuis le système natif
        $categories = WikiCategoryManager::getInstance()->getAllCategories();
        // Construction d'un mapping id => nom
        $catMapping = [];
        foreach ($categories as $cat) {
            $catMapping[$cat['id']] = $cat['name'];
        }

        // Ajout du libellé de la catégorie dans chaque page (récursif)
        $pagesTree = $this->addCategoryNameToPages($pagesTree, $catMapping);

        // Remarque : chaque page doit contenir aussi les clés 'created_at' et 'updated_at'
        // pour permettre au template d'afficher les dates de création et de dernière modification.

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-list');
        $response->setTitle("Liste des pages Wiki");
        $tpl->set('pagesTree', $pagesTree);
        // On passe le mapping au cas où le template souhaite l'utiliser
        $tpl->set('categoriesMapping', $catMapping);
        $tpl->set('adminMenu', $this->getAdminMenu());
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    public function edit() {
        $pageId = isset($_GET['page']) ? $_GET['page'] : '';
        $pageData = $this->pageManager->getPage($pageId);
        if (empty($pageData)) {
            // Définir des valeurs par défaut pour la création d'une page
            $pageData = [
                'filename'  => '',
                'title'     => '',
                'content'   => '',
                'category'  => '',
                'parent'    => '',
                'draft'     => false,
                'created_at'=> '',
                'updated_at'=> ''
            ];
        }
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-edit');
        $response->setTitle($pageId ? "Éditer la page" : "Nouvelle page");
        $contentEditor = new Editor('content');

        $tpl->set('contentEditor', $contentEditor);
        $tpl->set('page', $pageData);
        // Récupère la liste des catégories pour le champ "Catégorie"
        $tpl->set('categories', WikiCategoryManager::getInstance()->getAllCategories());

        // Récupération de l'arborescence des pages pour le choix du parent sous forme de dropdown.
        // On exclut la page en cours afin d'empêcher qu'elle ne se sélectionne elle-même comme parent.
        $pagesTree = $this->pageManager->getPagesTree();
        $exclude = !empty($pageData['filename']) ? $pageData['filename'] : null;
        $parentPages = $this->flattenPages($pagesTree, 0, $exclude);
        $tpl->set('parentPages', $parentPages);

        $tpl->set('adminMenu', $this->getAdminMenu());
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

    public function save() {
        // Sauvegarde de la page (création ou modification)
        $data = $_POST;
        // La méthode savePage s'occupe de mettre à jour 'updated_at'
        // et de définir 'created_at' lors de la création.
        $this->pageManager->savePage($data);
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
        exit;
    }

    public function delete() {
        $pageId = $_GET['page'] ?? '';
        if ($pageId) {
            $this->pageManager->deletePage($pageId);
        }
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
        exit;
    }

    protected function getAdminMenu() {
        // Menu commun pour l'administration du Wiki
        $router = ROUTER::getInstance();
        $menu = [
            ['label' => 'Pages',      'url' => $router->generate('admin-wiki')],
            ['label' => 'Catégories',  'url' => $router->generate('admin-wiki-categories')],
            ['label' => 'Paramètres',  'url' => $router->generate('admin-wiki-parameters')]
        ];
        $html = '<ul>';
        foreach ($menu as $item) {
            $html .= '<li><a href="' . $item['url'] . '">' . $item['label'] . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Parcours récursif pour ajouter la propriété "categoryName" à chaque page.
     *
     * @param array $pagesTree
     * @param array $catMapping Mapping id => nom de catégorie
     * @return array Arbre mis à jour
     */
    protected function addCategoryNameToPages(array $pagesTree, array $catMapping) {
        foreach ($pagesTree as &$page) {
            if (!empty($page['category']) && isset($catMapping[$page['category']])) {
                $page['categoryName'] = $catMapping[$page['category']];
            } else {
                $page['categoryName'] = 'Aucune';
            }
            if (!empty($page['children'])) {
                $page['children'] = $this->addCategoryNameToPages($page['children'], $catMapping);
            }
        }
        return $pagesTree;
    }

    /**
     * Aplati l'arborescence des pages en une liste plate avec indentation.
     * La page dont le filename correspond à $exclude n'est pas incluse.
     *
     * @param array $pages Arborescence des pages
     * @param int $depth Niveau d'indentation
     * @param string|null $exclude (Optionnel) Filename de la page à exclure
     * @return array Liste plate des pages avec 'filename' et 'title'
     */
    protected function flattenPages(array $pages, $depth = 0, $exclude = null) {
        $result = [];
        foreach ($pages as $page) {
            if ($exclude !== null && $page['filename'] == $exclude) {
                continue;
            }
            $result[] = [
                'filename' => $page['filename'],
                'title' => str_repeat('-- ', $depth) . $page['title']
            ];
            if (!empty($page['children'])) {
                $result = array_merge($result, $this->flattenPages($page['children'], $depth + 1, $exclude));
            }
        }
        return $result;
    }
}
