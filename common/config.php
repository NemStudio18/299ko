<?php

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

define("VERSION" ,'2.0.0');
define("COMMON", ROOT . 'common/');
define("DATA", ROOT . 'data/');
define("DATA_PLUGIN", ROOT . 'data/plugin/');
define("UPLOAD", ROOT . 'data/upload/');
define("PLUGINS", ROOT . 'Plugins/');
define("THEMES", ROOT . 'theme/');
define("ADMIN_PATH", ROOT . 'admin/');
define("FONTICON", 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
define("FANCYCSS", 'https://cdnjs.cloudflare.com/ajax/libs/fancyapps-ui/4.0.31/fancybox.min.css');
define("FANCYJS", 'https://cdnjs.cloudflare.com/ajax/libs/fancyapps-ui/4.0.31/fancybox.umd.min.js');
define("CAT_TPL", 'Common/Categories/template/');

$filename = DATA . 'key.php';
if (file_exists($filename)) {
    include $filename;
}