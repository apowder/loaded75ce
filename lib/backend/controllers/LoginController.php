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
 * login controller to handle user requests.
 */
class LoginController extends Controller {

    /**
     * Disable layout for the controller view
     */
    public $layout = false;
    public $errorMessage = '';
    public $enableCsrfValidation = false;

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        global $language, $languages_id, $navigation;

        \common\helpers\Translation::init('admin/main');

        $stamp = date('Y-m-d H:i:s', strtotime("-1 hour"));
        tep_db_query("update " . TABLE_ADMIN . " set login_failture = 0, login_failture_ip = '', login_failture_date = NULL where login_failture > 2 and login_failture_date IS NOT NULL and login_failture_date < '" . $stamp . "'");

// {{ From superadmin
        if ($_GET['uid'] > 0 && tep_not_null($_GET['tr'])) {
            $check_admin = tep_db_fetch_array(tep_db_query("select admin_id, admin_groups_id, admin_firstname from admin where admin_id = '" . (int) $_GET['uid'] . "' and admin_password = '" . tep_db_input(tep_db_prepare_input($_GET['tr'])) . "'"));
            if ($check_admin['admin_id'] > 0 && $_GET['uid'] == $check_admin['admin_id']) {
                $login_id = $check_admin['admin_id'];
                $login_groups_id = $check_admin['admin_groups_id'];
                $login_firstname = $check_admin['admin_firstname'];

                tep_session_register('login_id', $login_id);
                tep_session_register('login_groups_id', $login_groups_id);
                tep_session_register('login_first_name', $login_firstname);

                tep_redirect(tep_href_link(FILENAME_DEFAULT));
            }
        }
// }}
        if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
            $email_address = tep_db_prepare_input($_POST['email_address']);
            $password = tep_db_prepare_input($_POST['password']);

            // Check if email exists
            $check_admin_query = tep_db_query("select admin_id as login_id, admin_groups_id as login_groups_id, access_levels_id, admin_firstname as login_firstname, admin_email_address as login_email_address, admin_password as login_password, admin_modified as login_modified, admin_logdate as login_logdate, admin_lognum as login_lognum, languages from " . TABLE_ADMIN . " where login_failture < 3 and (admin_email_address = '" . tep_db_input($email_address) . "' or admin_username='" . tep_db_input($email_address) . "')");
            if (!tep_db_num_rows($check_admin_query)) {
                $_GET['login'] = 'fail';
            } else {
                $check_admin = tep_db_fetch_array($check_admin_query);
                // Check that password is good
                if (!\common\helpers\Password::validate_password($password, $check_admin['login_password'])) {
                    $_GET['login'] = 'fail';
                    tep_db_query("update " . TABLE_ADMIN . " set login_failture = login_failture + 1, login_failture_ip='" . tep_db_input($_SERVER['REMOTE_ADDR']) . "', login_failture_date = now() where admin_id = '" . (int) $check_admin['login_id'] . "'");
                } else {
                    if (tep_session_is_registered('password_forgotten')) {
                        tep_session_unregister('password_forgotten');
                    }

                    $login_id = $check_admin['login_id'];
                    $login_groups_id = $check_admin['login_groups_id'];
                    $login_firstname = $check_admin['login_firstname'];
                    $login_email_address = $check_admin['login_email_address'];
                    $login_logdate = $check_admin['login_logdate'];
                    $login_lognum = $check_admin['login_lognum'];
                    $login_modified = $check_admin['login_modified'];
                    $access_levels_id = $check_admin['access_levels_id'];
                    $language = $check_admin['languages'];

                    tep_session_register('login_id', $login_id);
                    tep_session_register('login_groups_id', $login_groups_id);
                    tep_session_register('login_first_name', $login_firstname);
                    tep_session_register('access_levels_id', $access_levels_id);
                    tep_session_register('language', $language);
                    $lng = new \common\classes\language();
                    $languages_id = $lng->language['id'];
                    tep_session_register('languages_id', $languages_id);
                    //$date_now = date('Ymd');
                    tep_db_query("update " . TABLE_ADMIN . " set login_failture = 0, login_failture_ip = '', admin_logdate = now(), admin_lognum = admin_lognum+1 where admin_id = '" . (int)$login_id . "'");

                    $check_languages_query = tep_db_query("select languages from " . TABLE_ADMIN . " where admin_id = '" . (int) $login_id . "'");
                    if (tep_db_num_rows($check_languages_query) > 0) {
                        $check_languages = tep_db_fetch_array($check_languages_query);
                        $lng = new \common\classes\language();
                        $lng->set_language($check_languages['languages']);
                        $language = $lng->language['directory'];
                        $languages_id = $lng->language['id'];
                    }

                    if (($login_lognum == 0) || !($login_logdate) || ($login_email_address == 'admin@localhost') || ($login_modified == '0000-00-00 00:00:00')) {
                        tep_redirect(tep_href_link(FILENAME_ADMIN_ACCOUNT));
                    } else {
                        if (sizeof($navigation->snapshot) > 0) {
                            $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
                            $navigation->clear_snapshot();
                            tep_redirect($origin_href);
                        } else {
                            tep_redirect(tep_href_link(FILENAME_DEFAULT));
                        }
                    }
                }
            }
            if ($_GET['login'] == 'fail') {
                $this->errorMessage = TEXT_LOGIN_ERROR;
            }
        }
        return $this->render('index');
    }

}
