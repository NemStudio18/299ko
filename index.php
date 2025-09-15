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
ob_start();
const ROOT = '.' . DIRECTORY_SEPARATOR;
const DS = DIRECTORY_SEPARATOR;

include_once(ROOT . 'common' . DS . 'common.php');

if (!$core->isInstalled()) {
    header('location:' . ROOT . 'install.php');
    die();
}

// Fonctions pour vérifier l'état de connexion en temps réel
function IS_LOGGED(): bool {
    return UsersManager::isLogged();
}

function IS_ADMIN(): bool {
    return IS_LOGGED();
}

Template::addGlobal('IS_LOGGED', IS_LOGGED());
Template::addGlobal('IS_ADMIN', IS_ADMIN());

$core->executeCallback($router, $runPlugin);