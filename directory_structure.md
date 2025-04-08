# Directory Structure

|-- .gitignore
|-- .htaccess
|-- README.md
|-- admin
|   |-- layout.tpl
|   |-- scripts.js
|   `-- styles.css
|-- changelog.md
|-- common
|   |-- Show.php
|   |-- Template.php
|   |-- Util.php
|   |-- common.php
|   |-- config.php
|   |-- content
|   |   |-- ContentParser.php
|   |   |-- Editor.php
|   |   `-- categories
|   |       |-- CategoriesManager.php
|   |       |-- Category.php
|   |       `-- template
|   |           |-- checkboxCategories.php
|   |           |-- listCategories.php
|   |           |-- selectCategory.php
|   |           `-- selectOneCategory.php
|   |-- core
|   |   |-- Core.php
|   |   |-- Lang.php
|   |   |-- Plugin.php
|   |   |-- PluginsManager.php
|   |   |-- Theme.php
|   |   |-- controllers
|   |   |   |-- AdminController.php
|   |   |   |-- Controller.php
|   |   |   |-- CoreController.php
|   |   |   `-- PublicController.php
|   |   |-- http
|   |   |   `-- Request.php
|   |   |-- responses
|   |   |   |-- AdminResponse.php
|   |   |   |-- ApiResponse.php
|   |   |   |-- PublicResponse.php
|   |   |   |-- Response.php
|   |   |   `-- StringResponse.php
|   |   |-- router
|   |   |   |-- AltoRouter.php
|   |   |   `-- Router.php
|   |   `-- storage
|   |       |-- JsonActiveRecord.php
|   |       `-- Zip.php
|   |-- langs
|   |   |-- en.ini
|   |   |-- fr.ini
|   |   `-- ru.ini
|   |-- template
|   |   `-- 404.tpl
|   `-- templates
|       `-- 404.tpl
|-- data
|   |-- .htaccess
|   |-- config.json
|   |-- key.php
|   |-- logs.txt
|   |-- plugin
|   |   |-- antispam
|   |   |   `-- config.json
|   |   |-- blog
|   |   |   `-- config.json
|   |   |-- configmanager
|   |   |   |-- cache.json
|   |   |   `-- config.json
|   |   |-- contact
|   |   |   |-- config.json
|   |   |   `-- emails.json
|   |   |-- filemanager
|   |   |   |-- config.json
|   |   |   `-- files.json
|   |   |-- galerie
|   |   |   |-- config.json
|   |   |   `-- galerie.json
|   |   |-- page
|   |   |   |-- config.json
|   |   |   `-- pages.json
|   |   |-- pluginsmanager
|   |   |   `-- config.json
|   |   |-- seo
|   |   |   `-- config.json
|   |   |-- tinymce
|   |   |   `-- config.json
|   |   |-- users
|   |   |   |-- config.json
|   |   |   `-- users.json
|   |   `-- wiki
|   |       |-- categories-categories.json
|   |       |-- config.json
|   |       `-- pages
|   |           |-- 67f4edbe95a04.json
|   |           `-- 67f4ff85baadd.json
|   `-- upload
|       |-- .htaccess
|       |-- files
|       `-- galerie
|-- index.php
|-- install.php
|-- license.txt
|-- plugin
|   |-- antispam
|   |   |-- antispam.php
|   |   |-- controllers
|   |   |   `-- AntispamAdminController.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- config.tpl
|   |       |-- help.tpl
|   |       `-- public.css
|   |-- blog
|   |   |-- blog.php
|   |   |-- controllers
|   |   |   |-- BlogAdminCategoriesController.php
|   |   |   |-- BlogAdminCommentsController.php
|   |   |   |-- BlogAdminConfigController.php
|   |   |   |-- BlogAdminPostsController.php
|   |   |   |-- BlogListController.php
|   |   |   `-- BlogReadController.php
|   |   |-- entities
|   |   |   |-- BlogCategoriesManager.php
|   |   |   |-- BlogCategory.php
|   |   |   |-- news.php
|   |   |   |-- newsComment.php
|   |   |   `-- newsManager.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- admin-edit-category.tpl
|   |       |-- admin-edit.tpl
|   |       |-- admin-list-comments.tpl
|   |       |-- admin-list.tpl
|   |       |-- admin.css
|   |       |-- comment.tpl
|   |       |-- list.tpl
|   |       |-- param.tpl
|   |       |-- public.css
|   |       |-- public.js
|   |       `-- read.tpl
|   |-- configmanager
|   |   |-- configmanager.php
|   |   |-- controllers
|   |   |   |-- ConfigManagerAdminController.php
|   |   |   |-- ConfigManagerBackupAdminController.php
|   |   |   `-- ConfigManagerUpdateController.php
|   |   |-- entities
|   |   |   |-- ConfigManagerBackup.php
|   |   |   `-- ConfigManagerBackupsManager.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- lib
|   |   |   `-- UpdaterManager.php
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- admin.css
|   |       |-- backup.tpl
|   |       `-- config.tpl
|   |-- contact
|   |   |-- contact.php
|   |   |-- controllers
|   |   |   |-- ContactAdminController.php
|   |   |   `-- ContactController.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- admin-contact.tpl
|   |       |-- contact.tpl
|   |       `-- param.tpl
|   |-- filemanager
|   |   |-- controllers
|   |   |   `-- FileManagerAPIController.php
|   |   |-- filemanager.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- lib
|   |   |   |-- File.php
|   |   |   |-- FileManager.php
|   |   |   `-- Folder.php
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- admin.css
|   |       `-- listview.tpl
|   |-- galerie
|   |   |-- controllers
|   |   |   |-- GalerieAdminController.php
|   |   |   `-- GalerieController.php
|   |   |-- galerie.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- admin.css
|   |       |-- admin.tpl
|   |       |-- galerie.tpl
|   |       |-- help.tpl
|   |       |-- param.tpl
|   |       |-- public.css
|   |       `-- public.js
|   |-- page
|   |   |-- controllers
|   |   |   |-- PageAdminController.php
|   |   |   `-- PageController.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- page.php
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   `-- template
|   |       |-- admin.js
|   |       |-- edit-link.tpl
|   |       |-- edit-parent.tpl
|   |       |-- edit.tpl
|   |       |-- help.tpl
|   |       |-- list.tpl
|   |       `-- read.tpl
|   |-- pluginsmanager
|   |   |-- controllers
|   |   |   `-- PluginsManagerController.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   |-- pluginsmanager.php
|   |   `-- template
|   |       |-- help.tpl
|   |       `-- list.tpl
|   |-- seo
|   |   |-- controllers
|   |   |   `-- SEOAdminController.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   |-- seo.php
|   |   `-- template
|   |       |-- admin.tpl
|   |       |-- help.tpl
|   |       `-- public.css
|   |-- tinymce
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- lib
|   |   |   `-- tinymce
|   |   |       `-- tinymce.min.js
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   `-- infos.json
|   |   |-- template
|   |   |   |-- admin.css
|   |   |   `-- editor.css
|   |   `-- tinymce.php
|   |-- users
|   |   |-- controllers
|   |   |   |-- UsersAdminController.php
|   |   |   |-- UsersAdminManagementController.php
|   |   |   `-- UsersLoginController.php
|   |   |-- entities
|   |   |   |-- PasswordRecovery.php
|   |   |   |-- User.php
|   |   |   `-- UsersManager.php
|   |   |-- langs
|   |   |   |-- en.ini
|   |   |   |-- fr.ini
|   |   |   `-- ru.ini
|   |   |-- param
|   |   |   |-- config.json
|   |   |   |-- hooks.json
|   |   |   |-- infos.json
|   |   |   `-- routes.php
|   |   |-- template
|   |   |   |-- login.tpl
|   |   |   |-- lostpwd-step2.tpl
|   |   |   |-- lostpwd.tpl
|   |   |   |-- public.css
|   |   |   |-- usersadd.tpl
|   |   |   |-- usersedit.tpl
|   |   |   `-- userslist.tpl
|   |   `-- users.php
|   `-- wiki
|       |-- controllers
|       |   |-- AdminWikiCategoriesController.php
|       |   |-- AdminWikiController.php
|       |   |-- AdminWikiParametersController.php
|       |   `-- WikiPageController.php
|       |-- entities
|       |   |-- WikiCategoryCMS.php
|       |   |-- WikiCategoryManager.php
|       |   |-- WikiPage.php
|       |   `-- WikiPageManager.php
|       |-- langs
|       |   `-- fr.ini
|       |-- param
|       |   |-- config.json
|       |   |-- hooks.json
|       |   |-- infos.json
|       |   `-- routes.php
|       |-- template
|       |   |-- admin
|       |   |   |-- Wiki-list.tpl
|       |   |   |-- wiki-categories-edit.tpl
|       |   |   |-- wiki-categories.tpl
|       |   |   |-- wiki-edit.tpl
|       |   |   `-- wiki-parameters.tpl
|       |   `-- public
|       |       |-- wiki-list.tpl
|       |       `-- wiki-view.tpl
|       `-- wiki.php
`-- theme
    `-- default
        |-- functions.php
        |-- header.jpg
        |-- icon.png
        |-- infos.json
        |-- layout.tpl
        |-- scripts.js
        `-- styles.css


