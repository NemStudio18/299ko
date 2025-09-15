<?php
/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxime Blanc <nemstudio18@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 *
 * Marketplace Theme for 299Ko CMS
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

defined('ROOT') or exit('Access denied!');

class ThemesMarketController extends AdminController
{
    protected MarketPlaceManager $marketManager;
    public function __construct() {
        parent::__construct();
        if(!function_exists('curl_init')) {
            show::msg(lang::get('marketplace.curl_not_installed'), 'error');
            $this->core->redirect($this->router->generate('themesmanager-list'));
        }
        $this->marketManager = new MarketPlaceManager();
    }

    public function index() {
        $themesData = $this->marketManager->getThemes() ?? [];
        $themes = [];
        foreach ($themesData as $theme) {
            $r = new MarketPlaceRessource(MarketPlaceRessource::TYPE_THEME, $theme);
            $themes[$theme->slug] = $r;
        }

        // Prepare the admin response with the themes marketplace template
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('marketplace', 'admin-marketplace-themes');
        $response->setTitle(lang::get('marketplace.list_themes'));
        
        $themesTpl = $response->createPluginTemplate('marketplace', 'display-ressources');
        $themesTpl->set('ressources', $themes);
        $themesTpl->set('token', $this->user->token);
        $tpl->set('THEMES_TPL', $themesTpl->output());
        $response->addTemplate($tpl);
        return $response;

    }


}