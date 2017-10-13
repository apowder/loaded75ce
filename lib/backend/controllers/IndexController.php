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

//use Yii;
//use yii\web\Controller;

/**
 * default controller to handle user requests.
 */
class IndexController extends Sceleton {

    public $test;
    public $stats = array();

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        global $languages_id, $language;
        /* $pageTitle=\Yii::$app->name;
          echo "<pre>";
          print_r($pageTitle);
          echo "</pre>";
          die(); */
        /* echo "<pre>";
          print_r(\Yii::$app->request->baseUrl);
          echo "</pre>";

          $theme =  \Yii::$app->view->theme->baseUrl;
          echo "<pre>";
          print_r($theme);
          echo "</pre>";

          echo "<pre>";
          print_r(\Yii::$app->urlManager->createUrl("index"));
          echo "</pre>";
          die(); */
        //$this->layout = 'main.tpl';

        $this->test = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        //$aa = Yii::app()->setTheme('basic');
        //echo  $this->createUrl('post/read',array('id'=>100));
        //{$Yii->createUrl("post/read",["id"=>100])}

				$lang_var = '';
				
				$languages = \common\helpers\Language::get_languages();
					foreach ($languages as $lKey => $lItem){
            $lang_var .= '<a href="' . \Yii::$app->urlManager->createUrl(['index?language=']) . $lItem['code'] .'">' . $lItem['image_svg'] . '</a>';
          }
				
				$this->topButtons[] = '<div class="admin_top_lang">'. $lang_var . '</div>';
	
        $currencies = new \common\classes\currencies();

