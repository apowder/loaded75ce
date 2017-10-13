<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use frontend\design\Info;
use Yii;
use \common\classes\order;
use \common\classes\currencies;
/**
 * Site controller
 */
class EmailTemplateController extends Sceleton
{

    public function actionIndex()
    {
        $this->layout = false;
        return $this->render('index.tpl');
        //return $this->render('index.tpl', ['description' => stripslashes($row['description']), 'title' => $title]);
    }



    public function actionInvoice() {

        \common\helpers\Translation::init('email-template');

        global $languages_id, $language;

        $this->layout = false;

        $oID = Yii::$app->request->get('orders_id');

        $currencies = new currencies();

        $order = new order($oID);

        $key = Yii::$app->request->get('key');
        if ($_SESSION['customer_id'] != $order->customer['id'] && !Info::isAdmin() && $key != 'UNJfMzvmwE6EVbL6') {
            return false;
        }
        
        if ($_GET['theme_name']) {
            $theme = $_GET['theme_name'];
        } else {
            $theme_array = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES . " where is_default = 1"));
            if ($theme_array['theme_name']){
                $theme = $theme_array['theme_name'];
            } else {
                $theme = 'theme-1';
            }
        }
        define('THEME_NAME', $theme);

        return $this->render('invoice' . ($_GET['to_pdf'] ? '_pdf' : '') . '.tpl', [
          'order' => $order,
          'params' => [
            'order' => $order,
            'currencies' => $currencies,
            'oID' => $oID
          ],
          'base_url' => (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
          'oID' => $oID,
          'currencies' => $currencies,
        ]);

    }

    public function actionPackingslip() {

        \common\helpers\Translation::init('email-template');

        $this->layout = false;

        $oID = Yii::$app->request->get('orders_id');

        $currencies = new currencies();

        $order = new order($oID);

        $key = Yii::$app->request->get('key');
        if ($_SESSION['customer_id'] != $order->customer['id'] && !Info::isAdmin() && $key != 'UNJfMzvmwE6EVbL6') {
            return false;
        }

        return $this->render('packingslip' . ($_GET['to_pdf'] ? '_pdf' : '') . '.tpl', [
          'order' => $order,
          'params' => [
            'order' => $order,
            'currencies' => $currencies,
            'oID' => $oID
          ],
          'base_url' => (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
          'oID' => $oID,
          'currencies' => $currencies,
        ]);
    }
    
    public function actionVirtualGiftCardTemplate() {
        $this->layout = false;
        return $this->render('virtual-gift-card-template.tpl');
        
    }


    public function actionOrderTotals() {

        \common\helpers\Translation::init('email-template');

        global $languages_id, $language;

        $this->layout = false;

        $oID = Yii::$app->request->get('orders_id');

        $currencies = new currencies();

        $order = new order($oID);

        $key = Yii::$app->request->get('key');
        /*if ($_SESSION['customer_id'] != $order->customer['id'] && !Info::isAdmin() && $key != 'UNJfMzvmwE6EVbL6') {
            return false;
        }*/

        if ($_GET['theme_name']) {
            $theme = $_GET['theme_name'];
        } else {
            $theme_array = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES . " where is_default = 1"));
            if ($theme_array['theme_name']){
                $theme = $theme_array['theme_name'];
            } else {
                $theme = 'theme-1';
            }
        }
        define('THEME_NAME', $theme);

        return \frontend\design\boxes\invoice\Totals::widget([
            'params' => [
              'order' => $order,
              'params' => [
                'order' => $order,
                'currencies' => $currencies,
                'oID' => $oID
              ],
              'base_url' => (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
              'oID' => $oID,
              'currencies' => $currencies,]

        ]);

    }
}
