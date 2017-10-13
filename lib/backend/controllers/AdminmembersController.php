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

class AdminmembersController extends Sceleton {

    public $acl = ['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_MEMBERS'];

    public function actionIndex() {

        global $languages_id, $language;

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="create_item" onclick="return editAdmin(0)">' . IMAGE_INSERT . '</a>';
        $this->view->adminTable = array(
            array(
                'title' => TABLE_HEADING_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_EMAIL,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_GROUPS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_LOGNUM,
                'not_important' => 1
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int) $_GET['row'];

        return $this->render('index');
    }

    public function actionMemberlist() {

        \common\helpers\Translation::init('admin/adminmembers');

        $draw = \Yii::$app->request->get('draw');
        $start = \Yii::$app->request->get('start');
        $length = \Yii::$app->request->get('length');

        $search = '';
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where (a.admin_firstname like '%" . $keywords . "%' or a.admin_lastname like '%" . $keywords . "%' or a.admin_email_address like '%" . $keywords . "%')";
        } else {
            $search_condition = " where 1 ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "a.admin_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "a.admin_email_address " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "al.access_levels_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "a.admin_lognum " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "a.admin_lastname, a.admin_firstname";
                    break;
            }
        } else {
            $orderBy = "a.admin_firstname, a.admin_lastname";
        }

        $db_admin_query_raw = "select a.*, al.access_levels_name
                            from " . TABLE_ADMIN . " a
                            left join " . TABLE_ACCESS_LEVELS . " al ON a.access_levels_id = al.access_levels_id
                            $search_condition
                            order by $orderBy";
        $current_page_number = ($start / $length) + 1;

        $db_admin_split = new \splitPageResults($current_page_number, $length, $db_admin_query_raw, $db_admin_query_numrows, 'a.admin_id');

        $db_admin_query = tep_db_query($db_admin_query_raw);

        $recordsTotal = $recordsFiltered = 0;
        $responseList = array();
        while ($admin = tep_db_fetch_array($db_admin_query)) {
            $disabledAdmin = '';
            if ($admin['login_failture'] > 2) {
                $disabledAdmin = 'dis_module';
            }
            $responseList[] = array(
                '<div class="' . $disabledAdmin . '">' . $admin['admin_firstname'] . " " . $admin['admin_lastname'] . '<input class="cell_identify" type="hidden" value="' . $admin['admin_id'] . '">' . '</div>',
                '<div class="' . $disabledAdmin . '">' . $admin['admin_email_address'] . '</div>',
                '<div class="' . $disabledAdmin . '">' . $admin['access_levels_name'] . (empty($admin['admin_persmissions']) ? '' : ' (' . TEXT_MANUALLY_UPDATED . ')') . '</div>',
                '<div class="' . $disabledAdmin . '">' . $admin['admin_lognum'] . '</div>',
            );
        }

