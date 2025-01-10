<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class AdminController extends Controller {

    /**
     * Current plugin instance
     * @var plugin
     */
    protected plugin $runPlugin;

    /**
     * Current User
     * @var User
     */
    protected User $user;

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

/**
 * Vérifie si l'utilisateur a au moins un des rôles spécifiés.
 *
 * @param array|string $requiredRoles Rôle(s) requis pour accéder à l'action.
 */
protected function checkAccess($requiredRoles) {
    // Convertir $requiredRoles en tableau si ce n'est pas déjà le cas
    $requiredRoles = is_array($requiredRoles) ? $requiredRoles : [$requiredRoles];

    // Vérifier si l'utilisateur a au moins un des rôles requis
    $hasAccess = false;
    foreach ($requiredRoles as $role) {
        if ($this->user && $this->user->hasRole($role)) {
            $hasAccess = true;
            break;
        }
    }

    // Si l'utilisateur n'a aucun des rôles requis, afficher un message d'erreur et rediriger
    if (!$hasAccess) {
        show::msg(Lang::get('core.access-denied'), 'error');
        $this->core->redirect($this->router->generate('admin'));
    }
}


    /**
     * Vérifie si l'utilisateur possède l'un des rôles autorisés.
     *
     * @param array $allowedRoles Liste des rôles autorisés.
     */
    protected function checkPermissions(array $allowedRoles) {
        if (!$this->user || !in_array($this->user->role, $allowedRoles)) {
            show::msg(Lang::get('core.access-denied'), 'error');
            $this->core->redirect($this->router->generate('home'));
        }
    }
        
}