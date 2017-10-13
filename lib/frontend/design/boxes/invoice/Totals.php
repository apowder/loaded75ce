<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Totals extends Widget
{

  public $id;
  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      $order_total_output = $this->params['order']->totals;

      $result = [];
        foreach ($order_total_output as $total) {
            if (file_exists(DIR_WS_MODULES . 'order_total/' . $total['class'] . '.php')) {
                include_once(DIR_WS_MODULES . 'order_total/' . $total['class'] . '.php');
            }
            if (class_exists($total['class'])) {
                $object = new $total['class'];
                if (method_exists($object, 'visibility')) {
                    if (true == $object->visibility(PLATFORM_ID, 'TEXT_INVOICE') ) {
                        if (method_exists($object, 'visibility')) {
                            $result[]  = $object->displayText(PLATFORM_ID, 'TEXT_INVOICE', $total);
                        } else {
                            $result[] = $total;
                        }
                    }
                }
            }
        }
        $order_total_output = $result;

    if ($this->settings[0]['pdf']){
      $html = file_get_contents(tep_catalog_href_link('email-template/order-totals?orders_id=' . $this->params['oID'] . '&platform_id=' . $this->params['platform_id'] . ''));
      return $html;
    } else {
      return IncludeTpl::widget(['file' => 'boxes/invoice/totals.tpl', 'params' => [
        'order' => $this->params['order'],
        'order_total_output' => $order_total_output,
        'currencies' => $this->params['currencies'],
        'to_pdf' => ($_GET['to_pdf'] ? 1 : 0),
        'width' => Info::blockWidth($this->id)
      ]]);
    }
  }
}