<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;
use backend\components\Information;

class Cms_pagesController extends Sceleton {

    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('cms', 'cms_pages');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('cms_pages/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        return $this->render('index');
    }

}
