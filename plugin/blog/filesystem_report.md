# Rapport sur le système de fichiers

## Vue ASCII de l'arborescence des fichiers

```
C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog
|-- blog.php
|-- controllers
  |-- BlogAdminCategoriesController.php
  |-- BlogAdminCommentsController.php
  |-- BlogAdminConfigController.php
  |-- BlogAdminPostsController.php
  |-- BlogListController.php
  |-- BlogReadController.php
|-- entities
  |-- BlogCategoriesManager.php
  |-- BlogCategory.php
  |-- news.php
  |-- newsComment.php
  |-- newsManager.php
|-- langs
  |-- en.ini
  |-- fr.ini
  |-- ru.ini
|-- param
  |-- config.json
  |-- hooks.json
  |-- infos.json
  |-- routes.php
|-- template
  |-- admin-edit-category.tpl
  |-- admin-edit.tpl
  |-- admin-edit.tpl.cache.php
  |-- admin-list-comments.tpl
  |-- admin-list.tpl
  |-- admin-list.tpl.cache.php
  |-- admin.css
  |-- comment.tpl
  |-- list.tpl
  |-- list.tpl.cache.php
  |-- param.tpl
  |-- param.tpl.cache.php
  |-- public.css
  |-- public.js
  |-- read.tpl
  |-- read.tpl.cache.php
```

## Contenu des fichiers

### Fichier : blog.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\blog.php

**Contenu :**
```
<?php

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
defined('ROOT') OR exit('No direct script access allowed');

require_once PLUGINS . 'blog/entities/news.php';
require_once PLUGINS . 'blog/entities/newsComment.php';
require_once PLUGINS . 'blog/entities/newsManager.php';
require_once PLUGINS . 'blog/entities/BlogCategoriesManager.php';
require_once PLUGINS . 'blog/entities/BlogCategory.php';



## Fonction d'installation

function blogInstall() {

}

## Hooks

function blogEndFrontHead() {
    $core = core::getInstance();
    echo '<link rel="alternate" type="application/rss+xml" href="' . router::getInstance()->generate('blog-rss') . '" title="' . $core->getConfigVal('siteName') . '">' . "\n";
}

function BlogAdminCategoriesTemplates() {
    global $runPlugin;
    if ($runPlugin->getName() !== 'blog') {
        return;
    }

    $catsManager = new BlogCategoriesManager();

    echo '<a title="' . lang::get('blog-categories-management-title') . '" id="cat_link" data-fancybox data-type="ajax" href="#" data-src="' .  $catsManager->getAjaxDisplayListUrl() . '"><i class="fa-regular fa-folder-open"></i></a>';
}


## Code relatif au plugin


```

