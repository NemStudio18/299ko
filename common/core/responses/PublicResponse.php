<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

class PublicResponse extends Response {

    /**
     * Current theme
     * @var Theme
     */
    protected Theme $theme;

    /**
     * Layout
     * @var Template
     */
    protected Template $layout;

    protected ?string $title = null;

    public function __construct() {
        parent::__construct();
        $this->theme = new Theme(core::getInstance()->getConfigVal('theme'));
        $this->layout = new Template($this->theme->getLayout());
    }

    /**
     * Create a new Template, from plugin
     * Eg : if plugin is 'blog' & asked template is 'read', look for 'THEMES/theme/template/blog.read.tpl'
     * else create tpl with PLUGINS/blog/template/read.tpl
     * @param string $pluginName
     * @param string $templateName
     * @return Template
     */
    public function createPluginTemplate(string $pluginName, string $templateName):Template {
        $themeFile = $this->theme->getPluginTemplatePath($pluginName, $templateName) ?? '';
        if (file_exists($themeFile)) {
            $tpl = new Template($themeFile);
        } else {
            $tpl = new Template(PLUGINS . $pluginName .'/template/' . $templateName . '.tpl');
        }
        return $tpl;
    }

    /**
     * Create a new Template, from core
     * Eg : if asked template is '404', look for 'THEMES/theme/template/core.404.tpl'
     * If the template exist in the current theme, use it, else use the one from common/template
     * @param string $templateName
     * @return Template
     */
    public function createCoreTemplate(string $templateName):Template {
        $themeFile = $this->theme->getCoreTemplatePath($templateName) ?? '';
        if (file_exists($themeFile)) {
            $tpl = new Template($themeFile);
        } else {
            $tpl = new Template(COMMON . 'template/' . $templateName . '.tpl');
        }
        return $tpl;
    }

    /**
     * Return the response
     * @return string Content of all template
     */
    public function output():string
    {
        $content = '';
        foreach ($this->templates as $tpl) {
            $content .= $tpl->output();
        }
        $this->layout->set('CONTENT', $content);
        $this->layout->set('PAGE_TITLE' , $this->title ?? false);
        return $this->layout->output();
    }

    /**
     * Set the title of the admin page
     * @param string $title
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }
}