<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class MarketPlaceManager
{

    protected Cache $cache;

    protected Logger $logger;

    public function __construct()
    {
        $this->cache = new Cache();
        $this->logger = core::getInstance()->getLogger();
    }

    /**
     * Retrieve the list of themes from the marketplace.
     *
     * This method attempts to get the themes from the cache first. If the cache is empty or expired,
     * it makes an API call to fetch the themes from the remote repository. The retrieved themes are then
     * cached for future requests.
     *
     * @return array An array of themes, or an empty array if the API call fails.
     */
    public function getThemes(): array
    {
        $themes = $this->cache->get('marketplace-themes');
        if ($themes === false) {
            $curl = new MarketPlaceCurl('repository/api/get-themes');
            $curl->post();
            $resp = $curl->execute()->getResponse();
            if ($resp['code'] !== 200 || empty($resp['body'])) {
                return [];
            }
            $themes = json_decode($resp['body']);
            $this->cache->set('marketplace-themes', $themes, 3600);
        }
        return $themes;
    }

    /**
     * Retrieve the list of plugins from the marketplace.
     *
     * This method attempts to get the plugins from the cache first. If the cache is empty or expired,
     * it makes an API call to fetch the plugins from the remote repository. The retrieved plugins are then
     * cached for future requests.
     *
     * @return array An array of plugins, or an empty array if the API call fails.
     */
    public function getPlugins(): array
    {
        $plugins = $this->cache->get('marketplace-plugins');
        if ($plugins === false) {
            $curl = new MarketPlaceCurl('repository/api/get-plugins');
            $curl->post();
            $resp = $curl->execute()->getResponse();
            if ($resp['code'] !== 200 || empty($resp['body'])) {
                return [];
            }
            $plugins = json_decode($resp['body']);
            $this->cache->set('marketplace-plugins', $plugins, 3600);
        }
        return $plugins;
    }

    /**
     * Retrieve a plugin by its slug from the marketplace.
     *
     * @param string $slug The slug of the plugin to retrieve.
     * @return object|false The plugin object, or false if the plugin is not found.
     */
    public function getPlugin(string $slug)
    {
        $plugins = $this->getPlugins();
        foreach ($plugins as $plugin) {
            if ($plugin->slug === $slug) {
                return $plugin;
            }
        }
        return false;
    }

    /**
     * Retrieve a theme by its slug from the marketplace.
     *
     * @param string $slug The slug of the theme to retrieve.
     * @return object|false The theme object, or false if the theme is not found.
     */
    public function getTheme(string $slug)
    {
        $themes = $this->getThemes();
        foreach ($themes as $theme) {
            if ($theme->slug === $slug) {
                return $theme;
            }
        }
        return false;
    }

    /**
     * Retrieve a marketplace resource by type and slug.
     *
     * This method checks the type of the resource: if it is a plugin, it retrieves
     * the plugin using its slug; if it is a theme, it retrieves the theme using its slug.
     * If the type is neither, it returns false.
     *
     * @param string $type The type of the resource, either plugin or theme.
     * @param string $slug The unique identifier of the resource.
     * @return object|false The resource object (plugin or theme), or false if not found or type is invalid.
     */
    public function getRessourceAsArray(string $type, string $slug)
    {
        if ($type === MarketPlaceRessource::TYPE_PLUGIN) {
            return $this->getPlugin($slug);
        } else if ($type === MarketPlaceRessource::TYPE_THEME) {
            return $this->getTheme($slug);
        } else {
            return false;
        }
    }

    /**
     * Install a marketplace ressource on the current website.
     *
     * This method installs a plugin or a theme from the marketplace.
     * If the ressource is a plugin, it is installed in the plugins directory.
     * If the ressource is a theme, it is installed in the themes directory.
     *
     * If the installation fails, the method returns false.
     * If the installation is successful, the method returns true.
     *
     * @param MarketPlaceRessource $ressource The ressource to install.
     * @return bool True if the installation is successful, false otherwise.
     */
    public function installRessource(MarketPlaceRessource $ressource): bool
    {
        if ($ressource->isInstalled) {
            $this->logger->info('updating ressource ' . $ressource->slug . ' to version ' . $ressource->getNextVersion());
        } else {
            $this->logger->info('installing ressource ' . $ressource->slug);
        }

        $curl = new MarketPlaceCurl('repository/api/install-' . $ressource->type);
        $curl->post();
        $curl->setDatas([
            'type' => $ressource->type,
            'slug' => $ressource->slug,
            'version' => $ressource->getNextVersion()
        ]);
        $resp = $curl->execute()->getResponse();
        if ($resp['code'] !== 200 || empty($resp['body'])) {
            $this->logger->error('Unable to install ressource ' . $ressource->slug . PHP_EOL . $resp['body']);
            return false;
        }
        file_put_contents(CACHE . 'tmp.zip', $resp['body']);
        if (filesize(CACHE . 'tmp.zip') === 0) {
            $this->logger->error('Unable to install ressource ' . $ressource->slug . ' (empty zip file)');
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open(CACHE . 'tmp.zip') === true) {
            if ($ressource->type === MarketPlaceRessource::TYPE_PLUGIN) {
                $zip->extractTo(PLUGINS);
            } else if ($ressource->type === MarketPlaceRessource::TYPE_THEME) {
                $zip->extractTo(THEMES);
            }
            $zip->close();
            unlink(CACHE . 'tmp.zip');
            $this->runAfterUpdatePlugin($ressource);
            $this->logger->info($ressource->slug . ' correctly installed');
            return true;
        }
        $this->logger->error('Unable to install ressource ' . $ressource->slug . ' (zip error)');
        return false;

    }

    protected function runAfterUpdatePlugin(MarketPlaceRessource $ressource) {
        if ($ressource->type !== MarketPlaceRessource::TYPE_PLUGIN) {
            // Theme dont have _afterUpdate file
            return;
        }
        $path = PLUGINS . $ressource->slug . DS . '_afterUpdate.php';
        if (!$ressource->isInstalled) {
            // New install, dont worry about update
            if (file_exists($path)) {
                unlink($path);
                return;
            }
        }
        if (!file_exists($path)) {
            return;
        }
        $this->logger->info('execute _afterUpdate.php from ' . $ressource->slug);
        include_once($path);
        unlink($path);
    }
}