# File Contents

## data\plugin\wiki\categories-categories.json

```
{
    "67f4ed5d14742": {
        "label": "test 1 enfant",
        "parentId": "67f4ed68ea848",
        "items": [],
        "childrenId": [],
        "pluginArgs": []
    },
    "67f4ed68ea848": {
        "label": "Test 1 Parent",
        "parentId": "",
        "items": [],
        "childrenId": [],
        "pluginArgs": []
    }
}
```

## data\plugin\wiki\config.json

```
{
    "priority": "2",
    "label": "299Docs",
    "itemsByPage": "5",
    "allowSearch": "1",
    "defaultDraft": "0",
    "defaultPage": "Accueil Docs",
    "storageDir": "wiki",
    "versionLimit": "4",
    "activate": 1
}
```

## data\plugin\wiki\pages\67f4edbe95a04.json

```
{
    "filename": "67f4edbe95a04.json",
    "title": "test 1 parent",
    "category": "67f4ed68ea848",
    "parent": "",
    "content": ";kjdnbkjdwnbgkjdwng",
    "updated_at": "2025-04-08 10:41:38"
}
```

## data\plugin\wiki\pages\67f4ff85baadd.json

```
{
    "filename": "67f4ff85baadd.json",
    "title": "fdwhwh",
    "category": "67f4ed68ea848",
    "parent": "67f4edbe95a04.json",
    "draft": "1",
    "wikiContent": "",
    "updated_at": "2025-04-08 10:51:30"
}
```

