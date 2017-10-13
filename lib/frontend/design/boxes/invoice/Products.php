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

class Products extends Widget
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
    global $currencies;

    if ($this->settings[0]['pdf']){
      $width = Info::blockWidth($this->id);
      $html = '
      <table class="invoice-products" style="width: 100%" cellpadding="5">
  <tr class="invoice-products-headings">
    <td style="padding-left: 0; width: 5%; background-color: #eee; ">' . QTY . '</td>
    <td style="width:35%; background-color: #eee; ">' . TEXT_NAME . '</td>
    <td style="width:17%; background-color: #eee; ">' . TEXT_MODEL . '</td>
    <td style="width:7%; background-color: #eee; ">' . TEXT_TAX . '</td>
    <td style="text-align:right;width:12%; background-color: #eee; ">' . TABLE_HEADING_PRICE_EXCLUDING_TAX . '</td>
    <td style="text-align:right;width:12%; background-color: #eee; ">' . TABLE_HEADING_TOTAL_EXCLUDING_TAX . '</td>
    <td style="padding-right: 0;text-align:right;width:12%; background-color: #eee; ">' . TABLE_HEADING_TOTAL_INCLUDING_TAX . '</td>
  </tr>';

      $order = $this->params['order'];

foreach ($order->products as $product) {
  $html .= '
      <tr>
        <td style=" border-top: 1px solid #ccc">' . $product['qty'] . '</td>
        <td style=" border-top: 1px solid #ccc">' . $product['name'];

  if (count($product['attributes'])){
    foreach ($product['attributes'] as $attribut){
      $html .= '
              <div><small>&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut['option'])) . ': ' . $attribut['value'] . '</i></small></div>';
    }
  }
  $html .= '
        </td>

        <td style=" border-top: 1px solid #ccc">' . $product['model'] . '</td>
        <td style=" border-top: 1px solid #ccc">' . \common\helpers\Tax::display_tax_value($product['tax']) . '%</td>
        <td style="text-align:right; border-top: 1px solid #ccc">' . $currencies->format($product['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</td>
        <td style="text-align:right; border-top: 1px solid #ccc">' . $currencies->format($product['final_price'] * $product['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>
        <td style="text-align:right; border-top: 1px solid #ccc"><b>' . $currencies->format(\common\helpers\Tax::add_tax($product['final_price'], $product['tax']) * $product['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>
      </tr>
';
}
      $html .= ' 
</table>
';




      return $html;
    } else {
      return IncludeTpl::widget(['file' => 'boxes/invoice/products.tpl', 'params' => [
        'order' => $this->params['order'],
        'currencies' => $this->params['currencies'],
        'to_pdf' => ($_GET['to_pdf'] ? 1 : 0),
        'width' => Info::blockWidth($this->id)
      ]]);
    }
  }
}