        $_response = array(
            'draw' => $draw,
            'recordsTotal' => $db_admin_query_numrows,
            'recordsFiltered' => $db_admin_query_numrows,
            'data' => $responseList
        );
        echo json_encode($_response, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    function actionAdminmembersactions() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/adminmembers');

        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        $query = tep_db_query("
          select distinct(a.admin_id), a.admin_groups_id, a.admin_firstname, a.admin_lastname,
          a.admin_email_address, a.admin_password, a.admin_created, a.admin_modified, a.admin_logdate,
          a.admin_lognum, a.login_failture, a.login_failture_date, a.login_failture_ip, a.individual_id,
          al.access_levels_name
          from " . TABLE_ADMIN . " a
          left join " . TABLE_ACCESS_LEVELS . " al ON a.access_levels_id = al.access_levels_id
          where a.admin_id = '" . (int) $admin_id . "'");

        $admins = tep_db_fetch_array($query);

        if (!is_array($admins)) {
            die("Wrong data.");
        }

        $mInfo = new \objectInfo($admins);

        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or"><div>' . TEXT_INFO_FULLNAME . '</div><div>' . $mInfo->admin_firstname . ' ' . $mInfo->admin_latname . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_EMAIL . '</div><div>' . $mInfo->admin_email_address . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_GROUP . '</div><div>' . $mInfo->access_levels_name . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_CREATED . '</div><div>' . \common\helpers\Date::date_short($mInfo->admin_created) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($mInfo->admin_modified) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_LOGDATE . '</div><div>' . \common\helpers\Date::date_short($mInfo->admin_logdate) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_LOGNUM . '</div><div>' . $mInfo->admin_lognum . '</div></div>';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<button class="btn btn-edit btn-no-margin" onclick="editAdmin(' . $mInfo->admin_id . ')">' . IMAGE_EDIT . '</button>' . (!tep_session_is_registered('login_affiliate') ? '<button onclick="confirmDeleteAdmin(' . $mInfo->admin_id . ')" class="btn btn-delete">' . IMAGE_DELETE . '</button>' : '') . '<a class="hidden btn" href="' . tep_href_link(FILENAME_ORDERS, 'mID=' . $mInfo->admin_id) . '">' . IMAGE_ORDERS . '</a><a class="hidden btn btn-primary" href="' . tep_href_link(FILENAME_MAIL, 'customer=' . $mInfo->customers_email_address) . '">' . IMAGE_EMAIL . '</a>';
        echo '<a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/override-permissions', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_OVERRIDE_PERMISSIONS . '</a>';
        if ($mInfo->login_failture > 2) {
            if (\common\helpers\Acl::rule(['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_MEMBERS', 'TEXT_ENABLE_USER'])) {
                echo '<button class="btn btn-primary btn-process-order" onclick="enableUser(' . $mInfo->admin_id . ')">' . TEXT_ENABLE_USER . '</button>';
            }
            if (!empty($mInfo->login_failture_date)) {
                echo '<div class="row_or"><div>DATE:</div><div>' . \common\helpers\Date::date_short($mInfo->login_failture_date) . '</div></div>';
            }
            if (!empty($mInfo->login_failture_ip)) {
                echo '<div class="row_or"><div>IP:</div><div>' . $mInfo->login_failture_ip . '</div></div>';
            }
        } else {
            if (\common\helpers\Acl::rule(['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_MEMBERS', 'TEXT_DISABLE_USER'])) {
                echo '<button class="btn btn-primary btn-process-order" onclick="disableUser(' . $mInfo->admin_id . ')">' . TEXT_DISABLE_USER . '</button>';
            }
        }
        echo '</div>';
    }

    function actionAdminedit() {

        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/adminmembers');

        $this->layout = false;
        $error = $entry_firstname_error = $entry_lastname_error = $entry_admin_email_address_error = false;
        $entry_admin_groups_name_error = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = $admin_id; ");
        if ($admin = tep_db_fetch_array($query))
            $mInfo = new \objectInfo($admin);

        $access_array = [];
        $access_query = tep_db_query("select * from " . TABLE_ACCESS_LEVELS . " order by access_levels_id ");
        while ($access = tep_db_fetch_array($access_query)) {
            $access_array[] = [
                'id' => $access['access_levels_id'],
                'text' => $access['access_levels_name'],
            ];
        }
        $access_array[] = array(
            array('id' => 0, 'text' => 'none')
        );
        ?>

        <?php
        echo tep_draw_form('admin', 'adminmembers', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="admin_edit" onSubmit="return check_form();"') .
        tep_draw_hidden_field('default_address_id', $mInfo->admin_email_address);
        echo '<div class="or_box_head">' . CATEGORY_PERSONAL . '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . ENTRY_FIRST_NAME . '</div>';
        echo '<div class="main_value">' . tep_draw_input_field('admin_firstname', $mInfo->admin_firstname, 'maxlength="32" class="form-control"', true) . '</div>';
        echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . ENTRY_LAST_NAME . '</div>';
        echo '<div class="main_value">' . tep_draw_input_field('admin_lastname', $mInfo->admin_lastname, 'maxlength="32" class="form-control"', false) . '</div>';
        echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . ENTRY_EMAIL_ADDRESS . '</div>';
        echo '<div class="main_value">' . tep_draw_input_field('admin_email_address', $mInfo->admin_email_address, 'maxlength="100" class="form-control"', true) . '</div>';
        echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . TEXT_INFO_GROUP . '</div>';
        echo '<div class="main_value">' . tep_draw_pull_down_menu('access_levels_name', $access_array, ( is_object($mInfo) ) ? $mInfo->access_levels_id : 0, 'class="form-control"', false) . '</div>';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        if ($admin_id > 0) {
            if (!tep_session_is_registered("login_affiliate")) {
                echo '<input type="submit" class="btn btn-no-margin" value="' . IMAGE_UPDATE . '" >';
            }
            echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
            ?>
        <?php } else { ?>
            <?php
            echo '<input type="submit" class="btn btn-no-margin" value="' . IMAGE_INSERT . '" >';
            echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
            ?>
            <?php
        }
        echo '</div>';
        ?>
        <?php
        echo tep_draw_hidden_field('admin_id', $mInfo->admin_id);
        ?>
        </form>
        <?php
    }

    function actionConfirmadmindelete() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/adminmembers');
        \common\helpers\Translation::init('admin/faqdesk');

        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = $admin_id; ");

        if ($admin = tep_db_fetch_array($query))
            $mInfo = new \objectInfo($admin);
        else
            die("Wrong admin data.");

        echo tep_draw_form('admin', FILENAME_ADMIN_ACCOUNT, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="admin_edit" onSubmit="return deleteAdmin();"');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_ITEM . '</div>';
        echo '<div class="col_desc">' . TEXT_DELETE_ITEM_INTRO . ' ' . $mInfo->admin_firstname . ' ' . $mInfo->admin_lastname . '</div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <button class="btn btn-delete btn-no-margin"><?php echo IMAGE_DELETE; ?></button><?php echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
        echo tep_draw_hidden_field('admin_id', $mInfo->admin_id);
        ?>
        </div>
        </form>
            <?php
        }

        function actionAdmindelete() {
            $this->layout = false;

            $admin_id = \Yii::$app->request->post('admin_id');

            tep_db_query("delete from " . TABLE_ADMIN . " where admin_id = '" . (int) $admin_id . "'");
        }

        private function randomize() {
            $salt = "abchefghjkmnpqrstuvwxyz0123456789";
            srand((double) microtime() * 1000000);
            $i = 0;
            while ($i <= 7) {
                $num = rand() % 33;
                $tmp = substr($salt, $num, 1);
                $pass = $pass . $tmp;
                $i++;
            }
            return $pass;
        }

        function actionAdminsubmit() {
            global $languages_id, $language;

            \common\helpers\Translation::init('admin/adminmembers');

            $this->layout = FALSE;
            $error = FALSE;
            $message = '';

            $messageType = 'success';

            $admin_id = \Yii::$app->request->post('admin_id');

            $admin_firstname = tep_db_prepare_input($_POST['admin_firstname']);
            $admin_lastname = tep_db_prepare_input($_POST['admin_lastname']);
            $admin_email_address = tep_db_prepare_input($_POST['admin_email_address']);
            $admin_group_level = tep_db_prepare_input($_POST['access_levels_name']);

            $sql_data_array = array(
                'admin_id' => $admin_id,
                'admin_firstname' => $admin_firstname,
                'admin_lastname' => $admin_lastname,
                'admin_email_address' => $admin_email_address,
                'access_levels_id' => $admin_group_level,
            );

            if (strlen($admin_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                $error = TRUE;
                $message .= 'Firstname: ' . sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH) . '<br/>';
            }
            if (trim($admin_email_address) == '') {
                $error = TRUE;
                $message .= ENTRY_EMAIL_ADDRESS_CHECK_ERROR . '<br/>';
            }

            $stored_email[] = 'NONE';
            $check_email_query = tep_db_query("select admin_email_address from " . TABLE_ADMIN . " where admin_id <> " . $admin_id . "");
            while ($check_email = tep_db_fetch_array($check_email_query)) {
                $stored_email[] = $check_email['admin_email_address'];
            }

            if (in_array($admin_email_address, $stored_email)) {
                $error = true;
                $message = 'Email already in use';
            }

            if ($error === false) {
                if ((int) $admin_id > 0) {
                    tep_db_perform(TABLE_ADMIN, $sql_data_array, 'update', "admin_id = '" . (int) $admin_id . "'");
                    tep_db_query("update " . TABLE_ADMIN . " set admin_modified = now() where admin_id = '" . (int) $admin_id . "'");

                    $message = SUCCESS_ADMIN_UPDATED;
                } else {
                    $makePassword = $this->randomize();
                    $sql_data_array['admin_password'] = \common\helpers\Password::encrypt_password($makePassword);

                    tep_db_perform(TABLE_ADMIN, $sql_data_array);
                    $admin_id = tep_db_insert_id();
                    $_GET['mID'] = $admin_id; // FIXME: Why do we need this?
                    tep_db_query("update " . TABLE_ADMIN . " set admin_created = now(), admin_modified = now() where admin_id = '" . (int) $admin_id . "'");

                    $message = SUCCESS_ADMIN_CREATED;

                    \common\helpers\Mail::send($sql_data_array['admin_firstname'] . ' ' . $sql_data_array['admin_lastname'], $sql_data_array['admin_email_address'], ADMIN_EMAIL_SUBJECT, sprintf(ADMIN_EMAIL_TEXT, $sql_data_array['admin_firstname'], \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN), $sql_data_array['admin_email_address'], $makePassword, STORE_OWNER), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                }
            }

            if ($error === true) {
                $messageType = 'warning';
                if ($message == '')
                    $message = WARN_UNKNOWN_ERROR;
            }
            ?>
        <div class="alert alert-<?= $messageType ?> fade in">
            <i data-dismiss="alert" class="icon-remove close"></i>
        <?= $message ?>
        </div>
        <?php
        $check_admin_id = \Yii::$app->request->post('admin_id');
        if ($check_admin_id > 0) {
            $this->actionAdminmembersactions();
        }
    }

    public function actionOverridePermissions() {
        global $languages_id, $language;
        \common\helpers\Translation::init('admin/adminmembers');

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $admin_id = (int) \Yii::$app->request->get('admin_id');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
        $admin = tep_db_fetch_array($query);

        if (!is_array($admin)) {
            die("Wrong data.");
        }

        $adminPersmissions = explode(",", $admin['admin_persmissions']);

        $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int) $admin['access_levels_id'] . "'");
        $access = tep_db_fetch_array($checkAccess);
        $selectedIds = explode(",", $access['access_levels_persmissions']);

        $aclTree = \common\helpers\Acl::buildOverrideTree($selectedIds, $adminPersmissions);

        return $this->render('override-permissions', [
                    'aclTree' => $aclTree,
                    'admin_id' => $admin_id,
        ]);
    }

