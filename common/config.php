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

const VERSION = '2.1.0b';
const COMMON = ROOT . 'common' . DS;
const DATA = ROOT . 'data' . DS;
const UPLOAD = ROOT . 'data' . DS . 'upload' . DS;
const DATA_PLUGIN = ROOT . 'data' . DS . 'plugin' . DS;
const CACHE = ROOT . 'data' . DS . 'cache' . DS;
const THEMES = ROOT . 'theme' . DS;
const PLUGINS = ROOT . 'plugin' . DS;
const CORE_PLUGINS = ROOT . 'common' . DS . 'core-plugins' . DS;
const ADMIN_PATH = ROOT . 'admin' . DS;
const FONTICON = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css';
const FANCYCSS = 'https://cdnjs.cloudflare.com/ajax/libs/fancyapps-ui/4.0.31/fancybox.min.css';
const FANCYJS = 'https://cdnjs.cloudflare.com/ajax/libs/fancyapps-ui/4.0.31/fancybox.umd.min.js';

$filename = DATA . 'key.php';
if (file_exists($filename)) {
    include $filename;
}