## plugin\wiki\controllers\AdminWikiCategoriesController.php

```
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
```

## plugin\wiki\controllers\AdminWikiController.php

```
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
        $contentEditor = new Editor('wikiContent');

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

```

## plugin\wiki\controllers\AdminWikiParametersController.php

```
<?php
// plugin/wiki/controllers/AdminWikiParametersController.php
defined('ROOT') or exit('Access denied!');

class AdminWikiParametersController extends AdminController {
    protected $configFile;

    public function __construct() {
        $this->configFile = DATA_PLUGIN.'wiki'.DS.'config.json';
        if (!file_exists($this->configFile)) {
            file_put_contents($this->configFile, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function index() {
        $config = json_decode(file_get_contents($this->configFile), true);
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin/wiki-parameters');
        $response->setTitle("Paramètres du Wiki");
        $tpl->set('config', $config);
        $tpl->set('adminMenu', $this->getAdminMenu());
        $tpl->set('router', ROUTER::getInstance());
        $response->addTemplate($tpl);
        return $response;
    }

public function save() {
    $data = $_POST;
    // Lecture de la configuration existante
    $existingConfig = [];
    if (file_exists($this->configFile)) {
        $existingConfig = json_decode(file_get_contents($this->configFile), true);
        if (!is_array($existingConfig)) {
            $existingConfig = [];
        }
    }
    // Fusionner l'ancienne configuration avec les nouvelles valeurs
    $newConfig = array_merge($existingConfig, $data);
    file_put_contents($this->configFile, json_encode($newConfig, JSON_PRETTY_PRINT));
    header("Location: ".ROUTER::getInstance()->generate('admin-wiki-parameters'));
    exit;
}


    protected function getAdminMenu() {
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
```

## plugin\wiki\controllers\WikiPageController.php

```
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
            $content = nl2br(htmlspecialchars($page['content']));
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
```

