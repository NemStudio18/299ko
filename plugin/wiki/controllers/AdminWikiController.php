<?php
/**
 * Plugin Wiki pour 299Ko CMS
 * Permet de gérer les pages Wiki depuis l'administration.
 */

defined('ROOT') or exit('Access denied!');

class AdminWikiController extends AdminController {

    // Affiche la liste des pages Wiki enregistrées
    public function index() {
        $wikiDir = DATA_PLUGIN . 'wiki' . DS;
        $pages = [];
        if (is_dir($wikiDir)) {
            $files = scandir($wikiDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                    $pages[] = $file;
                }
            }
        }
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin-wiki');
        $response->setTitle(lang::get('wiki.list_pages'));
        $tpl->set('router', ROUTER::getInstance());
        $tpl->set('pages', $pages);
        $tpl->set('adminWikiEdit', ROUTER::getInstance()->generate('admin-wiki-edit'));
        $response->addTemplate($tpl);
        return $response;
    }

    // Charge le formulaire d'édition d'une page Wiki
    public function edit() {
        $page = isset($_GET['page']) ? $_GET['page'] : 'Accueil.md';
        $wikiDir = DATA_PLUGIN . 'wiki' . DS;
        $filePath = $wikiDir . $page;
        $content = file_exists($filePath) ? file_get_contents($filePath) : '';
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('wiki', 'admin-wiki-edit');
        $response->setTitle(lang::get('wiki.edit_page'));
        $tpl->set('router', ROUTER::getInstance());
        $tpl->set('page', $page);
        $tpl->set('content', $content);
        $response->addTemplate($tpl);
        return $response;
    }

    // Sauvegarde le contenu d'une page Wiki
    public function save() {
        if (!isset($_POST['page']) || !isset($_POST['content'])) {
            die("Données invalides.");
        }
        $page = $_POST['page'];
        $content = $_POST['content'];
        $wikiDir = DATA_PLUGIN . 'wiki' . DS;
        $filePath = $wikiDir . $page;
        if (file_put_contents($filePath, $content) === false) {
            die("Erreur lors de la sauvegarde de la page.");
        }
        header("Location: " . ROUTER::getInstance()->generate('admin-wiki'));
        exit;
    }
}
