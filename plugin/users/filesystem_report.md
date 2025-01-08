# Rapport sur le système de fichiers

## Vue ASCII de l'arborescence des fichiers

```
C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users
|-- controllers
  |-- UsersAdminController.php
  |-- UsersAdminManagementController.php
  |-- UsersLoginController.php
|-- entities
  |-- PasswordRecovery.php
  |-- User.php
  |-- UsersManager.php
|-- filesystem_report.md
|-- langs
  |-- en.ini
  |-- fr.ini
  |-- ru.ini
|-- param
  |-- config.json
  |-- hooks.json
  |-- infos.json
  |-- routes.php
|-- template
  |-- login.tpl
  |-- lostpwd-step2.tpl
  |-- lostpwd.tpl
  |-- public.css
  |-- register.tpl
  |-- usersadd.tpl
  |-- usersedit.tpl
  |-- userslist.tpl
|-- users.php
```

## Contenu des fichiers

### Fichier : UsersAdminController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\controllers\UsersAdminController.php

**Contenu :**
```
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
       // $this->checkAccess('admin');

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
```

### Fichier : UsersAdminManagementController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\controllers\UsersAdminManagementController.php

**Contenu :**
```
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
        $mail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? false;
        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW) ?? false;
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? false;
        $user = UsersManager::getUserById($id);
        if (!$mail || !$id || $user === null) {
            show::msg(Lang::get('users-credentials-issue'), 'error');
            $this->core->redirect($this->router->generate('users-admin-home'));
        }
        // Check if mail is already taken
        foreach (UsersManager::getUsers() as $u) {
            if ($u->email === $mail && $u->id !== $id) {
                show::msg(Lang::get('users-already-exists'), 'error');
                $this->core->redirect($this->router->generate('users-admin-home'));
            }
        }
        
        if ($pwd !== false && $pwd !== '') {
            // Change password
            $user->pwd = UsersManager::encrypt($pwd);
        }
        $user->email = $mail;
        $user->save();
        show::msg(Lang::get('users-edited'), 'success');
        Logg('User edited: '. $mail);
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
```

