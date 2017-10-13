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

use common\classes\platform;
use common\classes\currencies;
use common\helpers\Acl;
use Yii;
use \yii\helpers\Html;

class PlatformsController extends Sceleton {

    public $acl = ['BOX_HEADING_FRONENDS'];
    
    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('fronends', 'platforms');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('platforms/index'), 'title' => HEADING_TITLE);
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'index')) {
            $ext::index();
        }
        //$this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('platforms/edit').'" class="create_item addprbtn"><i class="icon-tag"></i>'.TEXT_CREATE_NEW_PLATFORM.'</a>';
        $this->view->headingTitle = HEADING_TITLE;
        $this->view->groupsTable = array(
            array(
                'title' => TABLE_HEADING_PLATFORM_NAME,
                'not_important' => 1
            ),
            array(
                'title' => TABLE_HEADING_PLATFORM_URL,
                'not_important' => 1
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 1
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)$_GET['row'];
        
        return $this->render('index');

    }

    public function actionList() {
        global $languages_id;
        $draw   = Yii::$app->request->get( 'draw', 1 );
        $start  = Yii::$app->request->get( 'start', 0 );
        $length = Yii::$app->request->get( 'length', 10 );

        $responseList = array();
        if( $length == -1 ) $length = 10000;
        $query_numrows = 0;

        //TODO search
        if( isset( $_GET['search']['value'] ) && tep_not_null( $_GET['search']['value'] ) ) {
            $keywords = tep_db_input( tep_db_prepare_input( $_GET['search']['value'] ) );
            $search_condition = " where platform_name like '%" . $keywords . "%' ";

        } else {
            $search_condition = " where 1";
        }

        if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
            switch( $_GET['order'][0]['column'] ) {
                case 0:
                    $orderBy = "platform_name " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] ));
                    break;
                case 1:
                    $orderBy = "sort_order " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] )).", platform_id ";
                    break;
                default:
                    $orderBy = "sort_order, platform_name";
                    break;
            }
        } else {
            $orderBy = "sort_order, platform_name";
        }
        
        $query_show = 0;
        $groups_query_raw = "select * from " . TABLE_PLATFORMS . $search_condition . " order by " . $orderBy;
        $current_page_number = ( $start / $length ) + 1;
        $_split              = new \splitPageResults( $current_page_number, $length, $groups_query_raw, $query_numrows, 'platform_id' );
        $groups_query     = tep_db_query( $groups_query_raw );
        while( $groups = tep_db_fetch_array( $groups_query ) ) {

            $statement = '';
            if (!\common\helpers\Acl::checkExtension('AdditionalPlatforms', 'allowed')) {
                if ($groups['platform_id'] != 1) {
                    $statement = ' dis_module';
                    $groups['status'] = 0;
                }
            }
            Yii::$app->get('platform')->config($groups['platform_id']);
            
            $status = '<input type="checkbox" value="'. $groups['platform_id'] . '" name="status" class="check_on_off" ' . ((int) $groups['status'] > 0 ? 'checked="checked"' : '') . '>';
            
            $responseList[] = array(
                '<div class="handle_cat_list'.$statement.'"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                  $groups['platform_name'] .
                  '<input class="cell_identify" type="hidden" value="' . $groups['platform_id'] . '">'.
                  '<input class="cell_type" type="hidden" value="top">'.
                '</div></div>',
                '<a target="_blank" href="'.tep_catalog_href_link('').'">'.$groups['platform_url'].'</a>',
                $status,
            );
            $query_show++;
        }

        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => $query_numrows,
            'recordsFiltered' => $query_show,
            'data'            => $responseList
        );
        echo json_encode( $response );
    }
    
    public function actionSwitchStatus() {
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'switchStatus')) {
            $ext::switchStatus();
        }
    }

    public function actionItemPreedit()
    {
        $this->layout = false;

        global $languages_id, $language;

        \common\helpers\Translation::init('admin/platforms');
        
        $item_id   = (int) Yii::$app->request->post( 'item_id' );
        
        $groups_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
        $groups = tep_db_fetch_array($groups_query);
         
        if (!is_array($groups)) {
            die("");
        }     
                
        $mInfo = new \objectInfo($groups);
        
        echo '<div class="or_box_head">' . $mInfo->platform_name . '</div>';
        echo '<div class="row_or"><div>' . TEXT_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($mInfo->date_added) . '</div></div>';
        if (tep_not_null($mInfo->last_modified)){
                echo '<div class="row_or"><div>' . TEXT_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($mInfo->last_modified) . '</div></div>';
        }
        $multiplatform = '';
        if ( count(platform::getCategoriesAssignList())>1 ) {
            $multiplatform .= '<a href="' . Yii::$app->urlManager->createUrl(['platforms/edit-catalog', 'id' => $item_id]) . '" class="btn btn-edit btn-process-order js-open-tree-popup">'.BUTTON_ASSIGN_CATEGORIES_PRODUCTS.'</a>';
        }
        $statement = true;
        if (!\common\helpers\Acl::checkExtension('AdditionalPlatforms', 'allowed')) {
            if ($item_id != 1) {
                $statement = false;
            }
        }
        if ($statement) {
        echo '<div class="btn-toolbar btn-toolbar-order">
            <a href="' . Yii::$app->urlManager->createUrl(['platforms/edit', 'id' => $item_id]) . '" class="btn btn-edit btn-primary btn-process-order ">'.IMAGE_EDIT.'</a>
            '.($groups['is_default'] || in_array($item_id, [4, 5, 6]) ? '':('<button onclick="return deleteItemConfirm(' . $item_id . ')" class="btn btn-delete btn-no-margin btn-process-order ">'.IMAGE_DELETE.'</button>')).'
            '.$multiplatform.'
            <a href="' . Yii::$app->urlManager->createUrl(['platforms/configuration', 'platform_id' => $item_id]) . '" class="btn btn-edit btn-primary btn-process-order ">'.BOX_HEADING_CONFIGURATION.'</a>
        </div>';
        }
        
    }

    public function actionEdit()
    {
        global $languages_id;
        \common\helpers\Translation::init('admin/platforms');
        
        
        $days = [
            0 => TEXT_EVERYDAY,
            1 => TEXT_MONDAY,
            2 => TEXT_TUESDAY,
            3 => TEXT_WEDNESDAY,
            4 => TEXT_THURSDAY,
            5 => TEXT_FRIDAY,
            6 => TEXT_SATURDAY,
            7 => TEXT_SUNDAY,
        ];

        $item_id = 1;
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'edit')) {
            $item_id = $ext::edit();
        }
        
        $check_watermark_query = tep_db_query("SELECT * FROM " . TABLE_PLATFORMS_WATERMARK . " WHERE platform_id=" . (int)$item_id);
        if ( tep_db_num_rows($check_watermark_query) > 0 ) {
            $check_watermark = tep_db_fetch_array($check_watermark_query);
            unset($check_watermark['platform_id']);
            $check_watermark['watermark_status'] = $check_watermark['status'];
            unset($check_watermark['status']);
            $watermark = $check_watermark;
        } else {
            $watermark = [
                'watermark_status' => 0,
            ];
        }
        
        if ($item_id > 0) {
            $groups_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
            $groups = tep_db_fetch_array($groups_query);
            $pInfo = new \objectInfo(array_merge($groups, $watermark));
        } else {
            $pInfo = new \objectInfo($watermark);
        }
       
        $address_query = tep_db_query("select ab.*, if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as entry_state, c.countries_name  from " . TABLE_PLATFORMS_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where platform_id = '" . (int) $item_id . "' ");
        $d = tep_db_fetch_array($address_query);
        if (!isset($d['entry_country_id'])) {
            $d['entry_country_id'] = STORE_COUNTRY;
        }
        $addresses = new \objectInfo($d);
        
        $open_hours = [];
        $open_hours_query = tep_db_query("select * from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int) $item_id . "' ");
        while ($d = tep_db_fetch_array($open_hours_query)) {
            if (isset($d['open_days'])) {
                $d['open_days'] = explode(",", $d['open_days']);
            }
            $open_hours[] = new \objectInfo($d);
        }
        if (count($open_hours) == 0) {
            $open_hours[] = new \objectInfo([]);
        }
        
        $cut_off_times = [];
        $cut_off_times_query = tep_db_query("select * from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where platform_id = '" . (int) $item_id . "' ");
        while ($d = tep_db_fetch_array($cut_off_times_query)) {
            if (isset($d['cut_off_times_days'])) {
                $d['cut_off_times_days'] = explode(",", $d['cut_off_times_days']);
            }
            $cut_off_times[] = new \objectInfo($d);
        }
        if (count($cut_off_times) == 0) {
            $cut_off_times[] = new \objectInfo([]);
        }
        
        $theme_array = [];
        $pInfo->theme_id = 0;
        
        $theme = tep_db_fetch_array(tep_db_query("select t.* from " . TABLE_PLATFORMS_TO_THEMES . " AS p2t INNER JOIN " . TABLE_THEMES . " as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)$item_id));

        if (isset($theme['id'])) {
            $pInfo->theme_id = $theme['id'];
            $theme_array[] = $theme;
        }
        
        $text_new_or_edit = ($item_id == 0) ? TEXT_INFO_HEADING_NEW_PLATFORM : TEXT_INFO_HEADING_EDIT_PLATFORM;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('platforms/'), 'title' => $text_new_or_edit . ' ' . $pInfo->platforms_name);
        $this->selectedMenu = array('fronends', 'platforms');
        
        if (Yii::$app->request->isPost) {
            $this->layout = false;
        }
        $this->view->showState = (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible');

        $have_more_then_one_platform = true;
        if ( tep_db_num_rows(tep_db_query("select platform_id from ".TABLE_PLATFORMS.""))<2 ) {
            $have_more_then_one_platform = false;
            if ( !$pInfo->is_default ) $pInfo->is_default = 1;
        }
        $checkbox_default_platform_attr = array();
        if ($pInfo->is_default) {
            // disable off for default - only on available
            $checkbox_default_platform_attr['readonly'] = 'readonly';
        }
        return $this->render('edit.tpl',
          [
            'pInfo' => $pInfo,
            'addresses' => $addresses,
            'open_hours' => $open_hours, 'count_open_hours' => count($open_hours),
            'cut_off_times' => $cut_off_times, 'count_cut_off_times' => count($cut_off_times),
            'days' => $days,
            'theme_array' => $theme_array,
            'checkbox_default_platform_attr' => $checkbox_default_platform_attr,
            'have_more_then_one_platform' => $have_more_then_one_platform,
            'languages' => \common\helpers\Language::get_languages(),
            'platform_languages' => explode(",",strtolower($pInfo->defined_languages)),
            'currencies' => new currencies,
            'platform_currencies' => explode(",",$pInfo->defined_currencies),
          ]);
    }

    public function actionSubmit()
    {
        \common\helpers\Translation::init('admin/properties');

        $item_id = 1;
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'edit')) {
            $item_id = $ext::edit();
        }
        
        $platform_owner = tep_db_prepare_input(Yii::$app->request->post('platform_owner'));
        $platform_name = tep_db_prepare_input(Yii::$app->request->post('platform_name'));
        $platform_url = tep_db_prepare_input(Yii::$app->request->post('platform_url'));
        $platform_url = rtrim($platform_url,'/');
        $ssl_enabled = intval(Yii::$app->request->post('ssl_enabled',0));
        $platform_url_secure = tep_db_prepare_input(Yii::$app->request->post('platform_url_secure'));
        $platform_url_secure = rtrim($platform_url_secure,'/');
        $use_social_login = intval(Yii::$app->request->post('use_social_login',0));

        $platform_images_cdn_status = Yii::$app->request->post('platform_images_cdn_status');
        $platform_images_cdn_url = Yii::$app->request->post('platform_images_cdn_url');
        
        $platform_email_address = tep_db_prepare_input(Yii::$app->request->post('platform_email_address'));
        $platform_email_from = tep_db_prepare_input(Yii::$app->request->post('platform_email_from'));
        $platform_email_extra = tep_db_prepare_input(Yii::$app->request->post('platform_email_extra'));
        $platform_telephone = tep_db_prepare_input(Yii::$app->request->post('platform_telephone'));
        $platform_landline = tep_db_prepare_input(Yii::$app->request->post('platform_landline'));

        $planguages = [];            
        if (is_array(Yii::$app->request->post('planguages'))){
          foreach(Yii::$app->request->post('planguages') as $l){
            $planguages[] = $l;
          }
        }
        $default_language = strtolower(tep_db_prepare_input(Yii::$app->request->post('default_language')));
        
        $pcurrencies = [];            
        if (is_array(Yii::$app->request->post('pcurrencies'))){
          foreach(Yii::$app->request->post('pcurrencies') as $c){
            $pcurrencies[] = $c;
          }
        }
        $default_currency = strtoupper(tep_db_prepare_input(Yii::$app->request->post('default_currency')));
        
        $is_default = false;
        if ( Yii::$app->request->post('present_is_default') ) {
            $is_default = Yii::$app->request->post('is_default', 0);
        }
        $status = (int) Yii::$app->request->post('status');

        $is_virtual = (int) Yii::$app->request->post('is_virtual');
        if ($is_virtual == 0) {
            $is_default_contact = 0;
            $is_default_address = 0;
            $is_default = false;
        } else {
            $is_default_contact = (int) Yii::$app->request->post('is_default_contact');
            $is_default_address = (int) Yii::$app->request->post('is_default_address');
        }
        
        $this->layout = false;
        $error = false;
        $message = '';
        $script = '';
        $delete_btn = '';

        $messageType = 'success';

        $entry_company = tep_db_prepare_input(Yii::$app->request->post('entry_company'));
        $entry_company_vat = tep_db_prepare_input(Yii::$app->request->post('entry_company_vat'));
        $entry_company_reg_number = tep_db_prepare_input(Yii::$app->request->post('entry_company_reg_number'));
        $entry_postcode = tep_db_prepare_input(Yii::$app->request->post('entry_postcode'));
        $entry_street_address = tep_db_prepare_input(Yii::$app->request->post('entry_street_address'));
        $entry_suburb = tep_db_prepare_input(Yii::$app->request->post('entry_suburb'));
        $entry_city = tep_db_prepare_input(Yii::$app->request->post('entry_city'));
        $entry_state = tep_db_prepare_input(Yii::$app->request->post('entry_state'));
        $entry_country_id = tep_db_prepare_input(Yii::$app->request->post('entry_country_id'));
        $address_book_ids = tep_db_prepare_input(Yii::$app->request->post('platforms_address_book_id'));
        $entry_zone_id = [];

        $entry_post_code_error = false;
        $entry_street_address_error = false;
        $entry_city_error = false;
        $entry_country_error = false;
        $entry_state_error = false;

        if ($is_default_address == 1) {
            $address_book_ids = [];
        }
        foreach ($address_book_ids as $address_book_key => $address_book_id) {

            $skipAddress = false;

            /*if (strlen($entry_postcode[$address_book_key]) < ENTRY_POSTCODE_MIN_LENGTH) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_post_code_error = true;
                }
                $skipAddress = true;
            }

            if (strlen($entry_street_address[$address_book_key]) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_street_address_error = true;
                }
                $skipAddress = true;
            }

            if (strlen($entry_city[$address_book_key]) < ENTRY_CITY_MIN_LENGTH) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_city_error = true;
                }
                $skipAddress = true;
            }

            if ((int)$entry_country_id[$address_book_key] == 0) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_country_error = true;
                }
                $skipAddress = true;
            }*/

            if ($address_book_id == 0 && $skipAddress) {
                unset($address_book_ids[$address_book_key]);
                continue;
            }

            if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
                if ($entry_country_error == true) {
                    //$entry_state_error = true;
                } else {
                    $entry_zone_id[$address_book_key] = 0;
                    //$entry_state_error = false;
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "'");
                    $check_value = tep_db_fetch_array($check_query);
                    $entry_state_has_zones = ($check_value['total'] > 0);
                    if ($entry_state_has_zones == true) {
                        $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "' and (zone_name like '" . tep_db_input($entry_state[$address_book_key]) . "' or zone_code like '" . tep_db_input($entry_state[$address_book_key]) . "')");
                        if (tep_db_num_rows($zone_query) == 1) {
                            $zone_values = tep_db_fetch_array($zone_query);
                            $entry_zone_id[$address_book_key] = $zone_values['zone_id'];
                        } /*else {
                            $error = true;
                            $entry_state_error = true;
                        }*/
                    } else {

                        /*if ($entry_state[$address_book_key] == false) {
                            $error = true;
                            $entry_state_error = true;
                        }*/
                    }
                }
            }
        }
            
        
        $platforms_cut_off_times_ids = Yii::$app->request->post('platforms_cut_off_times_id');
        $platforms_cut_off_times_keys = Yii::$app->request->post('platforms_cut_off_times_key');
        $cut_off_times_today = Yii::$app->request->post('cut_off_times_today');
        $cut_off_times_next_day = Yii::$app->request->post('cut_off_times_next_day');
        
        
        $platforms_open_hours_ids = Yii::$app->request->post('platforms_open_hours_id');
        $platforms_open_hours_keys = Yii::$app->request->post('platforms_open_hours_key');
        $open_time_from = Yii::$app->request->post('open_time_from');
        $open_time_to = Yii::$app->request->post('open_time_to');
        
        if ($is_virtual == 1) {
            $platforms_open_hours_ids = [];
            $platforms_cut_off_times_ids = [];
        }
        
        if( $error === FALSE ) {
            $pre_update_default_platform_id = \common\classes\platform::defaultId();
            $sql_data_array = [
                'platform_owner' => $platform_owner,
                'platform_name' => $platform_name,
                'platform_url' => $platform_url,
                'platform_url_secure' => $platform_url_secure,
                'ssl_enabled' => $ssl_enabled,
                'platform_images_cdn_status' => $platform_images_cdn_status,
                'platform_images_cdn_url' => $platform_images_cdn_url,
                'use_social_login' => $use_social_login,
                'platform_email_address' => $platform_email_address,
                'platform_email_from' => $platform_email_from,
                'platform_email_extra' => $platform_email_extra,
                'platform_telephone' => $platform_telephone,
                'platform_landline' => $platform_landline,
                'is_virtual' => $is_virtual,
                'is_default_contact' => $is_default_contact,
                'is_default_address' => $is_default_address,
                'status' => $status,
                'defined_languages' => strtolower(implode(",", $planguages)),
                'defined_currencies' => implode(",", $pcurrencies),
                'default_language' => $default_language,
                'default_currency' => $default_currency,
            ];
            
            
            if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'save')) {
                $sql_data_array = array_merge($sql_data_array, $ext::save());
            }
                    
            if ( $is_default!==false ) {
                $sql_data_array['is_default'] = $is_default;
            }
            $platform_updated = false;
            if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'save')) {
                $ext::save($item_id, $planguages, $pcurrencies, $sql_data_array);
            } else {
                $message = "Item updated";
                $sql_data_array['last_modified'] = 'now()';
                tep_db_perform(TABLE_PLATFORMS, $sql_data_array, 'update', "platform_id = '" . $item_id . "'");
                $platform_updated = true;
            }
            if ( $is_default ) {
                tep_db_query(
                  "UPDATE ".TABLE_PLATFORMS." SET is_default=0 ".
                  "WHERE platform_id!='".(int)$item_id."'"
                );
            }

            $activeaddress_book_ids = [];
            foreach ($address_book_ids as $address_book_key => $address_book_id) {
                if ($entry_zone_id[$address_book_key] > 0)
                    $entry_state[$address_book_key] = '';

                $sql_data_array = [
                    'entry_street_address' => $entry_street_address[$address_book_key],
                    'entry_postcode' => $entry_postcode[$address_book_key],
                    'entry_city' => $entry_city[$address_book_key],
                    'entry_country_id' => $entry_country_id[$address_book_key],
                    'entry_company_reg_number' => $entry_company_reg_number[$address_book_key],
                    'is_default' => 1
                ];

                $sql_data_array['entry_company'] = $entry_company[$address_book_key];
                $sql_data_array['entry_suburb'] = $entry_suburb[$address_book_key];
                $sql_data_array['entry_company_vat'] = $entry_company_vat[$address_book_key];
                
                if ($entry_zone_id[$address_book_key] > 0) {
                    $sql_data_array['entry_zone_id'] = $entry_zone_id[$address_book_key];
                    $sql_data_array['entry_state'] = '';
                } else {
                    $sql_data_array['entry_zone_id'] = '0';
                    $sql_data_array['entry_state'] = $entry_state[$address_book_key];
                }
                

                if ((int)$address_book_id > 0) {
                    tep_db_perform(TABLE_PLATFORMS_ADDRESS_BOOK, $sql_data_array, 'update', "platform_id = '" . (int) $item_id . "' and platforms_address_book_id = '" . (int) $address_book_id . "'");
                    $activeaddress_book_ids[] = $address_book_id;
                } else {
                    tep_db_perform(TABLE_PLATFORMS_ADDRESS_BOOK, array_merge($sql_data_array, array('platform_id' => $item_id)));
                    $new_customers_address_id = tep_db_insert_id();
                    $activeaddress_book_ids[] = $new_customers_address_id;
                }


            }
            if (count($activeaddress_book_ids) > 0) {
                tep_db_query("delete from " . TABLE_PLATFORMS_ADDRESS_BOOK . " where platform_id = '" . (int) $item_id . "' and platforms_address_book_id NOT IN (" . implode(", ", $activeaddress_book_ids) . ")");
            }
            
            if ($is_default_address == 1) {
                
            }
            
            $active_open_hours_ids = [];
            foreach ($platforms_open_hours_ids as $platforms_open_hours_key => $platforms_open_hours_id) {
                
                $open_days = Yii::$app->request->post('open_days_' . $platforms_open_hours_keys[$platforms_open_hours_key]);

                $sql_data_array = [
                    'open_days' => implode(",", $open_days),
                    'open_time_from' => $open_time_from[$platforms_open_hours_key],
                    'open_time_to' => $open_time_to[$platforms_open_hours_key],
                ];
                if ((int)$platforms_open_hours_id > 0) {
                    tep_db_perform(TABLE_PLATFORMS_OPEN_HOURS, $sql_data_array, 'update', "platform_id = '" . (int) $item_id . "' and platforms_open_hours_id = '" . (int) $platforms_open_hours_id . "'");
                    $active_open_hours_ids[] = $platforms_open_hours_id;
                } else {
                    tep_db_perform(TABLE_PLATFORMS_OPEN_HOURS, array_merge($sql_data_array, array('platform_id' => $item_id)));
                    $new_open_hours_id = tep_db_insert_id();
                    $active_open_hours_ids[] = $new_open_hours_id;
                }
            }
            if (count($active_open_hours_ids) > 0) {
                tep_db_query("delete from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int) $item_id . "' and platforms_open_hours_id NOT IN (" . implode(", ", $active_open_hours_ids) . ")");
            }
            
            $active_cut_off_times_ids = [];
            if (is_array($platforms_cut_off_times_ids)) {
                foreach ($platforms_cut_off_times_ids as $platforms_cut_off_times_key => $platforms_cut_off_times_id) {
                    $cut_off_times_days = Yii::$app->request->post('cut_off_times_days_' . $platforms_cut_off_times_keys[$platforms_cut_off_times_key]);
                    if (!is_array($cut_off_times_days)) {
                        $cut_off_times_days = [];
                    }
                    $sql_data_array = [
                        'cut_off_times_days' => implode(",", $cut_off_times_days),
                        'cut_off_times_today' => $cut_off_times_today[$platforms_cut_off_times_key],
                        'cut_off_times_next_day' => $cut_off_times_next_day[$platforms_cut_off_times_key],
                    ];

                    if ((int)$platforms_cut_off_times_id > 0) {
                        tep_db_perform(TABLE_PLATFORMS_CUT_OFF_TIMES, $sql_data_array, 'update', "platform_id = '" . (int) $item_id . "' and platforms_cut_off_times_id = '" . (int) $platforms_cut_off_times_id . "'");
                        $active_cut_off_times_ids[] = $platforms_cut_off_times_id;
                    } else {
                        tep_db_perform(TABLE_PLATFORMS_CUT_OFF_TIMES, array_merge($sql_data_array, array('platform_id' => $item_id)));
                        $active_cut_off_times_ids[] = tep_db_insert_id();
                    }
                }
            }
            if (count($active_cut_off_times_ids) > 0) {
                tep_db_query("delete from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where platform_id = '" . (int) $item_id . "' and platforms_cut_off_times_id NOT IN (" . implode(", ", $active_cut_off_times_ids) . ")");
            }
            
            $theme_id = (int) Yii::$app->request->post('theme_id');
            tep_db_query("delete from " . TABLE_PLATFORMS_TO_THEMES . " where platform_id = '" . (int) $item_id . "'");
            if ($theme_id > 0) {
                $sql_data_array = [
                    'platform_id' => $item_id,
                    'theme_id' => $theme_id,
                    'is_default' => 1,
                ];
                tep_db_perform(TABLE_PLATFORMS_TO_THEMES, $sql_data_array);
            }

            if (!$platform_updated) {
                $get_default_config_keys_r = tep_db_query(
                        "SELECT * " .
                        "FROM " . TABLE_PLATFORMS_CONFIGURATION . " " .
                        "WHERE platform_id='" . (int) $pre_update_default_platform_id . "' " .
                        "  AND ( " .
                        "        configuration_key IN ('DD_MODULE_ORDER_TOTAL_SORT', 'DD_MODULE_PAYMENT_SORT', 'DD_MODULE_SHIPPING_SORT') " .
                        "        OR configuration_key LIKE 'MODULE\_ORDER\_TOTAL\_%' " .
                        "        OR configuration_key LIKE 'MODULE\_SHIPPING\_%' " .
                        "        OR configuration_key LIKE 'MODULE\_PAYMENT\_%' " .
                        "  )"
                );
                if (tep_db_num_rows($get_default_config_keys_r) > 0) {
                    while ($default_config_key = tep_db_fetch_array($get_default_config_keys_r)) {
                        unset($default_config_key['configuration_id']);
                        $default_config_key['platform_id'] = $item_id;
                        tep_db_perform(TABLE_PLATFORMS_CONFIGURATION, $default_config_key);
                    }
                }
                if ($item_id != $pre_update_default_platform_id) {
                    tep_db_query("DELETE FROM " . TABLE_VISIBILITY_AREA . " WHERE platform_id='" . (int) $item_id . "'");
                    $get_data_r = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY_AREA . " WHERE platform_id='" . (int) $pre_update_default_platform_id . "' ");
                    if (tep_db_num_rows($get_data_r) > 0) {
                        while ($data = tep_db_fetch_array($get_data_r)) {
                            $data['platform_id'] = (int) $item_id;
                            tep_db_perform(TABLE_VISIBILITY_AREA, $data);
                        }
                    }
                }
            }

            $sql_data_array = [
                'platform_id' => (int)$item_id,
                'status' => (int)Yii::$app->request->post('watermark_status'),
                'top_left_watermark30' => Yii::$app->request->post('top_left_watermark30'),
                'top_watermark30' => Yii::$app->request->post('top_watermark30'),
                'top_right_watermark30' => Yii::$app->request->post('top_right_watermark30'),
                'left_watermark30' => Yii::$app->request->post('left_watermark30'),
                'watermark30' => Yii::$app->request->post('watermark30'),
                'right_watermark30' => Yii::$app->request->post('right_watermark30'),
                'bottom_left_watermark30' => Yii::$app->request->post('bottom_left_watermark30'),
                'bottom_watermark30' => Yii::$app->request->post('bottom_watermark30'),
                'bottom_right_watermark30' => Yii::$app->request->post('bottom_right_watermark30'),
                
                'top_left_watermark170' => Yii::$app->request->post('top_left_watermark170'),
                'top_watermark170' => Yii::$app->request->post('top_watermark170'),
                'top_right_watermark170' => Yii::$app->request->post('top_right_watermark170'),
                'left_watermark170' => Yii::$app->request->post('left_watermark170'),
                'watermark170' => Yii::$app->request->post('watermark170'),
                'right_watermark170' => Yii::$app->request->post('right_watermark170'),
                'bottom_left_watermark170' => Yii::$app->request->post('bottom_left_watermark170'),
                'bottom_watermark170' => Yii::$app->request->post('bottom_watermark170'),
                'bottom_right_watermark170' => Yii::$app->request->post('bottom_right_watermark170'),
                
                'top_left_watermark300' => Yii::$app->request->post('top_left_watermark300'),
                'top_watermark300' => Yii::$app->request->post('top_watermark300'),
                'top_right_watermark300' => Yii::$app->request->post('top_right_watermark300'),
                'left_watermark300' => Yii::$app->request->post('left_watermark300'),
                'watermark300' => Yii::$app->request->post('watermark300'),
                'right_watermark300' => Yii::$app->request->post('right_watermark300'),
                'bottom_left_watermark300' => Yii::$app->request->post('bottom_left_watermark300'),
                'bottom_watermark300' => Yii::$app->request->post('bottom_watermark300'),
                'bottom_right_watermark300' => Yii::$app->request->post('bottom_right_watermark300'),
            ];
            $check_watermark_query = tep_db_query("SELECT * FROM " . TABLE_PLATFORMS_WATERMARK . " WHERE platform_id=" . (int)$item_id);
            if ( tep_db_num_rows($check_watermark_query) > 0 ) {
                $prev_data = tep_db_fetch_array($check_watermark_query);
                if ( $prev_data['watermark30']!=$sql_data_array['watermark30'] ) {
                  \common\classes\Images::cacheKeyInvalidateByWatermark($prev_data['watermark30'],$item_id);
                }
                if ( $prev_data['watermark170']!=$sql_data_array['watermark170'] ) {
                  \common\classes\Images::cacheKeyInvalidateByWatermark($prev_data['watermark170'],$item_id);
                }
                if ( $prev_data['watermark300']!=$sql_data_array['watermark300'] ) {
                  \common\classes\Images::cacheKeyInvalidateByWatermark($prev_data['watermark300'],$item_id);
                }
                tep_db_perform(TABLE_PLATFORMS_WATERMARK, $sql_data_array, 'update', 'platform_id=' . (int)$item_id);
            } else {
                $sql_data_array['platform_id'] = (int)$item_id;
                tep_db_perform(TABLE_PLATFORMS_WATERMARK, $sql_data_array);
            }

            if ( (int)$item_id>0 ) {
                tep_db_query(
                    "INSERT IGNORE INTO " . TABLE_PLATFORMS_CATEGORIES . " (platform_id, categories_id) " .
                    "VALUES('" . (int)$item_id . "', 0)"
                );
            }

        }

        if( $error === TRUE ) {
            $messageType = 'warning';

            if( $message == '' ) $message = WARN_UNKNOWN_ERROR;
        }

        ?>
        <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>   
                    </div>  
                    <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div> 
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
            

    <?php
        echo '<script>location.replace("'. Yii::$app->urlManager->createUrl(['platforms/edit', 'id' => $item_id]) .'");</script>';
        die();
        return $this->actionEdit();
    }

    public function actionConfirmitemdelete()
    {
        \common\helpers\Translation::init('admin/properties');

        $this->layout = false;

        $item_id   = (int) Yii::$app->request->post( 'item_id' );


        $message   = $name = $title = '';
        $heading   = array();
        $contents  = array();
        $parent_id = 0;


       $groups_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
        $groups = tep_db_fetch_array($groups_query);
        $pInfo = new \objectInfo($groups);


        $heading[]  = array( 'text' => '<b>' . TEXT_INFO_HEADING_DELETE_PLATFORM . '</b>' );
        $contents[] = array( 'text' => TEXT_INFO_DELETE_PLATFORM_INTRO . '<br>' );
        $contents[] = array( 'text' => '<br><b>' . $pInfo->platform_name . '</b>'  );

        echo tep_draw_form( 'item_delete', FILENAME_INVENTORY, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"' );

        $box = new \box;
        echo $box->infoBox( $heading, $contents );
        ?>
        <p class="btn-toolbar">
            <?php
                echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
                echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

                echo tep_draw_hidden_field( 'item_id', $item_id );
            ?>
        </p>
        </form>
    <?php
    }

    public function actionItemdelete()
    {
        $this->layout = false;

        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $messageType = 'success';
        $message     = TEXT_INFO_DELETED;

        $check_is_default = tep_db_fetch_array(tep_db_query(
          "SELECT COUNT(*) AS c FROM ".TABLE_PLATFORMS." WHERE is_default=1 AND  platform_id = '" . (int)$item_id . "'"
        ));
        if ( $check_is_default['c'] ) {
            
        }else {

            tep_db_query("delete from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_ADDRESS_BOOK . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_TO_THEMES . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_PLATFORMS_CATEGORIES . " where platform_id = '" . (int)$item_id . "'");
            //TODO: 
            // select plp.products_id, count(plp.platform_id) as used_count from platforms_products plp left join platforms_products p on p.products_id=plp.products_id where plp.platform_id=1 group by plp.products_id having used_count=1;

            tep_db_query("delete from " . TABLE_PLATFORMS_PRODUCTS . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_INFORMATION . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_BANNERS_TO_PLATFORM . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_BANNERS_LANGUAGES . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_META_TAGS . " where platform_id = '" . (int)$item_id . "'");

            \common\classes\Images::cacheKeyInvalidateByPlatformId((int)$item_id);
        }
        ?>
        <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>  
                    </div>  
                    <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div>   
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
            

        <p class="btn-toolbar">
            <?php
                echo '<input type="button" class="btn btn-primary" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
            ?>
        </p>
    <?php
    }
	public function actionAddtheme(){
		$this->layout = false;
		$results = tep_db_query("select * from " . TABLE_THEMES);
		$tInfo = array();
		while($results_array = tep_db_fetch_array ($results)){
			$tInfo[] = array(
				'id'=> $results_array['id'],
				'theme_name'=> $results_array['theme_name'],
				'title'=> $results_array['title'],
				'description'=> $results_array['description']
			);
		}
		return $this->render('addtheme.tpl', ['results' => $tInfo]);
	}

  public function actionEditCatalog()
  {
      \common\helpers\Translation::init('admin/platforms');

      $platform_id   = (int) Yii::$app->request->get('id');

      $this->layout = false;

      $assigned = $this->get_assigned_catalog($platform_id, true);

      $tree_init_data = $this->load_tree_slice($platform_id,0);
      foreach ($tree_init_data as $_idx=>$_data) {
          if ( isset($assigned[$_data['key']]) ){
              $tree_init_data[$_idx]['selected'] = true;
          }
      }

      $selected_data = json_encode($assigned);

      return $this->render('edit-catalog.tpl', [
        'selected_data' => $selected_data,
        'tree_data' => $tree_init_data,
        'tree_server_url' => Yii::$app->urlManager->createUrl(['platforms/load-tree', 'platform_id' => $platform_id]),
        'tree_server_save_url' => Yii::$app->urlManager->createUrl(['platforms/update-catalog-selection', 'platform_id' => $platform_id])
      ]);
  }

  private function get_assigned_catalog($platform_id,$validate=false){
    return \common\helpers\Categories::get_assigned_catalog($platform_id,$validate);
  }

  private function load_tree_slice($platform_id, $category_id){
    return \common\helpers\Categories::load_tree_slice($platform_id, $category_id);
  }

  private function tep_get_category_children(&$children, $platform_id, $categories_id) {
    if ( !is_array($children) ) $children = array();
    foreach($this->load_tree_slice($platform_id, $categories_id) as $item) {
      $key = $item['key'];
      $children[] = $key;
      if ($item['folder']) {
        $this->tep_get_category_children($children, $platform_id, intval(substr($item['key'],1)));
      }
    }
  }

  public function actionLoadTree()
  {
      \common\helpers\Translation::init('admin/platforms');
      $this->layout = false;

      $platform_id = Yii::$app->request->get('platform_id');
      $do = Yii::$app->request->post('do','');

      $response_data = array();

      if ( $do == 'missing_lazy' ) {
        $category_id = Yii::$app->request->post('id');
        $selected = Yii::$app->request->post('selected');
        $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
        $selected_data = json_decode($req_selected_data,true);
        if ( !is_array($selected_data) ) {
          $selected_data = json_decode($selected_data,true);
        }

        if (substr($category_id, 0, 1) == 'c') $category_id = intval(substr($category_id, 1));

        $response_data['tree_data'] = $this->load_tree_slice($platform_id,$category_id);
        foreach( $response_data['tree_data'] as $_idx=>$_data ) {
          $response_data['tree_data'][$_idx]['selected'] = isset($selected_data[$_data['key']]);
        }
        $response_data = $response_data['tree_data'];
      }

      if ( $do == 'update_selected' ) {
        $id = Yii::$app->request->post('id');
        $selected = Yii::$app->request->post('selected');
        $select_children = Yii::$app->request->post('select_children');
        $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
        $selected_data = json_decode($req_selected_data,true);
        if ( !is_array($selected_data) ) {
          $selected_data = json_decode($selected_data,true);
        }

        if ( substr($id,0,1)=='p' ) {
          list($ppid, $cat_id) = explode('_',$id,2);
          if ( $selected ) {
            // check parent categories
            $parent_ids = array((int)$cat_id);
            \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0]);
            foreach( $parent_ids as $parent_id ) {
              if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                $response_data['update_selection']['c'.(int)$parent_id] = true;
                $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
              }
            }
            if ( !isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = true;
              $selected_data[$id] = $id;
            }
          }else{
            if ( isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = false;
              unset($selected_data[$id]);
            }
          }
        }elseif ( substr($id,0,1)=='c' ) {
          $cat_id = (int)substr($id,1);
          if ( $selected ) {
            $parent_ids = array((int)$cat_id);
            \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0]);
            foreach( $parent_ids as $parent_id ) {
              if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                $response_data['update_selection']['c'.(int)$parent_id] = true;
                $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
              }
            }
            if ( $select_children ) {
              $children = array();
              $this->tep_get_category_children($children,$platform_id,$cat_id);
              foreach($children as $child_key){
                if ( !isset($selected_data[$child_key]) ) {
                  $response_data['update_selection'][$child_key] = true;
                  $selected_data[$child_key] = $child_key;
                }
              }
            }
            if ( !isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = true;
              $selected_data[$id] = $id;
            }
          }else{
            $children = array();
            $this->tep_get_category_children($children,$platform_id,$cat_id);
            foreach($children as $child_key){
              if ( isset($selected_data[$child_key]) ) {
                $response_data['update_selection'][$child_key] = false;
                unset($selected_data[$child_key]);
              }
            }
            if ( isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = false;
              unset($selected_data[$id]);
            }
          }
        }

        $response_data['selected_data'] = $selected_data;
      }

      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $response_data;

  }

  function actionUpdateCatalogSelection()
  {
    \common\helpers\Translation::init('admin/platforms');
    $this->layout = false;

    $platform_id = Yii::$app->request->get('platform_id');
    $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
    $selected_data = json_decode($req_selected_data,true);
    if ( !is_array($selected_data) ) {
      $selected_data = json_decode($selected_data,true);
    }

    $assigned = $this->get_assigned_catalog($platform_id);
    $assigned_products = array();
    foreach ( $assigned as $assigned_key ) {
      if ( substr($assigned_key,0,1)=='p' ) {
        $pid = intval(substr($assigned_key,1));
        $assigned_products[$pid] = $pid;
        unset($assigned[$assigned_key]);
      }
    }
    if (is_array($selected_data)) {
      $selected_products = array();
      foreach( $selected_data as $selection ) {
        if ( substr($selection,0,1)=='p' ) {
          $pid = intval(substr($selection,1));
          $selected_products[$pid] = $pid;
          continue;
        }
        if (isset($assigned[$selection])){
          unset($assigned[$selection]);
        }else{
          if ( substr($selection,0,1)=='c' ) {
            $cat_id = (int)substr($selection, 1);
            tep_db_perform(TABLE_PLATFORMS_CATEGORIES,array(
              'platform_id' => $platform_id,
              'categories_id' => $cat_id,
            ));
            unset($assigned[$selection]);
          }
        }
      }
      foreach( $selected_products as $pid ) {
        if (isset($assigned_products[$pid])) {
          unset($assigned_products[$pid]);
        }else{
          tep_db_perform(TABLE_PLATFORMS_PRODUCTS,array(
            'platform_id' => $platform_id,
            'products_id' => $pid,
          ));
        }
      }
    }

    foreach ($assigned as $clean_key) {
      if ( substr($clean_key,0,1)=='c' ) {
        $cat_id = (int)substr($clean_key, 1);
        tep_db_query(
          "DELETE FROM ".TABLE_PLATFORMS_CATEGORIES." ".
          "WHERE platform_id ='".$platform_id."' AND categories_id = '".$cat_id."' "
        );
        unset($assigned[$clean_key]);
      }
    }
    if ( count($assigned_products)>1000 ) {
      foreach( $assigned_products as $assigned_product_id ) {
        tep_db_query(
          "DELETE FROM ".TABLE_PLATFORMS_PRODUCTS." ".
          "WHERE platform_id ='".$platform_id."' AND products_id = '".$assigned_product_id."' "
        );
      }
    }elseif( count($assigned_products)>0 ){
      tep_db_query(
        "DELETE FROM ".TABLE_PLATFORMS_PRODUCTS." ".
        "WHERE platform_id ='".$platform_id."' AND products_id IN ('".implode("','",$assigned_products)."') "
      );
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    Yii::$app->response->data = array(
      'status' => 'ok'
    );

  }
  
  public function actionDefineFormats(){
      \common\helpers\Translation::init('admin/languages');
      \common\helpers\Translation::init('admin/texts');
      
      exec("locale -a", $output);
            
      if (Yii::$app->request->isPost){
        //echo '<pre>';print_r($_POST);die;
        $id = Yii::$app->request->post('id',0);
        if ($id){
          if (is_array($_POST['configuration_key']) && count($_POST['configuration_key']) > 0){
            foreach($_POST['configuration_key'] as $lang => $data){
              tep_db_query("delete from " . TABLE_PLATFORM_FORMATS . " where platform_id='" . (int)$id . "' and language_id = '" . (int)$lang . "'");
              foreach($data as $key => $value){
                if (!tep_not_null($value) || !isset($_POST['configuration_value'][$lang][$key]) || !tep_not_null($_POST['configuration_value'][$lang][$key])) continue;
                tep_db_query("insert into " . TABLE_PLATFORM_FORMATS . " (configuration_key, configuration_value, platform_id, language_id) values ('" . tep_db_input($value) . "', '" . tep_db_input($_POST['configuration_value'][$lang][$key]) . "', '" . (int)$id . "', '" . (int)$lang . "')");
              }
            }
          }
        }
        $messageType = 'success';
        $message = TEXT_MESSEAGE_SUCCESS;
?>
        <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>   
                    </div>  
                    <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div> 
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
<?php
        return $this->actionEdit();
      } else {
        $id = Yii::$app->request->get('id',0);
      }      

      if (is_array($output) && class_exists('\ResourceBundle')){
        $all_locales = \ResourceBundle::getLocales ('');
        $lList = [];
        foreach($output as $line){
          if (tep_not_null($line)){
            $ex = explode(".", $line);
            if (in_array($ex[0], $all_locales)){
              array_push($lList, ['id' => $ex[0], 'text' => $ex[0]]);
            }
          }
        }
      }
      if (count($lList) == 0 ){$lList[] = ['id'=> 'en_EN', 'text' => 'en_EN'];}    
      
      $l_formats = [];
      $formats_query = tep_db_query("select * from " . TABLE_LANGUAGES_FORMATS . " where 1");
      if (tep_db_num_rows($formats_query)){
        while($row = tep_db_fetch_array($formats_query)){
          $l_formats[] = $row;
        }
      }
      
      $p_formats = [];
      $formats_query = tep_db_query("select * from " . TABLE_PLATFORM_FORMATS . " where platform_id = '" . (int)$id . "'");
      if (tep_db_num_rows($formats_query)){
        while($row = tep_db_fetch_array($formats_query)){
          $p_formats[] = $row;
        }
      }
      tep_db_free_result($formats_query);
    
      return $this->renderAjax('formats.tpl', [
        'languages' => \common\helpers\Language::get_languages(),
        'lList' => $lList,
        'platform_id' => $id,
        'platform_formats' => \yii\helpers\ArrayHelper::map($p_formats, 'configuration_key', 'configuration_value', 'language_id'),      
        'defined_formats' => \yii\helpers\ArrayHelper::map($l_formats, 'configuration_key', 'configuration_value', 'language_id'),      
      ]);
  }

  public function actionSortOrder()
  {
    $moved_id = (int)$_POST['sort_top'];
    $ref_array = (isset($_POST['top']) && is_array($_POST['top']))?array_map('intval',$_POST['top']):array();
    if ( $moved_id && in_array($moved_id, $ref_array) ) {
      // {{ normalize
      $order_counter = 0;
      $order_list_r = tep_db_query(
        "SELECT platform_id, sort_order ".
        "FROM ". TABLE_PLATFORMS ." ".
        "WHERE 1 ".
        "ORDER BY sort_order, platform_name"
      );
      while( $order_list = tep_db_fetch_array($order_list_r) ){
        $order_counter++;
        tep_db_query("UPDATE ".TABLE_PLATFORMS." SET sort_order='{$order_counter}' WHERE platform_id='{$order_list['platform_id']}' ");
      }
      // }} normalize
      $get_current_order_r = tep_db_query(
        "SELECT platform_id, sort_order ".
        "FROM ".TABLE_PLATFORMS." ".
        "WHERE platform_id IN('".implode("','",$ref_array)."') ".
        "ORDER BY sort_order"
      );
      $ref_ids = array();
      $ref_so = array();
      while($_current_order = tep_db_fetch_array($get_current_order_r)){
        $ref_ids[] = (int)$_current_order['platform_id'];
        $ref_so[] = (int)$_current_order['sort_order'];
      }

      foreach( $ref_array as $_idx=>$id ) {
        tep_db_query("UPDATE ".TABLE_PLATFORMS." SET sort_order='{$ref_so[$_idx]}' WHERE platform_id='{$id}' ");
      }

    }
  }
  
  public function actionFileManagerUpload() {
        $text = '';
        if (isset($_FILES['files'])) {
            $path = DIR_FS_CATALOG . 'images/stamp/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $uploadfile = $path . basename($_FILES['files']['name']);

            if (move_uploaded_file($_FILES['files']['tmp_name'], $uploadfile)) {
              \common\classes\Images::cacheKeyInvalidateByWatermark(basename($uploadfile)); // override existing file

              $text = $_FILES['files']['name'];
            }
        }
        echo $text;
    }
    
    public function actionConfiguration() {
        \common\helpers\Translation::init('configuration');

        $platform_id = (int) Yii::$app->request->get('platform_id');

        $formats_query = tep_db_query("select platform_name from " . TABLE_PLATFORMS . " where platform_id = '" . (int) $platform_id . "'");
        $formats = tep_db_fetch_array($formats_query);

        $this->selectedMenu = array('fronends', 'platforms');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('platforms/configuration'), 'title' => BOX_HEADING_CONFIGURATION . "::" . $formats['platform_name']);
        $this->view->headingTitle = BOX_HEADING_CONFIGURATION . "::" . $formats['platform_name'];

        $this->view->adminTable = array(
            array(
                'title' => TEXT_TABLE_TITLE,
                'not_important' => 0
            ),
            array(
                'title' => TEXT_TABLE_VALUE,
                'not_important' => 0
            )
        );

        $filterEntity = [];
        $group_query = tep_db_query("select * from " . TABLE_CONFIGURATION_GROUP . " where visible=1");
        while ($group = tep_db_fetch_array($group_query)) {
            $title = \common\helpers\Translation::getTranslationValue('GROUP_' . $group['configuration_group_id'] . '_TITLE', 'configuration', $languages_id);
            if (tep_not_null($title)) {
                $group['configuration_group_title'] = $title;
            }
            $filterEntity[] = [
                'id' => $group['configuration_group_id'],
                'text' => $group['configuration_group_title'],
            ];
        }

        $this->view->row = (int) $_GET['row'];
        $this->view->filterGroups = tep_draw_pull_down_menu('group_id', $filterEntity, (isset($_GET['group_id']) ? $_GET['group_id'] : 1), 'class="form-control" onchange="return applyFilter();"');

        $this->view->platform_id = $platform_id;

        return $this->render('configuration');
    }

    function actionGetgroupcontent() {
        global $languages_id;

        $this->layout = false;
        $customers_query_numrows = 1;

        $draw = (int) Yii::$app->request->get('draw');
        $start = (int) Yii::$app->request->get('start');
        $length = (int) Yii::$app->request->get('length');

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);

        $groupid = (int) $filter['group_id'];
        $platform_id = (int) $filter['platform_id'];

        $responseList = array();
        $extra_html = '';

        $search = '';
        $search_condition = " where 1 ";
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            if (is_array($_GET['search'])) {
                if (isset($_GET['search']['value'])) {
                    if (trim($_GET['search']['value']) != '') {
                        $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
                        $search_condition = " where (configuration_title like '%" . $keywords . "%' or configuration_description like '%" . $keywords . "%' )";
                    }
                }
            }
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "configuration_title " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "configuration_description " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "sort_order";
                    break;
            }
        } else {
            $orderBy = "sort_order";
        }

        $_query = "select configuration_id, configuration_title, configuration_value, use_function, configuration_key
                   from " . TABLE_PLATFORMS_CONFIGURATION . "

                    $search_condition
                    and configuration_group_id = '" . (int) $groupid . "'
                    and platform_id = '" . (int) $platform_id . "'
                    order by $orderBy ";


        $current_page_number = ( $start / $length ) + 1;
        $db_split = new \splitPageResults($current_page_number, $length, $_query, $configuration_query_numrows, 'configuration_id');

        $configuration_query = tep_db_query($_query);
        while ($configuration = tep_db_fetch_array($configuration_query)) {

            if (tep_not_null($configuration['use_function'])) {
                $use_function = $configuration['use_function'];
                if (preg_match('/->/', $use_function)) {

                    $class_method = explode('->', $use_function);

                    if (!is_object(${$class_method[0]})) {

                        require( DIR_WS_CLASSES . 'currencies.php' );
                        ${$class_method[0]} = new $class_method[0]();
                    }

                    $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
                } else {
                    if (method_exists('backend\models\Configuration', $use_function)) {
                        $cfgValue = call_user_func(array('backend\models\Configuration', $use_function), $configuration['configuration_value']);
                    } else if (function_exists($use_function)) {
                        $cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
                    }
                }
            } else {
                $_t = \common\helpers\Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $configuration['configuration_value'])), 'configuration', $languages_id);
                $_t = (tep_not_null($_t) ? $_t : $configuration['configuration_value']);
                $cfgValue = $_t;
            }

            $cfg_extra_query = tep_db_query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_id = '" . (int) $configuration['configuration_id'] . "'");
            $cfg_extra = tep_db_fetch_array($cfg_extra_query);

            $cInfo_array = array_merge($configuration, $cfg_extra);

            if ($configuration['configuration_key'] == 'STORE_COUNTRY') {
                $cfgValue = \common\helpers\Country::get_country_name($configuration['configuration_value']);
            }

            if ($configuration['configuration_key'] == 'DOWNLOADS_CONTROLLER_ORDERS_STATUS' || $configuration['configuration_key'] == 'AFFILIATE_PAYMENT_ORDER_MIN_STATUS' || $configuration['configuration_key'] == 'VENDOR_PAYMENT_ORDER_MIN_STATUS') {
                $extra_html = \common\helpers\Order::get_status_name($cfgValue);
            } elseif ($configuration['configuration_key'] == 'DEFAULT_USER_GROUP' || $configuration['configuration_key'] == 'DEFAULT_USER_LOGIN_GROUP') {
                $extra_html = \common\helpers\Group::get_user_group_name($cfgValue);
            } else {
                $extra_html = htmlspecialchars($cfgValue);
            }

            if (strip_tags(trim(strtolower($extra_html))) === strip_tags(trim(strtolower($cfgValue))))
                $extra_html = '';

            $title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);
            if (!tep_not_null($title)) {
                $title = $cInfo_array['configuration_title'];
            }

            $responseList[] = array(
                $title . "<input class='cell_identify' type='hidden' value='" . $cInfo_array['configuration_id'] . "' />",
                $cfgValue . "<br/> $extra_html "
            );
        }

        $configuration_query_numrows1 = 0;

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $configuration_query_numrows + $configuration_query_numrows1,
            'recordsFiltered' => $configuration_query_numrows + $configuration_query_numrows1,
            'data' => $responseList
        );

        echo json_encode($response);
    }

    function actionPreedit() {
        global $access_levels_id;
        $this->layout = FALSE;

        global $languages_id, $language;


        $param_id = (int) Yii::$app->request->post('param_id');
        $group_id = (int) Yii::$app->request->post('group_id');
        $platform_id = (int) Yii::$app->request->post('platform_id');

        $table = TABLE_PLATFORMS_CONFIGURATION;

        $_query = "
                select configuration_id, configuration_title, date_added, configuration_value,configuration_description, use_function, set_function, configuration_key
                 from " . $table . " where configuration_group_id = '$group_id' and configuration_id = '$param_id'";
        $configuration_query = tep_db_query($_query);
        $configuration = tep_db_fetch_array($configuration_query);

        if (!is_array($configuration)) {
            return;
        }

        $title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);
        if (tep_not_null($title)) {
            $configuration['configuration_title'] = $title;
        }
        ?>
                <div class="or_box_head"> <?php echo $configuration['configuration_title']; ?></div>
                <div class="row_or"><?php echo '<div>' . TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($configuration['date_added']); ?></div></div>

                <input name="param_id" type="hidden" value="<?php echo $param_id; ?>">
                <input name="group_id" type="hidden" value="<?php echo $group_id; ?>">

                <div class="btn-toolbar btn-toolbar-order">
                    <button class="btn btn-primary btn-process-order btn-edit" onclick="return editItem( <?php echo "$param_id, $group_id, $platform_id"; ?>)"><?php echo IMAGE_EDIT; ?></button>
        <?php
        if ($access_levels_id == 1) {
            ?>
                              <button class="btn btn-process-order btn-delete" onclick="return deleteTrashedItem( <?php echo "$param_id, $group_id, $platform_id"; ?>)"><?php echo IMAGE_DELETE; ?></button>
            <?php
        }
        ?>
                </div>
        <?php
    }

    function actionGetparam() {
        global $languages_id;
        $this->layout = FALSE;

        $group_id = Yii::$app->request->post('group_id');
        $param_id = Yii::$app->request->post('param_id');

        $_query = "
                select configuration_id, configuration_title, configuration_value,configuration_description, use_function, set_function, configuration_key
                 from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_group_id = '$group_id' and configuration_id = '$param_id'";
        $configuration_query = tep_db_query($_query);
        $configuration = tep_db_fetch_array($configuration_query);

        if (!is_array($configuration))
            die("Wrong data");

        $method = trim(strtolower(substr($configuration['set_function'], 0, strpos($configuration['set_function'], '('))));

        if ((string) $configuration['set_function'] && method_exists('backend\models\Configuration', $method)) {

            $_args = preg_replace("/" . $method . "[\s\(]*/i", "", $configuration['set_function']) . "'" . htmlspecialchars($configuration['configuration_value']) . "', '" . $configuration['configuration_key'] . "'";

            $value_field = call_user_func(array('backend\models\Configuration', $method), $_args);

            /*
              if( strpos( $configuration['set_function'], 'tep_cfg_select_multioption' ) !== FALSE ) {
              eval( '$value_field = ' . $configuration['set_function'] . '"' . htmlspecialchars( $configuration['configuration_value'] ) . '","' . $configuration['configuration_key'] . '");' );
              } else {
              eval( '$value_field = ' . $configuration['set_function'] . '"' . htmlspecialchars( $configuration['configuration_value'] ) . '");' );
              } */
        } else {
            $value_field = tep_draw_input_field('configuration_value', $configuration['configuration_value'], 'class="form-control"');
        }

        $translated_title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);

        echo tep_draw_form(
                'save_param_form', 'configuration/index', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_param_form" onSubmit="return saveParam();"') .
        tep_draw_hidden_field('group_id', $group_id) .
        tep_draw_hidden_field('param_id', $param_id) .
        tep_draw_hidden_field('configuration_key', $configuration['configuration_key']);

        $languages = \common\helpers\Language::get_languages(true);

        $title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);
        if (tep_not_null($title)) {
            $configuration['configuration_title'] = $title;
        }
        $description = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_DESC', 'configuration', $languages_id);
        if (tep_not_null($description)) {
            $configuration['configuration_description'] = $description;
        }
        ?>
        				<div class="or_box_head"><?php echo $configuration['configuration_title']; ?></div>
        				<div class="row_or dataTableContent"><?php echo $configuration['configuration_description']; ?></div>
        				<div class="row_or dataTableContent"><?= $value_field ?></div>
        <?php
        if (!tep_not_null($translated_title)) {
            ?>
                    <br>
                    <div class="row_or dataTableContent">
                        <div class="tab-pane">
                            <div class="tabbable tabbable-custom">
                                <ul class="nav nav-tabs">
            <?php foreach ($languages as $lKey => $lItem) { ?>
                                        <li <?php if ($lKey == 0) { ?> class="active"<?php } ?> ><a href="#tab_2_<?= $lItem['id'] ?>" class="flag-span" data-toggle="tab"><?= $lItem['image']; ?><span><?= $lItem['name'] ?></span></a></li>
            <?php } ?>
                                </ul>
                                <div class="tab-content">
            <?php foreach ($languages as $lKey => $lItem) { ?>
                                        <div class="tab-pane<?php if ($lKey == 0) { ?>  active<?php } ?>" id="tab_2_<?= $lItem['id'] ?>">
                                            <div class="">
                                                <label><?= \common\helpers\Translation::getTranslationValue('TEXT_TITLE', 'admin/main', $lItem['id']) ?></label>
                <?php echo Html::textInput($configuration['configuration_key'] . '_TITLE[' . $lItem['id'] . ']', $configuration['configuration_title']); ?>
                                            </div>
                                            <div class="">
                                                <label><?= \common\helpers\Translation::getTranslationValue('TEXT_DESCRIPTION', 'admin/main', $lItem['id']) ?></label>
                <?php echo Html::textarea($configuration['configuration_key'] . '_DESC[' . $lItem['id'] . ']', $configuration['configuration_description']) ?>
                                            </div>                            
                                        </div>
            <?php } ?>
                                </div>                    
                            </div>                
                        </div>        
                    </div>
        <?php } ?>
        				<div class="btn-toolbar btn-toolbar-order">
        					<button class="btn btn-no-margin"><?php echo IMAGE_UPDATE; ?></button><button class="btn" onclick="return resetStatement()"><?php echo IMAGE_BACK; ?></button>					
        				</div>
                </form>
        <?php
    }

    public function actionDeleteParam() {
        global $languages_id, $language;

        $configuration_id = (int) Yii::$app->request->post('param_id');
        tep_db_query("delete from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_id = $configuration_id");
        if (TRUE) {
            $message = TEXT_PARAM_CHANGE_SUCCESS;
        }

        if ($error === TRUE) {
            $messageType = 'warning';
        }

        if ($message != '') {
            ?>
                        <div class="popup-box-wrap pop-mess">
                        <div class="around-pop-up"></div>
                        <div class="popup-box">
                            <div class="pop-up-close pop-up-close-alert"></div>
                            <div class="pop-up-content">
                                <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                                <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
            <?= $message ?>
                                </div>   
                            </div> 
                            <div class="noti-btn">
                                <div></div>
                                <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                            </div>
                        </div>
                        <script>
                        $('body').scrollTop(0);
                        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                            $(this).parents('.pop-mess').remove();
                            resetStatement();
                        });
                    </script>
                    </div>
            <?php
        }
    }

    function actionSaveparam() {
        global $languages_id, $language;


        $this->layout = FALSE;
        $error = FALSE;
        $message = '';
        $messageType = 'success';
        $html = "";

        $configuration_id = (int) Yii::$app->request->post('param_id');
        $configuration_key = Yii::$app->request->post('configuration_key');
        $configuration_value = Yii::$app->request->post('configuration_value');
        $configuration = Yii::$app->request->post('configuration');

        if (is_array($configuration_value)) {
            $configuration_value = implode(", ", $configuration_value);
            $configuration_value = preg_replace("/, --none--/", "", $configuration_value);
        } elseif (is_array($configuration)) {
            $configuration_value = $configuration[$configuration_key];
        }
        tep_db_query("update " . TABLE_PLATFORMS_CONFIGURATION . "
          set configuration_value = '" . tep_db_input(tep_db_prepare_input($configuration_value)) . "', last_modified = now()
          where configuration_id = '" . $configuration_id . "'");

        if (is_array($_POST)) {
            foreach (tep_db_prepare_input($_POST) as $translation_key => $value) {
                if (strpos($translation_key, 'TITLE') !== false || strpos($translation_key, 'DESC') !== false) {
                    if (is_array($value)) {
                        foreach ($value as $language_id => $translation_value) {
                            \common\helpers\Translation::setTranslationValue($translation_key, 'configuration', $language_id, $translation_value);
                        }
                    } else {
                        list($language_id, $translation_value) = each($value);
                        \common\helpers\Translation::setTranslationValue($translation_key, 'configuration', $language_id, $translation_value);
                    }
                }
            }
        }

        // TODO Check if there were no MySql errors
        if (TRUE) {
            $message = TEXT_PARAM_CHANGE_SUCCESS;
        }

        if ($error === TRUE) {
            $messageType = 'warning';
        }

        if ($message != '') {
            ?>
                        <div class="popup-box-wrap pop-mess">
                        <div class="around-pop-up"></div>
                        <div class="popup-box">
                            <div class="pop-up-close pop-up-close-alert"></div>
                            <div class="pop-up-content">
                                <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                                <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
            <?= $message ?>
                                </div>   
                            </div> 
                            <div class="noti-btn">
                                <div></div>
                                <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                            </div>
                        </div>
                        <script>
                        $('body').scrollTop(0);
                        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                            $(this).parents('.pop-mess').remove();
                            //location.reload();
                            resetStatement();
                        });
                    </script>
                    </div>
                    
            <?= $html ?>
            <?php
        }

        $this->actionGetParam();
    }

} 