## plugin\wiki\entities\WikiCategoryCMS.php

```
<?php
// plugin/wiki/entities/WikiCategoryCMS.php
// Cette classe concrète étend la classe native Category du CMS et
// permet d'utiliser les fonctionnalités centralisées de gestion des catégories.
defined('ROOT') or exit('Access denied!');

class WikiCategoryCMS extends Category {
    /**
     * Constructeur de WikiCategoryCMS
     * On définit ici les propriétés spécifiques pour le plugin Wiki.
     *
     * @param int $id ID de la catégorie, -1 pour une nouvelle instance
     */
    public function __construct(int $id = -1) {
        // Définition de l'ID du plugin qui utilise la gestion des catégories du CMS
        $this->pluginId = 'wiki';
        // On utilise 'categories' comme nom de fichier
        $this->name = 'categories';
        // Catégories hiérarchisées
        $this->nested = true;
        // Une seule sélection possible
        $this->chooseMany = false;

        // Appel du constructeur parent qui gère le chargement des données si $id != -1
        parent::__construct($id);
    }

    /**
     * Getter public pour pluginId.
     *
     * @return string
     */
    public function getPluginId(): string {
        return $this->pluginId;
    }

    /**
     * Getter public pour name.
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
}

```

## plugin\wiki\entities\WikiCategoryManager.php

```
<?php
// plugin/wiki/entities/WikiCategoryManager.php
// Adaptation de la gestion des catégories du plugin Wiki pour utiliser le core natif
// via la classe WikiCategoryCMS.
defined('ROOT') or exit('Access denied!');

// Inclure la classe concrète qui étend le core Category
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiCategoryCMS.php';

class WikiCategoryManager {
    // Instance unique (singleton)
    protected static $instance;

    /**
     * Constructeur privé pour le pattern singleton.
     */
    private function __construct() {
        // Aucun initialisation particulière
    }

    /**
     * Retourne l'instance unique de WikiCategoryManager.
     *
     * @return WikiCategoryManager
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new WikiCategoryManager();
        }
        return self::$instance;
    }

    /**
     * Récupère toutes les catégories sous forme de tableau associatif.
     *
     * @return array Liste des catégories (id, name, parent)
     */
    public function getAllCategories() {
        // Utilisation d'un objet dummy pour récupérer pluginId et name via les getters
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $data = util::readJsonFile($file);

        // Vérification que $data est bien un tableau
        if (!is_array($data)) {
            $data = [];
        }

        $categories = [];
        // Parcours des catégories dans le fichier JSON
        foreach ($data as $id => $catData) {
            $categories[] = [
                'id'     => $id,
                // Le core utilise 'label' pour le nom affiché
                'name'   => $catData['label'] ?? '',
                // Utilisation de 'parentId' pour la relation hiérarchique
                'parent' => $catData['parentId'] ?? ''
            ];
        }
        return $categories;
    }

    /**
     * Construit l'arborescence des catégories.
     *
     * @return array Arborescence des catégories
     */
    public function getCategoriesTree() {
        $all = $this->getAllCategories();
        $tree = [];
        $indexed = [];

        // Indexation par identifiant et initialisation des enfants
        foreach ($all as $cat) {
            $cat['children'] = [];
            $indexed[$cat['id']] = $cat;
        }

        // Construction de l'arborescence
        foreach ($indexed as $id => &$cat) {
            if (!empty($cat['parent']) && isset($indexed[$cat['parent']])) {
                $indexed[$cat['parent']]['children'][] = &$cat;
            } else {
                $tree[] = &$cat;
            }
        }
        return $tree;
    }

    /**
     * Récupère une catégorie par son identifiant.
     *
     * @param mixed $id
     * @return array|null
     */
    public function getCategory($id) {
        $all = $this->getAllCategories();
        foreach ($all as $cat) {
            if ($cat['id'] == $id) {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Sauvegarde une catégorie via le système natif.
     *
     * @param array $data Données de la catégorie (id, name, parent)
     */
    public function saveCategory($data) {
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $categories = util::readJsonFile($file);
        if (!is_array($categories)) {
            $categories = [];
        }

        // Si 'id' est vide, on en génère un nouveau via uniqid()
        $id = !empty($data['id']) ? $data['id'] : uniqid();

        $categories[$id] = [
            // On stocke le nom sous 'label'
            'label'      => $data['name'],
            // Le parent est stocké sous 'parentId'
            'parentId'   => $data['parent'] ?? '',
            // On préserve d'éventuelles données existantes
            'items'      => $categories[$id]['items'] ?? [],
            'childrenId' => $categories[$id]['childrenId'] ?? [],
            'pluginArgs' => $categories[$id]['pluginArgs'] ?? []
        ];
        file_put_contents($file, json_encode($categories, JSON_PRETTY_PRINT));
    }


    /**
     * Supprime une catégorie en supprimant sa clé du fichier natif.
     *
     * @param mixed $id Identifiant de la catégorie à supprimer
     */
    public function deleteCategory($id) {
        $dummy = new WikiCategoryCMS();
        $file = DATA_PLUGIN . $dummy->getPluginId() . '/categories-' . $dummy->getName() . '.json';
        $categories = util::readJsonFile($file);

        if (!is_array($categories)) {
            $categories = [];
        }

        if (isset($categories[$id])) {
            unset($categories[$id]);
            file_put_contents($file, json_encode($categories, JSON_PRETTY_PRINT));
        }
    }
}

```

