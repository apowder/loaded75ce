<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;

class Listing extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $languages_id;

    if ( !isset($this->params['listing_split']) || !is_object($this->params['listing_split']) || !is_a($this->params['listing_split'], 'frontend\design\splitPageResults' ) ) {
      return '';
    }
    $listing_split = $this->params['listing_split'];
    /**
     * @var $listing_split SplitPageResults
     */

    if ($listing_split->number_of_rows > 0){
      return IncludeTpl::widget([
        'file' => 'boxes/catalog/listing.tpl',
        'params' => [
          'products' => Info::getProducts(tep_db_query($listing_split->sql_query)),
          'settings' => $this->settings,
          'params' => [
            'url' => tep_href_link(Yii::$app->controller->id . '/' . Yii::$app->controller->action->id, \common\helpers\Output::get_all_get_params(array('page'))),
            'number_of_rows' => $listing_split->number_of_rows
          ],
          'fbl' => $_GET['fbl'],
          'languages_id' => $languages_id
        ]
      ]);

    } elseif (Yii::$app->controller->action->id == 'advanced-search-result') {
      return '<div class="no-found">' . ITEM_NOT_FOUND . '</div>';
    }

    return '';


  }
}