        $products = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '1'"));
        $this->stats['products'] = number_format($products['count']);
        $manufacturers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_MANUFACTURERS . " where 1"));
        $this->stats['manufacturers'] = number_format($manufacturers['count']);
        $reviews_confirmed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where status = '1'"));
        $this->stats['reviews_confirmed'] = number_format($reviews_confirmed['count']);
        $reviews_to_confirm = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where status = '0'"));
        $this->stats['reviews_to_confirm'] = number_format($reviews_to_confirm['count']);

        // Today stats
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['today']['customers'] = number_format($customers['count']);
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "'"));
        $this->stats['today']['orders'] = number_format($orders['count']);
        $orders_new = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "'")); // <<<< Processing for now
        $this->stats['today']['orders_new'] = number_format($orders_new['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal'"));
        $this->stats['today']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total'"));
        $this->stats['today']['orders_amount'] = $currencies->format($orders_amount['total_sum']);

        // This week stats
        $date_from = date('Y-m-d H:i:s', strtotime('monday this week'));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['week']['customers'] = number_format($customers['count']);
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "'"));
        $this->stats['week']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "'")); // <<<< Processing for now
        $this->stats['week']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal'"));
        $this->stats['week']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total'"));
        $this->stats['week']['orders_amount'] = $currencies->format($orders_amount['total_sum']);

        // This month stats
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['month']['customers'] = number_format($customers['count']);
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "'"));
        $this->stats['month']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "'")); // <<<< Processing for now
        $this->stats['month']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal'"));
        $this->stats['month']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total'"));
        $this->stats['month']['orders_amount'] = $currencies->format($orders_amount['total_sum']);

        // This year stats
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y')));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['year']['customers'] = number_format($customers['count']);
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "'"));
        $this->stats['year']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "'")); // <<<< Processing for now
        $this->stats['year']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal'"));
        $this->stats['year']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total'"));
        $this->stats['year']['orders_amount'] = $currencies->format($orders_amount['total_sum']);

        // All period stats
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1'"));
        $this->stats['all']['customers'] = number_format($customers['count']);
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where 1"));
        $this->stats['all']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "'")); // <<<< Processing for now
        $this->stats['all']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ot.class = 'ot_subtotal'"));
        $this->stats['all']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ot.class = 'ot_total'"));
        $this->stats['all']['orders_amount'] = $currencies->format($orders_amount['total_sum']);

        $orders_data_array = array('blue' => array(), 'green' => array(), 'red' => array(), 'blue2' => array(), 'green2' => array(), 'red2' => array());
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y') - 1));
        $orders_query = tep_db_query("select year(o.date_purchased) as date_year, month(o.date_purchased) as date_month, count(*) as total_orders, avg(ost.value*o.currency_value) as avg_order_amount, sum(ot.value*o.currency_value) as total_amount from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') left join " . TABLE_ORDERS_TOTAL . " ost on (o.orders_id = ost.orders_id and ost.class = 'ot_subtotal') where o.date_purchased >= '" . tep_db_input($date_from) . "' group by year(o.date_purchased), month(o.date_purchased) order by year(o.date_purchased), month(o.date_purchased)");
        $orders_counts = tep_db_num_rows($orders_query);
        $orders_count = 0;
        while ($orders = tep_db_fetch_array($orders_query)) {
            $orders_count++;
// {{
            if ($orders_count == $orders_counts) { // Last month
                if ((int)$orders['date_month'] != (int)date('m')) { // No orders in current month yet
                    $orders_data_array['blue2'] = $orders_data_array['green2'] = $orders_data_array['red2'] = array();
                    $orders_data_array['blue'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_orders'] . ']';
                    $orders_data_array['green'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['avg_order_amount'] . ']';
                    $orders_data_array['red'][]   = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_amount'] . ']';
                    break;
                } else { // Estimate to full month interval
                    $first_month_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
                    $last_month_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
                    $date_intervals = tep_db_fetch_array(tep_db_query("select timestampdiff(hour, '" . tep_db_input($first_month_date) . "', now()) as cur_month, timestampdiff(hour, '" . tep_db_input($first_month_date) . "', '" . tep_db_input($last_month_date) . "') as full_month"));
                    $orders['total_orders'] *= (int)($date_intervals['full_month'] / $date_intervals['cur_month']);
                    $orders['total_amount'] *= (float)($date_intervals['full_month'] / $date_intervals['cur_month']);
                }
            }
// }}
            if ($orders_count == $orders_counts - 1 || $orders_count == $orders_counts) {
                $orders_data_array['blue2'][]  = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_orders'] . ']';
                $orders_data_array['green2'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['avg_order_amount'] . ']';
                $orders_data_array['red2'][]   = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_amount'] . ']';
            }
            if ($orders_count != $orders_counts) {
                $orders_data_array['blue'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_orders'] . ']';
                $orders_data_array['green'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['avg_order_amount'] . ']';
                $orders_data_array['red'][]   = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_amount'] . ']';
            }
        }
        \Yii::$app->view->registerJs(
                "var data_blue  = [ " . implode(" , ", $orders_data_array['blue']) . " ];" .
                "var data_blue2  = [ " . implode(" , ", $orders_data_array['blue2']) . " ];" .
                "var data_green = [ " . implode(" , ", $orders_data_array['green']) . " ];" .
                "var data_green2 = [ " . implode(" , ", $orders_data_array['green2']) . " ];" .
                "var data_red   = [ " . implode(" , ", $orders_data_array['red']) . " ];" .
                "var data_red2   = [ " . implode(" , ", $orders_data_array['red2']) . " ];",
                \yii\web\View::POS_BEGIN
        );

      $params = ['currcode_left'=> $currencies->currencies[DEFAULT_CURRENCY]['symbol_left'], 'currcode_right'=>$currencies->currencies[DEFAULT_CURRENCY]['symbol_right']];
      
      if (defined('SHOW_GOOGLE_MAPS') ){
        $key = tep_db_fetch_array(tep_db_query("select configuration_title, configuration_id from " . TABLE_CONFIGURATION . " where configuration_key='SHOW_GOOGLE_MAPS'"));
        $params['enabled_map'] = $key;        
        if (isset($params['enabled_map']['configuration_title'])){
          $_t = \common\helpers\Translation::getTranslationValue('SHOW_GOOGLE_MAPS_TITLE', 'configuration');
          $params['enabled_map']['configuration_title'] = ($_t ? $_t : $params['enabled_map']['configuration_title'] );
        }
        if (SHOW_GOOGLE_MAPS == 'true'){
          $key = tep_db_fetch_array(tep_db_query("select info as setting_code from " . TABLE_GOOGLE_SETTINGS . " where module='mapskey'"));
        
          $params['mapskey'] = $key['setting_code'];
          
          $origPlace = array(0, 0, 2);
          $country_info = tep_db_fetch_array(tep_db_query("select ab.entry_country_id from " . TABLE_PLATFORMS_ADDRESS_BOOK . " ab inner join " . TABLE_PLATFORMS . " p on p.is_default = 1 and p.platform_id = ab.platform_id where ab.is_default = 1"));
          $_country = (int)STORE_COUNTRY;
          if ($country_info){
            $_country = $country_info['entry_country_id'];
          }
          if (defined('STORE_COUNTRY') && (int)STORE_COUNTRY > 0){
            $origPlace = tep_db_fetch_array(tep_db_query("select lat, lng, zoom from " .TABLE_COUNTRIES . " where countries_id = '" . (int)$_country . "'"));
          }
          $params['origPlace'] = $origPlace;
          $params['orders_count'] = tep_db_num_rows($orders_query);
        }
      } 
    
      return $this->render('index', $params);
    }
	
	public function actionLocations(){
		global $languages_id;
		
		$this->layout = false;
		
		if (\Yii::$app->request->isPost){
			$order_id = \Yii::$app->request->post('order_id', 0);
			$lat =  \Yii::$app->request->post('lat', 0);
			$lng =  \Yii::$app->request->post('lng', 0);
			
			if ($order_id > 0 ){
				tep_db_query("update " . TABLE_ORDERS . " set lat = '" . (float)$lat. "', lng = '" . (float)$lng . "' where orders_id = '" . (int)$order_id . "'");
			}			
		} else {
			$date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y') - 1));
			$orders_query = tep_db_query("select o.delivery_postcode as pcode, c.countries_iso_code_2 as isocode, o.orders_id, concat(o.customers_postcode, ' ', o.customers_street_address, ' ', o.customers_city, ' ', o.customers_country) as address, concat(o.customers_street_address, ' ', o.customers_city, ' ', o.customers_country) as addressnocode from " . TABLE_ORDERS ." o left join " . TABLE_COUNTRIES. " c on o.delivery_country = c.countries_name and c.language_id = '" . (int)$languages_id . "'  where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.lat = 0 and o.lng = 0 limit 100");
			$to_search = [];
			while ($orders = tep_db_fetch_array($orders_query)) {
				$to_search[] = $orders;
			}
			
			$orders_query = tep_db_query("select o.lat, o.lng, o.delivery_address_format_id, o.delivery_street_address, o.delivery_suburb, o.delivery_city, o.delivery_postcode, o.delivery_state, o.delivery_country from " . TABLE_ORDERS ." o where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.lat not in (0 , 9999) and o.lng not in (0 , 9999)");
			$founded = [];
			while ($orders = tep_db_fetch_array($orders_query)) {
				$orders['title'] = $orders['delivery_street_address']."\n" .$orders['delivery_city']."\n" . $orders['delivery_postcode']."\n" . $orders['delivery_state']."\n" . $orders['delivery_country'];
				$founded[] = $orders;
			}
						
			echo json_encode(array(
				'to_search' => $to_search,
				'founded' => $founded,
        'orders_count' => count($founded),
			));
			
		}
		
	}

    public function actionError() {

        if (($exception = \Yii::$app->getErrorHandler()->exception) === null) {
            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
            $exception = new HttpException(404, \Yii::t('yii', 'Page not found.'));
        }

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }
        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = \Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = \Yii::t('yii', 'An internal server error occurred.');
        }

        if (\Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message \n$exception";
        } else {
            $this->layout = 'error.tpl';
            return $this->render('error', [
                        'name' => $name,
                        'message' => $message,
                        'exception' => $exception,
            ]);
        }
        //return $this->render('error');
    }

    public function actionOrder() {
        global $languages_id;
        $responseList = array();
        $orders_query = tep_db_query("select o.orders_status, o.orders_id, o.customers_name, o.customers_email_address, o.delivery_postcode, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' group by o.orders_id order by o.date_purchased desc limit 6");
        while ($orders = tep_db_fetch_array($orders_query)) {
            $responseList[] = array (
                $orders['customers_name'] . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">',
                strip_tags($orders['order_total']),
                $orders['orders_id'],
                $orders['delivery_postcode']
            );
        }
        $response = array(
            //          'draw' => $draw,
            'data' => $responseList,
            'columns' => [
                'Customers',
                'Order Total',
                'Order Id',
                'Post Code'
            ]
        );
        echo json_encode($response);
    }
    
    private function getProduct($categories_id = '0') {
        global $languages_id, $currencies;
        
        $productList = [];
        $products_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pd on p.products_id = pd.products_id LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " as p2c on p.products_id = p2c.products_id where pd.language_id = '" . (int)$languages_id . "' and pd.affiliate_id = 0 and p2c.categories_id=" . $categories_id .  " group by p.products_id order by p2c.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)) {
            $productList[] = [
                    'id' => $products['products_id'],
                    'value' => $products['products_name'],
                    'image' => \common\classes\Images::getImageUrl($products['products_id'], 'Small'),
                    'title' => $products['products_name'],
                    'price' => $currencies->format(\common\helpers\Product::get_products_price($products['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])),
                ];
        }
        return $productList;
    }
    
    private function getTree($parent_id = '0') {
        global $languages_id;

        $categoriesTree = [];
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' and affiliate_id = 0 order by c.sort_order, cd.categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $products = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p  where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int)$categories['categories_id'] . "' and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right) "));
            if ($products['total'] > 0) {
            //if ($exclude != $categories['categories_id']) {
              $categoriesTree[] = [
                    'id' => $categories['categories_id'],
                    'text' => $categories['categories_name'],
                    'child' => $this->getTree($categories['categories_id']),
                    'products' => $this->getProduct($categories['categories_id']),
                ];
          }
        }
        return $categoriesTree;
    }
    
    private function renderTree($response, $spacer = '')
    {
        $html = '';
        if (is_array($response)) {
            foreach ($response as $key => $value) {
                $html .= '<strong>' . $spacer . $value['text'] . '</strong>';
                if (isset($value['products'])) {
                    foreach ($value['products'] as $pkey => $pvalue) {
                        $html .= '<a href="javascript:void(0)" onclick="return searchSuggestSelected('.$pvalue['id'].', \''.$pvalue['value'].'\');" class="item">
        <span class="suggest_table">
            <span class="td_image"><img src="' . $pvalue['image'] . '" alt=""></span>
            <span class="td_name">' . $pvalue['title'] . '</span>
            <span class="td_price">' . $pvalue['price'] . '</span>
        </span>
    </a>';
                    }
                }
                if (isset($value['child'])) {
                    $html .= $this->renderTree($value['child'], $spacer . ' ' . $value['text'] . ' > ');
                }
            }
        }
        return $html;
    }
    
    public function actionSearchSuggest()
    {
        $this->layout = false;
        global $HTTP_SESSION_VARS, $languages_id, $currencies;

        $currencies = new \common\classes\currencies();
        
        $response = array();

        if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
            $_SESSION['keywords'] = tep_db_input(tep_db_prepare_input($_GET['keywords']));
            //Add slashes to any quotes to avoid SQL problems.
            $search = preg_replace("/\//",'',tep_db_input(tep_db_prepare_input($_GET['keywords'])));
            $where_str_categories = "";
            $where_str_gapi ="";
            $where_str_products = "";
            $where_str_manufacturers = "";
            $where_str_information = "";
            $replace_keywords = array();

            if (\common\helpers\Output::parse_search_string($search, $search_keywords, false)) {
                $where_str_categories .= " and (";
                $where_str_gapi .= " and (";
                $where_str_products .= " and (";
                $where_str_manufacturers .= " (";
                $where_str_information .= " and (";
                for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
                    switch ($search_keywords[$i]) {
                        case '(':
                        case ')':
                        case 'and':
                        case 'or':
                            $where_str_gapi .= " " . $search_keywords[$i] . " ";
                            $where_str_categories .= " " . $search_keywords[$i] . " ";
                            $where_str_products .= " " . $search_keywords[$i] . " ";
                            $where_str_manufacturers .= " " . $search_keywords[$i] . " ";
                            $where_str_information .= " " . $search_keywords[$i] . " ";
                            break;
                        default:

                            $keyword = tep_db_prepare_input($search_keywords[$i]);
                            $replace_keywords[] = $search_keywords[$i];
                            $where_str_gapi .=" gs.gapi_keyword like '%" . tep_db_input($keyword) . "%' or  gs.gapi_keyword like '%" .tep_db_input($keyword) . "%' ";

                            $where_str_products .= "(if(length(pd1.products_name), pd1.products_name, pd.products_name) like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%'  or if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) like '%" . tep_db_input($keyword) . "%' or  gs.gapi_keyword like '%" . tep_db_input($keyword) . "%' )";
                            $where_str_categories .= "(if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) like '%" . tep_db_input($keyword) . "%' or if(length(cd1.categories_description), cd1.categories_description, cd.categories_description) like '%" . tep_db_input($keyword) . "%')";

                            $where_str_manufacturers .= "(manufacturers_name like '%" . tep_db_input($keyword) . "%')";

                            $where_str_information .= "(if(length(i1.info_title), i1.info_title, i.info_title) like '%" . tep_db_input($keyword) . "%' or if(length(i1.description), i1.description, i.description) like '%" . tep_db_input($keyword) . "%' or if(length(i1.page_title), i1.page_title, i.page_title) like '%" . tep_db_input($keyword) . "%')";
                            break;
                    }
                }
                $where_str_categories .= ") ";
                $where_str_gapi .= ") ";
                $where_str_products .= ") ";
                $where_str_manufacturers .= ") ";
                $where_str_information .= ") ";

            }else {
                $replace_keywords[] = $search;
                $where_str_gapi .= "and gs.gapi_keyword like ('%" . $search . "%')))";
                $where_str_products .= "and (if(length(pd1.products_name), pd1.products_name like ('%" . $search . "%'), pd.products_name like ('%" . $search . "%'))  or if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) like '%" . $search . "%'  or gs.gapi_keyword like ('%" . $search . "%'))";
                $where_str_categories .= "and (if(length(cd1.categories_name), cd1.categories_name like ('%" . $search . "%'), cd.categories_name like ('%" . $search . "%')) or if(length(cd1.categories_description), cd1.categories_description like ('%" . $search . "%'), cd.categories_description like ('%" . $search . "%'))  )";
                $where_str_manufacturers .= " (manufacturers_name like '%" . $search . "%')";
                $where_str_information .= "and (if(length(i1.info_title), i1.info_title, i.info_title) like '%" . $search . "%' or if(length(i1.description), i1.description, i.description) like '%" . $search . "%' or if(length(i1.page_title), i1.page_title, i.page_title) like '%" . $search . "%')";
            }

            $from_str = "select c.categories_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name,  (if(length(cd1.categories_name), if(position('" . $search . "' IN cd1.categories_name), position('" . $search . "' IN cd1.categories_name), 100), if(position('" . $search . "' IN cd.categories_name), position('" . $search . "' IN cd.categories_name), 100))) as pos, 1 as is_category  from " . TABLE_CATEGORIES . " c " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" LEFT join " . TABLE_CATEGORIES_TO_AFFILIATES . " c2a on c.categories_id = c2a.categories_id  and c2a.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "' ":'') . " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . $languages_id ."' and cd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "', " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = 1 " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and c2a.affiliate_id is not null ":'') . " and cd.affiliate_id = 0 and cd.categories_id = c.categories_id and cd.language_id = '" . $languages_id . "' " . $where_str_categories . " and c.quick_find = 1 order by pos limit 0, 3" ;

            $sql_gapi = "
      select   p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, m.manufacturers_name,
          (if(length(pd1.products_name),
            if(position('" . $search . "' IN pd1.products_name),
              position('" . $search . "' IN pd1.products_name),
              100
            ),
            if(position('" . $search . "' IN pd.products_name),
              position('" . $search . "' IN pd.products_name),
              100
            )
          )) as pos, 0 as is_category,
		  p.products_image
      from   " . TABLE_PRODUCTS . " p
          left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."'
                                              and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'
          left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . $customer_groups_id . "'
                                          and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'
		left join gapi_search_to_products gsp on p.products_id = gsp.products_id
		left join gapi_search gs on gsp.gapi_id = gs.gapi_id
        left join " . TABLE_MANUFACTURERS . " m on m.manufacturers_id = p.manufacturers_id ,
        " . TABLE_PRODUCTS_DESCRIPTION . " pd
    where   p.products_status = 1
      and   p.products_id = pd.products_id
      and   pd.language_id = '" . (int)$languages_id . "'
      and   if(pp.products_group_price is null, 1, pp.products_group_price != -1 )
      and   pd.affiliate_id = 0
    " . $where_str_gapi . "
    order by gsp.sort, pos
  ";

            $sql = "
      select   p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, m.manufacturers_name,
          (if(length(pd1.products_name),
            if(position('" . $search . "' IN pd1.products_name),
              position('" . $search . "' IN pd1.products_name),
              100
            ),
            if(position('" . $search . "' IN pd.products_name),
              position('" . $search . "' IN pd.products_name),
              100
            )
          )) as pos, 0 as is_category,
		  p.products_image
      from   " . TABLE_PRODUCTS . " p
          left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."'
                                              and pd1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'
          left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . $customer_groups_id . "'
                                          and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "'
		LEFT JOIN ".TABLE_INVENTORY." i on p.products_id = i.prid
		left join gapi_search_to_products gsp on p.products_id = gsp.products_id
		left join gapi_search gs on gsp.gapi_id = gs.gapi_id
        left join " . TABLE_MANUFACTURERS . " m on m.manufacturers_id = p.manufacturers_id ,
        " . TABLE_PRODUCTS_DESCRIPTION . " pd
    where   p.products_status = 1
    " . ($HTTP_SESSION_VARS['affiliate_ref']>0?" and p2a.affiliate_id is not null ":'') . "
      and   p.products_id = pd.products_id
      and   pd.language_id = '" . (int)$languages_id . "'
      and   if(pp.products_group_price is null, 1, pp.products_group_price != -1 )
      and   pd.affiliate_id = 0
    " . $where_str_products . "
	group by p.products_id
    order by gapi_keyword desc, gsp.sort, products_name, pos
    limit   0, 10
  ";

            /**
             * Set XML HTTP Header for ajax response
             */
            reset($replace_keywords);
            foreach ($replace_keywords as $k => $v)
            {
                $patterns[] = "/" . preg_quote($v) . "/i";
                $replace[] = str_replace('$', '/$/', '<span class="typed">' . $v . '</span>');
            }

            $re = array();
            foreach ($replace_keywords as $k => $v)
                $re[] = preg_quote($v);
            $re = "/(" . join("|", $re) . ")/i";
            $replace = '<span class="typed">\1</span>';

            $product_query = tep_db_query($sql);
            while($product_array = tep_db_fetch_array($product_query)) {
                $response[] = array(
                    'id' => $product_array['products_id'],
                    'value' => addslashes($product_array['products_name']),
                  //'link' => tep_href_link('catalog/product', 'products_id=' . $product_array['products_id']),
                  'image' => \common\classes\Images::getImageUrl($product_array['products_id'], 'Small'),
                  'title' => preg_replace($re, $replace, strip_tags($product_array['products_name'])),
                    'price' => $currencies->format(\common\helpers\Product::get_products_price($product_array['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])),
                );

            }

            return $this->render('search.tpl', ['list' => $response]);

        } else {
            $response = $this->getTree();
            return $this->renderTree($response);
            //return $this->render('tree.tpl', ['list' => $response]);
        }

    }
    
    public function actionEnableMap(){
      $configuration_id = \Yii::$app->request->get('configuration_id', 0);
      $status = \Yii::$app->request->get('status', 'false');
      
      if ($configuration_id){
        tep_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . tep_db_input($status) . '" where configuration_id = "' . (int)$configuration_id .'"');
        echo 'ok';
        exit();
      }
      return false;
    }
    
    public function actionLoadLanguagesJs(){
	  //header('X-Content-Type-Options: nosniff');
      $list = \common\helpers\Translation::loadJS('admin/js');
      
      return \common\widgets\JSLanguage::widget(['list' => $list]);
    }

}