## plugin\wiki\entities\WikiPage.php

```
<?php
// plugin/wiki/entities/WikiPage.php
defined('ROOT') or exit('Access denied!');

class WikiPage {
    public $filename;
    public $title;
    public $content;
    public $category;
    public $parent;
    public $draft;
    public $updated_at;
    public $created_at;
    public $versions;

    public function __construct($data) {
        $this->filename   = $data['filename'] ?? '';
        $this->title      = $data['title'] ?? '';
        $this->content    = $data['content'] ?? '';
        $this->category   = $data['category'] ?? '';
        $this->parent     = $data['parent'] ?? '';
        $this->draft      = $data['draft'] ?? false;
        $this->updated_at = $data['updated_at'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
        $this->versions   = $data['versions'] ?? [];
    }

    public function toArray() {
        return [
            'filename'   => $this->filename,
            'title'      => $this->title,
            'content'    => $this->content,
            'category'   => $this->category,
            'parent'     => $this->parent,
            'draft'      => $this->draft,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'versions'   => $this->versions
        ];
    }
}
```

## plugin\wiki\entities\WikiPageManager.php

```
<?php
// plugin/wiki/entities/WikiPageManager.php
defined('ROOT') or exit('Access denied!');
require_once PLUGINS.'wiki'.DS.'entities'.DS.'WikiPage.php';

class WikiPageManager {
    protected $pagesDir;

    public function __construct($pagesDir) {
        $this->pagesDir = $pagesDir;
        if (!is_dir($this->pagesDir)) {
            mkdir($this->pagesDir, 0755, true);
        }
    }

    public function getPagesTree() {
        $pages = $this->loadAllPages();
        return $this->buildTree($pages);
    }

public function getPage($filename) {
    // Si aucun nom de fichier n'est fourni (nouvelle page), retourner un tableau vide
    if (empty($filename)) {
        return [];
    }
    $filePath = $this->pagesDir . $filename;
    if (!file_exists($filePath)) return null;
    $data = json_decode(file_get_contents($filePath), true);
    $data['filename'] = $filename;
    return $data;
}


    public function savePage($data) {
        $filename = isset($data['filename']) && !empty($data['filename']) ? $data['filename'] : uniqid().'.json';
        $data['filename'] = $filename;
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (!file_exists($this->pagesDir.$filename)) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        file_put_contents($this->pagesDir.$filename, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function deletePage($filename) {
        $filePath = $this->pagesDir.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function searchPages($query) {
        $all = $this->loadAllPages();
        $results = [];
        foreach ($all as $page) {
            if (stripos($page['title'], $query)!==false || stripos($page['content'], $query)!==false) {
                $results[] = $page;
            }
        }
        return $results;
    }

    public function getPagesByCategory($catId) {
        $all = $this->loadAllPages();
        $filtered = [];
        foreach ($all as $page) {
            if (isset($page['category']) && $page['category'] == $catId) {
                $filtered[] = $page;
            }
        }
        return $this->buildTree($filtered);
    }

    public function getOrphanPages() {
        $all = $this->loadAllPages();
        $orphans = [];
        foreach ($all as $page) {
            if (empty($page['category'])) {
                $orphans[] = $page;
            }
        }
        return $orphans;
    }

    protected function loadAllPages() {
        $pages = [];
        $files = scandir($this->pagesDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') continue;
            $data = json_decode(file_get_contents($this->pagesDir.$file), true);
            $data['filename'] = $file;
            $pages[] = $data;
        }
        return $pages;
    }

    protected function buildTree(array $pages) {
        $tree = [];
        $indexed = [];
        foreach ($pages as $page) {
            $page['children'] = [];
            $indexed[$page['filename']] = $page;
        }
        foreach ($indexed as $filename => $page) {
            if (!empty($page['parent']) && isset($indexed[$page['parent']])) {
                $indexed[$page['parent']]['children'][] = &$indexed[$filename];
            } else {
                $tree[] = &$indexed[$filename];
            }
        }
        return $tree;
    }
}
```