    public function actionRecalcAcl() {
        $this->layout = false;

        $admin_id = (int) \Yii::$app->request->post('admin_id');
        $persmissions = \Yii::$app->request->post('persmissions');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
        $admin = tep_db_fetch_array($query);

        if (!is_array($admin)) {
            die("Wrong data.");
        }

        $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int) $admin['access_levels_id'] . "'");
        $access = tep_db_fetch_array($checkAccess);
        $selectedIds = explode(",", $access['access_levels_persmissions']);

        $adminPersmissions = [];
        foreach ($persmissions as $persmission) {
            if (!in_array($persmission, $selectedIds)) {
                $adminPersmissions[] = $persmission; //green - added
            }
        }
        foreach ($selectedIds as $selected) {
            if (!in_array($selected, $persmissions)) {
                $adminPersmissions[] = ($selected * -1); //red - removed
            }
        }

        $aclTree = \common\helpers\Acl::buildOverrideTree($selectedIds, $adminPersmissions);

        return $this->render('recalc-acl', [
                    'aclTree' => $aclTree,
        ]);
    }

    public function actionSubmitPermissions() {

        $admin_id = (int) \Yii::$app->request->post('admin_id');
        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
        $admin = tep_db_fetch_array($query);

        if (!is_array($admin)) {
            die("Wrong data.");
        }

        $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int) $admin['access_levels_id'] . "'");
        $access = tep_db_fetch_array($checkAccess);
        $selectedIds = explode(",", $access['access_levels_persmissions']);

        $persmissions = \Yii::$app->request->post('persmissions');
        if (!is_array($persmissions)) {
            $persmissions = [];
        }

        $adminPersmissions = [];
        foreach ($persmissions as $persmission) {
            if (!in_array($persmission, $selectedIds)) {
                $adminPersmissions[] = $persmission; //green - added
            }
        }
        foreach ($selectedIds as $selected) {
            if (!in_array($selected, $persmissions)) {
                $adminPersmissions[] = ($selected * -1); //red - removed
            }
        }

        $admin_persmissions = implode(",", $adminPersmissions);

        $sql_data_array = [
            'admin_persmissions' => $admin_persmissions,
        ];
        tep_db_perform(TABLE_ADMIN, $sql_data_array, 'update', "admin_id = '" . $admin_id . "'");

        echo '<script> window.location.replace("' . \Yii::$app->urlManager->createUrl(['adminmembers/override-permissions', 'admin_id' => $admin_id]) . '");</script>';
    }

    function actionEnableAdmin() {
        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        tep_db_query("update " . TABLE_ADMIN . " set login_failture = 0 where admin_id = '" . (int) $admin_id . "'");
    }

    function actionDisableAdmin() {
        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        tep_db_query("update " . TABLE_ADMIN . " set login_failture = 3 where admin_id = '" . (int) $admin_id . "'");
    }

}
