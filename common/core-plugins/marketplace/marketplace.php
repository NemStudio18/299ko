<?php
/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxime Blanc <nemstudio18@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 *
 * Marketplace Plugin for 299Ko CMS
 *
 * This plugin provides a marketplace that allows users to install
 * plugins and themes directly from GitHub.
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

defined('ROOT') or exit('Access denied!');

require_once CORE_PLUGINS . 'marketplace/lib/MarketPlaceCurl.php';
require_once CORE_PLUGINS . 'marketplace/lib/MarketPlaceManager.php';

require_once CORE_PLUGINS . 'marketplace/entities/MarketPlaceRessource.php';

function marketplaceInstall() {
    $marketConfig = util::readJsonFile(DATA_PLUGIN . 'marketplace/marketplace.json');
    if (!$marketConfig) {
        $marketConfig = [];
    }
    if (!isset($marketConfig['siteID'])) {
        $marketConfig['siteID'] = uniqid('299ko-', true);
    }
    util::writeJsonFile(DATA_PLUGIN . 'marketplace/marketplace.json', $marketConfig);

}