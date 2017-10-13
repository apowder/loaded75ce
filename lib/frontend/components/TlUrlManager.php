<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace app\components;

use yii\web\UrlManager;

class TlUrlManager extends UrlManager
{

    public function init()
    {
        $x = parent::init();
        $this->addRules([
          'robots.txt' => 'index/robots-txt',
          'xmlsitemap/products<page:\d+>' => 'xmlsitemap/products',
          'xmlsitemap/images<page:\d+>' => 'xmlsitemap/images',
          'images/cached/<image:.*>' => 'image/cached',
          'api/v1/<api_action:.*>' => 'api/v1',
        ],true);
        return $x;
    }


    public function createAbsoluteUrl($params, $scheme = null)
    {
        if ($scheme == 'https' && ENABLE_SSL == true) {
            $this->setHostInfo(HTTPS_SERVER);
            $this->setBaseUrl(rtrim(DIR_WS_HTTPS_CATALOG, '/'));
        } else {
            $this->setHostInfo(HTTP_SERVER);
            $this->setBaseUrl(rtrim(DIR_WS_HTTP_CATALOG, '/'));
        }
        return parent::createAbsoluteUrl($params, $scheme = null);
    }
}