### Fichier : UsersLoginController.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\controllers\UsersLoginController.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class UsersLoginController extends PublicController
{

    public function login()
    {
        $response = new StringResponse();
        $tpl = $response->createPluginTemplate('users', 'login');
        $tpl->set('loginLink', $this->router->generate('login-send'));

        $tpl->set('lostLink', $this->router->generate('lost-password'));
        $response->addTemplate($tpl);
        return $response;
    }

    public function loginSend()
    {
        if (empty($_POST['adminEmail']) || empty($_POST['adminPwd']) || $_POST['_email'] !== '') {
            // Empty field or robot
            return $this->login();
        }
        $useCookies = $_POST['remember'] ?? false;
        $logged = UsersManager::login(trim($_POST['adminEmail']), $_POST['adminPwd'], $useCookies);
        if ($logged) {
            show::msg(Lang::get("users.now-connected"), 'success');
            $this->core->redirect($this->router->generate('admin'));
        } else {
            show::msg(Lang::get("users.bad-credentials"), 'error');
            $this->core->redirect($this->router->generate('login'));
        }
    }

    public function logout()
    {
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        setcookie('koAutoConnect', '/', 1, '/');
        // Restart session for flash messages
        session_start();
        show::msg(Lang::get("users.now-disconnected"), 'success');
        $this->core->redirect($this->router->generate('home'));
    }

    public function lostPassword()
    {
        $response = new StringResponse();
        $tpl = $response->createPluginTemplate('users', 'lostpwd');
        $tpl->set('lostPwdLink', $this->router->generate('lost-password-send'));

        $response->addTemplate($tpl);
        return $response;
    }

    public function lostPasswordSend()
    {
        if (empty($_POST['email']) || $_POST['_email'] !== '') {
            // Empty field or robot
            return $this->login();
        }
        $user = UsersManager::getUser(trim($_POST['email']));
        if ($user === false) {
            show::msg(Lang::get("users.bad-credentials"), 'error');
            $this->core->redirect($this->router->generate('login'));
        }
        $passRecovery = new PasswordRecovery();
        $pwd = $passRecovery->generatePassword();
        $passRecovery->insertToken($user->email, $user->token, $pwd);
        $successMail = $this->sendMail($user, $pwd);
        if ($successMail) {
            show::msg(Lang::get("users-lost-password-mail-sent"), 'success');
            $response = new PublicResponse();
            $tpl = $response->createPluginTemplate('users', 'lostpwd-step2');
            $response->addTemplate($tpl);
            return $response;
        }
        show::msg(Lang::get("users-lost-password-mail-not-sent"), 'error');
        $this->core->redirect($this->router->generate('home'));
    }

    public function lostPasswordConfirm($token)
    {
        sleep(2);
        $passRecovery = new PasswordRecovery();
        $usrToken = $passRecovery->getTokenFromToken($token);
        if ($usrToken === false) {
            show::msg(Lang::get("users-lost-bad-token-link"), 'error');
            $this->core->redirect($this->router->generate('login'));
        }
        $user = UsersManager::getUser($usrToken['mail']);
        if ($user === false) {
            show::msg(Lang::get("users-lost-bad-token-link"), 'error');
            $this->core->redirect($this->router->generate('login'));
        }
        $user->pwd = UsersManager::encrypt($usrToken['pwd']);
        $user->save();
        $passRecovery->deleteToken($token);
        show::msg(Lang::get("users-lost-password-success"), 'success');
            $this->core->redirect($this->router->generate('login'));
    }

    protected function sendMail($user, $pwd): bool
    {
        $link = $this->router->generate('lost-password-confirm', ['token' => $user->token]);
        $to = $user->email;
        $from = '299ko@' . $_SERVER['SERVER_NAME'];
        $reply = $from;
        $subject = lang::get('users-lost-password-subject', $this->core->getConfigVal('siteName'));
        $msg = lang::get('users-lost-password-content', $pwd, $link);
        $mail = util::sendEmail($from, $reply, $to, $subject, $msg);
        if ($mail) {
            logg('User ' . $user->mail . ' asked to reset password');
        }
        return $mail;
    }

    public function register()
    {
        $response = new StringResponse();
        $tpl = $response->createPluginTemplate('users', 'register');
        $tpl->set('registerLink', $this->router->generate('register-send')); // Lien d'action
        $response->addTemplate($tpl);
        return $response;
    }

public function registerSend()
{
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    if (!$email || !$password) {
        show::msg(Lang::get('users-bad-entries'), 'error');
        $this->core->redirect($this->router->generate('register'));
    }

    // Vérifier si l'utilisateur existe déjà
    if (UsersManager::getUser($email)) {
        show::msg(Lang::get('users-already-exists'), 'error');
        $this->core->redirect($this->router->generate('register'));
    }

    // Créer un nouvel utilisateur
    $user = new User([
        'email' => $email,
        'pwd' => UsersManager::encrypt($password),
        'role' => 'member' // Rôle par défaut pour les nouveaux membres
    ]);
    $user->save();

    show::msg(Lang::get('users-registered'), 'success');
    $this->core->redirect($this->router->generate('login'));
}

}

```

### Fichier : PasswordRecovery.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\entities\PasswordRecovery.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class PasswordRecovery
{

    protected string $file;

    protected array $data;

    const EXPIRATION_TIME = 60 * 60 * 3;

    public function __construct()
    {
        $this->file = DATA_PLUGIN . 'users/pwd.json';
        if (!file_exists($this->file)) {
            util::writeJsonFile($this->file, []);
        }
        $this->data = util::readJsonFile($this->file);
        $this->sanitizeExpiredTokens();
    }

    protected function sanitizeExpiredTokens()
    {
        foreach ($this->data as $k => &$token) {
            if ($token['expiration'] < time()) {
                unset($this->data[$k]);
            }
        }
        $this->saveTokens();
    }

    protected function saveTokens()
    {
        util::writeJsonFile($this->file, $this->data);
    }

    public function insertToken(string $mail, string $token, string $pwd)
    {
        $this->data[] = [
            'mail' => $mail,
            'token' => $token,
            'pwd' => $pwd,
            'expiration' => time() + self::EXPIRATION_TIME
        ];
        $this->saveTokens();
    }

    public function deleteToken(string $token) {
        foreach ($this->data as $k => &$dToken) {
            if ($dToken['token'] == $token) {
                unset($this->data[$k]);
            }
        }
        $this->saveTokens();
    }

    /**
     * 
     */
    public function getTokenFromToken(string $token)
    {
        foreach ($this->data as $tk) {
            if ($tk['token'] === $token) {
                return $tk;
            }
        }
        return false;
    }

    public function generatePassword(): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, 8);
    }
}

```

### Fichier : User.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\entities\User.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

class User implements JsonSerializable
{

    /**
     * User id property.
     */
    public int $id;

    public string $email;

    public string $role = 'member'; // Définir le rôle par défaut

    /**
     * User password
     */
    public string $pwd;

    /**
     * User's authentication token.
     */
    public string $token;

