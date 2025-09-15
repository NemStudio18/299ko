<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class ConfigManagerAdminController extends AdminController {
    
    public function home() {
        if (is_dir(ROOT . 'update')) {
            show::msg(lang::get('configmanager-update-dir-found') . '<br/><a class="button" href="' . $this->router->generate('configmanager-manual-update', ['token' => $this->user->token]) . '">' . lang::get('configmanager-update') . '</a>', 'true');
        }

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('configmanager', 'config');

        $tpl->set('link', $this->router->generate('configmanager-admin-save'));
        $tpl->set('cacheClearLink', $this->router->generate('configmanager-admin-cache-clear', ['token' => $this->user->token]));

        // Get cache statistics
        $cacheManager = new CacheManager();
        $tpl->set('cacheStats', $cacheManager->getStats());

        $response->addTemplate($tpl);
        return $response;
    }

    public function save() {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        if (array_key_exists($_POST['siteLang'], lang::$availablesLocales)) {
            $lang = $_POST['siteLang'];
        } else {
            $lang = lang::getLocale();
        }
        $config = [
            'siteName' => (trim($_POST['siteName']) != '') ? trim($_POST['siteName']) : 'Demo',
            'siteDesc' => (trim($_POST['siteDesc']) != '') ? trim($_POST['siteDesc']) : '',
            'siteLang' => $lang,
            'siteUrl' => (trim($_POST['siteUrl']) != '') ? rtrim(trim($_POST['siteUrl']), '/') : $this->core->getConfigVal('siteUrl'),
            'theme' => $_POST['theme'],
            'defaultPlugin' => $_POST['defaultPlugin'],
            'hideTitles' => (isset($_POST['hideTitles'])) ? true : false,
            'debug' => (isset($_POST['debug'])) ? true : false,
            'defaultAdminPlugin' => $_POST['defaultAdminPlugin'],
            'cache_enabled' => (isset($_POST['cache_enabled'])) ? true : false,
            'cache_duration' => (int)$_POST['cache_duration'],
            'cache_minify' => (isset($_POST['cache_minify'])) ? true : false,
            'cache_lazy_loading' => (isset($_POST['cache_lazy_loading'])) ? true : false
        ];

        // Invalidate cache if needed (AVANT la sauvegarde pour avoir l'ancienne config)
        $this->invalidateCacheIfNeeded($config);
        
        if (!$this->core->saveConfig($config, $config)) {
            show::msg(lang::get("core-changes-not-saved"), 'error');
        } else {
            show::msg(lang::get("core-changes-saved"), 'success');
        }
        //$this->core->saveHtaccess($_POST['htaccess']);
        $this->core->redirect($this->router->generate('configmanager-admin'));
    }

    public function deleteInstall($token) {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        $del = unlink(ROOT . 'install.php');
        if ($del) {
            show::msg(lang::get('configmanager-deleted-install'), 'success');
        } else {
            show::msg(lang::get('configmanager-error-deleting-install'), 'error');
        }
        return $this->home();
    }

    public function clearCache($token) {
        if (!$this->user->isAuthorized()) {
            return $this->home();
        }
        $cacheManager = new CacheManager();
        if ($cacheManager->clearCache()) {
            show::msg(lang::get('configmanager-cache-clear-success'), 'success');
        } else {
            show::msg(lang::get('configmanager-cache-clear-error'), 'error');
        }
        return $this->home();
    }



    /**
     * Invalidate cache when configuration changes
     * 
     * @param array $newConfig
     * @return void
     */
    protected function invalidateCacheIfNeeded(array $newConfig): void
    {
        $oldConfig = $this->core->getconfig();
        
        // If theme has changed
        if (isset($newConfig['theme']) && $newConfig['theme'] !== ($oldConfig['theme'] ?? '')) {
            // Invalidate old theme cache
            if (!empty($oldConfig['theme'])) {
                $oldTheme = new Theme($oldConfig['theme']);
                $oldTheme->invalidateCache();
            }
            // Invalidate new theme cache
            $newTheme = new Theme($newConfig['theme']);
            $newTheme->invalidateCache();
            // Force invalidate all page caches to ensure theme change is reflected
            $this->core->invalidateCacheByTag('page');
        }
        
        // If language has changed
        if (isset($newConfig['siteLang']) && $newConfig['siteLang'] !== ($oldConfig['siteLang'] ?? '')) {
            // Invalidate old language cache
            if (!empty($oldConfig['siteLang'])) {
                lang::invalidateCache($oldConfig['siteLang']);
            }
            
            // Invalidate new language cache
            lang::invalidateCache($newConfig['siteLang']);
            
            // Force invalidate all page caches to ensure language change is reflected
            $this->core->invalidateCacheByTag('page');
        }
        
        // If cache settings have changed - only invalidate specific caches
        $cacheSettings = ['cache_minify', 'cache_lazy_loading'];
        
        foreach ($cacheSettings as $setting) {
            if (isset($newConfig[$setting]) && $newConfig[$setting] !== ($oldConfig[$setting] ?? false)) {
                // Only invalidate caches that depend on these settings
                $this->core->invalidateCacheByTag('minify_enabled');
                $this->core->invalidateCacheByTag('lazy_enabled');
                break;
            }
        }
        
        // If cache is disabled, invalidate all cache
        if (isset($newConfig['cache_enabled']) && $newConfig['cache_enabled'] !== ($oldConfig['cache_enabled'] ?? true)) {
            if (!$newConfig['cache_enabled']) {
                // Cache disabled - invalidate all
                $this->core->invalidateAllCache();
            }
        }
        
        // If cache duration changed, invalidate all cache (as duration affects all cached content)
        if (isset($newConfig['cache_duration']) && $newConfig['cache_duration'] !== ($oldConfig['cache_duration'] ?? 3600)) {
            $this->core->invalidateAllCache();
        }
        
        // Invalidate plugin caches if their content might have changed
        $this->invalidatePluginCachesIfNeeded($oldConfig, $newConfig);
    }
    
    /**
     * Invalidate plugin caches if their content might have changed
     * 
     * @param array $oldConfig
     * @param array $newConfig
     * @return void
     */
    protected function invalidatePluginCachesIfNeeded(array $oldConfig, array $newConfig): void
    {
        // Get plugins manager to access all plugins
        $pluginsManager = pluginsManager::getInstance();
        $plugins = $pluginsManager->getPlugins();
        
        foreach ($plugins as $plugin) {
            $pluginName = $plugin->getName();
            $pluginConfig = $plugin->getConfig();
            
            // Check if plugin is activated
            if (!$plugin->getConfigVal('activate')) {
                continue;
            }
            
            // Check if plugin configuration has changed
            $pluginDataPath = DATA_PLUGIN . $pluginName . '/config.json';
            if (file_exists($pluginDataPath)) {
                $currentPluginConfig = util::readJsonFile($pluginDataPath);
                if ($currentPluginConfig !== false) {
                    // Compare current config with what we have in memory
                    if ($this->hasPluginConfigChanged($pluginConfig, $currentPluginConfig)) {
                        // Plugin config changed, invalidate its cache
                        $plugin->invalidateCache();
                    }
                }
            }
            
            // Check for specific plugin content changes
            $this->checkPluginSpecificChanges($plugin, $oldConfig, $newConfig);
        }
    }
    
    /**
     * Check if plugin configuration has changed
     * 
     * @param array $memoryConfig
     * @param array $fileConfig
     * @return bool
     */
    protected function hasPluginConfigChanged(array $memoryConfig, array $fileConfig): bool
    {
        // Compare important config keys that would affect cache
        $importantKeys = ['activate', 'priority', 'label', 'itemsByPage', 'displayTOC', 'hideContent', 'comments'];
        
        foreach ($importantKeys as $key) {
            if (isset($memoryConfig[$key]) && isset($fileConfig[$key])) {
                if ($memoryConfig[$key] !== $fileConfig[$key]) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check for plugin-specific content changes
     * 
     * @param plugin $plugin
     * @param array $oldConfig
     * @param array $newConfig
     * @return void
     */
    protected function checkPluginSpecificChanges($plugin, array $oldConfig, array $newConfig): void
    {
        $pluginName = $plugin->getName();
        
        // Blog plugin specific checks
        if ($pluginName === 'blog') {
            // Check if blog settings that affect content have changed
            if (isset($newConfig['blog_itemsByPage']) && $newConfig['blog_itemsByPage'] !== ($oldConfig['blog_itemsByPage'] ?? 5)) {
                $plugin->invalidateCache();
            }
            if (isset($newConfig['blog_displayTOC']) && $newConfig['blog_displayTLS'] !== ($oldConfig['blog_displayTOC'] ?? 'no')) {
                $plugin->invalidateCache();
            }
        }
        
        // Galerie plugin specific checks
        if ($pluginName === 'galerie') {
            if (isset($newConfig['galerie_order']) && $newConfig['galerie_order'] !== ($oldConfig['galerie_order'] ?? 'byDate')) {
                $plugin->invalidateCache();
            }
            if (isset($newConfig['galerie_showTitles']) && $newConfig['galerie_showTitles'] !== ($oldConfig['galerie_showTitles'] ?? '1')) {
                $plugin->invalidateCache();
            }
        }
        

        
        // Default plugin change - invalidate all plugin caches
        if (isset($newConfig['defaultPlugin']) && $newConfig['defaultPlugin'] !== ($oldConfig['defaultPlugin'] ?? '')) {
            $plugin->invalidateCache();
        }
    }
}