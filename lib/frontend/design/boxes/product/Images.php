<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Images extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    if (!is_array($this->params) ) $this->params = array();
    parent::init();
  }

  public function run()
  {
    global $languages_id;

    if ( isset($this->params['uprid']) && $this->params['uprid']>0 ) {
      $show_uprid = $this->params['uprid'];
    }else {
      $show_uprid = Yii::$app->request->get('products_id',0);
    }

    if ($show_uprid) {

      $images = \common\classes\Images::getImageList($show_uprid);
      //echo '<pre>'; var_dump($show_uprid,array_keys($images)); echo '</pre>';
      if ( count($images)==0 ) {
        $show_uprid = \common\helpers\Inventory::get_prid($show_uprid);
        $images = \common\classes\Images::getImageList($show_uprid);
        //echo '<pre>'; var_dump($show_uprid,array_keys($images)); echo '</pre>';
      }
      $products_name = \common\helpers\Product::get_products_name($show_uprid); 
      $main_image_alt = $products_name;
      $main_image_title = $products_name;
      $main_image = \common\classes\Images::getImageUrl($show_uprid, 'Medium');
      foreach( $images as $__image ) {
        if ( $__image['image']['Medium']['url']==$main_image ) {
          $main_image_alt = $__image['alt'];
          $main_image_title = $__image['title'];
        }
      }

      return IncludeTpl::widget(['file' => 'boxes/product/images.tpl', 'params' => [
        'img' => $main_image,
        'main_image_alt' => $main_image_alt,
        'main_image_title' => $main_image_title,
        'images' => $images,
        'images_count' => count($images),
        'settings' => $this->settings
      ]]);
    } else {
      return '';
    }
  }
}