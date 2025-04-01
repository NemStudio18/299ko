<?php
/**
 * Plugin Wiki pour 299Ko CMS
 * Contrôleur pour l'affichage d'une page Wiki côté public.
 */

defined('ROOT') or exit('Access denied!');

class WikiPageController {

    public function view() {
        $page = isset($_GET['page']) ? $_GET['page'] : 'Accueil.md';
        $wikiDir = DATA_PLUGIN . 'wiki' . DS;
        $filePath = $wikiDir . $page;
        if (!file_exists($filePath)) {
            die("Page non trouvée.");
        }
        $content = file_get_contents($filePath);
        // Transformation simple du Markdown en HTML (à compléter avec un vrai parseur si besoin)
        $htmlContent = nl2br(htmlspecialchars($content));

        $response = new Response();
        $tpl = $response->createPluginTemplate('wiki', 'wiki-view');
        $response->setTitle($page);
        $tpl->set('router', ROUTER::getInstance());
        $tpl->set('content', $htmlContent);
        $tpl->set('page', $page);
        $response->addTemplate($tpl);
        return $response;
    }
}
