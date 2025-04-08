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