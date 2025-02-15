<?php
namespace Common\Controllers;

use Common\{PluginsManager, Plugin};
use Plugins\Users\Entities\UsersManager;

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

class AdminController extends Controller {

    /**
     * Current plugin instance
     * @var Plugin
     */
    protected Plugin $runPlugin;

    /**
     * Current User
     * @var \Plugins\Users\Entities\User
     */
    protected \Plugins\Users\Entities\User $user;

    public function __construct() {
        parent::__construct();
        if (IS_ADMIN === false) {
            $this->core->error404();
        }
        $pluginName = $this->core->getPluginToCall();
        if (pluginsManager::isActivePlugin($pluginName)) {
            $this->runPlugin = $this->pluginsManager->getPlugin($pluginName);
        } else {
            $this->core->error404();
        }

        if (!defined('ADMIN_MODE')) {
            define('ADMIN_MODE', true);
        }
        $this->user = UsersManager::getCurrentUser();
    }

}