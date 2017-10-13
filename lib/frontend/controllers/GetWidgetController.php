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

use Yii;

/**
 * Site controller
 */
class GetWidgetController extends Sceleton
{

    public function actionIndex()
    {
        $params = tep_db_prepare_input(Yii::$app->request->post());

        $response = array();
        foreach ($params as $widget) {
            $widget_name = 'frontend\design\boxes\\' . $widget['name'];
            $response[] = $widget_name::widget(['params' => $widget['params']]);
        }

        return json_encode($response);
    }

    public function actionOne()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        $items_query = tep_db_query("select id, widget_name, widget_params from " . ($get['admin'] ? TABLE_DESIGN_BOXES_TMP : TABLE_DESIGN_BOXES) . " where id = '" . (int)$get['id'] . "'");
        if ($item = tep_db_fetch_array($items_query)){

            \common\helpers\Translation::init($get['action']);



            $block = '';
            $widget_array = array();

            $settings = array();
            $settings_query = tep_db_query("select * from " . ($get['admin'] ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where box_id = '" . (int)$item['id'] . "'");
            while ($set = tep_db_fetch_array($settings_query)) {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }

            $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];

            $widget_array['id'] = $item['id'];

            $settings[0]['params'] = $item['widget_params'];
            $widget_array['settings'] = $settings;

            if (
              Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index' && $settings[0]['visibility_home'] ||
              Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'product' && $settings[0]['visibility_product'] ||
              Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'index' && $settings[0]['visibility_catalog'] ||
              Yii::$app->controller->id == 'info' && Yii::$app->controller->action->id == 'index' && $settings[0]['visibility_info'] ||
              Yii::$app->controller->id == 'cart' && Yii::$app->controller->action->id == 'index' && $settings[0]['visibility_cart'] ||
              Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id != 'success' && $settings[0]['visibility_checkout'] ||
              Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'success' && $settings[0]['visibility_success'] ||
              Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id != 'login' && $settings[0]['visibility_account'] ||
              Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login' && $settings[0]['visibility_login']
            ){
            } elseif(
              !(Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'design' ||
                Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'product' ||
                Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'info' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'cart' && Yii::$app->controller->action->id == 'index' ||
                Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id != 'success' ||
                Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'success' ||
                Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id != 'login' ||
                Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login') &&
              $settings[0]['visibility_other']
            ) {
            } else {

                if ($_GET['to_pdf']) {
                    $settings[0]['p_width'] = Info::blockWidth($item['id']);
                }

                $widget = $widget_name::widget($widget_array);
                

                if ($widget == ''){
                    if ($get['admin']) $block .= '<div class="no-widget-name">Here added ' . $item['widget_name'] . ' widget</div>';
                } else {
                    $block .= $widget;
                }

            }






        }

        return $block;
    }


}
