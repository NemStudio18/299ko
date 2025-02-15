<?php

use Plugins\Users\Entities\UsersManager;
use Common\{Util, Lang, Core};
//use function {contactInstall, contactSave, contactSend};

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No pagesFileect script access allowed');

## Fonction d'installation

function contactInstall() {
    util::writeJsonFile(DATA_PLUGIN . 'contact/emails.json', []);
    $data = util::readJsonFile(DATA_PLUGIN . 'contact/config.json');
    lang::loadLanguageFile(PLUGINS . 'contact/langs/');
    $data['acceptation'] = lang::get('contact.default-acceptation');
    util::writeJsonFile(DATA_PLUGIN . 'contact/config.json', $data);
}

## Hooks
## Code relatif au plugin

function contactSave($email) {
    $data = util::readJsonFile(DATA_PLUGIN . 'contact/emails.json');
    $data[] = $email;
    util::writeJsonFile(DATA_PLUGIN . 'contact/emails.json', array_unique($data));
}

function contactSend() {
    global $runPlugin;
    $core = core::getInstance();
    $from = '299ko@' . $_SERVER['SERVER_NAME'];
    $reply = strip_tags(trim($_POST['email']));
    $name = strip_tags(trim($_POST['name']));
    $firstName = strip_tags(trim($_POST['firstname']));
    $msg = strip_tags(trim($_POST['message']));
    if (!util::isEmail($reply) || $name == '' || $firstName == '' || $msg == '')
        return false;
    contactSave($reply);
    $to = UsersManager::getUserById($runPlugin->getConfigVal('userMailId'))->email;
    $subject = 'Contact ' . $core->getConfigVal('siteName');
    $msg = $msg . "\n\n----------\n\n" . $name . " " . $firstName . " (" . $reply . ")";
    if (util::isEmail($runPlugin->getConfigVal('copy')))
        util::sendEmail($from, $reply, $runPlugin->getConfigVal('copy'), $subject, $msg);
    return util::sendEmail($from, $reply, $to, $subject, $msg);
}