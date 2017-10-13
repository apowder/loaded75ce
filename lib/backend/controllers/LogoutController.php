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
use yii\web\Controller;

/**
 * default controller to handle user requests.
 */
class LogoutController extends Controller
{
	/**
	 * Index action is the default action in a controller.
	 */
	public function actionIndex()
	{
            //tep_session_destroy();
            tep_session_unregister('login_id');
            tep_session_unregister('login_firstname');
            tep_session_unregister('login_groups_id');
            tep_session_unregister('login_affiliate');
            tep_session_unregister('login_vendor');
            tep_redirect(tep_href_link(FILENAME_LOGIN));
	}
}
