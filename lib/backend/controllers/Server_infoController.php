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

class Server_infoController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_SERVER_INFO'];
    
    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('settings', 'tools', 'server_info');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('server_info/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();

		$phpinfo = str_replace('border: 1px', '', $phpinfo);
		preg_match('/<body>(.*)<\/body>/is', $phpinfo, $regs);
        return $this->render('index', array('system' => \common\helpers\System::get_system_information(), 'reg'=> $regs[1]));
    }

}
