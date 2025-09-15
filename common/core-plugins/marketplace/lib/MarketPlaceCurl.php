<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class MarketPlaceCurl extends Curl{

    protected string $siteID = '';

    protected string $endPoint = 'https://299ko.ovh/';

    public function __construct($url) {
        $this->endPoint = core::getInstance()->getEnv('marketplaceUrl', $this->endPoint);
        parent::__construct($this->endPoint . $url);
        $marketConfig = util::readJsonFile(DATA_PLUGIN . 'marketplace/marketplace.json');
        $this->siteID = $marketConfig['siteID'];
    }

    public function url(string $url): Curl {
        $this->url = $this->endPoint . $url;
        return $this;
    }

    public function setDatas($datas): self {
        $this->datas = $datas;
        $this->datas['siteID'] = $this->siteID;
        $this->datas['siteURL'] = core::getInstance()->getConfigVal('siteUrl');
        $this->datas['siteLang'] = core::getInstance()->getConfigVal('siteLang');
        $this->datas['siteVersion'] = VERSION;
        return $this;
    }

    public function execute(): Curl {
        if (empty($this->datas)) {
            $this->setDatas([]);
        }
        $this->addOption(CURLOPT_USERAGENT, '299ko-curl-marketplace');
        return parent::execute();

    }



}