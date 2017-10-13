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
use yii\helpers\Html;
use yii\helpers\Url;

class Banner_managerController extends Sceleton
{

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_TOOLS_BANNER_MANAGER'];
    public $banner_extension;
    public $dir_ok = false;

    private function banner_image_extension()
    {
        if (function_exists('imagetypes')) {
            if (imagetypes() & IMG_PNG) {
                return 'png';
            } elseif (imagetypes() & IMG_JPG) {
                return 'jpg';
            } elseif (imagetypes() & IMG_GIF) {
                return 'gif';
            }
        } elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
            return 'png';
        } elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
            return 'jpg';
        } elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
            return 'gif';
        }

        return false;
    }

    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);

        global $languages_id, $language;

        \common\helpers\Translation::init('admin/banner_manager');

        $this->banner_extension = $this->banner_image_extension();
        if (function_exists('imagecreate') && tep_not_null($this->banner_extension)) {
            if (is_dir(DIR_WS_IMAGES . 'graphs')) {
                if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
                    $this->dir_ok = true;
                } else {
                    $this->view->errorMessage = ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE;
                    $this->view->errorMessageType = 'danger';
                }
            } else {
                $this->view->errorMessage = ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST;
                $this->view->errorMessageType = 'danger';
            }
        }
    }

    private function _isAffiliate()
    {
        return tep_session_is_registered("login_affiliate");
    }

    public function actionIndex()
    {

        global $languages_id, $language, $messageStack;

        $this->selectedMenu = array('marketing', 'banner_manager');
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('banner_manager/banneredit') . '" class="create_item"><i class="icon-file-text"></i>' . IMAGE_NEW_BANNER . '</a>';
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('marketing/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $tmp = array();

        $tmp[] = array(
            'title' => TABLE_HEADING_BANNERS,
            'not_important' => 0
        );
        $tmp[] = array(
            'title' => TABLE_HEADING_GROUPS,
            'not_important' => 0
        );
        if (\common\classes\platform::isMulti()) {
            $tmp[] = array(
                'title' => TABLE_HEAD_PLATFORM_NAME,
                'not_important' => 0
            );
        }
        if (\common\classes\platform::isMulti()) {
            $tmp[] = array(
                'title' => TABLE_HEAD_PLATFORM_BANNER_ASSIGN,
                'not_important' => 0
            );
        } else {
            $tmp[] = array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0
            );
        }

        if ($messageStack->size > 0) {
            $this->view->errorMessage = $messageStack->output(true);
            $this->view->errorMessageType = $messageStack->messageType;
        }
        $this->view->filters = new \stdClass();
        $this->view->filters->platform = array();
        if (isset($_GET['platform']) && is_array($_GET['platform'])) {
            foreach ($_GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $banners_group = array();
        $banners_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . "");
        if (tep_db_num_rows($banners_query) > 0) {
            $banners_group[] = array('id' => '', 'text' => TEXT_BANNER_FILTER_BY);
            while ($banners_gr = tep_db_fetch_array($banners_query)) {
                $banners_group[] = array('id' => $banners_gr['banners_group'], 'text' => $banners_gr['banners_group']);
            }
        }
        /**/
        $filter_platform = '';
        $isMultiPlatforms = \common\classes\platform::isMulti();
        if ($isMultiPlatforms) {
            $filter_platform = '<div class="filt_left">
      <label>' . TEXT_COMMON_PLATFORM_FILTER . '</label><div class="f_row"><div class="f_td f_td_radio ftd_block"><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value="">' . TEXT_COMMON_PLATFORM_FILTER_ALL . '</label></div></div>';
            foreach (\common\classes\platform::getList() as $platform) {
                if ($platform['is_virtual'] == 0) {
                    $filter_platform .= '<div class="f_row"><div class="f_td f_td_radio ftd_block"><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value="' . $platform['id'] . '" ' . ( in_array($platform['id'], $this->view->filters->platform) ? ' data-checked="true" checked="checked"' : 'data-checked="false"') . '> ' . $platform['text'] . '</label></div></div>';
                }
            }
            $filter_platform .= '</div>';
        }
        /**/


        //print_r($banners_gr);die();
        $languages = \common\helpers\Language::get_languages();
        $this->view->bannerTable = $tmp;
        //return $this->render('index', array('lang' => tep_draw_pull_down_menu('language',\common\helpers\Language::pull_languages(),$languages[$languages_id-1]['code'],' onChange="javascript:window.location.href=\'' . FILENAME_BANNER_MANAGER. '?language=\'+this.value" class="slanguage form-control"')));
        return $this->render('index', array(
            'isMultiPlatforms' => $isMultiPlatforms,
            'filter' => $filter_platform . '<div class="filter_banners">' . tep_draw_pull_down_menu('filter_by', $banners_group, ( isset($_GET['banners_group']) && tep_not_null($_GET['banners_group']) ? $_GET['banners_group'] : ''), ' class="sfilter form-control"') . '</div>'));
    }

    public function actionGetimage($banner_id)
    {
        global $languages_id;
        $banners_id = tep_db_prepare_input($_GET['banner']);

        $banner_query = tep_db_query("select banners_title, banners_image from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . (int) $banner_id . "' and language_id = '" . $languages_id . "'");
        $banner = tep_db_fetch_array($banner_query);

        $page_title = $banner['banners_title'];
        if ($banner['banners_image']) {
            $image_source = tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $page_title, '100');
            return $image_source;
        } else {
            return '';
        }
    }

    public function actionList()
    {
        global $languages_id, $login_id, $language;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $responseList = array();
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $current_page_number = ( $start / $length ) + 1;

        /* if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php')) {
          include(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php');
          } */
        $filter_by_platform = array();
        if (isset($output['platform']) && is_array($output['platform'])) {
            foreach ($output['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $filter_by_platform[] = (int) $_platform_id;
        }
        $search_condition = '';
        if (count($filter_by_platform) > 0) {
            $search_condition = ' and b.banners_id IN (SELECT banners_id FROM ' . TABLE_BANNERS_TO_PLATFORM . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition .= " and (b.banners_group like '%" . $keywords . "%' or bl.banners_title like '%" . $keywords . "%') ";
        }

        if (tep_not_null($output['filter_by'])) {
            $search_condition .= " and (b.banners_group = '" . tep_db_input($output['filter_by']) . "') ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "bl.banners_title " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "b.banners_group " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ", bl.banners_title";
                    break;
                case 3:
                    $orderBy = "b.status " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ", bl.banners_title";
                    break;
                default:
                    $orderBy = "bl.banners_title, b.banners_group";
                    break;
            }
        } else {
            $orderBy = "bl.banners_title, b.banners_group";
        }

        $banners_query_raw = "select * from " . TABLE_BANNERS_NEW . " b, " . TABLE_BANNERS_LANGUAGES . " bl where bl.banners_id = b.banners_id and bl.language_id = " . (int) $languages_id . " " . $search_condition . " order by " . $orderBy;
        $banners_split = new \splitPageResults($current_page_number, $length, $banners_query_raw, $banners_query_numrows);

        $banners_query = tep_db_query($banners_query_raw);
        while ($banners = tep_db_fetch_array($banners_query)) {
            $tmp = array();
            $info_query = tep_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int) $banners['banners_id'] . "'");
            $info = tep_db_fetch_array($info_query);
            $banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
            $banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';

            $tmp[] = '<div class="click_double imgcenter" data-click-double="' . \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners['banners_id']]) . '">' . $this->actionGetimage($banners['banners_id']) . '<span>' . $banners['banners_title'] .
                    '<input class="cell_identify" type="hidden" value="' . $banners['banners_id'] . '"></span></div>';


            if (\common\classes\platform::isMulti()) {
                $platforms = '';
                $public_checkbox = '';

                $banner_statuses = array();
                $get_statuses_r = tep_db_query("SELECT banners_id, platform_id FROM " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . $banners['banners_id'] . "'");
                while ($get_status = tep_db_fetch_array($get_statuses_r)) {
                    $sub_row_key = $get_status['banners_id'] . '^' . $get_status['platform_id'];
                    $banner_statuses[$sub_row_key] = 1;
                }

                foreach (\common\classes\platform::getList() as $platform_variant) {

                    $sub_row_key = $banners['banners_id'] . '^' . $platform_variant['id'];
                    $sub_row_disabled = !isset($banner_statuses[$sub_row_key]);

                    $_row_key = $banners['banners_id'] . '-' . $platform_variant['id'];
                    if ($platform_variant['is_virtual'] == 0) {
                        $platforms .= '<div id="banner-' . $_row_key . '"' . ($sub_row_disabled ? ' class="platform-disable"' : '') . '>' . $platform_variant['text'] . '</div>';

                        $public_checkbox .= '<div>' .
                                (( isset($banner_statuses[$sub_row_key]) ) ?
                                '<input type="checkbox" value="' . $_row_key . '" name="status[' . $banners['banners_id'] . '][' . $platform_variant['id'] . ']" class="check_on_off" checked="checked" data-id="banner-' . $_row_key . '">' :
                                '<input type="checkbox" value="' . $_row_key . '" name="status[' . $banners['banners_id'] . '][' . $platform_variant['id'] . ']" class="check_on_off" data-id="banner-' . $_row_key . '">'
                                ) . '</div>';
                    }
                }

                $tmp[] = '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners['banners_id']]) . '">' . $banners['banners_group'] . '</div>';

                $tmp[] = '<div class="platforms-cell">' . $platforms . '</div>';
                $tmp[] = '<div class="platforms-cell-checkbox">' . $public_checkbox . '</div>';
            } else {
                $get_status = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS status FROM " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . $banners['banners_id'] . "' AND platform_id='" . \common\classes\platform::firstId() . "'"));
                $banners['status'] = $get_status['status'];
                $tmp[] = '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners['banners_id']]) . '"><a class="popupN" href="' . \Yii::$app->urlManager->createUrl('banner_manager/bannertype?group=' . $banners['banners_group']) . '"><i class="icon-pencil icon"></i>' . $banners['banners_group'] . '</a></div>';
                $tmp[] = '<input type="checkbox" value=' . $banners['banners_id'] . ' name="status" class="check_on_off"' . ($banners['status'] == '1' ? ' checked="checked"' : '') . '>';
            }
            $responseList[] = $tmp;
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $banners_query_numrows,
            'recordsFiltered' => $banners_query_numrows,
            'data' => $responseList,
        );
        echo json_encode($response);
    }

    function getBanner($bID)
    {
        global $login_id, $languages_id;
        $banners_query = tep_db_query("select * from " . TABLE_BANNERS_NEW . " b, " . TABLE_BANNERS_LANGUAGES . " bl where bl.banners_id = b.banners_id and bl.language_id = " . (int) $languages_id . " and b.affiliate_id = 0 and b.banners_id = '" . (int) $bID . "'");
        return (tep_db_num_rows($banners_query) ? (object) tep_db_fetch_array($banners_query) : false);
    }

    public function actionView()
    {
        global $languages_id, $language;

        /* if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php')) {
          include(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php');
          } */

        $bID = Yii::$app->request->get('bID', 0);

        if ($bID) {
            $bInfo = $this->getBanner($bID);
        }
        $heading = array();
        $contents = array();
        $b_platform = '';
        $banners_platform = tep_db_query("select platform_name from " . TABLE_PLATFORMS . " p left join " . TABLE_BANNERS_TO_PLATFORM . " bt on p.platform_id = bt.platform_id where bt.banners_id ='" . $bID . "' ");
        if (tep_db_num_rows($banners_platform) > 0) {
            while ($banners_platform_result = tep_db_fetch_array($banners_platform)) {
                $b_platform .= '<div class="platform_res">' . $banners_platform_result['platform_name'] . '</div>';
            }
        }
        if (is_object($bInfo)) {
            echo '<div class="or_box_head">' . $bInfo->banners_title . '</div>';
            //$heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');

            /* $contents[] = array('align' => 'left', 'text' => '<button class="btn btn-edit"  onClick="return editBanner(' . $bInfo->banners_id . ');">' . IMAGE_EDIT . '</button>&nbsp;' .
              '<button class="btn btn-delete"  onClick="return deleteItemConfirm(' . $bInfo->banners_id . ');">' . IMAGE_DELETE . '</button>'
              ); */

            echo '<div class="row_or_wrapp">';
            echo '<div class="row_or"><div>' . TEXT_BANNERS_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_format($bInfo->date_added, DATE_FORMAT_SHORT) . '</div></div>';
            echo '</div>';
            //$contents[] = array('text' => '<br>' . TEXT_BANNERS_DATE_ADDED . ' ' . \common\helpers\Date::date_short($bInfo->date_added));

            if ((function_exists('imagecreate')) && ($this->dir_ok) && ($this->banner_extension)) {
                $banner_id = $bInfo->banners_id;
                $banner_extension = $this->banner_extension;
                $days = '3';
                include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
                //$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $this->banner_extension));
                echo '<div class="graph b_imgcenter">' . $this->actionGetimage($bID) . '</div>';
            }
            echo '<div class="pad_bottom b_right"><strong>' . TEXT_GROUP . '</strong><span>' . $bInfo->banners_group . '</span></div>';
            echo '<div class="b_right"><strong>' . BOX_PLATFORMS . ':</strong><span>' . $b_platform . '</span></div>';

            //if ($bInfo->date_scheduled) $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, \common\helpers\Date::date_short($bInfo->date_scheduled)));
            if ($bInfo->date_scheduled) {
                echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, \common\helpers\Date::date_format($bInfo->date_scheduled, DATE_FORMAT_SHORT)) . '</div>';
            }
            if ($bInfo->expires_date) {
                //$contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, \common\helpers\Date::date_short($bInfo->expires_date)));
                echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, \common\helpers\Date::date_format($bInfo->expires_date, DATE_FORMAT_SHORT)) . '</div>';
            } elseif ($bInfo->expires_impressions) {
                //$contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
                echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions) . '</div>';
            }
            if ($bInfo->date_status_change) {
                //echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_STATUS_CHANGE, \common\helpers\Date::date_format($bInfo->date_status_change, DATE_FORMAT_SHORT)) . '</div>';
            }
            //echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-edit btn-no-margin"  onClick="return editBanner(' . $bInfo->banners_id . ');">' . IMAGE_EDIT . '</button><button class="btn btn-delete"  onClick="return deleteItemConfirm(' . $bInfo->banners_id . ');">' . IMAGE_DELETE . '</button></div>';
            echo '<div class="btn-toolbar btn-toolbar-order"><a class="btn btn-no-margin btn-edit" href="' . tep_href_link(FILENAME_BANNER_MANAGER . '/banneredit', 'banners_id=' . $bInfo->banners_id) . '">' . IMAGE_EDIT . '</a><button class="btn btn-delete"  onClick="return deleteItemConfirm(' . $bInfo->banners_id . ');">' . IMAGE_DELETE . '</button></div>';
        }

        //$box = new \box;
        //echo $box->infoBox($heading, $contents);
    }

    public function actionEdit()
    {
        global $languages_id, $language;

        /* if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php')) {
          include(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php');
          } */

        $form_action = 'insert';

        $parameters = array('expires_date' => '',
            'date_scheduled' => '',
            'banner_type' => '',
            'sort_order' => '',
            'banners_title' => '',
            'banners_url' => '',
            'banners_group' => '',
            'banners_image' => '',
            'banners_html_text' => '',
            'expires_impressions' => '');

        $bInfo = new \objectInfo($parameters);

        if (isset($_GET['bID'])) {
            $form_action = 'update';

            $bID = tep_db_prepare_input($_GET['bID']);
            $bInfo = $this->getBanner($bID);
        } elseif (tep_not_null($_POST)) {
            $bInfo = new \objectInfo(tep_db_prepare_input($_POST));
        }

        $groups_array = array();
        $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " order by banners_group");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
        }
        $banner_type[0] = array('id' => 'banner', 'text' => 'banner');
        $banner_type[1] = array('id' => 'carousel', 'text' => 'carousel');
        $banner_type[2] = array('id' => 'slider', 'text' => 'slider');
        $languages = \common\helpers\Language::get_languages();
        ob_start();
        echo tep_draw_form('new_banner', FILENAME_BANNER_MANAGER . '/' . $form_action, '', 'post', 'enctype="multipart/form-data"');
        if ($form_action == 'update')
            echo tep_draw_hidden_field('banners_id', $bID);
        ?>
            <?php $count = 0; ?>
        <div class="banner_page">
                <?php
                echo '<ul class="nav nav-tabs">';
                foreach ($languages as $lang) {
                    echo '<li' . ($count == 0 ? ' class="active"' : '') . '><a href="#tab_' . $lang['code'] . '" data-toggle="tab">' . $lang['image'] . '<span>' . $lang['name'] . '</span></a></li>';
                    $count++;
                }
                echo '</ul>';
                ?>
            <div class="tab-content">
        <?php $counter = 0; ?>
        <?php foreach ($languages as $lang) { ?>    
                    <div class="tab-pane<?php echo ($counter == 0 ? ' active' : ''); ?>" id="tab_<?php echo $lang['code']; ?>">
                        <table border="0" cellspacing="0" cellpadding="2" height="100%">
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_TITLE; ?></td>
                                <td class="label_value"><?php echo tep_draw_input_field('banners_title', $bInfo->banners_title, '', true); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_URL; ?></td>
                                <td class="label_value"><?php echo tep_draw_input_field('banners_url', $bInfo->banners_url); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name" valign="top"><?php echo TEXT_BANNERS_GROUP; ?></td>
                                <td class="label_value"><?php echo tep_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group) . (!tep_session_is_registered('login_affiliate') ? TEXT_BANNERS_NEW_GROUP . '<br>' . tep_draw_input_field('new_banners_group', '', '', ((sizeof($groups_array) > 0) ? false : true)) : ''); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name" valign="top"><?php echo TEXT_BANNERS_TYPE; ?></td>
                                <td class="label_value"><?php echo tep_draw_pull_down_menu('banner_type', $banner_type, $bInfo->banner_type); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
                                <td class="label_value"><?php echo tep_draw_file_field('banners_image') . (!tep_session_is_registered('login_affiliate') ? ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br>' . DIR_FS_CATALOG_IMAGES . tep_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : '')) : ''); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
                                <td class="label_value"><?php echo DIR_FS_CATALOG_IMAGES . ($this->_isAffiliate() ? 'banners/' . $login_id . '/' : '') . tep_draw_input_field('banners_image_target'); ?></td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
                                <td class="label_value"><?php echo tep_draw_textarea_field('banners_html_text', 'soft', '60', '10', $bInfo->banners_html_text); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?><br><small>(<?php echo strtolower(DATE_FORMAT_SPIFFYCAL); ?>)</small></td>
                                <td valign="top" class="label_value"><?php echo tep_draw_calendar_jquery('date_scheduled', $bInfo->date_scheduled); ?></td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name"><?php echo TEXT_BANNERS_EXPIRES_ON; ?><br><small>(<?php echo strtolower(DATE_FORMAT_SPIFFYCAL); ?>)</small></td>
                                <td class="label_value"><?php echo tep_draw_calendar_jquery('expires_date', $bInfo->expires_date); ?><?php echo TEXT_BANNERS_OR_AT . '<br>' . tep_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name"><?php echo TEXT_BANNER_STATUS; ?></td>
                                <td class="label_value"><?php echo tep_draw_checkbox_field('status', '', ($bInfo->status ? true : false), '', 'class="check_on_off"'); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNER_SORT_ORDER; ?></td>
                                <td class="label_value"><?php echo tep_draw_input_field('sort_order', $bInfo->sort_order); ?></td>
                            </tr>
                        </table>
                    </div>
            <?php
            $counter++;
        }
        ?>
            </div>
            <div class="btn-bar">
                <div class="btn-left"><?php echo (($form_action == 'insert') ? '<input type="submit" value="' . IMAGE_INSERT . '" class="btn btn-primary">' : '<input type="submit" value="' . IMAGE_UPDATE . '" class="btn btn-primary">'); ?></div>
                <div class="btn-right"><?php echo '<button class="btn btn-cancel" onclick="return resetStatement();">' . IMAGE_CANCEL . '</button>'; ?></div>
            </div>
        </div>
        </form>
        <?php
        $page = ob_get_clean();
        echo $page;
    }

    public function actionSubmit()
    {
        global $languages_id, $language, $login_id, $messageStack;

        \common\helpers\Translation::init('admin/banner_manager');
        /* if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php')) {
          include(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php');
          } */

        $banners_id = $_POST['banners_id'];
        $this->view->errorMessageType = 'success';
        $this->view->errorMessage = '';
        //	die($banners_id);
        if ($banners_id > 0) {
            $action = 'update';
        } else {
            $action = 'insert';
        }

        $banner_params = array();

        $platforms = \common\classes\platform::getList();

        $sql_data_array = array();
        $new_banners_group = tep_db_prepare_input($_POST['new_banners_group']);
        if ($this->_isAffiliate()) {
            $banners_group = tep_db_prepare_input($_POST['banners_group']);
        } else {
            $banners_group = (empty($new_banners_group)) ? tep_db_prepare_input($_POST['banners_group']) : $new_banners_group;
        }

        $expires_date = 'null';
        if (!empty($_POST['expires_date'])) {
            $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $_POST['expires_date']);
            $expires_date = $date->format('Y-m-d');
        }

        $sql_data_array['expires_date'] = $expires_date;

        $expires_impressions = tep_db_prepare_input($_POST['expires_impressions']);
        if (tep_not_null($expires_impressions)) {
            $sql_data_array['expires_impressions'] = $expires_impressions;
        }
        $date_scheduled = 'null';
        if (!empty($_POST['date_scheduled'])) {
            $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $_POST['date_scheduled']);
            $date_scheduled = $date->format('Y-m-d');
        }


        $sql_data_array['date_scheduled'] = $date_scheduled;

        //$banner_type = ($_POST['banner_type'] ? tep_db_prepare_input($_POST['banner_type']) : '');
        $sort_order = tep_db_prepare_input($_POST['sort_order']);
        $status = 0;
        if (is_array($_POST['status'])) {
            $status = isset($_POST['status'][\common\classes\platform::firstId()]) ? 1 : 0;
        } else {
            $status = isset($_POST['status']) ? 1 : 0;
        }

        if (($status == '0') || ($status == '1')) {
            $sql_data_array['status'] = $status;
        }
        if ($this->_isAffiliate()) {
            $sql_data_array['affiliate_id'] = $login_id;
        }

        //$sql_data_array['banner_type'] = tep_db_prepare_input($_POST['banner_type']);
        $sql_data_array['sort_order'] = tep_db_prepare_input($_POST['sort_order']);
        $sql_data_array['banners_group'] = tep_db_prepare_input($_POST['banners_group']);

        if (!empty($sql_data_array['banners_group']) && !isset($banner_params[$sql_data_array['banners_group']])) {
            $get_banner_params_r = tep_db_query(
                    "SELECT banner_type FROM " . TABLE_BANNERS_NEW . " WHERE '" . tep_db_input($sql_data_array['banners_group']) . "' and banner_type!='' LIMIT 1"
            );
            if (tep_db_num_rows($get_banner_params_r) > 0) {
                $get_banner_param = tep_db_fetch_array($get_banner_params_r);
                $banner_params[$sql_data_array['banners_group']] = $get_banner_param['banner_type'];
            }
        }

        if ($action == 'insert' || $banners_id == 0) {
            $insert_sql_data['date_added'] = 'now()';
            $insert_sql_data['banner_type'] = $banner_params[$sql_data_array['banners_group']];

            tep_db_perform(TABLE_BANNERS_NEW, array_merge($sql_data_array, $insert_sql_data));
            $banners_id = tep_db_insert_id();
            Yii::$app->request->setBodyParams(['banners_id' => $banners_id]);
            $this->view->errorMessage = TEXT_INFO_SAVED;
            $action = 'update';
        } elseif ($action == 'update') {
            $sql_data_array['banners_id'] = $banners_id;
            $check = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS c FROM " . TABLE_BANNERS_NEW . " WHERE banners_id='" . (int) $banners_id . "'"
            ));
            if ($check['c'] == 0) {
                $insert_sql_data['date_added'] = 'now()';
                $insert_sql_data['banner_type'] = $banner_params[$sql_data_array['banners_group']];

                tep_db_perform(TABLE_BANNERS_NEW, array_merge($sql_data_array, $insert_sql_data));
            } else {
                $update_sql_data['date_status_change'] = 'now()';

                tep_db_perform(TABLE_BANNERS_NEW, array_merge($sql_data_array, $update_sql_data), 'update', "banners_id = '" . (int) $banners_id . "'");
            }

            $this->view->errorMessage = defined('TEXT_INFO_UPDATED') ? TEXT_INFO_UPDATED : 'Updated';
        }
        if (\common\classes\platform::isMulti()) {
            foreach ($platforms as $_platform_info) {
                if (isset($_POST['status'][$_platform_info['id']])) {
                    tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $banners_id . "', '" . (int) $_platform_info['id'] . "')");
                } else {
                    tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "' AND platform_id='" . (int) $_platform_info['id'] . "'");
                }
            }
        } else {
            if ($status) {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $banners_id . "', '" . (int) \common\classes\platform::firstId() . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "' AND platform_id='" . (int) \common\classes\platform::firstId() . "'");
            }
        }

        $languages = \common\helpers\Language::get_languages();


        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $banners_title = tep_db_prepare_input($_POST['banners_title'][$language_id]);


            $banners_url = tep_db_prepare_input($_POST['banners_url'][$language_id]);
            $target = tep_db_prepare_input($_POST['target'][$language_id]);
            $banner_display = tep_db_prepare_input($_POST['banner_display'][$language_id]);
            $text_position = tep_db_prepare_input($_POST['text_position'][$language_id]);


            $banners_html_text = tep_db_prepare_input($_POST['banners_html_text'][$language_id]);

            $banner_error = false;
            
        
            $sql_data_array = [
                'banners_title' => $banners_title,
                'banners_url' => $banners_url,
                'target' => ($target == 'on' ? 1 : 0),
                'banner_display' => $banner_display,
                'banners_html_text' => $banners_html_text,
                'language_id' => $language_id,
                'text_position' => $text_position
            ];
            
            $sql_data_array['banners_image'] = str_replace('images/', '', $_POST['banners_image'][$language_id]);
            if ($_POST['banners_image_upload'][$language_id] != '') {
                $val = \backend\design\Uploads::move($_POST['banners_image_upload'][$language_id], 'images', false);
                $sql_data_array['banners_image'] = $val;
            }

            $check_banner = tep_db_query("select * from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . $banners_id . "' and language_id = '" . $languages[$i]['id'] . "' ");
            if (tep_db_num_rows($check_banner) == 0) {
                if ($sql_data_array['banners_title'] || $sql_data_array['banners_url'] || $sql_data_array['banners_html_text'] || $sql_data_array['banners_image']) {
                    $insert_sql_data = [
                        'banners_id' => $banners_id
                    ];
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                    tep_db_perform(TABLE_BANNERS_LANGUAGES, $sql_data_array);
                    $this->view->errorMessage = SUCCESS_BANNER_INSERTED;
                    $messageType = 'success';
                } else {
                    if ($messageType != 'success') {
                        $this->view->errorMessage = ERROR_WARNING;
                        $messageType = 'warning';
                    }
                }
            } else {

                tep_db_perform(TABLE_BANNERS_LANGUAGES, $sql_data_array, 'update', "banners_id = '" . (int) $banners_id . "' and language_id ='" . (int) $language_id . "'");
                $this->view->errorMessage = SUCCESS_BANNER_UPDATED;
                $messageType = 'success';
            }
        }

        if ($this->view->errorMessage) {
            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
            <?php echo $this->view->errorMessage; ?>
                        </div>  
                    </div>   
                    <div class="noti-btn">
                        <div></div>
                        <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                    </div>
                </div>  
                <script>
                    $('body').scrollTop(0);
                    $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                        $(this).parents('.pop-mess').remove();
                    });
                </script>
            </div>

            <?php
        }

        /* else {
          $action = 'new';
          } */

        //return $this->redirect(Url::toRoute('banner_manager/'));
        return $this->actionBanneredit();
    }

    public function actionDeleteconfirm()
    {
        global $languages_id, $language;

        /* if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php')) {
          include(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php');
          } */

        $heading = array();
        $contents = array();
        $bInfo = $this->getBanner(Yii::$app->request->get('bID', 0));
        if ($bInfo) {
            $heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');
            echo '<div class="or_box_head">' . $bInfo->banners_title . '</div>';
            $contents = array('form' => tep_draw_form('banners', FILENAME_BANNER_MANAGER . '/delete', 'bID=' . $bInfo->banners_id, 'post'));
            echo tep_draw_form('banners', FILENAME_BANNER_MANAGER . '/delete', 'bID=' . $bInfo->banners_id, 'post');
            $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
            echo '<div class="pad_bottom">' . TEXT_INFO_DELETE_INTRO . '</div>';
            if ($bInfo->banners_image) {
                echo '<div class="pad_bottom">' . tep_draw_checkbox_field('delete_image', 'on', true, '', 'class="uniform"') . '<span>' . TEXT_INFO_DELETE_IMAGE . '</span></div>';
            }
            // $contents[] = array('text' => '<br><b>' . $bInfo->banners_title . '</b>');
            //if ($bInfo->banners_image) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);

            echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><button class="btn btn-cancel"  onClick="return resetStatement();">' . IMAGE_CANCEL . '</button></div>';
            //$contents[] = array('align' => 'left', 'text' => '<input type="submit" class="btn btn-delete" value="">&nbsp;' .'');		  
        }
        /* $box = new \box;
          echo $box->infoBox($heading, $contents); */
    }

    public function actionDelete()
    {
        global $messageStack;
        global $languages_id, $language;

        /* if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php')) {
          include(DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php');
          } */

        $banners_id = Yii::$app->request->get('bID', 0);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
            $banner_query = tep_db_query("select banners_image from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . (int) $banners_id . "'");
            $banner = tep_db_fetch_array($banner_query);

            if (is_file(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
                if (is_writeable(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
                    unlink(DIR_FS_CATALOG_IMAGES . $banner['banners_image']);
                } else {
                    $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
                }
            } else {
                $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
            }
        }

        tep_db_query("delete from " . TABLE_BANNERS_NEW . " where banners_id = '" . (int) $banners_id . "'");
        tep_db_query("delete from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . (int) $banners_id . "'");
        tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int) $banners_id . "'");

        if (function_exists('imagecreate') && tep_not_null($this->banner_extension)) {
            if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $this->banner_extension)) {
                if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $this->banner_extension)) {
                    unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $this->banner_extension);
                }
            }

            if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $this->banner_extension)) {
                if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $this->banner_extension)) {
                    unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $this->banner_extension);
                }
            }

            if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $this->banner_extension)) {
                if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $this->banner_extension)) {
                    unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $this->banner_extension);
                }
            }

            if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $this->banner_extension)) {
                if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $this->banner_extension)) {
                    unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $this->banner_extension);
                }
            }
        }

        $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');

        return $this->redirect(Url::toRoute('banner_manager/'));
    }

    public function actionSwitchStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        if (strpos($id, '-') !== false) {
            list($bid, $pid) = explode('-', $id, 2);
            if ($status == 'true') {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $bid . "', '" . (int) $pid . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $bid . "' AND platform_id='" . (int) $pid . "'");
            }
        } else {
            tep_db_query("update " . TABLE_BANNERS_NEW . " set status = '" . ($status == 'true' ? 1 : 0) . "' where banners_id = '" . (int) $id . "'");
            if ($status == 'true') {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $id . "', '" . (int) \common\classes\platform::firstId() . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $id . "' AND platform_id='" . (int) \common\classes\platform::firstId() . "'");
            }
        }
    }

    public function actionBanneredit()
    {

        if (Yii::$app->request->isPost) {
            $banners_id = (int) Yii::$app->request->getBodyParam('banners_id');
        } else {
            $banners_id = (int) Yii::$app->request->get('banners_id');
        }

        if ($banners_id > 0) {
            $banner_query = tep_db_query("select * from " . TABLE_BANNERS_NEW . " where banners_id = " . $banners_id);
            $banner = tep_db_fetch_array($banner_query);
        }
        $cInfo = new \objectInfo($banner);
        $groups_array = array();
        $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " order by banners_group");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
        }
        $banner_type[0] = array('id' => 'banner', 'text' => 'banner');
        $banner_type[1] = array('id' => 'carousel', 'text' => 'carousel');
        $banner_type[2] = array('id' => 'slider', 'text' => 'slider');



        $banner_statuses = array();
        $platform_statuses = array();
        $get_statuses_r = tep_db_query("SELECT banners_id, platform_id FROM " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "'");
        while ($get_status = tep_db_fetch_array($get_statuses_r)) {
            $sub_row_key = $get_status['platform_id'];
            $banner_statuses[$sub_row_key] = 1;
        }
        $banners_data = array();

        $cDescription = [];
        $mainDesc = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $cDescription[$i]['code'] = $languages[$i]['code'];

            $banner_description_query = tep_db_query(
                    "select * from " . TABLE_BANNERS_NEW . " b " .
                    " left join " . TABLE_BANNERS_LANGUAGES . " bl on b.banners_id = bl.banners_id and bl.language_id = '" . (int) $languages[$i]['id'] . "' " .
                    "where   b.banners_id = '" . $banners_id . "'  " .
                    " and b.affiliate_id=0 "
            );
            $banner_data = false;
            if (tep_db_num_rows($banner_description_query) > 0) {
                $banner_data = tep_db_fetch_array($banner_description_query);
            }

            $cDescription[$i]['banners_title'] = tep_draw_input_field('banners_title[' . $languages[$i]['id'] . ']', $banner_data ? $banner_data['banners_title'] : '', 'class="form-control"');
            $cDescription[$i]['banners_url'] = tep_draw_input_field('banners_url[' . $languages[$i]['id'] . ']', $banner_data ? $banner_data['banners_url'] : '', 'class="form-control"');

            $cDescription[$i]['target'] = tep_draw_checkbox_field('target[' . $languages[$i]['id'] . ']', 0, $banner_data['target'] == 1 ? true : false, '', 'class="uniform"');
            
            $cDescription[$i]['banner_display'] = $banner_data['banner_display'];
            $cDescription[$i]['banner_display_name'] = 'banner_display[' . $languages[$i]['id'] . ']';
            
            $cDescription[$i]['banners_html_text'] = tep_draw_textarea_field('banners_html_text[' . $languages[$i]['id'] . ']', 'soft', '70', '15', $banner_data ? $banner_data['banners_html_text'] : '', 'form-control"');
            $cDescription[$i]['banners_image'] = '<div class="banner_image">' .
                    '<div class="upload" data-name="banners_image[' . $languages[$i]['id'] . ']" data-value="' . \common\helpers\Output::output_string($banner_data ? $banner_data['banners_image'] : '') . '"></div>' .
                    '</div>';
            
            $cDescription[$i]['name'] = 'banners_image[' . $languages[$i]['id'] . ']';
            $cDescription[$i]['value'] = $banner_data['banners_image'];
            $cDescription[$i]['upload'] = 'banners_image_upload[' . $languages[$i]['id'] . ']';
            $cDescription[$i]['delete'] = 'banners_image_delete[' . $languages[$i]['id'] . ']';
            
            $cDescription[$i]['text_position'] = $banner_data['text_position'];
            $cDescription[$i]['text_position_name'] = 'text_position[' . $languages[$i]['id'] . ']';


            $mainDesc['banners_group'] = tep_draw_pull_down_menu('banners_group', $groups_array, $banner_data ? $banner_data['banners_group'] : '', 'class="form-control"') . (!tep_session_is_registered('login_affiliate') ? tep_draw_hidden_field('new_banners_group', '', 'class="form-control new_ban_field"', ((sizeof($groups_array) > 0) ? false : true)) : '');
            $mainDesc['banner_type'] = tep_draw_pull_down_menu('banner_type', $banner_type, $banner_data ? $banner_data['banner_type'] : '', 'class="form-control"');
            $mainDesc['date_scheduled'] = '<input type="text" name="date_scheduled" value="' . \common\helpers\Date::date_short($banner_data && $banner_data['date_scheduled'] > 0 ? $banner_data['date_scheduled'] : '') . '" class="form-control datepicker">';
            $mainDesc['expires_date'] = '<input type="text" name="expires_date" value="' . \common\helpers\Date::date_short($banner_data && $banner_data['expires_date'] > 0 ? $banner_data['expires_date'] : '') . '" class="form-control datepicker">';

            //$mainDesc[$platform['id']]['expires_impressions'] =  tep_draw_input_field('expires_impressions', $cInfo->expires_impressions, 'maxlength="7" size="7"');

            if (\common\classes\platform::isMulti()) {
                foreach (\common\classes\platform::getList() as $_platform_info) {
                    $platform_statuses[$_platform_info['id']] = tep_draw_checkbox_field('status[' . $_platform_info['id'] . ']', '1', (isset($banner_statuses[$_platform_info['id']]) ? true : false), '', 'class="check_on_off"');
                }
            }
            $mainDesc['status'] = tep_draw_checkbox_field('status', '1', ((isset($banner_statuses[\common\classes\platform::firstId()])) ? true : false), '', 'class="check_on_off"');

            $mainDesc['sort_order'] = tep_draw_input_field('sort_order', ($banner_data ? $banner_data['sort_order'] : ''), 'class="form-control"');

            $banners_data = $mainDesc;
        }
        $banners_data['lang'] = $cDescription;
        $banners_data['platform_statuses'] = $platform_statuses;

        $this->selectedMenu = array('marketing', 'banner_manager');

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }
        $text_new_or_edit = ($banners_id == 0) ? TEXT_BANNER_INSERT : TEXT_BANNER_EDIT;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('banner_manager/index'), 'title' => $text_new_or_edit);

        $render_data = [
            'banners_id' => $banners_id,
            'cInfo' => $cInfo,
            'languages' => $languages,
            //'cDescription' => $cDescription,
            //'mainDesc'=>$mainDesc,
            'banners_data' => $banners_data,
            'platforms' => \common\classes\platform::getList(),
            'first_platform_id' => \common\classes\platform::firstId(),
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
        ];
        return $this->render('banneredit.tpl', $render_data);
    }

    function actionNewgroup()
    {
        global $languages_id, $language;

        /* if( file_exists( DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php' ) ) {
          include( DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php' );
          } */
        if (Yii::$app->request->isPost) {
            $banners_id = (int) Yii::$app->request->getBodyParam('banners_id');
        } else {
            $banners_id = (int) Yii::$app->request->get('banners_id');
        }

        $this->layout = false;
        $this->view->usePopupMode = true;

        $groups_array = array();
        $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " order by banners_group");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
        }
        $html = '<div id="bannerpopup">';
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
									<tr>
											<td class="dataTableContent">' . TEXT_BANNER_NEW_GROUP . '</td>
											<td class="dataTableContent">' . tep_draw_input_field('new_ban_group_popup', '', 'class="form-control"', ((sizeof($groups_array) > 0) ? false : true)) . '</td>
									</tr>
							</table>
							<div class="btn-bar">
									<div class="btn-left"><button class="btn btn-cancel" onclick="return closePopup();">' . IMAGE_CANCEL . '</button></div>
									<div class="btn-right"><button class="btn btn-primary" onclick="return saveGroupnew();">' . IMAGE_INSERT . '</button></div>
							</div></div>';

        return $html;
    }

    function actionBannertype()
    {
        global $languages_id, $language;
        /* if( file_exists( DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php' ) ) {
          include( DIR_WS_LANGUAGES . $language . '/' . 'banner_manager.php' );
          } */
        if (Yii::$app->request->isPost) {
            $group = Yii::$app->request->getBodyParam('group');
        } else {
            $group = Yii::$app->request->get('group');
        }
        $this->layout = false;
        $this->view->usePopupMode = true;
        $banner_effect = array();
        $banner_effect[0] = array('id' => 'sliceDown', 'text' => 'sliceDown');
        $banner_effect[1] = array('id' => 'sliceDownLeft', 'text' => 'sliceDownLeft');
        $banner_effect[2] = array('id' => 'sliceUp', 'text' => 'sliceUp');
        $banner_effect[3] = array('id' => 'sliceUpLeft', 'text' => 'sliceUpLeft');
        $banner_effect[4] = array('id' => 'sliceUpDown', 'text' => 'sliceUpDown');
        $banner_effect[5] = array('id' => 'sliceUpDownLeft', 'text' => 'sliceUpDownLeft');
        $banner_effect[6] = array('id' => 'fold', 'text' => 'fold');
        $banner_effect[7] = array('id' => 'fade', 'text' => 'fade');
        $banner_effect[8] = array('id' => 'random', 'text' => 'random');
        $banner_effect[9] = array('id' => 'slideInRight', 'text' => 'slideInRight');
        $banner_effect[10] = array('id' => 'slideInLeft', 'text' => 'slideInLeft');
        $banner_effect[11] = array('id' => 'boxRandom', 'text' => 'boxRandom');
        $banner_effect[12] = array('id' => 'boxRain', 'text' => 'boxRain');
        $banner_effect[13] = array('id' => 'boxRainReverse', 'text' => 'boxRainReverse');
        $banner_effect[14] = array('id' => 'boxRainGrow', 'text' => 'boxRainGrow');
        $banner_effect[15] = array('id' => 'boxRainGrowReverse', 'text' => 'boxRainGrowReverse');

        $groups_query = tep_db_query("select distinct banners_group, banner_type from " . TABLE_BANNERS_NEW . " where banners_group = '" . tep_db_input($group) . "'");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $banner_type = $groups['banner_type'];
        }
        $banner_type_array = explode(';', $banner_type);
        $html = '<form name="save_banner_type" onSubmit="return saveBannertype();" id="save_banner_type"><div class="banner_type">
					<input name="banner_group" type="hidden" value="' . $group . '">
						<div class="after">
							<div class="type_title"><strong>' . TEXT_BANNERS_GROUP . '</strong></div>
							<div class="type_value group_val"><strong>' . $group . '</strong></div>
						</div>
						<div class="after">
							<div class="type_title">' . TEXT_BANNERS_TYPE . '</div>
							<div class="type_value">
								<select name="banner_type" class="form-control">
									<option value="banner"' . ($banner_type_array[0] == 'banner' ? ' selected' : '') . '>banner</option>
									<option value="carousel"' . ($banner_type_array[0] == 'carousel' ? ' selected' : '') . '>carousel</option>
									<option value="slider"' . ($banner_type_array[0] == 'slider' ? ' selected' : '') . '>slider</option>
								</select>
							</div>
						</div>
						<div class="after slider_effect"' . ($banner_type_array[0] == 'slider' ? '' : ' style="display:none;"') . '>
							<div class="type_title">' . TEXT_BANNER_EFFECT . '</div>
							<div class="type_value"> ' . tep_draw_pull_down_menu('banner_effect', $banner_effect, ($banner_type_array[1] ? $banner_type_array[1] : ''), 'class="form-control"') . '</div>
						</div>
						<div class="after speed"' . ($banner_type_array[0] == 'banner' || $banner_type_array[0] == '' ? ' style="display:none;"' : '') . '>
							<div class="type_title">' . TEXT_ANIMATED_SPEED . '</div>
							<div class="type_value">' . tep_draw_input_field('animated_speed', ($banner_type_array[2] ? $banner_type_array[2] : ''), 'class="form-control"') . '</div>
						</div>
						<div class="btn-bar">
									<div class="btn-left"><button class="btn btn-cancel" onclick="return closePopup();">' . IMAGE_CANCEL . '</button></div>
									<div class="btn-right"><button class="btn btn-primary">' . IMAGE_SAVE . '</button></div>
							</div>
						</div></form>';
        $html .= '<script type="text/javascript">
						$(document).ready(function(){
							$("select[name=banner_type]").on("change", function() {
								if($(this).val() == "slider"){
									$(".slider_effect, .speed").show();
								}else if($(this).val() == "carousel"){
									$(".slider_effect").hide();
									$(".speed").show();
								}else{
									$(".slider_effect, .speed").hide();
								}
							});
						})
						</script>';

        return $html;
    }

    function actionSavetype()
    {
        $banner_group = tep_db_prepare_input($_POST['banner_group']);
        $banner_type = $_POST['banner_type'] ? tep_db_prepare_input($_POST['banner_type']) : '';
        $banner_effect = $_POST['banner_effect'] ? tep_db_prepare_input($_POST['banner_effect']) : '';
        $animated_speed = $_POST['animated_speed'] ? tep_db_prepare_input($_POST['animated_speed']) : '';
        $sql_data_array = array('banner_type' => $banner_type . ';' . $banner_effect . ';' . $animated_speed);
        tep_db_perform(TABLE_BANNERS_NEW, $sql_data_array, 'update', "banners_group = '" . tep_db_input($banner_group) . "'");
    }

}
