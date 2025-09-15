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
defined('ROOT') OR exit('Access denied!');

class plugin {

    private $infos;
    private $config;
    private $name;
    private $hooks;
    private $isValid;
    private $isDefaultPlugin;
    private $titleTag;
    private $metaDescriptionTag;
    private $mainTitle;
    private $libFile;
    private $publicFile;
    private $adminFile;
    private $paramTemplate;
    private $dataPath;
    private $initConfig;
    private $navigation;
    private $publicCssFile;
    private $publicJsFile;
    private $adminCssFile;
    private $adminJsFile;
    private $isDefaultAdminPlugin;
    private $helpTemplate;
    private $publicUrl;

    private bool $isCallableOnPublic = false;

    private array $callablePublic = [];

    private bool $isCallableOnAdmin = false;

    private array $callableAdmin = [];

    ## Constructeur

    public function __construct($name, $config = [], $infos = [], $hooks = [], $initConfig = [], $pluginPath = null) {
        $core = core::getInstance();
        // Identifiant du plugin
        $this->name = $name;
        // Tableau de configuration
        $this->config = $config;
        // Tableau d'informations
        $this->infos = $infos;
        // Liste des hooks
        $this->hooks = $hooks;
        // Validité du plugin
        $this->isValid = true;
        // On détermine si il s'agit du plugin par défaut en mode public
        $this->isDefaultPlugin = ($name == $core->getConfigVal('defaultPlugin')) ? true : false;
        // On détermine si il s'agit du plugin par défaut en mode admin
        $this->isDefaultAdminPlugin = ($name == $core->getConfigVal('defaultAdminPlugin')) ? true : false;
        // Meta title
        $this->setTitleTag($infos['name']);
        // Titre de page
        $this->setMainTitle($infos['name']);
        
        // Determine plugin path (core-plugins first, then regular plugins)
        if ($pluginPath === null) {
            $pluginPath = CORE_PLUGINS . $this->name . '/';
            if (!is_dir($pluginPath)) {
                $pluginPath = PLUGINS . $this->name . '/';
            }
        }
        
        // Fichier php principal
        $this->libFile = (file_exists($pluginPath . $this->name . '.php')) ? $pluginPath . $this->name . '.php' : false;

        $this->setCallables();
        
        // CSS
        $this->publicCssFile = (file_exists($pluginPath . 'template/public.css')) ? $pluginPath . 'template/public.css' : false;
        $this->adminCssFile = (file_exists($pluginPath . 'template/admin.css')) ? util::urlBuild($pluginPath . 'template/admin.css') : false;
        // JS
        $this->publicJsFile = (file_exists($pluginPath . 'template/public.js')) ? $pluginPath . 'template/public.js' : false;
        $this->adminJsFile = (file_exists($pluginPath . 'template/admin.js')) ? util::urlBuild($pluginPath . 'template/admin.js') : false;
        // Répertoir de sauvegarde des données internes du plugin
        $this->dataPath = (is_dir(DATA_PLUGIN . $this->name)) ? DATA_PLUGIN . $this->name . '/' : false;
        // Configuration d'usine
        $this->initConfig = $initConfig;
        // Navigation
        $this->navigation = [];
        // URL public
        $this->publicUrl = $core->getConfigVal('siteUrl') . '/' . $this->name . '/';
        $this->determineTemplatesFiles();
    }

    protected function setCallables() {
        $homePublic = $this->getInfoVal('homePublicMethod') ?? false;
        if ($homePublic) {
            list($controller, $action) = explode('#', $homePublic);
            $this->callablePublic = [$controller, $action];
            $this->isCallableOnPublic = true;
        }
        $homeAdmin = $this->getInfoVal('homeAdminMethod') ?? false;
        if ($homeAdmin) {
            list($controller, $action) = explode('#', $homeAdmin);
            $this->callableAdmin = [$controller, $action];
            $this->isCallableOnAdmin = true;
        }
    }

    /**
     * Determine Templates Files if files exist or not
     */
    protected function determineTemplatesFiles() {
        $core = core::getInstance();
        
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $this->name . '/';
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $this->name . '/';
        }
        
        // Template public (peut etre le template par defaut ou un template présent dans le dossier du theme portant le nom du plugin)
       

        // Template parametres
        if (file_exists($pluginPath . 'template/param.tpl'))
            $this->paramTemplate = $pluginPath . 'template/param.tpl';
        else
            $this->paramTemplate = false;

