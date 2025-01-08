<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class UsersAdminManagementController extends AdminController {

    public function addUser() {

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('users', 'usersadd');

        $tpl->set('link', $this->router->generate('users-add-send'));

        $response->addTemplate($tpl);
        return $response;
    }

    public function addUserSend() {
        if (!$this->user->isAuthorized()) {
            return $this->addUser();
        }
        $mail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? false;
        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW) ?? false;
        if (!$mail || !$pwd) {
            show::msg(Lang::get('users-bad-entries'), 'error');
            return $this->addUser();
        }
        if (UsersManager::getUser($mail) !== false) {
            show::msg(Lang::get('users-already-exists'), 'error');
            return $this->addUser();
        }
        $user = new User();
        $user->email = $mail;
        $user->pwd = UsersManager::encrypt($pwd);
        $user->save();
        show::msg(Lang::get('users-added'), 'success');
        Logg('User added: '. $mail);
        $this->core->redirect($this->router->generate('users-admin-home'));
    }

    public function edit($id) {
        $user = UsersManager::getUserById($id);
        if ($user === null) {
            $this->core->redirect($this->router->generate('users-admin-home'));
        }
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('users', 'usersedit');

        $tpl->set('link', $this->router->generate('users-edit-send'));
        $tpl->set('user', $user);

        $response->addTemplate($tpl);
        return $response;
    }

public function editUserSend() {
    if (!$this->user->isAuthorized()) {
        return $this->addUser();
    }

    // Récupérer les données du formulaire
    $mail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? false;
    $pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW) ?? false;
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? false;
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW) ?? false; // Récupérer le rôle

    // Vérifier les données obligatoires
    if (!$mail || !$id || !$role) { // Ajouter la vérification du rôle
        show::msg(Lang::get('users-credentials-issue'), 'error');
        $this->core->redirect($this->router->generate('users-admin-home'));
    }

    // Récupérer l'utilisateur à modifier
    $user = UsersManager::getUserById($id);
    if ($user === null) {
        show::msg(Lang::get('users-credentials-issue'), 'error');
        $this->core->redirect($this->router->generate('users-admin-home'));
    }

    // Vérifier si l'e-mail est déjà utilisé par un autre utilisateur
    foreach (UsersManager::getUsers() as $u) {
        if ($u->email === $mail && $u->id !== $id) {
            show::msg(Lang::get('users-already-exists'), 'error');
            $this->core->redirect($this->router->generate('users-admin-home'));
        }
    }

    // Mettre à jour les informations de l'utilisateur
    if ($pwd !== false && $pwd !== '') {
        // Changer le mot de passe
        $user->pwd = UsersManager::encrypt($pwd);
    }
    $user->email = $mail;
    $user->role = $role; // Mettre à jour le rôle
    $user->save();

    // Afficher un message de succès et rediriger
    show::msg(Lang::get('users-edited'), 'success');
    Logg('User edited: ' . $mail);
    $this->core->redirect($this->router->generate('users-admin-home'));
}

    public function delete($id, $token) {
        if (!$this->user->isAuthorized()) {
            $this->core->redirect($this->router->generate('users-admin-home'));
        }
        $user = UsersManager::getUserById($id);
        if ($user === null) {
            show::msg(Lang::get('users-credentials-issue'), 'error');
            $this->core->redirect($this->router->generate('users-admin-home'));
        }
        $mail = $user->email;
        if ($user->delete()) {
            show::msg(Lang::get('users-deleted'), 'success');
            Logg('User deleted: '. $mail);
            $this->core->redirect($this->router->generate('users-admin-home'));
        }
        show::msg(Lang::get('core-changes-not-saved'), 'error');
            $this->core->redirect($this->router->generate('users-admin-home'));
    }

    public function registerUser() {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW);

        if (!$email || !$password) {
            show::msg(Lang::get('users-bad-entries'), 'error');
            return $this->addUser();
        }

        $user = new User(['email' => $email, 'pwd' => UsersManager::encrypt($password)]);
        $user->save();

        show::msg(Lang::get('users-registered'), 'success');
        $this->core->redirect($this->router->generate('home'));
    }

}