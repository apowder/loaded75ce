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
use frontend\design\Block;
use frontend\design\Info;

class Tabs extends Widget
{

  public $settings;
  public $params;
  public $id;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $languages_id;

    $block_id = 'block-' . $this->id;
    $tabs_count = 0;
    $tabs_headings = '';
    for ($i = 1; $i <= 10; $i++){
      if ($this->settings[$languages_id]['tab_' . $i]){
        $tabs_count++;
      }
    }
    $var = '';
    $var_content = '';

    if ($tabs_count){
      for($i = 1; $i <= $tabs_count; $i++){
        $widget = Block::widget(['name' => $block_id . '-' . $i, 'params' => ['params' => $this->params, 'cols' => $i, 'tabs' => true]]);
        if ($widget || Info::isAdmin()) {
          $var_content .= $widget;

          $tabs_headings .= '<div class="tab-' . $block_id . '-' . $i . '"><a data-href="#tab-' . $block_id . '-' . $i . '">' . $this->settings[$languages_id]['tab_' . $i] . '</a></div>';
        }
      }
      $var .= '<div class="tab-navigation">' . $tabs_headings . '</div>';
      $var .= $var_content;


      $var .= "
  <script type=\"text/javascript\">
  tl('" . Info::themeFile('/js/main.js') . "', function(){
    $('#box-" . $this->id . "').each(function(){
      var _this = $(this);
      $('> .tab-navigation div:first a', this).addClass('active');
      $('> .block', this).each(function(){
        if ($(this).text().length == 0 && !$(this).data('name')){
          $('.' + $(this).attr('id'), _this).remove()
        }
      }).hideTab();
      $('> .block:first', this).showTab();
      $('> .tab-navigation a', this).on('click', function(){
        $(this).closest('.tab-navigation').find('a').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.tabs').find('> .block').hideTab().filter($(this).data('href')).showTab();

        return false
      });
      _this.on('tabHide', function(){
        $('> .block', this).hideTab();
        $('> .block:first', this).showTab();
      })
    });
  })
  </script>
    ";
    } else {
      $var .= "<div class=\"no-block-settings_tab\"></div>
  <script type=\"text/javascript\">
  tl('" . Info::themeFile('/js/main.js') . "', function(){
    setTimeout(function(){
      $('.no-block-settings_tab').closest('.box-block').find('.edit-box').trigger('click')
    }, 2000)
  })
  </script>";
    }

    return $var;

    /*return IncludeTpl::widget([
      'file' => 'boxes/block.tpl',
      'params' => [
        'block_id' => 'block-' . $this->id,
        'params' => $this->params,
        'settings' => $this->settings,
      ]
    ]);*/
  }
}