    /**
     * Delete link for the user. 
     */
    public string $deleteLink;

    /**
     * Construct a new User instance.
     *
     * @param array $infos User data including id, email, password hash, role, and token.
     */
    public function __construct($infos = [])
    {
        if (!empty($infos)) {
            $this->id = $infos['id'];
            $this->email = $infos['email'];
            $this->pwd = $infos['pwd'];
            $this->token = $infos['token'];
            $this->role = $infos['role'] ?? 'member'; // Rôle défini ou par défaut
        }
    }

    /**
     * Converts the User object into an array for JSON serialization. 
     * Returns an array containing the id, email, password hash, and auth token for the user.
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'pwd' => $this->pwd,
            'token' => $this->token,
            'role' => $this->role // Ajout du rôle
        ];
    }

    /**
     * Saves the user to the database.
     * Generates an ID and auth token for the user if they don't already exist.
     * Delegates to the UsersManager to handle the actual saving.
     */
    public function save()
    {
        $this->id = $this->id ?? UsersManager::getNextId();
        $this->token = $this->token ?? UsersManager::generateToken();
        return UsersManager::saveUser($this);
    }

    /**
     * Checks if the user is authorized based on the request token matching the user's token.
     * 
     * Checks if a 'token' parameter is present in the request (URL params or POST data).
     * If so, returns true if it matches the user's token, false otherwise.
     * 
     * Also checks the $_REQUEST global for a 'token' value and compares it to the user's token.
     * 
     * Returns false if no token is present. 
     */
    public function isAuthorized(): bool
    {
        $matches = router::getInstance()->match();
        if (isset($matches['params']['token'])) {
            if ($matches['params']['token'] === $this->token) {
                return true;
            }
            return false;
        }
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $data = json_decode($content, true);
            if (isset($data['token'])) {
                if ($data['token'] === $this->token) {
                    return true;
                }
                return false;
            }
        }
        if (!isset($_REQUEST['token']))
            return false;
        if ($_REQUEST['token'] != $this->token)
            return false;
        return true;
    }

    /**
     * Deletes the user from the database.
     * Delegates to the UsersManager to handle the actual deletion.
     * 
     * @return bool True if the user was deleted successfully, false otherwise.
     */
    public function delete(): bool
    {
        return UsersManager::deleteUser($this);
    }

    /**
     * Checks user role 
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

}
```

### Fichier : UsersManager.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\entities\UsersManager.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('No direct script access allowed');

/**
 * UsersManager provides methods for user management and authentication.
 * 
 * It handles login/logout, retrieving user objects, password encryption, 
 * auth tokens, and persistence of user data.
 */
class UsersManager
{

    /**
     * Path file where users are stored
     */
    protected static $file = DATA_PLUGIN . 'users/users.json';

    /**
     * Logs in a user with the provided email and password.
     * 
     * @param string $email The user's email address
     * @param string $password The user's password
     * @param bool $useCookies Whether to set auth cookies after successful login
     * @return bool True if login succeeded, false otherwise
     */
    public static function login(string $mail, string $password, bool $useCookies = false): bool
    {
        $user = self::getUser($mail);
        if ($user === false) {
            // User dont exist
            return false;
        }
        if ($user->pwd !== self::encrypt($password)) {
            // Incorrect mail & pwd
            return false;
        }
        $user->token = self::generateToken();
        $user->save();
        self::logon($user);
        if ($useCookies) {
            self::setRememberCookies($user);
        }
        return true;
    }

    /**
     * Checks if the user is logged in using auth cookies.
     *
     * @return bool True if user is logged in via cookies, false otherwise.
     */
    protected static function loginByCookies(): bool
    {
        $parts = explode('//', $_COOKIE['koAutoConnect']);
        $mail = $parts[0] ?? '';
        $cryptedPwd = $parts[1] ?? '';

        $user = self::getUser($mail);
        if ($user === false) {
            // User dont exist
            setcookie('koAutoConnect', '/', 1, '/');
            return false;
        }
        if ($user->pwd !== $cryptedPwd) {
            // Incorrect mail & pwd
            setcookie('koAutoConnect', '/', 1, '/');
            return false;
        }
        $user->token = self::generateToken();
        $user->save();
        self::logon($user);
        return true;
    }

    /**
     * Checks if the user is currently logged in.
     *
     * @return bool True if user is logged in, false otherwise.
     */
    public static function isLogged(): bool
    {
        if (self::getCurrentUser() === null) {
            // Try to connect by cookies
            if (isset($_COOKIE['koAutoConnect']) && is_string($_COOKIE['koAutoConnect'])) {
                return self::loginByCookies();
            }
            return false;
        }
        return true;
    }

