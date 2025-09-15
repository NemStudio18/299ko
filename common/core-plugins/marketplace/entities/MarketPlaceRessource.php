<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

/**
 * This class definition represents a marketplace resource, which can be either a plugin or a theme. 
 */

class MarketPlaceRessource {

    const TYPE_PLUGIN = 'plugin';
    const TYPE_THEME = 'theme';
    public string $slug = '';

    public string $name = '';

    public string $description = '';

    public string $lastVersion = '';

    public string $version = '';

    public string $authorEmail = '';

    public string $authorWebsite = '';

    public string $type = '';

    public bool $isInstalled = false;

    public bool $isInstallable = false;

    protected $origRessource;

    /**
     * List of all versions for update
     * @var stdClass
     */
    protected $versionsUpdate;

    /**
     * Construct a MarketPlaceRessource object
     * @param string $type the type of the ressource, either plugin or theme
     * @param stdClass $origRessource the original ressource object
     */
    public function __construct(string $type, $origRessource) {
        $this->type = $type;
        $this->origRessource = $origRessource;
        $this->slug = $origRessource->slug;
        $this->name = $origRessource->name;
        $this->description = $origRessource->description ?? '';
        $this->lastVersion = $origRessource->version ?? '';
        $this->authorEmail = $origRessource->authorEmail ?? '';
        $this->authorWebsite = $origRessource->authorWebsite ?? '';
        $this->versionsUpdate = $origRessource->versionsUpdate ?? new stdClass();
        
        if ($this->type === self::TYPE_PLUGIN) {
            // Check if the plugin is installed
            $plu = pluginsManager::getInstance()->getPlugin($this->slug);
            if ($plu !== false) {
                $this->isInstalled = true;
                $this->version = $plu->getInfoVal('version') ?? '';
            }
        } else {
            // Check if the theme is installed
            $theme = new Theme($this->slug);
            if ($theme->isInstalled()) {
                $this->isInstalled = true;
                $this->version = $theme->version ?? '';
            }
        }
        $this->checkIsInstallable();
    }

    /**
     * Checks if the marketplace resource is installable.
     *
     * This method sets the isInstallable property to true if the current
     * environment meets the minimum version requirements for 299Ko CMS and PHP
     * specified by the resource. If the requirements are not met, it sets 
     * isInstallable to false.
     */
    protected function checkIsInstallable() {
        $this->isInstallable = true;
        $min299koVersion = $this->origRessource->required299koVersion ?? '1.0.0';
        if( !version_compare(VERSION, $min299koVersion, '>=')) {
            $this->isInstallable = false;
        }
        $minPHPVersion = $this->origRessource->requiredPHPVersion ?? '7.4.0';
        if( !version_compare(PHP_VERSION, $minPHPVersion, '>=')) {
            $this->isInstallable = false;
        }
        
    }

    /**
     * Determine if an update is needed for the ressource.
     *
     * Checks whether the ressource is installed and if the current version
     * differs from the last available version.
     *
     * @return bool True if an update is needed, false otherwise.
     */
    public function updateNeeded():bool {
        return ($this->isInstalled && $this->version != $this->lastVersion);
    }

    /**
     * Retrieve the URL of the primary preview image for the resource.
     *
     * @return string|false The URL of the primary preview image, or false if not available.
     */
    public function getPreviewUrl() {
        return $this->origRessource->preview_images[0] ?? false;
    }

    /**
     * Retrieve the URLs of the additional preview images for the resource.
     *
     * Returns an array of URLs for all the preview images except the primary one.
     * If there are no additional preview images, it returns false.
     *
     * @return array|false An array of URLs of the additional preview images, or false if none exist.
     */
    public function getOthersPreviewsUrl() {
        if (count($this->origRessource->preview_images) > 1) {
            return array_slice($this->origRessource->preview_images, 1, count($this->origRessource->preview_images) - 1);
        }
        return false;
    }

    /**
     * Get the next version that should be installed
     *
     * If the current version of the resource is not in the list of updates or ressource is not installed, it returns 'init'
     *
     * @return string The next version to install
     */
    public function getNextVersion(): string {
        return $this->versionsUpdate->{$this->version} ?? 'init';
    }

}