## plugin\wiki\langs\fr.ini

```
; Wiki - Langue Française

wiki.name = Wiki
wiki.list_pages = "Liste des pages Wiki"
wiki.edit_page = "Éditer la page"
wiki.new_page = "Nouvelle page"
wiki.save = "Enregistrer"
wiki.versions = "Historique des versions"
wiki.view_page = "Voir la page"
wiki.publish = "Publier"

```

## plugin\wiki\param\config.json

```
{
    "priority": "2",
    "label": "299Docs",
    "itemsByPage": "10",
    "allowSearch": "1",
    "defaultDraft": "0",
    "defaultPage": "Accueil Docs",
    "storageDir": "wiki",
    "versionLimit": 2
}
```

## plugin\wiki\param\hooks.json

```
{
    "beforeRunPlugin": "wikiBeforeRunPlugin"
}
```

## plugin\wiki\param\infos.json

```
{
  "name": "299Docs",
  "icon": "fa-solid fa-book",
  "description": "Plugin Wiki pour gérer des pages collaboratives basées sur des fichiers JSON avec catégories, brouillons et versionning.",
  "authorEmail": "nemstudio18@gmail.com",
  "authorWebsite": "https://flexcb.fr",
  "version": "0.2",
  "homeAdminMethod": "AdminWikiController#index"
}

```

## plugin\wiki\param\routes.php

```
<?php
// plugin/wiki/param/routes.php
defined('ROOT') or exit('Access denied!');
$router = ROUTER::getInstance();

// Routes côté public
$router->map('GET', '/wiki[/?]', 'WikiPageController#view', 'wiki-view');

// Routes côté admin pour la gestion des pages
$router->map('GET', '/admin/wiki[/?]', 'AdminWikiController#index', 'admin-wiki');
$router->map('GET', '/admin/wiki/edit[/?]', 'AdminWikiController#edit', 'admin-wiki-edit');
$router->map('POST', '/admin/wiki/save[/?]', 'AdminWikiController#save', 'admin-wiki-save');
$router->map('GET', '/admin/wiki/delete[/?]', 'AdminWikiController#delete', 'admin-wiki-delete');

// Routes côté admin pour la gestion des catégories
$router->map('GET', '/admin/wiki/categories[/?]', 'AdminWikiCategoriesController#index', 'admin-wiki-categories');
$router->map('GET', '/admin/wiki/categories/edit[/?]', 'AdminWikiCategoriesController#edit', 'admin-wiki-categories-edit');
$router->map('POST', '/admin/wiki/categories/save[/?]', 'AdminWikiCategoriesController#save', 'admin-wiki-categories-save');
$router->map('GET', '/admin/wiki/categories/delete[/?]', 'AdminWikiCategoriesController#delete', 'admin-wiki-categories-delete');

// Routes côté admin pour les paramètres
$router->map('GET', '/admin/wiki/parameters[/?]', 'AdminWikiParametersController#index', 'admin-wiki-parameters');
$router->map('POST', '/admin/wiki/parameters/save[/?]', 'AdminWikiParametersController#save', 'admin-wiki-parameters-save');
```

## plugin\wiki\template\admin\Wiki-list.tpl

