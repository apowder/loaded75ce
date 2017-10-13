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

class GroupsController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_GROUPS'];

    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('customers', 'groups');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('groups/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $this->view->groupsTable = array(
            array(
                'title' => TABLE_HEADING_GROUPS,
                'not_important' => 1
            ),
            array(
                'title' => TABLE_HEADING_DISCOUNT,
                'not_important' => 1
            ),
        );

        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminGroups')) {
            return $ext::adminGroups();
        }
        return $this->render('index');
    }

    public function actionList() {
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $responseList = array();
        if ($length == -1)
            $length = 10000;
        $query_numrows = 0;

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where groups_name like '%" . $keywords . "%' ";
        } else {
            $search_condition = " where 1";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "groups_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "groups_discount " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "groups_name";
                    break;
            }
        } else {
            $orderBy = "groups_name";
        }

        $groups_query_raw = "select groups_id, groups_name, groups_discount, groups_is_tax_applicable,  groups_disable_checkout, date_added, last_modified, groups_is_show_price, new_approve, groups_is_reseller, image_active, image_inactive from " . TABLE_GROUPS . $search_condition . " order by " . $orderBy;
        $current_page_number = ( $start / $length ) + 1;
        $_split = new \splitPageResults($current_page_number, $length, $groups_query_raw, $query_numrows, 'groups_id');
        $groups_query = tep_db_query($groups_query_raw);
        while ($groups = tep_db_fetch_array($groups_query)) {

            $responseList[] = array(
                $groups['groups_name'] .
                '<input class="cell_identify" type="hidden" value="' . $groups['groups_id'] . '">',
                trim(rtrim($groups['groups_discount'], '0'), '.') . '%',
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionItempreedit() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminPreeditGroups')) {
            return $ext::adminPreeditGroups();
        }
    }

    public function actionItemedit() {
        $this->layout = false;
        
        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminEditGroups')) {
            return $ext::adminEditGroups();
        }
    }

    public function actionConfirmitemdelete() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminConfirmDeleteGroups')) {
            return $ext::adminConfirmDeleteGroups();
        }
    }

    public function actionSubmit() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminSubmitGroups')) {
            return $ext::adminSubmitGroups();
        }
    }

    public function actionItemdelete() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminDeleteGroups')) {
            return $ext::adminDeleteGroups();
        }
    }

    public function actionCustomers() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'adminCustomersGroups')) {
            return $ext::adminCustomersGroups();
        }
    }

    public function actionCustomersAdd() {
        $groups_id = Yii::$app->request->get('groups_id');
        $customers_id = Yii::$app->request->get('customers_id');
        tep_db_query("update " . TABLE_CUSTOMERS . " set groups_id = '" . (int) $groups_id . "' where customers_id = '" . (int) $customers_id . "'");
        return $this->actionCustomers();
    }

    public function actionCustomersDelete() {
        $customers_id = Yii::$app->request->get('customers_id');
        tep_db_query("update " . TABLE_CUSTOMERS . " set groups_id = '0' where customers_id = '" . (int) $customers_id . "'");
        return $this->actionCustomers();
    }

}