### Fichier : BlogAdminCategoriesController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\controllers\BlogAdminCategoriesController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class BlogAdminCategoriesController extends AdminController {

    public BlogCategoriesManager $categoriesManager;

    public newsManager $newsManager;

    public function __construct() {
        parent::__construct();
                                // Vérification si l'utilisateur est administrateur
        $this->checkAccess('admin');
        $this->categoriesManager = new BlogCategoriesManager();
        $this->newsManager = new newsManager();
    }

    public function addCategory() {
        $response = new ApiResponse();
        if (!$this->user->isAuthorized()) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        $label = filter_var($this->jsonData['label'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $parentId = filter_var($this->jsonData['parentId'], FILTER_SANITIZE_NUMBER_INT) ?? 0;
        $this->categoriesManager->createCategory($label, $parentId);
        $response->status = ApiResponse::STATUS_CREATED;
        return $response;
    }

    public function deleteCategory() {
        // Called by Ajax
        $response = new ApiResponse();
        $id = (int) $this->jsonData['id'] ?? 0;
        if (!$this->user->isAuthorized()) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        if ($this->categoriesManager->isCategoryExist($id)) {
            if ($this->categoriesManager->deleteCategory($id)) {
                $response->status = ApiResponse::STATUS_NO_CONTENT;
            } else {
                $response->status = ApiResponse::STATUS_NOT_FOUND;
            }
        }
        return $response;
    }

    public function editCategory() {
        // Called By Fancybox
        if (!$this->user->isAuthorized()) {
            echo 'forbidden';
            die();
        }
        $response = new StringResponse();
        $tpl = $response->createPluginTemplate('blog', 'admin-edit-category');
        $id = (int) $_POST['id'] ?? 0;
        if (!$this->categoriesManager->isCategoryExist($id)) {
            echo 'dont exist';
            die();
        }
        $category = $this->categoriesManager->getCategory($id);

        $tpl->set('categoriesManager', $this->categoriesManager);
        $tpl->set('category', $category);
        $tpl->set('token', $this->user->token);
        $response->addTemplate($tpl);
        return $response;
    }

    public function saveCategory($id) {
        // Called by Ajax
        $response = new ApiResponse();
        if (!$this->user->isAuthorized()) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        $label = filter_var($this->jsonData['label'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $parentId = (int)filter_var($this->jsonData['parentId'], FILTER_SANITIZE_NUMBER_INT) ?? 0;
        if (!$this->categoriesManager->isCategoryExist($id)) {
            $response->status = ApiResponse::STATUS_NOT_FOUND;
            return $response;
        }
        if ($parentId !== 0 && !$this->categoriesManager->isCategoryExist($parentId)) {
            $response->status = ApiResponse::STATUS_BAD_REQUEST;
            return $response;
        }
        $category = $this->categoriesManager->getCategory($id);
        $category->parentId = $parentId;
        $category->label = $label;
        $this->categoriesManager->saveCategory($category);
        $response->status = ApiResponse::STATUS_ACCEPTED;
        return $response;
    }

    public function listAjaxCategories() {
        echo $this->categoriesManager->outputAsList();
        die();
    }


}
```

### Fichier : BlogAdminCommentsController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\controllers\BlogAdminCommentsController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class BlogAdminCommentsController extends AdminController {

    public newsManager $newsManager;

    public function __construct()
    {
        parent::__construct();
                                // Vérification si l'utilisateur est administrateur
        $this->checkAccess('admin');
        $this->newsManager = new newsManager();
    }

    public function listComments($id) {
        $this->newsManager->loadComments($id);
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('blog', 'admin-list-comments');

        $tpl->set('newsManager', $this->newsManager);
        $tpl->set('idPost', $id);
        $tpl->set('token', $this->user->token);

        $response->addTemplate($tpl);
        return $response;
    }

    public function deleteComment()
    {
        $response = new ApiResponse();
        if (!$this->user->isAuthorized()) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        $idPost = (int) $this->jsonData['idPost'] ?? 0;
        $idComment = (int) $this->jsonData['idComment'] ?? 0;

        $this->newsManager->loadComments($idPost);
        $comment = $this->newsManager->createComment($idComment);
        if (!$comment) {
            $response->status = ApiResponse::STATUS_NOT_FOUND;
            return $response;
        }
        if ($this->newsManager->delComment($comment)) {
            $response->status = ApiResponse::STATUS_NO_CONTENT;
        } else {
            $response->status = ApiResponse::STATUS_BAD_REQUEST;
        }
        return $response;
    }

    public function saveComment() {
        $response = new ApiResponse();
        $response->body = 'Updated';
        if (!$this->user->isAuthorized()) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        $idPost = (int) $this->jsonData['idPost'] ?? 0;
        $idComment = (int) $this->jsonData['idComment'] ?? 0;
        $content = $this->jsonData['content'] ?? '';
        $this->newsManager->loadComments($idPost);
        $comment = $this->newsManager->createComment($idComment);
        if (!$comment) {
            $response->status = ApiResponse::STATUS_NOT_FOUND;
            return $response;
        }
        $comment->setContent($content);
        if ($this->newsManager->saveComment($comment)) {
            $response->status = ApiResponse::STATUS_ACCEPTED;
            $response->body = 'Updated';
        } else {
            $response->status = ApiResponse::STATUS_BAD_REQUEST;
        }
        return $response;
    }
}
```

### Fichier : BlogAdminConfigController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\controllers\BlogAdminConfigController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class BlogAdminConfigController extends AdminController {

    public function saveConfig() {
        if ($this->user->isAuthorized()) {
            $this->runPlugin->setConfigVal('label', trim($_REQUEST['label']));
            $this->runPlugin->setConfigVal('itemsByPage', trim(intval($_REQUEST['itemsByPage'])));
            $this->runPlugin->setConfigVal('hideContent', (isset($_POST['hideContent']) ? 1 : 0));
            $this->runPlugin->setConfigVal('comments', (isset($_POST['comments']) ? 1 : 0));
            $this->runPlugin->setConfigVal('displayTOC', filter_input(INPUT_POST, 'displayTOC', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $this->runPlugin->setConfigVal('displayAuthor', (isset($_POST['displayAuthor']) ? 1 : 0));
            $this->runPlugin->setConfigVal('authorName', trim($_POST['authorName']));
            $this->runPlugin->setConfigVal('authorAvatar', trim($_POST['authorAvatar']));
            $this->runPlugin->setConfigVal('authorBio', $this->core->callHook('beforeSaveEditor',htmlspecialchars($_POST['authorBio'])));
            if ($this->pluginsManager->savePluginConfig($this->runPlugin)) {
                show::msg(lang::get('core-changes-saved'), 'success');
            } else {
                show::msg(lang::get('core-changes-not-saved'), 'error');
            }            
        }
        // Open the posts list
        $controller = new BlogAdminPostsController();
        return $controller->list();
    }
}
```

### Fichier : BlogAdminPostsController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\controllers\BlogAdminPostsController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class BlogAdminPostsController extends AdminController
{

    public BlogCategoriesManager $categoriesManager;

    public newsManager $newsManager;

    public function __construct()
    {
        parent::__construct();
                                // Vérification si l'utilisateur est administrateur
        $this->checkAccess('admin');
        $this->categoriesManager = new BlogCategoriesManager();
        $this->newsManager = new newsManager();
    }

    public function list()
    {
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('blog', 'admin-list');

        $tpl->set('newsManager', $this->newsManager);
        $tpl->set('token', $this->user->token);

        $response->addTemplate($tpl);
        return $response;
    }

    public function deletePost()
    {
        $response = new ApiResponse();
        if (!$this->user->isAuthorized()) {
            $response->status = ApiResponse::STATUS_NOT_AUTHORIZED;
            return $response;
        }
        $newsManager = new newsManager();
        $id = (int) $this->jsonData['id'] ?? 0;
        $item = $newsManager->create($id);
        if (!$item) {
            $response->status = ApiResponse::STATUS_NOT_FOUND;
            return $response;
        }
        $title = $item->getName();
        if ($newsManager->delNews($item)) {
            logg($this->user->email . ' deleted post ' . $title);
            $response->status = ApiResponse::STATUS_NO_CONTENT;
        } else {
            $response->status = ApiResponse::STATUS_BAD_REQUEST;
        }
        return $response;
    }

    public function editPost($id = false)
    {
        if ($id === false) {
            $news = new news();
            $showDate = false;
        } else {
            $news = $this->newsManager->create($id);
            $showDate = true;
            if ($news === false) {
                // News id dont exist
                show::msg(lang::get('blog-item-dont-exist'), 'error');
                $this->core->redirect($this->router->generate('admin-blog-list'));
            }
        }
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('blog', 'admin-edit');

        $contentEditor = new Editor('blogContent', $news->getContent(), lang::get('blog-content'));

        $tpl->set('contentEditor', $contentEditor);
        $tpl->set('news', $news);
        $tpl->set('news', $news);
        $tpl->set('showDate', $showDate);
        $tpl->set('categoriesManager', $this->categoriesManager);

        $response->addTemplate($tpl);
        return $response;
    }

    public function savePost()
    {
        if (!$this->user->isAuthorized()) {
            return $this->list();
        }
        $imgId = (isset($_POST['delImg'])) ? '' : $_REQUEST['imgId'];
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            if ($this->pluginsManager->isActivePlugin('galerie')) {
                $galerie = new galerie();
                $img = new galerieItem(array('category' => ''));
                $img->setTitle($_POST['name'] . ' ('.lang::get('blog-featured-img').')');
                $img->setHidden(1);
                $galerie->saveItem($img);
                $imgId = $galerie->getLastId() . '.' . util::getFileExtension($_FILES['file']['name']);
            }
        }
        $contentEditor = new Editor('blogContent', '', lang::get('blog-content'));


        $news = ($_REQUEST['id']) ? $this->newsManager->create($_REQUEST['id']) : new news();
        $news->setName($_REQUEST['name']);
        $news->setContent($contentEditor->getPostContent());
        $news->setIntro($this->core->callHook('beforeSaveEditor', htmlspecialchars($_REQUEST['intro'])));
        $news->setSEODesc($_REQUEST['seoDesc']);
        $news->setDraft((isset($_POST['draft']) ? 1 : 0));
        if (!isset($_REQUEST['date']) || $_REQUEST['date'] == "")
            $news->setDate($news->getDate());
        else
            $news->setDate($_REQUEST['date']);
        $news->setImg($imgId);
        $news->setCommentsOff((isset($_POST['commentsOff']) ? 1 : 0));
        if ($this->newsManager->saveNews($news)) {
            $choosenCats = [];
            if (isset($_POST['categoriesCheckbox'])) {
                foreach ($_POST['categoriesCheckbox'] as $cat) {
                    $choosenCats[] = (int) $cat;
                }
            }
            $label = filter_input(INPUT_POST, 'category-add-label', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($label !== '') {
                $parentId = filter_input(INPUT_POST, 'category-add-parentId', FILTER_VALIDATE_INT) ?? 0;
                $choosenCats[] = $this->categoriesManager->createCategory($label, $parentId);
            }
            BlogCategoriesManager::saveItemToCategories($news->getId(), $choosenCats);
            show::msg(lang::get('core-changes-saved'), 'success');
        } else {
            show::msg(lang::get('core-changes-not-saved'), 'error');
        }
        $this->core->redirect($this->router->generate('admin-blog-edit-post', ['id' => $news->getId()]));
    }
}
```

### Fichier : BlogListController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\controllers\BlogListController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit ('No direct script access allowed');

class BlogListController extends PublicController
{

    public function home($currentPage = 1)
    {
        $newsManager = new newsManager();
        $categoriesManager = new BlogCategoriesManager();
        // Mode d'affichage
        $mode = ($newsManager->count() > 0) ? 'list' : 'list_empty';

        // Contruction de la pagination
        $nbNews = $newsManager->getNbItemsToPublic();
        $newsByPage = $this->runPlugin->getConfigVal('itemsByPage');
        $nbPages = ceil($nbNews / $newsByPage);
        $start = ($currentPage - 1) * $newsByPage + 1;
        $end = $start + $newsByPage - 1;
        if ($nbPages > 1) {
            $pagination = [];
            for ($i = 0; $i != $nbPages; $i++) {
                if ($i != 0)
                    $pagination[$i]['url'] = $this->router->generate('blog-page', ['page' => $i + 1]);
                else
                    $pagination[$i]['url'] = $this->runPlugin->getPublicUrl();
                $pagination[$i]['num'] = $i + 1;
            }
        } else {
            $pagination = false;
        }
        // Récupération des news
        $news = [];
        $i = 1;
        foreach ($newsManager->getItems() as $k => $v)
            if (!$v->getDraft()) {
                $date = $v->getDate();
                if ($i >= $start && $i <= $end) {
                    $news[$k]['name'] = $v->getName();
                    $news[$k]['date'] = util::FormatDate($date, 'en', 'fr');
                    $news[$k]['id'] = $v->getId();
                    $news[$k]['cats'] = [];
                    foreach ($categoriesManager->getCategories() as $cat) {
                        if (in_array($v->getId(), $cat->items)) {
                            $news[$k]['cats'][] = [
                                'label' => $cat->label,
                                'url' => $this->router->generate('blog-category', ['name' => util::strToUrl($cat->label), 'id' => $cat->id]),
                            ];
                        }
                    }
                    $news[$k]['content'] = $v->getContent();
                    $news[$k]['intro'] = $v->getIntro();
                    $news[$k]['url'] = $this->runPlugin->getPublicUrl() . util::strToUrl($v->getName()) . '-' . $v->getId() . '.html';
                    $news[$k]['img'] = $v->getImg();
                    $news[$k]['imgUrl'] = util::urlBuild(UPLOAD . 'galerie/' . $v->getImg());
                    $news[$k]['commentsOff'] = $v->getcommentsOff();
                }
                $i++;
            }
        // Traitements divers : métas, fil d'ariane...
        $this->runPlugin->setMainTitle($this->pluginsManager->getPlugin('blog')->getConfigVal('label'));
        $this->runPlugin->setTitleTag($this->pluginsManager->getPlugin('blog')->getConfigVal('label') . ' : page ' . $currentPage);
        if ($this->runPlugin->getIsDefaultPlugin() && $currentPage == 1) {
            $this->runPlugin->setTitleTag($this->pluginsManager->getPlugin('blog')->getConfigVal('label'));
            $this->runPlugin->setMetaDescriptionTag($this->core->getConfigVal('siteDescription'));
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('blog', 'list');

        $tpl->set('news', $news);
        $tpl->set('newsManager', $newsManager);
        $tpl->set('pagination', $pagination);
        $tpl->set('mode', $mode);
        $response->addTemplate($tpl);
        return $response;
    }

    public function category($name,$id, $currentPage = 1)
    {
        $categoriesManager = new BlogCategoriesManager();
        $category = $categoriesManager->getCategory($id);
        if (!$category) {
            $this->core->error404();
        }
        $newsManager = new newsManager();
        $news = [];

        $newsByPage = $this->runPlugin->getConfigVal('itemsByPage');

        $start = ($currentPage - 1) * $newsByPage + 1;
        $end = $start + $newsByPage - 1;
        $i = 1;

        foreach ($newsManager->getItems() as $k => $v) {
            if ($v->getDraft()) {
                continue;
            }
            if (in_array($v->getId(), $category->items)) {
                $date = $v->getDate();
                if ($i >= $start && $i <= $end) {
                    $news[$k]['name'] = $v->getName();
                    $news[$k]['date'] = util::FormatDate($date, 'en', 'fr');
                    $news[$k]['id'] = $v->getId();
                    $news[$k]['cats'] = [];
                    foreach ($categoriesManager->getCategories() as $cat) {
                        if (in_array($v->getId(), $cat->items)) {
                            $news[$k]['cats'][] = [
                                'label' => $cat->label,
                                'url' => $this->router->generate('blog-category', ['name' => util::strToUrl($cat->label), 'id' => $cat->id]),
                            ];
                        }
                    }
                    $news[$k]['content'] = $v->getContent();
                    $news[$k]['intro'] = $v->getIntro();
                    $news[$k]['url'] = $this->runPlugin->getPublicUrl() . util::strToUrl($v->getName()) . '-' . $v->getId() . '.html';
                    $news[$k]['img'] = $v->getImg();
                    $news[$k]['imgUrl'] = util::urlBuild(UPLOAD . 'galerie/' . $v->getImg());
                    $news[$k]['commentsOff'] = $v->getcommentsOff();

                }
                $i++;
            }
        }
        $nbNews = $i - 1;
        $mode = ($nbNews > 0) ? 'list' : 'list_empty';
        if ($mode === 'list') {
            $nbPages = ceil($nbNews / $newsByPage);
            if ($currentPage > $nbPages) {
                return $this->category($name,$id, 1);
            }
            if ($nbPages > 1) {
                $pagination = [];
                for ($i = 0; $i != $nbPages; $i++) {
                    if ($i != 0)
                        $pagination[$i]['url'] = $this->router->generate('blog-category-page', ['name' => util::strToUrl($category->label), 'id' => $category->id, 'page' => $i + 1]);
                    else
                        $pagination[$i]['url'] = $this->router->generate('blog-category', ['name' => util::strToUrl($category->label), 'id' => $category->id]);
                    $pagination[$i]['num'] = $i + 1;
                }
            } else {
                $pagination = false;
            }
        } else {
            $pagination = false;
        }



        // Traitements divers : métas, fil d'ariane...
        $this->runPlugin->setMainTitle('News de la catégorie ' . $category->label);
        $this->runPlugin->setTitleTag($this->pluginsManager->getPlugin('blog')->getConfigVal('label') . ' : page ' . $currentPage);
        if ($this->runPlugin->getIsDefaultPlugin() && $currentPage == 1) {
            $this->runPlugin->setTitleTag($this->pluginsManager->getPlugin('blog')->getConfigVal('label'));
            $this->runPlugin->setMetaDescriptionTag($this->core->getConfigVal('siteDescription'));
        }

        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('blog', 'list');

        $tpl->set('news', $news);
        $tpl->set('newsManager', $newsManager);
        $tpl->set('pagination', $pagination);
        $tpl->set('mode', $mode);
        $response->addTemplate($tpl);
        return $response;
    }

    public function page(int $page)
    {
        $page = $page > 1 ? $page : 1;
        return $this->home($page);
    }

    public function categoryPage(int $id, string $name, int $page)
    {
        $page = $page > 1 ? $page : 1;
        return $this->category($name,$id, $page);
    }
}
```

### Fichier : BlogReadController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\controllers\BlogReadController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2023, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class BlogReadController extends PublicController
{

    public function read($name, $id)
    {
        $antispam = ($this->pluginsManager->isActivePlugin('antispam')) ? new antispam() : false;
        $newsManager = new newsManager();
        $categoriesManager = new BlogCategoriesManager();

        $item = $newsManager->create($id);
        if (!$item) {
            $this->core->error404();
        }

        $newsManager->loadComments($item->getId());
        $this->addMetas($item);

        $antispamField = ($antispam) ? $antispam->show() : '';
        // Traitements divers : métas, fil d'ariane...
        $this->runPlugin->setMainTitle($item->getName());
        $this->runPlugin->setTitleTag($item->getName());

        $generatedHTML = util::generateIdForTitle(htmlspecialchars_decode($item->getContent()));
        $toc = $this->generateTOC($generatedHTML);

        $categories = [];
        foreach ($categoriesManager->getCategories() as $cat) {
            if (in_array($item->getId(), $cat->items)) {
                $categories[] = [
                    'label' => $cat->label,
                    'url' => $this->router->generate('blog-category', ['name' => util::strToUrl($cat->label), 'id' => $cat->id]),
                ];
            }
        }
        
        $response = new PublicResponse();
        $tpl = $response->createPluginTemplate('blog', 'read');

        show::addSidebarPublicModule('Catégories du blog', $this->generateCategoriesSidebar());
        show::addSidebarPublicModule('Derniers commentaires', $this->generateLastCommentsSidebar());

        $tpl->set('antispam', $antispam);
        $tpl->set('antispamField', $antispamField);
        $tpl->set('item', $item);
        $tpl->set('generatedHtml', $generatedHTML);
        $tpl->set('TOC', $toc);
        $tpl->set('categories', $categories);
        $tpl->set('newsManager', $newsManager);
        $tpl->set('commentSendUrl', $this->router->generate('blog-send'));

        $response->addTemplate($tpl);
        return $response;

    }

    protected function generateTOC($html)
    {
        $displayTOC = $this->runPlugin->getConfigVal('displayTOC');
        $toc = false;

        if ($displayTOC === 'content') {
            $toc = util::generateTableOfContents($html, lang::get('blog-toc-title'));
            if (!$toc) {
                return false;
            }
        } elseif ($displayTOC === 'sidebar') {
            $toc = util::generateTableOfContentAsModule($html);
            if ($toc) {
                show::addSidebarPublicModule(lang::get('blog-toc-title'), $toc);
                return false;
            }
        }
        return $toc;
    }

    protected function addMetas($item)
    {
        $this->core->addMeta('<meta property="og:url" content="' . util::getCurrentURL() . '" />');
        $this->core->addMeta('<meta property="twitter:url" content="' . util::getCurrentURL() . '" />');
        $this->core->addMeta('<meta property="og:type" content="article" />');
        $this->core->addMeta('<meta property="og:title" content="' . $item->getName() . '" />');
        $this->core->addMeta('<meta name="twitter:card" content="summary" />');
        $this->core->addMeta('<meta name="twitter:title" content="' . $item->getName() . '" />');
        $this->core->addMeta('<meta property="og:description" content="' . $item->getSEODesc() . '" />');
        $this->core->addMeta('<meta name="twitter:description" content="' . $item->getSEODesc() . '" />');

        if ($this->pluginsManager->isActivePlugin('galerie') && galerie::searchByfileName($item->getImg())) {
            $this->core->addMeta('<meta property="og:image" content="' . util::urlBuild(UPLOAD . 'galerie/' . $item->getImg()) . '" />');
            $this->core->addMeta('<meta name="twitter:image" content="' . util::urlBuild(UPLOAD . 'galerie/' . $item->getImg()) . '" />');
        }
    }

    protected function generateLastCommentsSidebar(int $nbComments = 10) {
        $comments = newsManager::getLatestComments($nbComments);
        $str = '<ul class="comments-recent-list">';
        foreach ($comments as $comment) {
            $str .= "<li class='comment-recent'>";
            $str .= "<span class='comment-recent-author'>";
            if ($comment['comment']->getAuthorWebsite()) {
                $str .= "<a href='" . $comment['comment']->getAuthorWebsite() . "'>" . $comment['comment']->getAuthor() . "</a>";
            } else {
                $str .= $comment['comment']->getAuthor();
            }
            $str .= "</span> ";
            $str .= Lang::get('blog.comments.in');
            $str .= " <span class='comment-recent-news'>";
            $str .= "<a href='" . $comment['news']->getUrl() . "#comment". $comment['comment']->getId() ."'>" .$comment['news']->getName() . "</a></span>";
            $str .= "</li>";
        }
        $str .= "</ul>";
        return $str;
    }

    public function send()
    {
        $antispam = ($this->pluginsManager->isActivePlugin('antispam')) ? new antispam() : false;
        $newsManager = new newsManager();
        // quelques contrôle et temps mort volontaire avant le send...
        sleep(2);
        if ($this->runPlugin->getConfigVal('comments') && $_POST['_author'] == '') {
            if (($antispam && $antispam->isValid()) || !$antispam) {
                $idNews = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? 0;
                $item = $newsManager->create($idNews);
                if ($item && $item->getCommentsOff() == false) {
                    $newsManager->loadComments($idNews);
                    $comment = new newsComment();
                    $comment->setIdNews($idNews);
                    $comment->setAuthor($_POST['author']);
                    $email = filter_input(INPUT_POST,'authorEmail', FILTER_VALIDATE_EMAIL);
                    if ($email !== false) {
                        $comment->setAuthorEmail($_POST['authorEmail']);
                    } else {
                        show::msg(Lang::get("blog.comments.bad-mail"), 'error');
                        header('location:' . $_POST['back']);
                        die();
                    }
                    
                    $comment->setAuthorWebsite(filter_input(INPUT_POST, 'authorWebsite', FILTER_VALIDATE_URL) ?? null);
                    $comment->setDate('');
                    $comment->setContent($_POST['commentContent']);
                    $parentId = filter_input(INPUT_POST, 'commentParentId', FILTER_VALIDATE_INT) ?? 0;
                    if ($parentId !== 0) {
                        $newsManager->addReplyToComment($comment, $parentId);
                    }
                    if ($newsManager->saveComment($comment)) {
                        header('location:' . $_POST['back'] . '#comment' . $comment->getId());
                        die();
                    }
                }

            }
        }
        header('location:' . $_POST['back']);
        die();
    }

    public function rss()
    {
        $newsManager = new newsManager();
        echo $newsManager->rss();
        die();
    }

    protected function generateCategoriesSidebar() {
        $content = '';
        $categoriesManager = new BlogCategoriesManager();
        $categories = $categoriesManager->getNestedCategories();
        if (empty($categories)) {
            return false;
        }
        $content .= '<ul>';
        foreach ($categories as $category) {
            $content .= $this->generateCategorySidebar($category);
        }
        $content .= '</ul>';
        return $content;
    }

    protected function generateCategorySidebar($category) {
        $router = router::getInstance();
        $content = '<li><a href="' . $router->generate('blog-category', ['name' => util::strToUrl($category->label), 'id' => $category->id]) . '">' .
            $category->label . '</a>';
        if (!empty($category->children)) {
            $content .= '<ul>';
            foreach ($category->children as $child) {
                $content .= $this->generateCategorySidebar($child);
            }
            $content .= '</ul>';
        }
        $content .= '</li>';
        return $content;
    }
}
```

### Fichier : BlogCategoriesManager.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\entities\BlogCategoriesManager.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class BlogCategoriesManager extends CategoriesManager {

    protected string $pluginId = 'blog';
    protected string $name = 'categories';
    protected string $className = 'BlogCategory';
    protected bool $nested = true;
    protected bool $chooseMany = true;

    public function getAddCategoryUrl():string {
        return router::getInstance()->generate('admin-blog-add-category');
    }

    public function getDeleteUrl() :string {
        return router::getInstance()->generate('admin-blog-delete-category');
    }

    public function getAjaxDisplayListUrl():string {
        return router::getInstance()->generate('admin-blog-list-ajax-categories');
    }

    public function getEditUrl():string {
        return router::getInstance()->generate('admin-blog-edit-category');
    }

    public function outputAsList() {
        echo '<section id="categories_panel">';
        echo '<header>'. lang::get('blog-categories-management-title').'</header>';
        echo parent::outputAsList();
        echo '</section>';
    }
}
```

### Fichier : BlogCategory.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\entities\BlogCategory.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2023, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class BlogCategory extends Category {

    protected string $pluginId = 'blog';
    protected string $name = 'categories';
    protected bool $nested = true;
    protected bool $chooseMany = true;


}
```

### Fichier : news.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\entities\news.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class news
{

    private $id;
    private $name;
    private $date;
    private $content;
    private $intro;
    private $seoDesc;
    private $draft;
    private $img;
    private $commentsOff;

    /**
     * Array with Id of categories
     * @var array
     */
    public array $categories = [];

    public function __construct($val = array())
    {
        if (count($val) > 0) {
            $this->id = $val['id'];
            $this->name = $val['name'];
            $this->content = $val['content'];
            $this->intro = $val['intro'] ?? '';
            $this->seoDesc = $val['seoDesc'] ?? '';
            $this->date = $val['date'];
            $this->draft = $val['draft'];
            $this->img = (isset($val['img']) ? $val['img'] : '');
            $this->commentsOff = (isset($val['commentsOff']) ? $val['commentsOff'] : 0);
            $this->categories = $val['categories'] ?? [];
        }
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setName($val)
    {
        $this->name = trim($val);
    }

    public function setContent($val)
    {
        $this->content = trim($val);
    }

    public function setIntro($val)
    {
        $this->intro = trim($val);
    }

    public function setSEODesc($val)
    {
        $this->seoDesc = trim($val);
    }

    public function setDate($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d');
        }
        $val = trim($val);
        $this->date = $val;
    }

    public function setDraft($val)
    {
        $this->draft = trim($val);
    }

    public function setImg($val)
    {
        $this->img = trim($val);
    }

    public function setCommentsOff($val)
    {
        $this->commentsOff = trim($val);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getUrl()
    {
        return router::getInstance()->generate('blog-read', ['name' => util::strToUrl($this->name), 'id' => $this->id]);
    }

    public function getIntro()
    {
        return ($this->intro === '' ? false : $this->intro);
    }

    public function getSEODesc()
    {
        return ($this->seoDesc === '' ? false : $this->seoDesc);
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getReadableDate() {
        return util::getDate($this->date);
    }

    public function getDraft()
    {
        return $this->draft;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function getImgUrl()
    {
        return util::urlBuild(UPLOAD . 'galerie/' . $this->img);
    }

    public function getCommentsOff()
    {
        return $this->commentsOff;
    }

}
```

### Fichier : newsComment.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\entities\newsComment.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class newsComment
{

    private $id;
    private $idNews;
    private $author;
    private $authorEmail;
    private $authorWebsite;
    private $date;
    private $content;

    /**
     * Array with ID of children comments
     */
    public $repliesId;

    public $replies = [];

    public function __construct($val = array())
    {

        if (count($val) > 0) {
            $this->id = $val['id'];
            $this->idNews = $val['idNews'];
            $this->content = $val['content'];
            $this->date = $val['date'];
            $this->author = $val['author'];
            $this->authorEmail = $val['authorEmail'];
            $this->authorWebsite = $val['authorWebsite'];
            $this->repliesId = $val['replies'] ?? [];
        } else {
            $this->id = time();
        }
    }

    public function hasReplies(): bool
    {
        return (!empty($this->replies));
    }

    public function show()
    {
        
        $tpl = new Template(PLUGINS . 'blog/template/comment.tpl');
        $tpl->set('comment', $this);
        echo $tpl->output();
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setIdNews($val)
    {
        $this->idNews = intval($val);
    }

    public function setAuthor($val)
    {
        $this->author = trim(htmlEntities($val, ENT_QUOTES));
    }

    public function setAuthorEmail($val)
    {
        $this->authorEmail = trim(htmlEntities($val, ENT_QUOTES));
    }

    public function setAuthorWebsite($val)
    {
        $this->authorWebsite = trim(htmlEntities($val, ENT_QUOTES));
    }

    public function setDate($val)
    {
        $val = trim($val);
        if ($val == '')
            $val = new DateTime();
        $this->date = $val->getTimestamp();
    }

    public function setContent($val)
    {
        $this->content = trim(htmlEntities($val, ENT_QUOTES));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIdNews()
    {
        return $this->idNews;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function getAuthorWebsite()
    {
        return $this->authorWebsite;
    }

    public function getAuthorAvatar()
    {
        return 'https://seccdn.libravatar.org/avatar/' . md5($this->authorEmail) . '?s=200';
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getContent()
    {
        return $this->content;
    }

}
```

### Fichier : newsManager.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\entities\newsManager.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class newsManager {

    private $items;
    private $comments;

    /**
     * All comments from a news, non imbricated
     */
    protected $flatComments;

    private int $nbItemsToPublic;

    public function __construct() {
        $categoriesManager = new BlogCategoriesManager();
        $i = 0;
        $data = [];
        if (file_exists(ROOT . 'data/plugin/blog/blog.json')) {
            $temp = util::readJsonFile(ROOT . 'data/plugin/blog/blog.json');
            if (!is_array($temp)) {
                $temp = [];
            }
            $temp = util::sort2DimArray($temp, 'date', 'desc');
            foreach ($temp as $k => $v) {
                $categories = [];
                foreach ($categoriesManager->getCategories() as $cat) {
                    if (in_array($v['id'], $cat->items)) {
                        $categories['categories'][$cat->id] = [
                            'label' => $cat->label,
                            'url' => router::getInstance()->generate('blog-category', ['name' => util::strToUrl($cat->label), 'id' => $cat->id]),
                        ];
                    }
                }
                $v = array_merge($v, $categories);
                $data[] = new news($v);
                if ($v['draft'] === "0") {
                    $i++;
                }
            }
        }
        $this->nbItemsToPublic = $i;
        $this->items = $data;
    }

    // News

    public function getItems() {
        return $this->items;
    }


    /**
     * Summary of create
     * @param mixed $id
     * @return \news | boolean
     */
    public function create($id) {
        foreach ($this->items as $obj) {
            if ($obj->getId() == $id)
                return $obj;
        }
        return false;
    }

    public function saveNews($obj) {
        $id = intval($obj->getId());
        if ($id < 1) {
            $obj->setId($this->makeId());
            $this->items[] = $obj;
        } else {
            foreach ($this->items as $k => $v) {
                if ($id == $v->getId())
                    $this->items[$k] = $obj;
            }
        }
        return $this->save();
    }

    /**
     * Delete a News from blog & her comments
     * @param news $obj
     * @return bool News correctly deleted
     */
    public function delNews(\news $obj):bool
    {
        $id = $obj->getId();
        foreach ($this->items as $k => $v) {
            if ($id == $v->getId())
                unset($this->items[$k]);
        }
        if ($this->save()) {
            return $this->deleteCommentsFromNews($id);
        }
        return false;
    }

    public function count() {
        return count($this->items);
    }

    /**
     * Return number of news who can be displayed in public mode
     */
    public function getNbItemsToPublic() {
        return $this->nbItemsToPublic;
    }

    public function rss() {
        $core = core::getInstance();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0">';
        $xml .= '<channel>';
        $xml .= ' <title>' . $core->getConfigVal('siteName') . ' - ' . pluginsManager::getPluginConfVal('blog', 'label') . '</title>';
        $xml .= ' <link>' . $core->getConfigVal('siteUrl') . '/</link>';
        $xml .= ' <description>' . $core->getConfigVal('siteDescription') . '</description>';
        $xml .= ' <language>' . $core->getConfigVal('siteLang') . '</language>';
        foreach ($this->getItems() as $k => $v)
            if (!$v->getDraft()) {
                $xml .= '<item>';
                $xml .= '<title><![CDATA[' . $v->getName() . ']]></title>';
                $xml .= '<link>' . $core->getConfigVal('siteUrl') . '/news/' . util::strToUrl($v->getName()) . '-' . $v->getId() . '.html</link>';
                $xml .= '<pubDate>' . (date("D, d M Y H:i:s O", strtotime($v->getDate()))) . '</pubDate>';
                $xml .= '<description><![CDATA[' . $v->getContent() . ']]></description>';
                $xml .= '</item>';
            }
        $xml .= '</channel>';
        $xml .= '</rss>';
        header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo $xml;
        die();
    }

    private function makeId() {
        $ids = array(0);
        foreach ($this->items as $obj) {
            $ids[] = $obj->getId();
        }
        return max($ids) + 1;
    }

    private function save() {
        $data = array();
        foreach ($this->items as $k => $v) {
            $data[] = array(
                'id' => $v->getId(),
                'name' => $v->getName(),
                'content' => $v->getContent(),
                'intro' => $v->getIntro(),
                'seoDesc' => $v->getSEODesc(),
                'date' => $v->getDate(),
                'draft' => $v->getDraft(),
                'img' => $v->getImg(),
                'commentsOff' => $v->getCommentsOff(),
            );
        }
        if (util::writeJsonFile(ROOT . 'data/plugin/blog/blog.json', $data))
            return true;
        return false;
    }

    // Comments

    public function getComments() {
        return $this->comments;
    }

    public function getFlatComments() {
        return $this->flatComments;
    }

    public function createComment($id) {
        foreach ($this->flatComments as $obj) {
            if ($obj->getId() == $id)
                return $obj;
        }
        return false;
    }

    public function loadComments($idNews) {
        if (!file_exists(DATA_PLUGIN . 'blog/comments.json'))
            util::writeJsonFile(DATA_PLUGIN . 'blog/comments.json', []);
        $temp = util::readJsonFile(DATA_PLUGIN . 'blog/comments.json');
        $comments = $temp[$idNews] ?? [];
        $temp = util::sort2DimArray($comments, 'id', 'asc');
        $data = [];
        foreach ($temp as $v) {
            $data[$v['id']] = new newsComment($v);
        }
        $this->flatComments = $data;
        $this->comments = $data;
        foreach($this->comments as $k => &$com) {
            foreach($com->repliesId as $comId) {
                $com->replies[$comId] = $data[$comId];
                unset($this->comments[$comId]);   
            }
            foreach($com->replies as &$comment) {
                $comment = $this->hydrateReplies($comment);
            }
        }
    }

    protected function deleteCommentsFromNews($idNews) {
        $temp = util::readJsonFile(DATA_PLUGIN . 'blog/comments.json');
        unset($temp[$idNews]);
        return util::writeJsonFile(DATA_PLUGIN . 'blog/comments.json', $temp);
    }

    public static function getLatestComments(int $nbComments = 10) {
        $newsManager = new newsManager();
        $rawComments = util::readJsonFile(DATA_PLUGIN . 'blog/comments.json');
        $timeComments = [];
        foreach ($rawComments as $idNews => $comments) {
            $news = $newsManager->create($idNews);
            foreach ($comments as $comment) {
                $timeComments[util::getTimestampFromDate($comment['date'])] = ['comment' => new newsComment($comment),'news' => $news];
            }
        }
        krsort($timeComments);
        return array_slice($timeComments, 0, $nbComments, true);
    }

    protected function hydrateReplies(\newsComment $comment):newsComment {
        if (!empty($comment->repliesId)) {
            foreach($comment->repliesId as $comId) {
                $comment->replies[$comId] = $this->comments[$comId];
                unset($this->comments[$comId]);
            }
            foreach($comment->replies as &$childComment) {
                $childComment = $this->hydrateReplies($childComment);
            }
        }
        return $comment;

    }

    public function countComments($idNews = 0) {
        if ($idNews == 0)
            return count($this->flatComments);
        else {
            $this->loadComments($idNews);
            return count($this->flatComments);
        }
    }

    public function saveComment(\newsComment $comment) {
        
        $this->flatComments[$comment->getId()] = $comment;
        $this->saveComments($comment->getIdNews());
        return true;
    }

    public function addReplyToComment(\newsComment $comment, int $parentId):void {
        if (isset($this->flatComments[$parentId]) && $comment->getIdNews()) {
            $this->flatComments[$parentId]->repliesId[] = $comment->getId();
            $this->flatComments[$parentId]->repliesId = array_unique($this->flatComments[$parentId]->repliesId);
            $this->saveComments($comment->getIdNews());
        }
    }

    protected function saveComments($idNews):void {
        $rawComments = util::readJsonFile(DATA_PLUGIN . 'blog/comments.json');
        $objData = [];
        foreach ($rawComments as $newsId => $comment) {
            foreach ($comment as $idComment => $v) {
                $objData[$newsId][$idComment] = new newsComment($v);
            }
        }
        $objData[$idNews] = $this->flatComments;
        $data = [];
        foreach ($objData as $newsId) {
            foreach ($newsId as $k => $v) {
                logg($newsId);
                $data[$v->getIdNews()][$k] = [
                    'id' => $v->getId(),
                    'idNews' => $v->getIdNews(),
                    'content' => $v->getContent(),
                    'date' => $v->getDate(),
                    'author' => $v->getAuthor(),
                    'authorEmail' => $v->getAuthorEmail(),
                    'authorWebsite' => $v->getAuthorWebsite(),
                    'replies' => $v->repliesId
                ];
            }
        }
        util::writeJsonFile(DATA_PLUGIN . 'blog/comments.json', $data);
    }

    /**
     * Delete a comment and all occurences from this comment in others it was a reply
     * and save the comments
     * @param newsComment $obj
     * @return bool Comment deleted
     */
    public function delComment(\newsComment $obj):bool {
        $rawComments = util::readJsonFile(DATA_PLUGIN . 'blog/comments.json');
        if (isset($rawComments[$obj->getIdNews()][$obj->getId()])) {
            $idNews = $obj->getIdNews();
            $newsComments = &$rawComments[$idNews];
            foreach ($newsComments as $idComment => &$comment) {
                if (!is_array($comment['replies'])) {
                    $comment['replies'] = [];
                }
                if (in_array($obj->getId(), $comment['replies'] )) {
                    $comment['replies'] = array_diff($comment['replies'], [$obj->getId()]);
                }
            }
            unset($rawComments[$obj->getIdNews()][$obj->getId()]);
        }
        return util::writeJsonFile(DATA_PLUGIN . 'blog/comments.json', $rawComments);
    }
}
```

### Fichier : en.ini
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\langs\en.ini

**Contenu :**
```
; General

blog.name = Blog
blog.description = "Management of a mini blog"

; Articles

blog.posted-date = "Posted at %s"
blog.back-to-list = "Back to the posts list"
blog.there-is-no-comment = "No comment for this post yet"

blog-posts-list = Blog Posts List
blog-add = Add a Post
blog-edit = "Edit Post"
blog-title = Title
blog-date = Date
blog-see = "View Post"
blog-item-dont-exist = "The item does not exist"
blog-content = Content
blog-date-placeh = "Example: 2017-07-06 12:28:51"
blog-intro = Introduction
blog-seo = SEO
blog-title = Title
blog-settings = Settings
blog-settings-post = "Post Settings"
blog-do-not-publish = "Do Not Publish (Draft)"
blog-disable-comments-once = "Disable Comments for This Post"
blog-featured-img = "Featured Image"
blog-intro-content = "Introduction Content"
blog-seo-content = "Description for Social Media"
blog-seo-content-tooltip = "One or two sentences summarizing the article. It is recommended not to exceed 250 characters."
blog-affect-new-category = "Add Post to a New Category"
blog-back-to-posts = "Back to Post List"

; Table of Contents

blog-toc-title = Table of Contents

; Categories

blog-categories = Categories
blog.categories.none = "No categories."
blog.categories.addOne = "Add one."
blog.categories.name = Name
blog.categories.itemsNumber = "Number of items"
blog.categories.actions = Actions
blog.categories.addCategory = Add a category
blog.categories.editCategory = Edit category
blog.categories.deleteCategory = Delete category
blog.categories.categoryName = Category name
blog.categories.categoryParent = Parent category
blog.categories.collapseExpandChildren = "Collapse/Expand child items"
blog-categories-created = "The category has been created"
blog-categories-not-created = "The category could not be created"
blog-categories-management-title = "Blog Categories Management"

; Comments

blog-comments = Comments
blog.comments.add-comment = Leave a comment
blog.comments.respond-to = "Respond to %s"
blog.comments.respond = Respond
blog.comments.cancel-response = Cancel response
blog.comments.none-comments = "No comments"
blog.comments-one-comment = "1 comment"
blog.comments-nb-comments = "%s comments"
blog.comments.in = in
blog.comments.bad-mail = "The entered email is invalid"
blog-comments-list = "Comments List"
blog.comments-name = "Name *"
blog.comments-mail = "Email *"
blog.comments-website = "Website"
blog.comments-content = "Comment *"
blog.comments-publish = "Publish comment"

; Configuration

blog-hide-content = "Hide post content in the list"
blog-hide-content-tooltip = "If checked, only displays the title in the list of posts."
blog-allow-comments = "Allow comments"
blog-page-title = "Page title"
blog-entries-per-page = "Entries per page"
blog-display-toc = "Display table of contents"
blog-toc-no = "No"
blog-toc-in-content = "In Content"
blog-toc-in-sidebar = "In Sidebar"
blog-display-author-block = "Display 'author' block"
blog-author-name = "Author's name"
blog-author-image = "Author's image"
blog-author-bio = "Biography"

```

### Fichier : fr.ini
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\langs\fr.ini

**Contenu :**
```
; General

blog.name = Blog
blog.description = "Gestion d'un mini blog"

; Articles

blog.posted-date = "Publié le %s"
blog.back-to-list = "Retour à la liste des posts"
blog.there-is-no-comment = "Aucun commentaire pour cet article"

blog-posts-list = Liste des articles de blog
blog-add = Ajouter un article
blog-edit = "Modifier l'article"
blog-title = Titre
blog-date = Date
blog-see = "Voir l'article"
blog-item-dont-exist = "L'élément n'existe pas"
blog-content = Contenu
blog-date-placeh = "Exemple : 2017-07-06 12:28:51"
blog-intro = Introduction
blog-seo = SEO
blog-title = Titre
blog-settings = Paramètres
blog-settings-post = "Paramètres de l'article"
blog-do-not-publish = "Ne pas publier (brouillon)"
blog-disable-comments-once = "Désactiver les commentaires pour cet article"
blog-featured-img = "Image à la une"
blog-intro-content = "Contenu d'introduction"
blog-seo-content = "Description pour les réseaux sociaux"
blog-seo-content-tooltip = "Une ou 2 phrases résumant l'article. Il est recommandé de ne pas dépasser les 250 caractères."
blog-affect-new-category = "Ajouter l'article à une nouvelle catégorie"
blog-back-to-posts = "Retour à la liste des articles"

; Table des matières

blog-toc-title = Table des matières

; Catégories

blog-categories = Catégories
blog.categories.none = Aucune catégorie.
blog.categories.addOne = Ajoutez-en une.
blog.categories.name = Nom
blog.categories.itemsNumber = "Nombre d'éléments"
blog.categories.actions = Actions
blog.categories.addCategory = Ajouter une catégorie
blog.categories.editCategory = Editer la catégorie
blog.categories.deleteCategory = Supprimer la catégorie
blog.categories.categoryName = Nom de la catégorie
blog.categories.categoryParent = Catégorie parente
blog.categories.collapseExpandChildren = Replier/Déplier les éléments enfants
blog-categories-created = "La catégorie a été créée"
blog-categories-not-created = "La catégorie n'a pas pu être créée"
blog-categories-management-title = "Gestion des catégories du blog"

; Commentaires

blog-comments = Commentaires
blog.comments.add-comment = Laisser un commentaire
blog.comments.respond-to = "Répondre à %s"
blog.comments.respond = Répondre
blog.comments.cancel-response = Annuler la réponse
blog.comments.none-comments = Aucun commentaire
blog.comments-one-comment = "1 commentaire"
blog.comments-nb-comments = "%s commentaires"
blog.comments.in = dans
blog.comments.bad-mail = "L'email entré est invalide"
blog-comments-list = "Liste des commentaires"
blog.comments-name = "Nom *"
blog.comments-mail = "Email *"
blog.comments-website = "Site Web"
blog.comments-content = "Commentaire *"
blog.comments-publish = "Publier le commentaire"

; Config

blog-hide-content = "Masquer le contenu des articles dans la liste"
blog-hide-content-tooltip = "Si cette case est cochée, n'affiche que le titre dans la liste des articles."
blog-allow-comments = "Autoriser les commentaires"
blog-page-title = "Titre de page"
blog-entries-per-page = "Nombre d'entrées par page"
blog-display-toc = "Afficher la table des matières"
blog-toc-no = "Non"
blog-toc-in-content = "Dans le contenu"
blog-toc-in-sidebar = "Dans la Sidebar"
blog-display-author-block = "Afficher le bloc 'auteur'"
blog-author-name = "Nom de l'auteur"
blog-author-image = "Image de l'auteur"
blog-author-bio = "Biographie"
```

### Fichier : ru.ini
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\langs\ru.ini

**Contenu :**
```
; Основное

blog.name = Блог
blog.description = "Управление мини-блогом"

; Статьи

blog.posted-date = "Опубликовано %s"
blog.back-to-list = "Вернуться к списку постов"
blog.there-is-no-comment = "К этому посту пока нет комментариев"

blog-posts-list = Список постов
blog-add = Добавить пост
blog-edit = "Редактировать пост"
blog-title = Заголовок
blog-date = Дата
blog-see = "Просмотреть пост"
blog-item-dont-exist = "Объект не существует"
blog-content = Содержание
blog-date-placeh = "Например: 2017-07-06 12:28:51"
blog-intro = Введение
blog-seo = SEO
blog-title = Заголовок
blog-settings = Настройки
blog-settings-post = "Настройки поста"
blog-do-not-publish = "Не публиковать (черновик)"
blog-disable-comments-once = "Отключить комментарии к этому посту"
blog-featured-img = "Изображение на странице"
blog-intro-content = "Вводное содержание"
blog-seo-content = "Описание для социальных сетей"
blog-seo-content-tooltip = "Одно или два предложения с кратким содержанием статьи. Рекомендуется не превышать 250 символов."
blog-affect-new-category = "Добавить пост в новую категорию"
blog-back-to-posts = "Назад к списку постов"

; Содержание

blog-toc-title = Содержание

; Категории

blog-categories = Категории
blog.categories.none = "Нет категорий"
blog.categories.addOne = "Добавить"
blog.categories.name = Наименование
blog.categories.itemsNumber = "Количество объектов"
blog.categories.actions = Действия
blog.categories.addCategory = Добавить категорию
blog.categories.editCategory = Редактировать категорию
blog.categories.deleteCategory = Удалить категорию
blog.categories.categoryName = Наименование категории
blog.categories.categoryParent = Родительская категория
blog.categories.collapseExpandChildren = "Свернуть/развернуть дочерние элементы"
blog-categories-created = "Категория создана"
blog-categories-not-created = "Категория не может быть создана"
blog-categories-management-title = "Управление категориями блога"

; Комментарии

blog-comments = Комментарии
blog.comments.add-comment = Оставить комментарий
blog.comments.respond-to = "Ответ для %s"
blog.comments.respond = Ответить
blog.comments.cancel-response = Отменить реакцию
blog.comments.none-comments = "Нет комментариев"
blog.comments-one-comment = "1 комментарий"
blog.comments-nb-comments = "Комментариев: %s"
blog.comments.in = в
blog.comments.bad-mail = "Введенный адрес электронной почты недействителен"
blog-comments-list = "Список комментариев"
blog.comments-name = "Имя *"
blog.comments-mail = "Электронная почта *"
blog.comments-website = "Вебсайт"
blog.comments-content = "Комментарий *"
blog.comments-publish = "Опубликовать комментарий"

; Конфигурация

blog-hide-content = "Скрыть содержимое постов в списке"
blog-hide-content-tooltip = "Если отмечен этот флажок, в списке сообщений отображается только заголовок."
blog-allow-comments = "Разрешить комментарии"
blog-page-title = "Заголовок страницы"
blog-entries-per-page = "Постов на странице"
blog-display-toc = "Отображение содержания"
blog-toc-no = "Нет"
blog-toc-in-content = "В содержании"
blog-toc-in-sidebar = "На боковой панели"
blog-display-author-block = "Отображение блока 'Автор'"
blog-author-name = "Имя автора"
blog-author-image = "Изображение автора"
blog-author-bio = "Биография"

```

### Fichier : config.json
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\param\config.json

**Contenu :**
```
{
    "priority" : "2",
    "label" : "Blog",
    "itemsByPage" : "5",
    "displayTOC" : "no",
    "hideContent" : "0",
    "comments" : "1",
    "authorName" : "",
    "authorAvatar" : "",
    "authorBio" : "",
    "displayAuthor" : false
}
```

### Fichier : hooks.json
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\param\hooks.json

**Contenu :**
```
{
    "endFrontHead" : "blogEndFrontHead",
    "adminToolsTemplates" : "BlogAdminCategoriesTemplates"
}
```

### Fichier : infos.json
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\param\infos.json

**Contenu :**
```
{
    "name" : "Blog",
    "icon" : "fa-regular fa-newspaper",
    "description" : "Blog management",
    "authorEmail" : "",
    "authorWebsite" : "",
    "version" : "1.2",
    "homePublicMethod" : "BlogListController#home",
    "homeAdminMethod" : "BlogAdminPostsController#list"
}
```

### Fichier : routes.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\param\routes.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2023, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

$router = router::getInstance();

// Public
$router->map('GET', '/blog[/?]', 'BlogListController#home', 'blog-home');
$router->map('GET', '/blog/cat-[*:name]-[i:id]/[i:page][/?]', 'BlogListController#categoryPage', 'blog-category-page');
$router->map('GET', '/blog/cat-[*:name]-[i:id].html', 'BlogListController#category', 'blog-category');
$router->map('GET', '/blog/[*:name]-[i:id].html', 'BlogReadController#read', 'blog-read');
$router->map('POST', '/blog/send.html', 'BlogReadController#send', 'blog-send');
$router->map('GET', '/blog/rss.html', 'BlogReadController#rss', 'blog-rss');
$router->map('GET', '/blog/[i:page][/?]', 'BlogListController#page', 'blog-page');

// Categories
$router->map('POST', '/admin/blog/addCategory', 'BlogAdminCategoriesController#addCategory', 'admin-blog-add-category');
$router->map('POST', '/admin/blog/deleteCategory', 'BlogAdminCategoriesController#deleteCategory', 'admin-blog-delete-category');
$router->map('POST', '/admin/blog/editCategory', 'BlogAdminCategoriesController#editCategory', 'admin-blog-edit-category');
$router->map('POST', '/admin/blog/saveCategory/[i:id]', 'BlogAdminCategoriesController#saveCategory', 'admin-blog-save-category');
$router->map('GET', '/admin/blog/listAjaxCategories', 'BlogAdminCategoriesController#listAjaxCategories', 'admin-blog-list-ajax-categories');

// Configuration
$router->map('POST', '/admin/blog/saveConfig', 'BlogAdminConfigController#saveConfig', 'admin-blog-save-config');

// Posts
$router->map('GET', '/admin/blog[/?]', 'BlogAdminPostsController#list', 'admin-blog-list');
$router->map('POST', '/admin/blog/deletePost', 'BlogAdminPostsController#deletePost', 'admin-blog-delete-post');
$router->map('GET', '/admin/blog/editPost/[i:id]?', 'BlogAdminPostsController#editPost', 'admin-blog-edit-post');
$router->map('POST', '/admin/blog/savePost', 'BlogAdminPostsController#savePost', 'admin-blog-save-post');

// Comments
$router->map('POST', '/admin/blog/deleteComment', 'BlogAdminCommentsController#deleteComment', 'admin-blog-delete-comment');
$router->map('POST', '/admin/blog/saveComment', 'BlogAdminCommentsController#saveComment', 'admin-blog-save-comment');
$router->map('GET', '/admin/blog/listComments/[i:id]', 'BlogAdminCommentsController#listComments', 'admin-blog-list-comments');
```

### Fichier : admin-edit-category.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin-edit-category.tpl

**Contenu :**
```
<section>
    <header>{{ Lang.blog.categories.editCategory}}</header>
    <label for="category-list-edit-label">{{Lang.blog.categories.categoryName}}</label>
    <input type="text" value="{{category.label}}" id="category-list-edit-label" name="category-list-edit-label"/>
    <label for="category-list-edit-parentId">{{Lang.blog.categories.categoryParent}}</label>
    {{categoriesManager.outputAsSelect(category.parentId, category.id, "category-list-edit-parentId")}}
    <button onclick="BlogEditSaveCategory()">{{ Lang.blog.categories.editCategory}}</button>
</section>
<script>
    async function BlogEditSaveCategory() {
        let url = '{{ROUTER.generate("admin-blog-save-category", ["id" => category.id])}}';
        let data = {
            label: document.querySelector('#category-list-edit-label').value,
            parentId: document.querySelector('#category-list-edit-parentId').value,
            token: '{{ token }}'
        };
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        let result = await response;
        console.log(result);
        if (result.status === 202) {
            Toastify({
                text: "{{ Lang.core-item-edited }}",
                className: "success"		
            }).showToast();
            // Refresh list
            Fancybox.close();
            Fancybox.show([
                {
                    src: "{{categoriesManager.getAjaxDisplayListUrl()}}",
                    type: "ajax",
                },
            ]);
        } else {
            Toastify({
                text: "{{ Lang.core-item-not-edited }}",
                className: "error"		
            }).showToast();
        }	
    };
</script>
```

### Fichier : admin-edit.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin-edit.tpl

**Contenu :**
```
<form method="post" id="mainForm" action="{{ ROUTER.generate("admin-blog-save-post")}}" enctype="multipart/form-data">
    {{SHOW.tokenField}}
    <input type="hidden" name="id" value="{{ news.getId() }}" />
    {% if pluginsManager.isActivePlugin("galerie") %}
        <input type="hidden" name="imgId" value="{{ news.getImg() }}" />
    {% endif %}

    <div class='tabs-container'>
        <ul class="tabs-header">
            <li class="default-tab"><i class="fa-solid fa-file-pen"></i> {{Lang.blog-content}}</li>
            <li><i class="fa-regular fa-newspaper"></i> {{Lang.blog-intro}}</li>
            <li><i class="fa-regular fa-thumbs-up"></i> {{Lang.blog-seo}}</li>
            <li><i class="fa-solid fa-heading"></i> {{Lang.blog-title}}</li>
            <li><i class="fa-solid fa-sliders"></i> {{Lang.blog-settings}}</li>
            {% if pluginsManager.isActivePlugin("galerie") %}
                <li><i class="fa-regular fa-image"></i> {{Lang.blog-featured-img}}</li>
            {% endif %}
        </ul>
        <ul class="tabs">
            <li class="tab">
                {{ contentEditor }}
            </li>
            <li class="tab">
                <label for="intro">{{Lang.blog-intro-content}}</label><br>
                <textarea name="intro" id="intro" class="editor">{%HOOK.beforeEditEditor(news.getIntro())%}</textarea><br>
                {{filemanagerDisplayManagerButton()}}
            </li>
            <li class="tab">
                <div class='form'>
                    <label for="seoDesc">{{Lang.blog-seo-content}}</label>
                    <div class='tooltip'>
                        <span id='seoDescDesc'>{{Lang.blog-seo-content-tooltip}}</span>
                    </div>
                    <textarea name="seoDesc" id="seoDesc" aria-describedby="seoDescDesc">{{ news.getSEODesc() }}</textarea>
                    <div id='seoDescProgress'></div>
                    <div id='seoDescCounter'></div>
                    <script>
                        function refreshSEODescCounter() {
                            var length = document.getElementById('seoDesc').value.length;
                            var progress = document.getElementById('seoDescProgress');
                            document.getElementById('seoDescCounter').innerHTML = length + ' caractère(s)';
                            if (length <= 100 || length > 250) {
                                progress.classList.remove("good", "care");
                                progress.classList.add("warning");
                            } else if (length <= 160) {
                                progress.classList.remove("good", "warning");
                                progress.classList.add("care");
                            } else {
                                progress.classList.remove("care", "warning");
                                progress.classList.add("good");
                            }
                            progress.style.width = (100 / 250 * length) + "%";
                        }

                        document.addEventListener("DOMContentLoaded", function () {
                            refreshSEODescCounter();
                        });
                        document.getElementById('seoDesc').addEventListener('keyup', function () {
                            refreshSEODescCounter();
                        });
                        document.getElementById('seoDesc').addEventListener('paste', function () {
                            refreshSEODescCounter();
                        });
                    </script>
                </div>                    
            </li>
            <li class='tab'>
                <label for="name">{{Lang.blog-title}}</label><br>
                <input type="text" name="name" id="name" value="{{ news.getName() }}" required="required" />
                {% if showDate %}
                    <label for="date">{{Lang.blog-date}}</label><br>
                    <input placeholder="{{Lang.blog-date-placeh}}" type="date" name="date" id="date" value="{{news.getDate()}}" required="required" />
                {% endif %}
            </li>
            <li class='tab'>
                <h4>{{Lang.blog-settings-post}}</h4>
                <p>
                    <input {% if news.getdraft() %}checked{% endif %} type="checkbox" name="draft" id="draft"/>
                    <label for="draft">{{Lang.blog-do-not-publish}}</label>
                </p>
                {% if runPlugin.getConfigVal("comments") %}
                    <p>
                        <input {% if news.getCommentsOff() %}checked{% endif %} type="checkbox" name="commentsOff" id="commentsOff"/>
                        <label for="commentsOff">{{Lang.blog-disable-comments-once}}</label>
                    </p>
                {% endif %}
                <h4>{{Lang.blog-categories}}</h4>
                {{ categoriesManager.outputAsCheckbox(news.getId())}}

                <h4>{{Lang.blog-affect-new-category}}</h4>
                <div class="input-field">
                    <label class="active" for="category-add-label">{{Lang.blog.categories.categoryName}}</label>
                    <input type="text" name="category-add-label" id="category-add-label"/>
                    <label for="category-add-parentId">{{Lang.blog.categories.categoryParent}}</label>
                    {{ categoriesManager.outputAsSelectOne(0, "category-add-parentId")}}
                </div>
            </li>
            {% if pluginsManager.isActivePlugin("galerie") %}
                <li class='tab'>
                    <h4>{{Lang.blog-featured-img}}</h4>
                    {% if news.getImg() %}
                        <input type="checkbox" name="delImg" id="delImg" /><label for="delImg">{{ Lang.galerie.delete-featured-image }}</label>
                    {% else %}
                         <label for="file">{{Lang.page.file}}</label><br><input type="file" name="file" id="file" accept="image/*" />
                    {% endif %}
                    <br>
                    {% if news.getImg() %}
                        <img src="{{ news.getImgUrl() }}" alt="{{ news.getImg() }}" />
                    {% endif %}
                </li>
            {% endif %}
        </ul>
    </div>
    <p><button id="mainSubmit" type="submit" class="floating" title='{{ Lang.save }}'><i class="fa-regular fa-floppy-disk"></i></button></p>
</form>
```

### Fichier : admin-edit.tpl.cache.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin-edit.tpl.cache.php

**Contenu :**
```
<form method="post" id="mainForm" action="<?php $this->_show_var(' ROUTER.generate("admin-blog-save-post")'); ?>" enctype="multipart/form-data">
    <?php $this->_show_var('SHOW.tokenField'); ?>
    <input type="hidden" name="id" value="<?php $this->_show_var(' news.getId()'); ?>" />
    <?php if($this->getVar('pluginsManager.isActivePlugin("galerie")', $this->data)){ ?>
        <input type="hidden" name="imgId" value="<?php $this->_show_var(' news.getImg()'); ?>" />
    <?php } ?>

    <div class='tabs-container'>
        <ul class="tabs-header">
            <li class="default-tab"><i class="fa-solid fa-file-pen"></i> <?php echo lang::get('blog-content'); ?></li>
            <li><i class="fa-regular fa-newspaper"></i> <?php echo lang::get('blog-intro'); ?></li>
            <li><i class="fa-regular fa-thumbs-up"></i> <?php echo lang::get('blog-seo'); ?></li>
            <li><i class="fa-solid fa-heading"></i> <?php echo lang::get('blog-title'); ?></li>
            <li><i class="fa-solid fa-sliders"></i> <?php echo lang::get('blog-settings'); ?></li>
            <?php if($this->getVar('pluginsManager.isActivePlugin("galerie")', $this->data)){ ?>
                <li><i class="fa-regular fa-image"></i> <?php echo lang::get('blog-featured-img'); ?></li>
            <?php } ?>
        </ul>
        <ul class="tabs">
            <li class="tab">
                <?php $this->_show_var(' contentEditor'); ?>
            </li>
            <li class="tab">
                <label for="intro"><?php echo lang::get('blog-intro-content'); ?></label><br>
                <textarea name="intro" id="intro" class="editor"><?php echo core::getInstance()->callHook('beforeEditEditor', $this->getVar('(news.getIntro())', $this->data) ); ?></textarea><br>
                <?php $this->_show_var('filemanagerDisplayManagerButton()'); ?>
            </li>
            <li class="tab">
                <div class='form'>
                    <label for="seoDesc"><?php echo lang::get('blog-seo-content'); ?></label>
                    <div class='tooltip'>
                        <span id='seoDescDesc'><?php echo lang::get('blog-seo-content-tooltip'); ?></span>
                    </div>
                    <textarea name="seoDesc" id="seoDesc" aria-describedby="seoDescDesc"><?php $this->_show_var(' news.getSEODesc()'); ?></textarea>
                    <div id='seoDescProgress'></div>
                    <div id='seoDescCounter'></div>
                    <script>
                        function refreshSEODescCounter() {
                            var length = document.getElementById('seoDesc').value.length;
                            var progress = document.getElementById('seoDescProgress');
                            document.getElementById('seoDescCounter').innerHTML = length + ' caractère(s)';
                            if (length <= 100 || length > 250) {
                                progress.classList.remove("good", "care");
                                progress.classList.add("warning");
                            } else if (length <= 160) {
                                progress.classList.remove("good", "warning");
                                progress.classList.add("care");
                            } else {
                                progress.classList.remove("care", "warning");
                                progress.classList.add("good");
                            }
                            progress.style.width = (100 / 250 * length) + "%";
                        }

                        document.addEventListener("DOMContentLoaded", function () {
                            refreshSEODescCounter();
                        });
                        document.getElementById('seoDesc').addEventListener('keyup', function () {
                            refreshSEODescCounter();
                        });
                        document.getElementById('seoDesc').addEventListener('paste', function () {
                            refreshSEODescCounter();
                        });
                    </script>
                </div>                    
            </li>
            <li class='tab'>
                <label for="name"><?php echo lang::get('blog-title'); ?></label><br>
                <input type="text" name="name" id="name" value="<?php $this->_show_var(' news.getName()'); ?>" required="required" />
                <?php if($this->getVar('showDate', $this->data)){ ?>
                    <label for="date"><?php echo lang::get('blog-date'); ?></label><br>
                    <input placeholder="<?php echo lang::get('blog-date-placeh'); ?>" type="date" name="date" id="date" value="<?php $this->_show_var('news.getDate()'); ?>" required="required" />
                <?php } ?>
            </li>
            <li class='tab'>
                <h4><?php echo lang::get('blog-settings-post'); ?></h4>
                <p>
                    <input <?php if($this->getVar('news.getdraft()', $this->data)){ ?>checked<?php } ?> type="checkbox" name="draft" id="draft"/>
                    <label for="draft"><?php echo lang::get('blog-do-not-publish'); ?></label>
                </p>
                <?php if($this->getVar('runPlugin.getConfigVal("comments")', $this->data)){ ?>
                    <p>
                        <input <?php if($this->getVar('news.getCommentsOff()', $this->data)){ ?>checked<?php } ?> type="checkbox" name="commentsOff" id="commentsOff"/>
                        <label for="commentsOff"><?php echo lang::get('blog-disable-comments-once'); ?></label>
                    </p>
                <?php } ?>
                <h4><?php echo lang::get('blog-categories'); ?></h4>
                <?php $this->_show_var(' categoriesManager.outputAsCheckbox(news.getId())'); ?>

                <h4><?php echo lang::get('blog-affect-new-category'); ?></h4>
                <div class="input-field">
                    <label class="active" for="category-add-label"><?php echo lang::get('blog.categories.categoryName'); ?></label>
                    <input type="text" name="category-add-label" id="category-add-label"/>
                    <label for="category-add-parentId"><?php echo lang::get('blog.categories.categoryParent'); ?></label>
                    <?php $this->_show_var(' categoriesManager.outputAsSelectOne(0, "category-add-parentId")'); ?>
                </div>
            </li>
            <?php if($this->getVar('pluginsManager.isActivePlugin("galerie")', $this->data)){ ?>
                <li class='tab'>
                    <h4><?php echo lang::get('blog-featured-img'); ?></h4>
                    <?php if($this->getVar('news.getImg()', $this->data)){ ?>
                        <input type="checkbox" name="delImg" id="delImg" /><label for="delImg"><?php echo lang::get('galerie.delete-featured-image'); ?></label>
                    <?php }else{ ?>
                         <label for="file"><?php echo lang::get('page.file'); ?></label><br><input type="file" name="file" id="file" accept="image/*" />
                    <?php } ?>
                    <br>
                    <?php if($this->getVar('news.getImg()', $this->data)){ ?>
                        <img src="<?php $this->_show_var(' news.getImgUrl()'); ?>" alt="<?php $this->_show_var(' news.getImg()'); ?>" />
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
    </div>
    <p><button id="mainSubmit" type="submit" class="floating" title='<?php echo lang::get('save'); ?>'><i class="fa-regular fa-floppy-disk"></i></button></p>
</form>
```

### Fichier : admin-list-comments.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin-list-comments.tpl

**Contenu :**
```
<section>
    <header>{{Lang.blog-comments-list}}</header>
    <a class="button" href="{{ ROUTER.generate("admin-blog-list")}}">{{Lang.blog-back-to-posts}}</a>
    <table>
        <tr>
            <th>{{Lang.blog-comments}}</th>
            <th></th>
        </tr>
        {% for k, v in newsManager.getFlatComments() %}
            <tr id="list-comment-{{v.getId()}}">
                <td>
                    {{v.getAuthor()}} <i>{{v.getAuthorMail()}}</i> - {{ util::getNaturalDate(v.getDate()) }} :<br><br>
                    <textarea id="content{{v.getId()}}" name="content{{v.getId()}}">{{v.getContent()}}</textarea>
                </td>
                <td>
                    <a onclick="BlogUpdateComment({{v.getId()}});" class="button">{{Lang.save}}</a>
                    <a onclick="BlogDeleteComment('{{ v.getId() }}')" class="button alert">{{Lang.delete}}</a>
                </td>
            </tr>
        {% endfor %}
    </table>
    <script>
        async function BlogUpdateComment(id) {
            let url = '{{ROUTER.generate("admin-blog-save-comment")}}';
                let data = {
                    idComment: id,
                    token: '{{ token }}',
                    idPost: '{{ idPost }}',
                    content: document.querySelector('#content' + id).value
                };
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                let result = await response;
                if (result.status === 202) {
                    Toastify({
                        text: "{{ Lang.core-changes-saved}}",
                        className: "success"		
                    }).showToast();
                } else {
                    Toastify({
                        text: "{{ Lang.core-changes-not-saved}}",
                        className: "error"		
                    }).showToast();
                }
        }

        async function BlogDeleteComment(id) {
            if (confirm('{{ Lang.confirm.deleteItem }}') === true) {
                let url = '{{ROUTER.generate("admin-blog-delete-comment")}}';
                let data = {
                    idComment: id,
                    token: '{{ token }}',
                    idPost: '{{ idPost }}'
                };
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                // See body : let result = await response.json();
                let result = await response;
                if (result.status === 204) {
                    fadeOut(document.querySelector('#list-comment-' + id));
                    Toastify({
                        text: "{{ Lang.core-item-deleted}}",
                        className: "success"		
                    }).showToast();
                } else {
                    Toastify({
                        text: "{{ Lang.core-item-not-deleted}}",
                        className: "error"		
                    }).showToast();
                }				
            };
        }
    </script>
</section>

<script>

</script>
```

### Fichier : admin-list.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin-list.tpl

**Contenu :**
```
<section class="overflow-auto">
	<header>{{ Lang.blog-posts-list }}</header>
	<a class="button" href="{{ ROUTER.generate("admin-blog-edit-post") }}">{{ Lang.blog-add }}</a>
	<a target="_blank" class="button" href="{{ ROUTER.generate("blog-rss") }}">{{ Lang.rss_feed }}</a>
	<table class="small">
		<tr>
			<th>{{ Lang.blog-title }}</th>
			<th>{{ Lang.blog-date }}</th>
			<th>{{ Lang.blog-comments }}</th>
			<th>{{ Lang.blog-see }}</th>
			<th>{{ Lang.blog-categories }}</th>
			<th>{{ Lang.delete }}</th>
		</tr>
		{% for item in newsManager.getItems() %}
			<tr id="post{{ item.getId() }}">
				<td>
					<a title="{{ Lang.blog-edit }}" href="{{ ROUTER.generate("admin-blog-edit-post", ["id" => item.getId()]) }}">{{ item.getName() }}</a>
				</td>
				<td>
                    {{ util.getDate(item.getDate()) }}
                </td>
				<td style="text-align: center">
					{% if newsManager.countComments(item.getId()) > 0 %}
						<a title="{{ newsManager.countComments(item.getId()) }} {{ Lang.blog-comments}}" href="{{ ROUTER.generate("admin-blog-list-comments", ["id" => item.getId()]) }}" class="button">
							<i class="fa-regular fa-comments"></i>
							{{ newsManager.countComments(item.getId()) }}</a>
                    {% else %}
                        0
					{% endif %}
				</td>
				<td style="text-align: center">
					<a title="{{ Lang.blog-see }}" target="_blank" href="{{ item.getUrl }}" class="button">
						<i class="fa-solid fa-eye"></i>
					</a>
				</td>
                <td>
                    {% if item.categories %}
						{% for cat in item.categories %}
                            <span class="blog-category">{{ cat.label }}</span>
                        {% endfor %}
                    {% else %}
                        {{ Lang.blog.categories.none}}
                    {% endif %}
                </td>
				<td style="text-align: center">
					<a title="{{ Lang.delete }}" onclick="BlogDeletePost('{{ item.getId() }}')" class="button alert">
						<i class="fa-regular fa-trash-can"></i>
					</a>
				</td>
			</tr>
		{% endfor %}
	</table>
</section>

<script>
async function BlogDeletePost(id) {
	if (confirm('{{ Lang.confirm.deleteItem }}') === true) {
		let url = '{{ ROUTER.generate("admin-blog-delete-post") }}';
		let data = {
			id: id,
			token: '{{ token }}'
		};
		let response = await fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(data)
		});
		// See body : let result = await response.json();
		let result = await response;
		if (result.status === 204) {
			fadeOut(document.querySelector('#post' + id));
			Toastify({
				text: "{{ Lang.core-item-deleted}}",
				className: "success"		
			}).showToast();
		} else {
			Toastify({
				text: "{{ Lang.core-item-not-deleted}}",
				className: "error"		
			}).showToast();
		}				
	};
}
</script>
```

### Fichier : admin-list.tpl.cache.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin-list.tpl.cache.php

**Contenu :**
```
<section class="overflow-auto">
	<header><?php echo lang::get('blog-posts-list'); ?></header>
	<a class="button" href="<?php $this->_show_var(' ROUTER.generate("admin-blog-edit-post")'); ?>"><?php echo lang::get('blog-add'); ?></a>
	<a target="_blank" class="button" href="<?php $this->_show_var(' ROUTER.generate("blog-rss")'); ?>"><?php echo lang::get('rss_feed'); ?></a>
	<table class="small">
		<tr>
			<th><?php echo lang::get('blog-title'); ?></th>
			<th><?php echo lang::get('blog-date'); ?></th>
			<th><?php echo lang::get('blog-comments'); ?></th>
			<th><?php echo lang::get('blog-see'); ?></th>
			<th><?php echo lang::get('blog-categories'); ?></th>
			<th><?php echo lang::get('delete'); ?></th>
		</tr>
		<?php foreach ($this->getVar('newsManager.getItems() ', $this->data) as $item): $this->data['item' ] = $item; ?>
			<tr id="post<?php $this->_show_var(' item.getId()'); ?>">
				<td>
					<a title="<?php echo lang::get('blog-edit'); ?>" href="<?php $this->_show_var(' ROUTER.generate("admin-blog-edit-post", ["id" => item.getId()])'); ?>"><?php $this->_show_var(' item.getName()'); ?></a>
				</td>
				<td>
                    <?php $this->_show_var(' util.getDate(item.getDate())'); ?>
                </td>
				<td style="text-align: center">
					<?php if($this->getVar('newsManager.countComments(item.getId())', $this->data)>0){ ?>
						<a title="<?php $this->_show_var(' newsManager.countComments(item.getId())'); ?> <?php echo lang::get('blog-comments'); ?>" href="<?php $this->_show_var(' ROUTER.generate("admin-blog-list-comments", ["id" => item.getId()])'); ?>" class="button">
							<i class="fa-regular fa-comments"></i>
							<?php $this->_show_var(' newsManager.countComments(item.getId())'); ?></a>
                    <?php }else{ ?>
                        0
					<?php } ?>
				</td>
				<td style="text-align: center">
					<a title="<?php echo lang::get('blog-see'); ?>" target="_blank" href="<?php $this->_show_var(' item.getUrl'); ?>" class="button">
						<i class="fa-solid fa-eye"></i>
					</a>
				</td>
                <td>
                    <?php if($this->getVar('item.categories', $this->data)){ ?>
						<?php foreach ($this->getVar('item.categories ', $this->data) as $cat): $this->data['cat' ] = $cat; ?>
                            <span class="blog-category"><?php $this->_show_var(' cat.label'); ?></span>
                        <?php endforeach; ?>
                    <?php }else{ ?>
                        <?php echo lang::get('blog.categories.none'); ?>
                    <?php } ?>
                </td>
				<td style="text-align: center">
					<a title="<?php echo lang::get('delete'); ?>" onclick="BlogDeletePost('<?php $this->_show_var(' item.getId()'); ?>')" class="button alert">
						<i class="fa-regular fa-trash-can"></i>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
</section>

<script>
async function BlogDeletePost(id) {
	if (confirm('<?php echo lang::get('confirm.deleteItem'); ?>') === true) {
		let url = '<?php $this->_show_var(' ROUTER.generate("admin-blog-delete-post")'); ?>';
		let data = {
			id: id,
			token: '<?php $this->_show_var(' token'); ?>'
		};
		let response = await fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(data)
		});
		// See body : let result = await response.json();
		let result = await response;
		if (result.status === 204) {
			fadeOut(document.querySelector('#post' + id));
			Toastify({
				text: "<?php echo lang::get('core-item-deleted'); ?>",
				className: "success"		
			}).showToast();
		} else {
			Toastify({
				text: "<?php echo lang::get('core-item-not-deleted'); ?>",
				className: "error"		
			}).showToast();
		}				
	};
}
</script>
```

### Fichier : admin.css
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\admin.css

**Contenu :**
```
#seoDesc {
    margin-bottom: 0;
}

#seoDescProgress {
    height:8px;
    max-width: 100%;
    transition: background-color 0.5s ease;
}

#seoDescProgress.warning {
    background-color: #e53935;
}

#seoDescProgress.care {
    background-color: #ffb300;
}

#seoDescProgress.good {
    background-color: #7cb342;
}

#seoDescCounter {
    font-size: 13px;
}

span.blog-category {
    margin-right: 10px;
    position: relative;
}
span.blog-category:after {
    position: absolute;
    left:100%;
    content:';';
    padding-left:5px;
}
```

### Fichier : comment.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\comment.tpl

**Contenu :**
```
<li class="comments-item">
    <article class="comment" id="comment{{ comment.getId }}">
        <div class="comment-infos" id="comment{{ comment.getId }}Infos" data-author="{{ Lang.blog.comments.respond-to(comment.getAuthor)}}">
            <div class="comment-avatar">
                <img src="{{ comment.getAuthorAvatar }}" alt="{{ comment.getAuthor }}" title="{{ comment.getAuthor }}" />
            </div>
            <div class="comment-author">
                {% if comment.getAuthorWebsite %}
                    <a href="{{ comment.getAuthorWebsite }}" target="_blank">{{ comment.getAuthor }}</a>
                {% else %}
                    {{ comment.getAuthor }}
                {% endif %}
                
            </div>
            <div class="comment-metadata">
                <a href="#comment{{ comment.getId }}">{{ util::getNaturalDate(comment.getDate()) }}</a>
            </div>
        </div>
        <div class="comment-content">
		{{nl2br(comment.getContent())}}
        </div>
        <div class="comment-reply">
            <button class="btn-add-respond small" data-id="{{ comment.getId }}">
                {{ Lang.blog.comments.respond }}
            </button>
        </div>
	</article>
    {% if comment.hasReplies %}
    <ul class="comments-list comments-children">
        {% for reply in comment.replies %}
            {{ reply.show}}
        {% endfor %}
    </ul>
    {% endif %}
    
</li>

```

### Fichier : list.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\list.tpl

**Contenu :**
```
{% if mode === "list_empty" %}
	<p>{{Lang.galerie.no-item-found}}</p>
{% else %}
	{% for k, v in news %}
		<article>
			{% if runPlugin.getConfigVal("hideContent") == false %}
				<header>
					{% if pluginsManager.isActivePlugin("galerie") && galerie.searchByFileName(v.img) %}
						<img class="featured" src="{{v.imgUrl}}" alt="{{v.img}}"/>
					{% endif %}
					<div class="item-head">
						<h2>
							<a href="{{v.url}}">{{v.name}}</a>
						</h2>
						<p class="date">{{v.date}}
							{% if runPlugin.getConfigVal("comments") && v.commentsOff == false %}
								|
								<a href="{{v.url}}#comments">{{ newsManager.countComments(v.id) }}
								commentaire{% if newsManager.countComments(v.id) > 1 %}s{% endif %}</a>
							{% endif %}
							 | <span class="item-categories"><i class="fa-regular fa-folder-open"></i>
							{% if count(v.cats) == 0 %}
								Non classé
							{% else %}
								{% for cat in v.cats %}
									<span class="blog-label-category"><a href="{{ cat.url }}">{{ cat.label }}</a></span>
								{% endfor %}
							{% endif %}
						</p>
                    </span>
					</div>
				</header>
				{% if v.intro %}
					{{htmlspecialchars_decode(v.intro)}}
				{% else %}
					{{htmlspecialchars_decode(v.content)}}
				{% endif %}
			{% else %}
				<h2>
					<a href="{{v.url}}">{{v.name}}</a>
				</h2>
				<p class="date">{{v.date}}
					{% if runPlugin.getConfigVal("comments") && v.commentsOff == false %}
						|
						{{ newsManager.countComments(v.id) }}
						commentaire{% if newsManager.countComments(v.id) > 1 %}s{% endif %}
					{% endif %}
				</p>
			{% endif %}
		</article>
	{% endfor %}
	{% if pagination %}
		<ul class="pagination">
			{% for k, v in pagination %}
				<li>
					<a href="{{v.url}}">{{v.num}}</a>
				</li>
			{% endfor %}
		</ul>
	{% endif %}
{% endif %}

```

### Fichier : list.tpl.cache.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\list.tpl.cache.php

**Contenu :**
```
<?php if($this->getVar('mode', $this->data)===$this->getVar('"list_empty"', $this->data)){ ?>
	<p><?php echo lang::get('galerie.no-item-found'); ?></p>
<?php }else{ ?>
	<?php foreach ($this->getVar('news ', $this->data) as $k => $v ): $this->data['k' ] = $k; $this->data['v' ] = $v; ?>
		<article>
			<?php if($this->getVar('runPlugin.getConfigVal("hideContent")', $this->data)==false){ ?>
				<header>
					<?php if($this->getVar('pluginsManager.isActivePlugin("galerie")', $this->data)&&$this->getVar('galerie.searchByFileName(v.img)', $this->data)){ ?>
						<img class="featured" src="<?php $this->_show_var('v.imgUrl'); ?>" alt="<?php $this->_show_var('v.img'); ?>"/>
					<?php } ?>
					<div class="item-head">
						<h2>
							<a href="<?php $this->_show_var('v.url'); ?>"><?php $this->_show_var('v.name'); ?></a>
						</h2>
						<p class="date"><?php $this->_show_var('v.date'); ?>
							<?php if($this->getVar('runPlugin.getConfigVal("comments")', $this->data)&&$this->getVar('v.commentsOff', $this->data)==false){ ?>
								|
								<a href="<?php $this->_show_var('v.url'); ?>#comments"><?php $this->_show_var(' newsManager.countComments(v.id)'); ?>
								commentaire<?php if($this->getVar('newsManager.countComments(v.id)', $this->data)>1){ ?>s<?php } ?></a>
							<?php } ?>
							 | <span class="item-categories"><i class="fa-regular fa-folder-open"></i>
							<?php if($this->getVar('count(v.cats)', $this->data)==0){ ?>
								Non classé
							<?php }else{ ?>
								<?php foreach ($this->getVar('v.cats ', $this->data) as $cat): $this->data['cat' ] = $cat; ?>
									<span class="blog-label-category"><a href="<?php $this->_show_var(' cat.url'); ?>"><?php $this->_show_var(' cat.label'); ?></a></span>
								<?php endforeach; ?>
							<?php } ?>
						</p>
                    </span>
					</div>
				</header>
				<?php if($this->getVar('v.intro', $this->data)){ ?>
					<?php $this->_show_var('htmlspecialchars_decode(v.intro)'); ?>
				<?php }else{ ?>
					<?php $this->_show_var('htmlspecialchars_decode(v.content)'); ?>
				<?php } ?>
			<?php }else{ ?>
				<h2>
					<a href="<?php $this->_show_var('v.url'); ?>"><?php $this->_show_var('v.name'); ?></a>
				</h2>
				<p class="date"><?php $this->_show_var('v.date'); ?>
					<?php if($this->getVar('runPlugin.getConfigVal("comments")', $this->data)&&$this->getVar('v.commentsOff', $this->data)==false){ ?>
						|
						<?php $this->_show_var(' newsManager.countComments(v.id)'); ?>
						commentaire<?php if($this->getVar('newsManager.countComments(v.id)', $this->data)>1){ ?>s<?php } ?>
					<?php } ?>
				</p>
			<?php } ?>
		</article>
	<?php endforeach; ?>
	<?php if($this->getVar('pagination', $this->data)){ ?>
		<ul class="pagination">
			<?php foreach ($this->getVar('pagination ', $this->data) as $k => $v ): $this->data['k' ] = $k; $this->data['v' ] = $v; ?>
				<li>
					<a href="<?php $this->_show_var('v.url'); ?>"><?php $this->_show_var('v.num'); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php } ?>
<?php } ?>

```

### Fichier : param.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\param.tpl

**Contenu :**
```
<form method="post" action="{{ ROUTER.generate("admin-blog-save-config") }}">
    {{ show.tokenField() }}
    <script>
        function onCheckAuthor() {
            if (document.getElementById("displayAuthor").checked) {
                document.getElementById("author-fields").style.display = 'block';
            } else {
                document.getElementById("author-fields").style.display = 'none';
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            onCheckAuthor();
            document.getElementById("displayAuthor").addEventListener("click", function () {
                onCheckAuthor();
            });
        });
    </script>
    <div class='form'>
        <input {% if runPlugin.getConfigVal("hideContent") %}checked{% endif %} type="checkbox" name="hideContent" id="hideContent" aria-describedby='hideContentDesc' />
        <label for="hideContent">{{ Lang.blog-hide-content }}</label>
        <div class='tooltip'>
            <span id='hideContentDesc'>{{ Lang.blog-hide-content-tooltip }}</span>
        </div>
    </div>
    <div class='form'>
        <input {% if runPlugin.getConfigVal("comments") %}checked{% endif %} type="checkbox" name="comments" id="comments" />
        <label for="comments">{{ Lang.blog-allow-comments }}</label>
    </div>
    <div class='form'>
        <label for="label">{{ Lang.blog-page-title }}</label><br>
        <input type="text" name="label" id="label" value="{{ runPlugin.getConfigVal("label") }}" />
    </div>
    <div class='form'>
        <label for="itemsByPage">{{ Lang.blog-entries-per-page }}</label><br>
        <input type="number" name="itemsByPage" id="itemsByPage" value="{{ runPlugin.getConfigVal("itemsByPage") }}" />
    </div>
    <div class='form'>
        <label for="displayTOC">{{ Lang.blog-display-toc }}</label><br>
        <select id="displayTOC" name="displayTOC">
            <option value="no" {% if runPlugin.getConfigVal("displayTOC") == "no" %}selected{% endif %}>{{ Lang.blog-toc-no }}</option>
            <option value="content" {% if runPlugin.getConfigVal("displayTOC") == "content" %}selected{% endif %}>{{ Lang.blog-toc-in-content }}</option>
            <option value="sidebar" {% if runPlugin.getConfigVal("displayTOC") == "sidebar" %}selected{% endif %}>{{ Lang.blog-toc-in-sidebar }}</option>
        </select>
    </div>
    <div class='form'>
        <input {% if runPlugin.getConfigVal("displayAuthor") %}checked{% endif %} type="checkbox" name="displayAuthor" id="displayAuthor" />
        <label for="displayAuthor">{{ Lang.blog-display-author-block }}</label>
    </div>
    <div id="author-fields">
        <div class='form'>
            <label for="authorName">{{ Lang.blog-author-name }}</label><br>
            <input type="text" name="authorName" id="authorName" value="{{ runPlugin.getConfigVal("authorName") }}" />
        </div>
        <div class='form'>
            <label for="authorAvatar">{{ Lang.blog-author-image }}</label><br>
            <input type="url" name="authorAvatar" id="authorAvatar" value="{{ runPlugin.getConfigVal("authorAvatar") }}" />
            {{ filemanagerDisplayManagerButton() }}
        </div>
        <div class='form'>
            <label for="authorBio">{{ Lang.blog-author-bio }}</label><br>
            <textarea name="authorBio" id="authorBio" class="editor">{% HOOK.beforeEditEditor(runPlugin.getConfigVal("authorBio")) %}</textarea><br>
        </div>
    </div>

    <div class='form'><button type="submit" class="button">{{ Lang.submit }}</button></div>
</form>
```

### Fichier : param.tpl.cache.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\param.tpl.cache.php

**Contenu :**
```
<form method="post" action="<?php $this->_show_var(' ROUTER.generate("admin-blog-save-config")'); ?>">
    <?php $this->_show_var(' show.tokenField()'); ?>
    <script>
        function onCheckAuthor() {
            if (document.getElementById("displayAuthor").checked) {
                document.getElementById("author-fields").style.display = 'block';
            } else {
                document.getElementById("author-fields").style.display = 'none';
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            onCheckAuthor();
            document.getElementById("displayAuthor").addEventListener("click", function () {
                onCheckAuthor();
            });
        });
    </script>
    <div class='form'>
        <input <?php if($this->getVar('runPlugin.getConfigVal("hideContent")', $this->data)){ ?>checked<?php } ?> type="checkbox" name="hideContent" id="hideContent" aria-describedby='hideContentDesc' />
        <label for="hideContent"><?php echo lang::get('blog-hide-content'); ?></label>
        <div class='tooltip'>
            <span id='hideContentDesc'><?php echo lang::get('blog-hide-content-tooltip'); ?></span>
        </div>
    </div>
    <div class='form'>
        <input <?php if($this->getVar('runPlugin.getConfigVal("comments")', $this->data)){ ?>checked<?php } ?> type="checkbox" name="comments" id="comments" />
        <label for="comments"><?php echo lang::get('blog-allow-comments'); ?></label>
    </div>
    <div class='form'>
        <label for="label"><?php echo lang::get('blog-page-title'); ?></label><br>
        <input type="text" name="label" id="label" value="<?php $this->_show_var(' runPlugin.getConfigVal("label")'); ?>" />
    </div>
    <div class='form'>
        <label for="itemsByPage"><?php echo lang::get('blog-entries-per-page'); ?></label><br>
        <input type="number" name="itemsByPage" id="itemsByPage" value="<?php $this->_show_var(' runPlugin.getConfigVal("itemsByPage")'); ?>" />
    </div>
    <div class='form'>
        <label for="displayTOC"><?php echo lang::get('blog-display-toc'); ?></label><br>
        <select id="displayTOC" name="displayTOC">
            <option value="no" <?php if($this->getVar('runPlugin.getConfigVal("displayTOC")', $this->data)==$this->getVar('"no"', $this->data)){ ?>selected<?php } ?>><?php echo lang::get('blog-toc-no'); ?></option>
            <option value="content" <?php if($this->getVar('runPlugin.getConfigVal("displayTOC")', $this->data)==$this->getVar('"content"', $this->data)){ ?>selected<?php } ?>><?php echo lang::get('blog-toc-in-content'); ?></option>
            <option value="sidebar" <?php if($this->getVar('runPlugin.getConfigVal("displayTOC")', $this->data)==$this->getVar('"sidebar"', $this->data)){ ?>selected<?php } ?>><?php echo lang::get('blog-toc-in-sidebar'); ?></option>
        </select>
    </div>
    <div class='form'>
        <input <?php if($this->getVar('runPlugin.getConfigVal("displayAuthor")', $this->data)){ ?>checked<?php } ?> type="checkbox" name="displayAuthor" id="displayAuthor" />
        <label for="displayAuthor"><?php echo lang::get('blog-display-author-block'); ?></label>
    </div>
    <div id="author-fields">
        <div class='form'>
            <label for="authorName"><?php echo lang::get('blog-author-name'); ?></label><br>
            <input type="text" name="authorName" id="authorName" value="<?php $this->_show_var(' runPlugin.getConfigVal("authorName")'); ?>" />
        </div>
        <div class='form'>
            <label for="authorAvatar"><?php echo lang::get('blog-author-image'); ?></label><br>
            <input type="url" name="authorAvatar" id="authorAvatar" value="<?php $this->_show_var(' runPlugin.getConfigVal("authorAvatar")'); ?>" />
            <?php $this->_show_var(' filemanagerDisplayManagerButton()'); ?>
        </div>
        <div class='form'>
            <label for="authorBio"><?php echo lang::get('blog-author-bio'); ?></label><br>
            <textarea name="authorBio" id="authorBio" class="editor"><?php echo core::getInstance()->callHook('beforeEditEditor', $this->getVar('(runPlugin.getConfigVal("authorBio"))', $this->data) ); ?></textarea><br>
        </div>
    </div>

    <div class='form'><button type="submit" class="button"><?php echo lang::get('submit'); ?></button></div>
</form>
```

### Fichier : public.css
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\public.css

**Contenu :**
```
.blog-author {
    display: flex;
}

.blog-avatar {
     width:auto;
}

.blog-avatar img {
    max-width: 80px;
    border-radius: 50%;
    border:2px solid #ccc;
    box-shadow: 0 0 5px rgba(0,0,0,0.15);
}

.blog-infos {
    margin-left: 20px;
}

.blog-infos-name span {
    font-size: 14px;
    font-weight: 600;
}

.blog-infos-bio {
    font-size: 12px;
}

.blog-label-category {
    position: relative;
}

.blog-label-category:after {
    content: ',';
    margin-left: 5px;
    margin-right: 5px;
}

.blog-label-category:last-of-type:after {
    content: '';
    margin-left: 0px;
    margin-right: 0px;
}

/* Comments */

#comments-add-respond {
    position: relative;
}

#comments-cancel-respond {
    display: none;
    position: absolute;
    top:0;
    right:0;
}

.comment-content {
    clear:both;
}
```

### Fichier : public.js
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\public.js

**Contenu :**
```
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.btn-add-respond').forEach(function (item) {
        item.addEventListener("click", function (e) {
            e.preventDefault();
            var $form = document.querySelector('#comments-add-respond');
            var parent_id = item.getAttribute('data-id');
            var $comment = document.querySelector('#comment' + parent_id);
            document.querySelector("#comments-title").textContent = document.querySelector('#comment' + parent_id + "Infos").getAttribute('data-author');
            document.querySelector('#commentParentId').value = parent_id;
            $comment.after($form);
            var $aRem = document.querySelector("#comments-cancel-respond");
            $aRem.style.display = "block";
        });
    });

    if (document.querySelector('#comments-cancel-respond')) {
        document.querySelector('#comments-cancel-respond').addEventListener("click", function (e) {
            e.preventDefault();
            var $aRem = document.querySelector("#comments-cancel-respond");
            $aRem.style.display = "none";
            var $form = document.querySelector('#comments-add-respond');
            var $container = document.querySelector('#comments-add-container');
            document.querySelector("#comments-title").textContent = document.querySelector("#comments-title").getAttribute('data-title');
            document.querySelector('#commentParentId').value = 0;
            $container.after($form);
        });
    }

});
```

### Fichier : read.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\read.tpl

**Contenu :**
```
<article>
	<header>
		{% if pluginsManager.isActivePlugin("galerie") && galerie.searchByFileName(item.getImg) %}
			<img class="featured" src="{{ item.getImgUrl }}" alt="{{ item.getName }}"/>
		{% endif %}
		<div class="item-head">
			<p class="date">
			{{ Lang.blog.posted-date(item.getReadableDate())}}
				{% if runPlugin.getConfigVal("comments") && item.getCommentsOff == false %}
					|
					<a href="#comments">
					{% if newsManager.countComments() == 0 %}
						{{ Lang.blog.comments.none-comments }}
					{% elseif newsManager.countComments() == 1 %}
						{{ Lang.blog.comments-one-comment }}
					{% else %}
						{{ Lang.blog.comments-nb-comments(newsManager.countComments()) }}
					{% endif %}
					</a> | 
				{% endif %}
				{% if count(categories) == 0 %}
					{{ Lang.blog.categories.none}}
				{% else %}
					dans
					{% for cat in categories %}
						<span class="blog-label-category"><a href="{{ cat.url }}">{{ cat.label }}</a></span> 
					{% endfor %}
				{% endif %}
				| <a href="{{ runPlugin.getPublicUrl }}">{{ Lang.blog.back-to-list }}</a>
			</p>
		</div>
		
	</header>
	{{ TOC }}
	{{ generatedHtml }}
	{% if runPlugin.getConfigVal("displayAuthor") %}
		<footer>
			<div class='blog-author'>
				<div class='blog-avatar'>
					<img src='{{runPlugin.getConfigVal("authorAvatar")}}' alt='{{runPlugin.getConfigVal("authorName")}}'/>
				</div>
				<div class='blog-infos'>
					<div class='blog-infos-name'>
						<span>{{runPlugin.getConfigVal("authorName")}}</span>
					</div>
					<div class='blog-infos-bio'>
						{{htmlspecialchars_decode(runPlugin.getConfigVal("authorBio"))}}
					</div>
				</div>
			</div>
		</footer>
	{% endif %}
</article>
{% if runPlugin.getConfigVal("comments") && item.getCommentsOff == false %}
	<section id="comments">
		<header>
			<div class="item-head">
				<h2>{{ Lang.blog-comments }}</h2>
			</div>
		</header>
		{% if newsManager.countComments(item.getId) == 0 %}
			<p>{{ Lang.blog.there-is-no-comment }}</p>
		{% else %}
			<ul class="comments-list">
				{% for k, v in newsManager.getComments() %}
					{{ v. show }}
				{% endfor %}
			</ul>
		{% endif %}
		<div id="comments-add-container">
			<div id="comments-add-respond">
				<h2 id="comments-title" data-title="{{ Lang.blog.comments.add-comment}}">{{ Lang.blog.comments.add-comment}}</h2>
				<form method="post" action="{{ commentSendUrl }}">
					<button id="comments-cancel-respond" class="small" title="{{Lang.blog.comments.cancel-response}}" aria-label="{{Lang.blog.comments.cancel-response}}"><i class="fa-solid fa-xmark"></i></button>
					<input type="hidden" name="id" value="{{item.getId}}"/>
					<input type="hidden" name="commentParentId" id="commentParentId" value="0"/>
					<input type="hidden" name="back" value="{{item.getUrl}}"/>
					<p>
						<label for="author">{{ Lang.blog.comments-name }}</label><br>
						<input style="display:none;" type="text" name="_author" value=""/>
						<input type="text" name="author" id="author" required="required"/>
					</p>
					<p>
						<label for="authorEmail">{{ Lang.blog.comments-mail }}</label><br><input type="email" name="authorEmail" id="authorEmail" required="required"/></p>
					<p>
					<p>
						<label for="authorWebsite">{{ Lang.blog.comments-website }}</label><br><input type="url" name="authorWebsite" id="authorWebsite"/></p>
					<p>
						<label for="commentContent">{{ Lang.blog.comments-content }}</label>
						<textarea name="commentContent" id="commentContent" required="required"></textarea>
					</p>
					{% if antispam %}
						{{antispamField}}
					{% endif %}
					<p><input type="submit" value="{{ Lang.contact.form_send }}"/></p>
				</form>
			</div>
		</div>
	</section>
{% endif %}
```

### Fichier : read.tpl.cache.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\blog\template\read.tpl.cache.php

**Contenu :**
```
<article>
	<header>
		<?php if($this->getVar('pluginsManager.isActivePlugin("galerie")', $this->data)&&$this->getVar('galerie.searchByFileName(item.getImg)', $this->data)){ ?>
			<img class="featured" src="<?php $this->_show_var(' item.getImgUrl'); ?>" alt="<?php $this->_show_var(' item.getName'); ?>"/>
		<?php } ?>
		<div class="item-head">
			<p class="date">
			<?php echo lang::get('blog.posted-date', $this->getVar('item.getReadableDate', $this->data)); ?>
				<?php if($this->getVar('runPlugin.getConfigVal("comments")', $this->data)&&$this->getVar('item.getCommentsOff', $this->data)==false){ ?>
					|
					<a href="#comments">
					<?php if($this->getVar('newsManager.countComments()', $this->data)==0){ ?>
						<?php echo lang::get('blog.comments.none-comments'); ?>
					<?php } elseif($this->getVar('newsManager.countComments()', $this->data)==1){ ?>
						<?php echo lang::get('blog.comments-one-comment'); ?>
					<?php }else{ ?>
						<?php echo lang::get('blog.comments-nb-comments', $this->getVar('newsManager.countComments', $this->data)); ?>
					<?php } ?>
					</a> | 
				<?php } ?>
				<?php if($this->getVar('count(categories)', $this->data)==0){ ?>
					<?php echo lang::get('blog.categories.none'); ?>
				<?php }else{ ?>
					dans
					<?php foreach ($this->getVar('categories ', $this->data) as $cat): $this->data['cat' ] = $cat; ?>
						<span class="blog-label-category"><a href="<?php $this->_show_var(' cat.url'); ?>"><?php $this->_show_var(' cat.label'); ?></a></span> 
					<?php endforeach; ?>
				<?php } ?>
				| <a href="<?php $this->_show_var(' runPlugin.getPublicUrl'); ?>"><?php echo lang::get('blog.back-to-list'); ?></a>
			</p>
		</div>
		
	</header>
	<?php $this->_show_var(' TOC'); ?>
	<?php $this->_show_var(' generatedHtml'); ?>
	<?php if($this->getVar('runPlugin.getConfigVal("displayAuthor")', $this->data)){ ?>
		<footer>
			<div class='blog-author'>
				<div class='blog-avatar'>
					<img src='<?php $this->_show_var('runPlugin.getConfigVal("authorAvatar")'); ?>' alt='<?php $this->_show_var('runPlugin.getConfigVal("authorName")'); ?>'/>
				</div>
				<div class='blog-infos'>
					<div class='blog-infos-name'>
						<span><?php $this->_show_var('runPlugin.getConfigVal("authorName")'); ?></span>
					</div>
					<div class='blog-infos-bio'>
						<?php $this->_show_var('htmlspecialchars_decode(runPlugin.getConfigVal("authorBio"))'); ?>
					</div>
				</div>
			</div>
		</footer>
	<?php } ?>
</article>
<?php if($this->getVar('runPlugin.getConfigVal("comments")', $this->data)&&$this->getVar('item.getCommentsOff', $this->data)==false){ ?>
	<section id="comments">
		<header>
			<div class="item-head">
				<h2><?php echo lang::get('blog-comments'); ?></h2>
			</div>
		</header>
		<?php if($this->getVar('newsManager.countComments(item.getId)', $this->data)==0){ ?>
			<p><?php echo lang::get('blog.there-is-no-comment'); ?></p>
		<?php }else{ ?>
			<ul class="comments-list">
				<?php foreach ($this->getVar('newsManager.getComments() ', $this->data) as $k => $v ): $this->data['k' ] = $k; $this->data['v' ] = $v; ?>
					<?php $this->_show_var(' v. show'); ?>
				<?php endforeach; ?>
			</ul>
		<?php } ?>
		<div id="comments-add-container">
			<div id="comments-add-respond">
				<h2 id="comments-title" data-title="<?php echo lang::get('blog.comments.add-comment'); ?>"><?php echo lang::get('blog.comments.add-comment'); ?></h2>
				<form method="post" action="<?php $this->_show_var(' commentSendUrl'); ?>">
					<button id="comments-cancel-respond" class="small" title="<?php echo lang::get('blog.comments.cancel-response'); ?>" aria-label="<?php echo lang::get('blog.comments.cancel-response'); ?>"><i class="fa-solid fa-xmark"></i></button>
					<input type="hidden" name="id" value="<?php $this->_show_var('item.getId'); ?>"/>
					<input type="hidden" name="commentParentId" id="commentParentId" value="0"/>
					<input type="hidden" name="back" value="<?php $this->_show_var('item.getUrl'); ?>"/>
					<p>
						<label for="author"><?php echo lang::get('blog.comments-name'); ?></label><br>
						<input style="display:none;" type="text" name="_author" value=""/>
						<input type="text" name="author" id="author" required="required"/>
					</p>
					<p>
						<label for="authorEmail"><?php echo lang::get('blog.comments-mail'); ?></label><br><input type="email" name="authorEmail" id="authorEmail" required="required"/></p>
					<p>
					<p>
						<label for="authorWebsite"><?php echo lang::get('blog.comments-website'); ?></label><br><input type="url" name="authorWebsite" id="authorWebsite"/></p>
					<p>
						<label for="commentContent"><?php echo lang::get('blog.comments-content'); ?></label>
						<textarea name="commentContent" id="commentContent" required="required"></textarea>
					</p>
					<?php if($this->getVar('antispam', $this->data)){ ?>
						<?php $this->_show_var('antispamField'); ?>
					<?php } ?>
					<p><input type="submit" value="<?php echo lang::get('contact.form_send'); ?>"/></p>
				</form>
			</div>
		</div>
	</section>
<?php } ?>
```