```
<!-- templates/admin/wiki-list.tpl -->
<h1>Liste des pages Wiki</h1>
<div class="admin-menu">
    {{ adminMenu }}
</div>
<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Parent</th>
            <th>Date de Création</th>
            <th>last update</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {% for page in pagesTree %}
        <tr>
            <td>{{ page.title }}</td>
            <td>{{ page.categoryName }}</td>
            <td>{{ page.parent }}</td>
            <td>{{ page.created_at }}</td>
            <td>{{ page.updated_at }}</td>
            <td>
                <a href="{{ router.generate("admin-wiki-edit") }}?page={{ page.filename }}">Éditer</a>
                <a href="{{ router.generate("admin-wiki-delete") }}?page={{ page.filename }}" onclick="return confirm('Supprimer cette page ?');">Supprimer</a>
            </td>
        </tr>
        {% if page.children %}
            {% for child in page.children %}
            <tr>
                <td>&nbsp;&nbsp;&nbsp;-- {{ child.title }}</td>
                <td>{{ child.category }}</td>
                <td>{{ child.parent }}</td>
                <td>
                    <a href="{{ router.generate("admin-wiki-edit") }}?page={{ child.filename }}">Éditer</a>
                    <a href="{{ router.generate("admin-wiki-delete") }}?page={{ child.filename }}" onclick="return confirm('Supprimer cette page ?');">Supprimer</a>
                </td>
            </tr>
            {% endfor %}
        {% endif %}
        {% endfor %}
    </tbody>
</table>
<a href="{{ router.generate("admin-wiki-edit") }}">Nouvelle page</a>
```

## plugin\wiki\template\admin\wiki-categories-edit.tpl

```
<!-- templates/admin/wiki-categories-edit.tpl -->
<h1>{{ category.id ? "Modifier la catégorie" : "Nouvelle catégorie" }}</h1>
<div class="admin-menu">
    {{ adminMenu }}
</div>
<form method="post" action="{{ router.generate("admin-wiki-categories-save") }}">
    <input type="hidden" name="id" value="{{ category.id }}">
    <label>Nom :</label>
    <input type="text" name="name" value="{{ category.name }}">
    <br>
    <label>Catégorie parente :</label>
    <select name="parent">
        <option value="">-- Aucune --</option>
        {% for cat in allCategories %}
            {% if cat.id != category.id %}
                <option value="{{ cat.id }}" {% if category.parent == cat.id %}selected{% endif %}>{{ cat.name }}</option>
            {% endif %}
        {% endfor %}
    </select>
    <br>
    <button type="submit">Enregistrer</button>
</form>
```

## plugin\wiki\template\admin\wiki-categories.tpl

```
<!-- templates/admin/wiki-categories.tpl -->
<h1>Gestion des catégories</h1>
<div class="admin-menu">
    {{ adminMenu }}
</div>
<ul>
    {% for cat in categoriesTree %}
        <li>
            {{ cat.name }}
            <a href="{{ router.generate("admin-wiki-categories-edit") }}?id={{ cat.id }}">Éditer</a>
            <a href="{{ router.generate("admin-wiki-categories-delete") }}?id={{ cat.id }}" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
        </li>
        {% if cat.children %}
            <ul>
                {% for child in cat.children %}
                    <li>&nbsp;&nbsp;&nbsp;-- {{ child.name }}
                        <a href="{{ router.generate("admin-wiki-categories-edit") }}?id={{ child.id }}">Éditer</a>
                        <a href="{{ router.generate("admin-wiki-categories-delete") }}?id={{ child.id }}" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
                    </li>
                {% endfor %}
            </ul>
        {% endif %}
    {% endfor %}
</ul>
<a href="{{ router.generate("admin-wiki-categories-edit") }}">Nouvelle catégorie</a>
```

## plugin\wiki\template\admin\wiki-edit.tpl