        // Template d'aide
        if (file_exists($pluginPath . 'template/help.tpl'))
            $this->helpTemplate = $pluginPath . 'template/help.tpl';
        else
            $this->helpTemplate = false;
    }

    ## Getters

    public function getConfigVal($val) {
        return $this->config[$val] ?? false;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getInfoVal($val) {
        return $this->infos[$val] ?? false;
    }

    public function getName() {
        return $this->name;
    }

    public function getTranslatedName() {
        if (lang::get($this->name . '.name') === $this->name . '.name' ) {
            return $this->getInfoVal('name');
        }
        return lang::get($this->name . '.name');
    }

    public function getTranslatedDesc() {
        if (lang::get($this->name . '.description') === $this->name . '.description' ) {
            return $this->getInfoVal('description');
        }
        return lang::get($this->name . '.description');
    }

    public function getHooks() {
        return $this->hooks;
    }

    public function getIsDefaultPlugin() {
        return $this->isDefaultPlugin;
    }

    public function getTitleTag() {
        return $this->titleTag;
    }

    public function getMetaDescriptionTag() {
        return $this->metaDescriptionTag;
    }

    public function getMainTitle() {
        return $this->mainTitle;
    }

    public function getLibFile() {
        return $this->libFile;
    }

    public function getPublicFile() {
        return $this->publicFile;
    }

    public function getAdminFile() {
        return $this->adminFile;
    }
    
    public function getPublicCssFile() {
        return $this->publicCssFile;
    }

    public function getAdminCssFile() {
        return $this->adminCssFile;
    }

    public function getPublicJsFile() {
        return $this->publicJsFile;
    }

    public function getAdminJsFile() {
        return $this->adminJsFile;
    }

    public function getDataPath() {
        return $this->dataPath;
    }

    public function getParamTemplate() {
        return $this->paramTemplate;
    }

    public function getHelpTemplate() {
        return $this->helpTemplate;
    }

    public function getIsValid() {
        return $this->isValid;
    }

    public function getNavigation() {
        return $this->navigation;
    }

    public function getIsDefaultAdminPlugin() {
        return $this->isDefaultAdminPlugin;
    }

    public function getPublicUrl() {
        return $this->publicUrl;
    }

    ## Permet de modifier une valeur de configuration

    public function setConfigVal($k, $v) {
        $this->config[$k] = $v;
        if ($k == 'activate' && $v < 1 && $this->isRequired())
            $this->isValid = false;
    }

    ## Permet de forcer la meta title

    public function setTitleTag($val) {
        $this->titleTag = trim($val);
    }

    ## Permet de forcer la meta description

    public function setMetaDescriptionTag($val) {
        $this->metaDescriptionTag = trim($val);
    }

    ## Permet de forcer le titre de page

    public function setMainTitle($val) {
        $this->mainTitle = trim($val);
    }

    ## Ajoute un item dans la navigation

    public function addToNavigation($label, $target, $targetAttribut = '_self', $id = 0, $parent = 0, $cssClass = '') {
        $this->navigation[] = array('label' => $label, 'target' => $target, 'targetAttribut' => $targetAttribut, 'id' => $id, 'parent' => $parent, 'cssClass' => $cssClass);
    }

    ## Supprime un item de la navigation

    public function removeToNavigation($k) {
        unset($this->navigation[$k]);
    }

    ## Initialise la navigation

    public function initNavigation() {
        $this->navigation = [];
    }

    ## Détermine si le plugin est installé

    public function isInstalled() {
        $temp = $this->config;
        unset($temp['activate']);
        $currentConfig = implode(',', array_keys($temp));
        $initConfig = @implode(',', array_keys($this->initConfig));
        if (count($this->config) < 1 || $currentConfig != $initConfig)
            return false;
        return true;
    }

    ## Détermine si le plugin est protégé, ce qui empèche de le désactiver

    public function isRequired() {
        if (isset($this->config['protected']) && $this->config['protected'] == 1)
            return true;
        if ($this->isDefaultPlugin)
            return true;
        return false;
    }
    
    public function loadLangFile() {
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $this->name . '/';
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $this->name . '/';
        }
        lang::loadLanguageFile($pluginPath . 'langs/');
    }
    
    public function loadRoutes() {
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $this->name . '/';
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $this->name . '/';
        }
        if (is_file($pluginPath . 'param/routes.php')) {
            require_once $pluginPath . 'param/routes.php';
        }
    }
    
    public function loadControllers() {
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $this->name . '/';
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $this->name . '/';
        }
        foreach (glob($pluginPath . 'controllers/' . "*.php") as $file) {
            include_once $file;
        }
    }

	/**
     * Return if this plugin is callbale in public mode
	 * @return bool
	 */
	public function getIsCallableOnPublic(): bool {
		return $this->isCallableOnPublic;
	}

	/**
     * Get a callable array from a plugin
     * This callable is the plugin's homepage
	 * @return array
	 */
	public function getCallablePublic(): array {
		return $this->callablePublic;
	}

    /**
     * Return if this plugin is callbale in admin mode
	 * @return bool
	 */
	public function getIsCallableOnAdmin(): bool {
		return $this->isCallableOnAdmin;
	}

	/**
     * Get a callable array from a plugin
     * This callable is the plugin's admin homepage
	 * @return array
	 */
	public function getCallableAdmin(): array {
		return $this->callableAdmin;
	}

	/**
	 * Invalidate plugin cache
	 * 
	 * @return void
	 */
	public function invalidateCache(): void
	{
		$cache = new Cache();
		$cache->deleteByTag('plugin_' . $this->name);
	}
}
