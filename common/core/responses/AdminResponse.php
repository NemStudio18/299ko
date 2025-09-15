<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('Access denied!');

class AdminResponse extends Response {

    /**
     * Layout
     * @var Template
     */
    protected Template $layout;

    protected ?string $title = null;

    public function __construct() {
        parent::__construct();
        $this->layout = new Template(ADMIN_PATH .'layout.tpl');
    }

    /**
     * Create a new Template, from plugin
     * @param string $pluginName
     * @param string $templateName
     * @return Template
     */
    public function createPluginTemplate(string $pluginName, string $templateName):Template {
        // Determine plugin path (core-plugins first, then regular plugins)
        $pluginPath = CORE_PLUGINS . $pluginName . '/';
        if (!is_dir($pluginPath)) {
            $pluginPath = PLUGINS . $pluginName . '/';
        }
        
        $file = $pluginPath . 'template/' . $templateName . '.tpl';
        $tpl = new Template($file);
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
        $this->layout->set('CONTENT', core::getInstance()->callHook('adminContent', $content));
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