```
<!-- templates/admin/wiki-edit.tpl -->
<div class="admin-menu">
    {{ adminMenu }}
</div>
<form method="post" action="{{ router.generate("admin-wiki-save") }}">
    <input type="hidden" name="filename" value="{{ page.filename }}">
    <label>Titre :</label>
    <input type="text" name="title" value="{{ page.title }}">
    <br>
    <label>Catégorie :</label>
    <select name="category">
        <option value="">-- Sélectionner --</option>
        {% for cat in categories %}
            <option value="{{ cat.id }}" {% if page.category == cat.id %}selected{% endif %}>{{ cat.name }}</option>
        {% endfor %}
    </select>
    <br>
<label>Parent :</label>
<select name="parent">
    <option value="">-- Aucune --</option>
    {% for p in parentPages %}
        <option value="{{ p.filename }}" {% if page.parent == p.filename %}selected{% endif %}>
            {{ p.title }}
        </option>
    {% endfor %}
</select>

    <br>
    <label>Brouillon :</label>
    <input type="checkbox" name="draft" value="1" {% if page.draft %}checked{% endif %}>
    <br>
    <label>Contenu :</label>
{{ contentEditor }}
    <br>
    <button type="submit">Enregistrer</button>
</form>
```

## plugin\wiki\template\admin\wiki-parameters.tpl

```
<!-- templates/admin/wiki-parameters.tpl -->
<h1>Paramètres du Wiki</h1>
<div class="admin-menu">
    {{ adminMenu }}
</div>
<form method="post" action="{{ router.generate("admin-wiki-parameters-save") }}">
    <label>Items par page :</label>
    <input type="number" name="itemsByPage" value="{{ config.itemsByPage }}">
    <br>
    <label>Version Limit :</label>
    <input type="number" name="versionLimit" value="{{ config.versionLimit }}">
    <br>
    <button type="submit">Enregistrer</button>
</form>
```

## plugin\wiki\template\public\wiki-list.tpl

```
<!-- templates/public/wiki-list.tpl -->
<h1>Wiki</h1>
<div class="wiki-navigation">
    {{ menu }}
</div>
<form method="get" action="{{ router.generate("wiki-view") }}">
    <input type="text" name="q" placeholder="Rechercher…" value="{{ searchQuery }}">
    <button type="submit">Rechercher</button>
</form>
{% if pages %}
    <ul>
        {% for page in pages %}
            <li>
                <a href="{{ router.generate("wiki-view") }}?page={{ page.filename }}">{{ page.title }}</a>
            </li>
        {% endfor %}
    </ul>
{% else %}
    <p>Aucune page trouvée.</p>
{% endif %}
```

## plugin\wiki\template\public\wiki-view.tpl

```
<!-- templates/public/wiki-view.tpl -->
<style>
.flex-content {
    display: flex;
    flex-direction: row;
}
.wiki-content {
    flex: 1;
    margin-right: 20px;
}
.wiki-navigation {
    width: 250px;
}
</style>
<h1>{{ pageTitle }}</h1>
<div class="flex-content">
    <div class="wiki-content">
        {{ content }}
    </div>
    <aside class="wiki-navigation">
        <h2>Navigation</h2>
        {{ menu }}
    </aside>
</div>
```

## plugin\wiki\wiki.php

```
<?php
/**
 * Plugin Wiki pour 299Ko CMS
 * Créé le plugin Wiki en créant le répertoire dédié aux pages.
 */

defined('ROOT') or exit('Access denied!');

// Détection du mode (administration ou public) en fonction de l'URL
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    define('ADMIN_MODE', true);
} else {
    define('ADMIN_MODE', false);
}

// Fonction d'installation du plugin Wiki
function wikiInstall() {
    $wikiDir = DATA_PLUGIN . 'wiki' . DS;
    if (!is_dir($wikiDir)) {
        if (!mkdir($wikiDir, 0755, true)) {
            die("Erreur lors de la création du répertoire Wiki.");
        }
    }
    echo "Installation du plugin Wiki terminée avec succès.";
}

/**
 * Hook appelé avant l'exécution du plugin Wiki.
 * Vérifie que le dossier de stockage existe et le crée si nécessaire.
 */
function wikiBeforeRunPlugin() {
    // Chargement de la configuration du plugin
    $configPath = PLUGINS . 'wiki' . DS . 'config.json';
    if (file_exists($configPath)) {
        $config = json_decode(file_get_contents($configPath), true);
    } else {
        $config = [
            "defaultPage" => "Accueil",
            "storageDir"   => "wiki",
            "versionLimit" => 2
        ];
    }
    $storageDir = DATA_PLUGIN . $config['storageDir'] . DS;
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    return true;
}

```