    /**
     * Logs in the given user.
     *
     * @param User $user The user to log in.
     */
    protected static function logon(User $user):void
    {
        $_SESSION['email'] = $user->email;
        $_SESSION['token'] = $user->token;
    }

    /**
     * Sets remember me cookies for the given user.
     */
    protected static function setRememberCookies(User $user)
    {
        setcookie(
            'koAutoConnect',
            $user->email . '//' . $user->pwd,
            [
                'expires' => time() + 60 * 24 * 3600,
                'secure' => true,
                'httponly' => true,
                'path' => '/'
            ]
        );
    }

    /**
     * Get an user from his mail
     * @return User|false
     */
    public static function getUser(string $mail)
    {
        $users = self::getUsers();
        foreach ($users as $user) {
            if ($user->email == $mail) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Get a user by their ID.
     *
     * @param int $id The ID of the user to retrieve.
     * @return User|false The User object if found, false if not found.
     */
    public static function getUserById(int $id): ?User
    {
        $users = self::getUsers();
        foreach ($users as $user) {
            if ($user->id == $id) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Return the current User, if connected by session
     * @return User|false User or false if not connected
     */
    public static function getCurrentUser(): ?User
    {
        if (!isset($_SESSION['email'])) {
            return null;
        }
        $user = self::getUser($_SESSION['email']);
        if ($user !== false) {
            if ($_SESSION['token'] === $user->token) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Saves a User object to persistent storage.
     */
    public static function saveUser(User $user):bool
    {
        $users = self::getUsers();
        $users[$user->id] = $user;
        return self::saveUsers($users);
    }

    /**
     * Deletes a user from persistent storage.
     *
     * @param User $user The user object to delete.
     * @return bool True if the user was deleted, false otherwise.
     */
    public static function deleteUser(User $user): bool
    {
        $users = self::getUsers();
        unset($users[$user->id]);
        return self::saveUsers($users);
    }

    /**
     * Saves the given array of User objects to persistent storage.
     */
    protected static function saveUsers(array $users)
    {
        return util::writeJsonFile(self::$file, $users);
    }

    /**
     * Returns all User objects.
     */
    public static function getUsers(): array
    {
        $userSource = util::readJsonFile(self::$file);
        if ($userSource === false) {
            return [];
        }
        $users = [];
        foreach ($userSource as $rawUser) {
            $users[$rawUser['id']] = new User($rawUser);
        }
        return $users;
    }

    /**
     * Encrypts the given data string.
     *
     * @param string $data The data to encrypt
     * @return string The encrypted data
     */
    public static function encrypt(string $data): string
    {
        return hash_hmac('sha1', $data, KEY);
    }

    /**
     * Generates a random token string to be used for authentication.
     *
     * @return string A random token string.
     */
    public static function generateToken(): string
    {
        return sha1(uniqid(mt_rand(), true));
    }

    /**
     * Gets the next available user ID.
     *
     * @return string The next available user ID.
     */
    public static function getNextId(): int
    {
        if (empty(self::getUsers())) {
            return 1;
        }
        return max(array_keys(self::getUsers())) + 1;
    }
}
```

### Fichier : en.ini
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\langs\en.ini

**Contenu :**
```
; General
users.name = Users
users.description = "User Management"

; Public
users.now-connected = "You are now connected"
users.now-disconnected = "You are now disconnected"
users.bad-credentials = "The entered information does not allow logging in"
users.bad-mail = "The entered email address does not match any user"
users.remember = Remember me

; Admin
users-list = "User List"
users-add = "Add User"
users-mail = "Email"
users-actions = "Actions"
users-edit = "Edit User"
users-delete = "Delete User"
users-bad-entries = "The entered information does not allow creating a user account"
users-added = "The user has been created"
users-edited = "The user has been modified"
users-deleted = "The user has been deleted"
users-already-exists = "The email address is already in use"
users-credentials-issue = "Issue with the entered information"

; Lost Password
users-password-change = Password Change
users-ask-change-password = "Enter the administrator email and validate. If it's correct, you will receive a new password that needs to be confirmed immediately via the validation link."
users-admin-email = Administrator Email
users-lost-password = "Forgot Password"
users-lost-password-step = "A password has been sent by email. Here are the steps to validate its change:"
users-lost-password-step1 = "Open the received email, and copy the password it contains"
users-lost-password-step2 = "Click on the validation link. It will automatically expire in 3 hours"
users-lost-password-step3 = "Log in with your email address and the new password"
users-lost-password-step4 = "You can change the password in the configuration section"
users-lost-password-success = "Your password has been successfully changed. You can now log in."
users-lost-password-mail-sent = "Email sent"
users-lost-password-mail-not-sent = "Email not sent. Please contact the site administrator"
users-lost-bad-token-link = "The link appears to be no longer valid"

users-lost-password-subject = "Password Change Request for %s Site"
users-lost-password-content = "A password change request has been made for your email address.

If you did not make this request, please ignore the step below and delete this email.
If you did make this request, please confirm the password change by clicking the link below:
Your new password: %s 
Confirmation link: %s"

```

### Fichier : fr.ini
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\langs\fr.ini

**Contenu :**
```
; General
users.name = Utilisateurs
users.description = "Gestion des utilisateurs"

; Public
users.now-connected = "Vous êtes maintenant connecté"
users.now-disconnected = "Vous êtes maintenant déconnecté"
users.bad-credentials = "Les informations entrées ne permettent pas de se connecter"
users.bad-mail = "L'adresse mail entrée ne correspond à aucun utilisateur"
users.remember = Rester connecté

; Admin
users-list = "Liste des utilisateurs"
users-add = "Ajouter un utilisateur"
users-mail = "Adresse mail"
users-actions = "Actions"
users-edit = "Editer l'utilisateur"
users-delete = "Supprimer l'utilisateur"
users-bad-entries = "Les informations entrées ne permettent pas de créer un compte utilisateur"
users-added = "L'utilisateur a été créé"
users-edited = "L'utilisateur a été modifié"
users-deleted = "L'utilisateur a été supprimé"
users-already-exists = "L'adresse email est déjà utilisée"
users-credentials-issue = "Erreur avec les informations entrées"
users-register = "Inscription"
users-registered = "Votre compte a été créé avec succès."
users-bad-entries = "Les informations fournies sont incorrectes."
users-already-exists = "Un compte avec cet email existe déjà."


; Mot de passe perdu
users-password-change = Changement de mot de passe
users-ask-change-password = "Entrez l'email administrateur et validez. Si celui-ci est correct, vous recevrez un nouveau mot de passe qu'il faudra confirmer immédiatement via le lien de validation."
users-admin-email = Email administrateur
users-lost-password = "Mot de passe oublié"
users-lost-password-step = "Un mot de passe vient d'être envoyé par email, voici les étapes permettant de valider son changement :"
users-lost-password-step1 = "Ouvrez l'email reçu, et copiez le mot de passe qu'il contient"
users-lost-password-step2 = "Cliquez sur le lien de validation. Celui-ci expirera automatiquement dans 3 heures"
users-lost-password-step3 = "Connectez-vous avec votre adresse mail et le nouveau mot de passe"
users-lost-password-step4 = "Vous pourrez changer le mot de passe dans la section configuration"
users-lost-password-success = "Votre mot de passe a bien été modifié. Vous pouvez maintenant vous connecter."
users-lost-password-mail-sent = "Email envoyé"
users-lost-password-mail-not-sent = "Email non envoyé. Veuillez contacter l'administrateur du site"
users-lost-bad-token-link = "Le lien ne semble plus valide"

users-lost-password-subject = "Demande de changement de mot de passe pour le site %s"
users-lost-password-content = "Une demande de changement de mot de passe a été faite pour votre adresse email.

Si vous n'êtes pas l'auteur de cette demande, veuillez ignorer l'étape ci-dessous et supprimer cet email.
Si vous êtes l'auteur de cette demande, veuillez confirmer le changement de mot de passe en cliquant sur le lien ci-dessous :
Votre nouveau mot de passe : %s 
Lien de confirmation : %s"

```

### Fichier : ru.ini
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\langs\ru.ini

**Contenu :**
```
; Основное
users.name = Пользователи
users.description = "Управление пользователями"

; Публичное
users.now-connected = "Теперь вы подключены"
users.now-disconnected = "Теперь вы отключены"
users.bad-credentials = "Введенная информация не позволяет войти в систему"
users.bad-mail = "Введенный адрес электронной почты не совпадает ни с одним пользователем"
users.remember = Запомнить меня

; Администратор
users-list = "Список пользователей"
users-add = "Добавить пользователя"
users-mail = "Электронная почта"
users-actions = "Действия"
users-edit = "Редактировать пользователя"
users-delete = "Удалить пользователя"
users-bad-entries = "Введенная информация не позволяет создать учетную запись пользователя"
users-added = "Пользователь был создан"
users-edited = "Пользователь был отредактирован"
users-deleted = "Пользователь был удален"
users-already-exists = "Этот адрес электронной почты уже используется"
users-credentials-issue = "Проблема с введенной информацией"

; Забытый пароль
users-password-change = Смена пароля
users-ask-change-password = "Введите адрес электронной почты администратора и подтвердите пароль. Если все верно, вы получите новый пароль, который необходимо сразу же подтвердить по ссылке для проверки."
users-admin-email = Электронная почта администратора
users-lost-password = "Забыл(-а) пароль"
users-lost-password-step = "Пароль был отправлен по электронной почте. Ниже описаны шаги для подтверждения его изменения:"
users-lost-password-step1 = "Откройте полученное письмо и скопируйте содержащийся в нем пароль"
users-lost-password-step2 = "Нажмите на ссылку для подтверждения. Срок действия автоматически истечет через 3 часа"
users-lost-password-step3 = "Войдите в систему, используя свой адрес электронной почты и новый пароль"
users-lost-password-step4 = "Вы можете изменить пароль в разделе конфигурации"
users-lost-password-success = "Ваш пароль был успешно изменен. Теперь вы можете войти в систему."
users-lost-password-mail-sent = "Электронное письмо отправлено"
users-lost-password-mail-not-sent = "Электронное письмо не отправлено. Пожалуйста, свяжитесь с администратором сайта"
users-lost-bad-token-link = "Ссылка, похоже, больше не действует"

users-lost-password-subject = "Запрос на изменение пароля для сайта %s"
users-lost-password-content = "Для вашего адреса электронной почты был сделан запрос на изменение пароля.

Если вы не делали этого запроса, пожалуйста, проигнорируйте приведенный ниже шаг и удалите это письмо.
Если запрос был сделан, подтвердите смену пароля, нажав на ссылку ниже:
Ваш новый пароль: %s 
Ссылка для подтверждения: %s"

```

### Fichier : config.json
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\param\config.json

**Contenu :**
```
{
    "priority" : "2",
    "protected" : "1"
}
```

### Fichier : hooks.json
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\param\hooks.json

**Contenu :**
```
{
    
}
```

### Fichier : infos.json
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\param\infos.json

**Contenu :**
```
{
    "name" : "Users",
    "icon" : "fa-solid fa-users",
    "description" : "Gestion des utilisateurs",
    "authorEmail" : "mx.koder@gmail.com",
    "authorWebsite" : "https://kodercloud.ovh",
    "version" : "2.0",
    "homeAdminMethod" : "UsersAdminController#home"
}
```

### Fichier : routes.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\param\routes.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

$router = router::getInstance();

$router->map('GET', '/users/login[/?]', 'UsersLoginController#login', 'login');
$router->map('POST', '/users/login-send[/?]', 'UsersLoginController#loginSend', 'login-send');
$router->map('GET', '/users/logout[/?]', 'UsersLoginController#logout', 'logout');
$router->map('GET', '/users/lost-password[/?]', 'UsersLoginController#lostPassword', 'lost-password');
$router->map('POST', '/users/lost-password-send[/?]', 'UsersLoginController#lostPasswordSend', 'lost-password-send');
$router->map('GET', '/users/lost-password/confirm/[a:token][/?]', 'UsersLoginController#lostPasswordConfirm', 'lost-password-confirm');

$router->map('GET', '/admin/users[/?]', 'UsersAdminController#home', 'users-admin-home');
$router->map('GET', '/admin/users/add', 'UsersAdminManagementController#addUser', 'users-add');
$router->map('POST', '/admin/users/add/send', 'UsersAdminManagementController#addUserSend', 'users-add-send');
$router->map('GET', '/admin/users/edit/[i:id]', 'UsersAdminManagementController#edit', 'users-edit');
$router->map('POST', '/admin/users/edit/send', 'UsersAdminManagementController#editUserSend', 'users-edit-send');
$router->map('GET', '/admin/users/delete/[i:id]/[a:token]', 'UsersAdminManagementController#delete', 'users-delete');
$router->map('GET', '/register[/?]', function () {
    echo "Route accessible.";
    exit;
}, 'register');
$router->map('POST', '/register-send[/?]', 'UsersLoginController#registerSend', 'register-send');

```

### Fichier : login.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\login.tpl

**Contenu :**
```
<!doctype html>
<html lang="{{ lang.getLocale}}">
	<head>
		{% HOOK.frontHead %}
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="robots" content="noindex"><meta name="googlebot" content="noindex">
		<title>299ko - {{ Lang.core-connection }}</title>
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5"/>
		<meta name="description" content="{{ SHOW.metaDescriptionTag }}"/>
		<link rel="icon" href="{{ SHOW.themeIcon }}"/>
		{{ SHOW.linkTags }}
		{{ SHOW.scriptTags }}
		{{ SHOW.showMetas }}
		{% HOOK.endFrontHead %}
	</head>
	<body class="login">
		<div id="alert-msg">
			{{ SHOW.displayMsg }}
		</div>
		<div id="login" class="card">
			<header>
				<div>
					<h2>{{ Lang.core-connection }}</h2>
				</div>
			</header>
			<form method="post" action="{{ loginLink}}">
				<p>
					<label for="adminEmail">{{Lang.email}}</label><br>
					<input style="display:none;" type="text" name="_email" value="" autocomplete="off"/>
					<input type="email" id="adminEmail" name="adminEmail" required>
				</p>
                <p>
                    <label for="adminPwd">{{Lang.password}}</label>
                    <input type="password" id="adminPwd" name="adminPwd" required></p>
                <p>
                    <input type="checkbox" name="remember" id="remember"/>
                    <label for="remember">{{ Lang.users.remember}}</label>
                </p>
                <p>
                    <a class="button alert" href='{{CORE.getConfigVal("siteUrl")}}'>{{Lang.quit}}</a>
                    <input type="submit" class="button" value="{{Lang.validate}}"/>
                </p>

				<p>
					<a href="{{lostLink}}">{{Lang.lost-password}}</a>
				</p>
				<p class="just_using">
					<a target="_blank" href="https://github.com/299ko/">{{Lang.site-just-using( )}}</a>
				</p>
			</form>
		</div>
	</body>
</html>

```

### Fichier : lostpwd-step2.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\lostpwd-step2.tpl

**Contenu :**
```
<p>{{Lang.users-lost-password-step}}</p>
<ul>
    <li>{{ Lang.users-lost-password-step1}}</li>
    <li>{{ Lang.users-lost-password-step2}}</li>
    <li>{{ Lang.users-lost-password-step3}}</li>
    <li>{{ Lang.users-lost-password-step4}}</li>
</ul>
```

### Fichier : lostpwd.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\lostpwd.tpl

**Contenu :**
```
<!doctype html>
<html lang="{{ lang.getLocale}}">
	<head>
		{% HOOK.frontHead %}
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="robots" content="noindex"><meta name="googlebot" content="noindex">
		<title>299ko - {{ Lang.users-lost-password }}</title>
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5"/>
		<meta name="description" content="{{ SHOW.metaDescriptionTag }}"/>
		<link rel="icon" href="{{ SHOW.themeIcon }}"/>
		{{ SHOW.linkTags }}
		{{ SHOW.scriptTags }}
		{{ SHOW.showMetas }}
		{% HOOK.endFrontHead %}
	</head>
	<body class="login">
		<div id="alert-msg">
			{{ SHOW.displayMsg }}
		</div>
		<div id="login" class="card">
			<header>
				<div>
					<h2>{{ Lang.users-lost-password }}</h2>
				</div>
			</header>
			<form method="post" action="{{lostPwdLink}}">
				<p>
					<label for="email">{{Lang.email}}</label><br>
					<input style="display:none;" type="text" name="_email" value="" autocomplete="off"/>
					<input type="email" id="email" name="email" required>
				</p>
                <p>
                    <a class="button alert" href='{{CORE.getConfigVal("siteUrl")}}'>{{Lang.cancel}}</a>
                    <input type="submit" class="button" value="{{Lang.validate}}"/>
                </p>
				<p class="just_using">
					<a target="_blank" href="https://github.com/299ko/">{{Lang.site-just-using( )}}</a>
				</p>
			</form>
		</div>
	</body>
</html>

```

### Fichier : public.css
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\public.css

**Contenu :**
```
body.login {
    background: rgb(63,160,155);
    background: radial-gradient(circle, rgba(63,160,155,1) 0%, rgba(43,39,58,1) 100%);
    margin: auto;
    padding: 60px;
}

#login {
    width: 100%;
    max-width: 400px;
    margin: 0px auto;
}

#login .just_using a {
    color: #fff;
}

@media only screen and (max-width: 960px) {
    body.login {
        padding: 30px 0;
    }
}
```

### Fichier : register.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\register.tpl

**Contenu :**
```
<!doctype html>
<html lang="{{ lang.getLocale }}">
<head>
    {% HOOK.frontHead %}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="robots" content="noindex"><meta name="googlebot" content="noindex">
    <title>{{ Lang.users-register }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5" />
    <link rel="icon" href="{{ SHOW.themeIcon }}" />
    {{ SHOW.linkTags }}
    {{ SHOW.scriptTags }}
    {{ SHOW.showMetas }}
    {% HOOK.endFrontHead %}
</head>
<body class="login">
    <div id="alert-msg">
        {{ SHOW.displayMsg }}
    </div>
    <div id="register" class="card">
        <header>
            <div>
                <h2>{{ Lang.users-register }}</h2>
            </div>
        </header>
        <form method="post" action="{{ registerLink }}">
            <p>
                <label for="email">{{ Lang.email }}</label><br>
                <input style="display:none;" type="text" name="_email" value="" autocomplete="off" />
                <input type="email" id="email" name="email" required />
            </p>
            <p>
                <label for="password">{{ Lang.password }}</label>
                <input type="password" id="password" name="password" required />
            </p>
            <p>
                <a class="button alert" href="{{CORE.getConfigVal("siteUrl")}}">{{ Lang.cancel }}</a>
                <input type="submit" class="button" value="{{ Lang.validate }}" />
            </p>
            <p class="just_using">
                <a target="_blank" href="https://github.com/299ko/">{{ Lang.site-just-using() }}</a>
            </p>
        </form>
    </div>
</body>
</html>

```

### Fichier : usersadd.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\usersadd.tpl

**Contenu :**
```
<section>
	<header>{{Lang.users-add}}</header>
	<form method="POST" action="{{link}}">
		{{SHOW.tokenField}}
		<label for="email">{{ Lang.users-mail}}</label>
		<input type="email" id="email" name="email" required />
		<label for="pwd">{{Lang.password}}</label>
		<input type="text" id="pwd" name="pwd" required />
		<button>{{Lang.submit}}</button>
	</form>
</section>

```

### Fichier : usersedit.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\usersedit.tpl

**Contenu :**
```
<section>
	<header>{{Lang.users-edit}}</header>
	<form method="POST" action="{{link}}">
		{{SHOW.tokenField}}
		<input type="hidden" name="id" value="{{user.id}}" />
		<label for="email">{{ Lang.users-mail}}</label>
		<input type="email" id="email" name="email" value="{{user.email}}" required />
		<label for="pwd">{{Lang.password}}</label>
		<label for="role">{{Lang.users-role}}</label>
<select id="role" name="role">
    <option value="admin" {{ user.role == 'admin' ? 'selected' : '' }}>Admin</option>
    <option value="modo" {{ user.role == 'modo' ? 'selected' : '' }}>Modo</option>
    <option value="editor" {{ user.role == 'editor' ? 'selected' : '' }}>Rédacteur</option>
    <option value="member" {{ user.role == 'member' ? 'selected' : '' }}>Membre</option>
</select>
		<input type="text" id="pwd" name="pwd" />
		<button>{{Lang.submit}}</button>
	</form>
</section>

```

### Fichier : userslist.tpl
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\template\userslist.tpl

**Contenu :**
```
<section>
	<header>{{Lang.users-list}}</header>
	<a class='button' href='{{ROUTER.generate("users-add")}}'>
		<i class="fa-solid fa-user-plus"></i>
		{{Lang.users-add}}</a>
	<table>
		<thead>
			<tr>
				<th>{{Lang.users-mail}}</th>
				<th>{{Lang.users-actions}}</th>
				<th>{{Lang.users-role}}</th>
			</tr>
		</thead>
		{% FOR user IN users %}
			<tr>
				<td>{{user.email}}</td>
				<td>
					<div role="group">
						<a class="button small" title="{{Lang.users-edit}}" href='{{ ROUTER.generate("users-edit", ["id" => user.id]) }}'>
							<i class="fa-solid fa-user-pen"></i>
						</a>
						<a class="button small alert" title="{{Lang.users-delete}}" href='{{ user.deleteLink }}' onclick="if (!confirm('{{Lang.confirm.deleteItem}}')) return false;">
							<i class="fa-solid fa-user-xmark"></i>
						</a>
					</div>
				</td>
				<td>{{user.role}}</td>
			</tr>
		{% ENDFOR %}
	</table>
</section>

```

### Fichier : users.php
**Chemin :** C:\Users\maxim\OneDrive\Bureau\DevFlux\devInstall\flexCB\refactnomod\299\plugin\users\users.php

**Contenu :**
```
<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

require_once PLUGINS . 'users/entities/User.php';
require_once PLUGINS . 'users/entities/UsersManager.php';
require_once PLUGINS . 'users/entities/PasswordRecovery.php';

```

