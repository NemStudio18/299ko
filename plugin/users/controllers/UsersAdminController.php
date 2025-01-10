<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class UsersAdminController extends AdminController {

    public function home() {

// Vérification si l'utilisateur est administrateur
//***** a déplacer vers data **** //
$permissionsJson = '{
  "roles": {
    "admin": {
      "antispam": 1,
      "blog": 1,
      "configmanager": 1,
      "contact": 1,
      "filemanager": 1,
      "galerie": 1,
      "page": 1,
      "pluginsmanager": 1,
      "seo": 1,
      "tinymce": 1,
      "users": 1
    },
    "modo": {
      "antispam": 1,
      "blog": 1,
      "configmanager": 0,
      "contact": 1,
      "filemanager": 1,
      "galerie": 1,
      "page": 1,
      "pluginsmanager": 0,
      "seo": 1,
      "tinymce": 1,
      "users": 0
    },
    "auteur": {
      "antispam": 0,
      "blog": 1,
      "configmanager": 0,
      "contact": 0,
      "filemanager": 0,
      "galerie": 0,
      "page": 1,
      "pluginsmanager": 0,
      "seo": 0,
      "tinymce": 1,
      "users": 0
    },
    "member": {
      "antispam": 0,
      "blog": 0,
      "configmanager": 0,
      "contact": 0,
      "filemanager": 0,
      "galerie": 0,
      "page": 0,
      "pluginsmanager": 0,
      "seo": 0,
      "tinymce": 0,
      "users": 0
    }
  }
}';

//***** a déplacer vers Controller ou AdminController  **** //
function getAllowedRolesForPlugin($permissionsJson, $pluginName) {
    // Décoder le JSON en tableau associatif
    $permissions = json_decode($permissionsJson, true);

    // Tableau pour stocker les rôles autorisés
    $allowedRoles = [];

    // Parcourir les rôles et vérifier les autorisations pour le plugin donné
    foreach ($permissions['roles'] as $role => $plugins) {
        if (isset($plugins[$pluginName]) && $plugins[$pluginName] === 1) {
            $allowedRoles[] = $role; // Ajouter le rôle si l'accès est autorisé
        }
    }

    return $allowedRoles;
}
$currentPlugin = 'users';

$allowedRoles = getAllowedRolesForPlugin($permissionsJson, $currentPlugin);
//***** a garder sur la page pour le controle **** //
$this->checkAccess($allowedRoles);


                // Vérification si l'utilisateur est administrateur
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('users', 'userslist');

        $users = UsersManager::getUsers();
        foreach ($users as $user) {
            $user->deleteLink = $this->router->generate("users-delete", ["id" => $user->id , "token" => $this->user->token]);
        }
        $tpl->set('users', $users);
        $tpl->set('token', $this->user->token);

        $response->addTemplate($tpl);
        return $response;
    }
}