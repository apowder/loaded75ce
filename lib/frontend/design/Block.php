<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design;

use Yii;
use yii\base\Widget;

class Block extends Widget
{

  public $name;
  public $params;

  public function init()
  {
    parent::init();
  }
  
  public static function getStyles(){
    global $block_styles;
    $styles = $block_styles[0];

    $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . THEME_NAME . "' and setting_name = 'media_query'");
    while ($item = tep_db_fetch_array($media_query_arr)){
      $arr = explode('w', $item['setting_value']);
      $styles .= '@media';
      if ($arr[0]){
        $styles .= ' (min-width:' . $arr[0] . 'px)';
      }
      if ($arr[0] && $arr[1]){
        $styles .= ' and ';
      }
      if ($arr[1]){
        $styles .= ' (max-width:' . $arr[1] . 'px)';
      }
      $styles .= '{';
      $styles .= $block_styles[$item['id']];
      $styles .= '} ';
    }

    return $styles;
  }

  public static function dimension($dimension, $default = 'px ;'){
    if ($dimension){
      if ($dimension == 'pr'){
        $dimension = '%';
      }
      $text = $dimension;
    } else {
      $text = $default;
    }
    return $text;
  }

  public static function styles($settings, $teg = false){
    $style = '';


    if ($settings[0]['overflow']) {
      $style .= 'overflow:' . $settings[0]['overflow'] . ';';
    }
    if ($settings[0]['content']) {
      $style .= 'content:\'' . ($settings[0]['content'] == '\_' ? '' : $settings[0]['content']) . '\';';
    }
    if ($settings[0]['display']) {
      if ($settings[0]['display'] == 'none' && \frontend\design\Info::isAdmin()) {
        $style .= 'opacity: 0.2;';
      } else {
        $style .= 'display:' . $settings[0]['display'] . ';';
      }
    }
    if ($settings[0]['position']) {
      $style .= 'position:' . $settings[0]['position'] . ';';
    }
    if (isset($settings[0]['top'])) {
      $style .= 'top:' . $settings[0]['top'] . ($settings[0]['top_dimension'] ? $settings[0]['top_dimension'] : 'px') . ';';
    }
    if (isset($settings[0]['left'])) {
      $style .= 'left:' . $settings[0]['left'] . ($settings[0]['left_dimension'] ? $settings[0]['left_dimension'] : 'px') . ';';
    }
    if (isset($settings[0]['right'])) {
      $style .= 'right:' . $settings[0]['right'] . ($settings[0]['right_dimension'] ? $settings[0]['right_dimension'] : 'px') . ';';
    }
    if (isset($settings[0]['bottom'])) {
      $style .= 'bottom:' . $settings[0]['bottom'] . ($settings[0]['bottom_dimension'] ? $settings[0]['bottom_dimension'] : 'px') . ';';
    }
    if ($settings[0]['vertical_align']){
      $style .= 'vertical-align:' . $settings[0]['vertical_align'] . ';';
    }
    if ($settings[0]['text_transform']){
      $style .= 'text-transform:' . $settings[0]['text_transform'] . ';';
    }
    if ($settings[0]['text_decoration']){
      $style .= 'text-decoration:' . $settings[0]['text_decoration'] . ';';
    }
    if ($settings[0]['width']){
      $style .= 'width:' . $settings[0]['width'] . ($settings[0]['width_measure'] ? '%' : 'px') . ';';
    }
    if ($settings[0]['min_width']){
      $style .= 'min-width:' . $settings[0]['min_width'] . ($settings[0]['min_width_measure'] ? '%' : 'px') . ';';
    }
    if ($settings[0]['max_width']){
      $style .= 'max-width:' . $settings[0]['max_width'] . ($settings[0]['max_width_measure'] ? '%' : 'px') . ';';
    }
    if ($settings[0]['height']){
      $style .= 'height:' . $settings[0]['height'] . ($settings[0]['height_measure'] ? '%' : 'px') . ';';
    }
    if ($settings[0]['min_height']){
      $style .= 'min-height:' . $settings[0]['min_height'] . ($settings[0]['min_height_measure'] ? '%' : 'px') . ';';
    }
    if ($settings[0]['max_height']){
      $style .= 'max-height:' . $settings[0]['max_height'] . ($settings[0]['max_height_measure'] ? '%' : 'px') . ';';
    }
    if ($settings[0]['float']){
      $style .= 'float:' . $settings[0]['float'] . ';';
    }
    if ($settings[0]['clear']){
      $style .= 'clear:' . $settings[0]['clear'] . ';';
    }

    if (isset($settings[0]['p_width'])) {
      $style .= 'width:' . ($settings[0]['p_width'] - $settings[0]['padding_left'] - $settings[0]['padding_right'] - $settings[0]['border_left_width'] - $settings[0]['border_right_width']) . 'px;';
    }
    if ($settings[0]['font_family'] && !$_GET['to_pdf']){
      $style .= 'font-family:\'' . $settings[0]['font_family'] . '\', Verdana, Arial, sans-serif;';
    }
    if ($settings[0]['color']){
      $style .= 'color:' . $settings[0]['color'] . ';';
    }
    if ($settings[0]['font_size']){
      $style .= 'font-size:' . $settings[0]['font_size'] . ($settings[0]['font_size_dimension'] ? self::dimension($settings[0]['font_size_dimension']) : 'px') .';';
    }
    if ($settings[0]['font_weight']){
      $style .= 'font-weight:' . $settings[0]['font_weight'] . ';';
    }
    if ($settings[0]['line_height']){
      $style .= 'line-height:' . $settings[0]['line_height'] . ($settings[0]['line_height_measure'] ? $settings[0]['line_height_measure'] : 'px') . ';';
    }
    if ($settings[0]['text_align']){
      $style .= 'text-align:' . $settings[0]['text_align'] . ';';
    }
    if (
        $settings[0]['text_shadow_left'] ||
        $settings[0]['text_shadow_top'] ||
        $settings[0]['text_shadow_size'] ||
        $settings[0]['text_shadow_color']
    ){
      $text_shadow_left = $settings[0]['text_shadow_left'];
      $text_shadow_top = $settings[0]['text_shadow_top'];
      $text_shadow_size = $settings[0]['text_shadow_size'];
      $text_shadow_color = $settings[0]['text_shadow_color'];
      if ($text_shadow_left) $text_shadow_left .= 'px';
      else $text_shadow_left = '0';
      if ($text_shadow_top) $text_shadow_top .= 'px';
      else $text_shadow_top = '0';
      if ($text_shadow_size) $text_shadow_size .= 'px';
      else $text_shadow_size = '0';
      if ($text_shadow_size && $text_shadow_color){
        $style .= 'text-shadow:' . $text_shadow_left.' '.$text_shadow_top.' '.$text_shadow_size.' '.$text_shadow_color . ';';
      }
    }
    if (
        ($settings[0]['box_shadow_blur'] ||
        $settings[0]['box_shadow_spread']) &&
        $settings[0]['box_shadow_color']
    ){
      $box_shadow_left = $settings[0]['box_shadow_left'];
      $box_shadow_top = $settings[0]['box_shadow_top'];
      $box_shadow_blur = $settings[0]['box_shadow_blur'];
      $box_shadow_spread = $settings[0]['box_shadow_spread'];
      if ($box_shadow_left) $box_shadow_left .= 'px';
      else $box_shadow_left = '0';
      if ($box_shadow_top) $box_shadow_top .= 'px';
      else $box_shadow_top = '0';
      if ($box_shadow_blur) $box_shadow_blur .= 'px';
      else $box_shadow_blur = '0';
      if ($box_shadow_spread) $box_shadow_spread .= 'px';
      else $box_shadow_spread = '0';
      $style .= 'box-shadow:' . $settings[0]['box_shadow_set'].' ' . $box_shadow_left.' '.
          $box_shadow_top.' '.$box_shadow_blur.' '.$box_shadow_spread.' '.$settings[0]['box_shadow_color'] . ';';
    }
    if ($settings[0]['background_image']){
      $style .= 'background-image:url(\'' . \frontend\design\Info::themeImage($settings[0]['background_image']) . '\');';
    }
    if ($settings[0]['background_color']){
      $style .= 'background-color:' . $settings[0]['background_color'] . ';';
    }
    if ($settings[0]['background_position']){
      $style .= 'background-position:' . $settings[0]['background_position'] . ';';
    }
    if ($settings[0]['background_repeat']){
      $style .= 'background-repeat:' . $settings[0]['background_repeat'] . ';';
    }
    if ($settings[0]['background_size']){
      $style .= 'background-size:' . $settings[0]['background_size'] . ';';
    }
    if (isset($settings[0]['padding_top'])){
      $style .= 'padding-top:' . $settings[0]['padding_top'] . 'px;';
    }
    if (isset($settings[0]['padding_left'])){
      $style .= 'padding-left:' . $settings[0]['padding_left'] . 'px;';
    }
    if (isset($settings[0]['padding_right'])){
      $style .= 'padding-right:' . $settings[0]['padding_right'] . 'px;';
    }
    if (isset($settings[0]['padding_bottom'])){
      $style .= 'padding-bottom:' . $settings[0]['padding_bottom'] . 'px;';
    }
    if (isset($settings[0]['margin_top'])){
      $style .= 'margin-top:' . $settings[0]['margin_top'] . 'px;';
    }
    if (isset($settings[0]['margin_left'])){
      $style .= 'margin-left:' . $settings[0]['margin_left'] . 'px;';
    }
    if (isset($settings[0]['margin_right'])){
      $style .= 'margin-right:' . $settings[0]['margin_right'] . 'px;';
    }
    if (isset($settings[0]['margin_bottom'])){
      $style .= 'margin-bottom:' . $settings[0]['margin_bottom'] . 'px;';
    }
    if (isset($settings[0]['border_top_width'])) {
      $style .= 'border-top:' . $settings[0]['border_top_width'] . 'px solid ' . $settings[0]['border_top_color'] . ';';
    }
    if (isset($settings[0]['border_left_width'])) {
      $style .= 'border-left:' . $settings[0]['border_left_width'] . 'px solid ' . $settings[0]['border_left_color'] . ';';
    }
    if (isset($settings[0]['border_right_width'])) {
      $style .= 'border-right:' . $settings[0]['border_right_width'] . 'px solid ' . $settings[0]['border_right_color'] . ';';
    }
    if (isset($settings[0]['border_bottom_width'])) {
      $style .= 'border-bottom:' . $settings[0]['border_bottom_width'] . 'px solid ' . $settings[0]['border_bottom_color'] . ';';
    }
    if (isset($settings[0]['border_radius_1'])) {
      $style .= 'border-top-left-radius:' . $settings[0]['border_radius_1'] . 'px;';
    }
    if (isset($settings[0]['border_radius_2'])) {
      $style .= 'border-top-right-radius:' . $settings[0]['border_radius_2'] . 'px;';
    }
    if (isset($settings[0]['border_radius_3'])) {
      $style .= 'border-bottom-right-radius:' . $settings[0]['border_radius_3'] . 'px;';
    }
    if (isset($settings[0]['border_radius_4'])) {
      $style .= 'border-bottom-left-radius:' . $settings[0]['border_radius_4'] . 'px;';
    }
    if ($settings[0]['display_none']){
      $style .= 'display:none;';
    }

    if ($settings[0]['box_align']){
      if ($settings[0]['box_align'] == 1){
        $style .= 'float: left;clear: none;';
      }
      if ($settings[0]['box_align'] == 2){
        $style .= 'display: inline-block;';
      }
      if ($settings[0]['box_align'] == 3){
        $style .= 'float: right;clear: none;';
      }
    }


    if ($style && $teg) {
      $style = ' style="' . $style . '"';
    }


    return $style;
  }

  public function schema($val, $id){
    $htm = '';
    $block_table = '#box-' . $id . '{display:table;width:100%} ';
    $flex = '#box-' . $id . '{display:flex;flex-wrap:wrap;} ';
    $div = '#box-' . $id . ' > div{width:100%;}';
    $div_n = '#box-' . $id . ' > div:nth-child(%s){width:%s;%s}';
    $clear = 'clear:both;';
    $header = 'display:table-header-group;';
    $body = 'display:table-cell;';
    $footer = 'display:table-footer-group;';
    $float_none = 'float:none;';

    switch ($val){
      case '2-2':
      case '3-4':
      case '4-2':
      case '5-2':
      case '6-2':
      case '7-2':
      case '8-4':
      case '13-4':
      case '9-2':
      case '10-2':
      case '11-2':
      case '12-2':
      case '14-3':
      case '15-6':
        $htm .= $div;
        break;
      case '2-3':
      case '4-3':
      case '5-3':
      case '6-3':
      case '7-3':
      case '9-3':
      case '10-3':
      case '11-3':
      case '12-3':
        $htm .= $block_table;
        $htm .= sprintf($div_n, 1, '100%', $footer . $float_none);
        $htm .= sprintf($div_n, 2, '100%', $header . $float_none);
        break;
      case '3-2':
        $htm .= sprintf($div_n, 1, '50%', '');
        $htm .= sprintf($div_n, 2, '50%', '');
        $htm .= sprintf($div_n, 3, '100%', '');
        break;
      case '3-3':
        $htm .= sprintf($div_n, 1, '100%', '');
        $htm .= sprintf($div_n, 2, '50%', '');
        $htm .= sprintf($div_n, 3, '50%', '');
        break;
      case '3-5':
      case '8-5':
      case '13-5':
        $htm .= $block_table;
        $htm .= sprintf($div_n, 1, '100%', $footer . $float_none);
        $htm .= sprintf($div_n, 2, '100%', $body . $float_none);
        $htm .= sprintf($div_n, 3, '100%', $header . $float_none);
        break;
      case '3-6':
      case '8-6':
      case '13-6':
        $htm .= $block_table;
        $htm .= sprintf($div_n, 1, '100%', $body . $float_none);
        $htm .= sprintf($div_n, 2, '100%', $header . $float_none);
        $htm .= sprintf($div_n, 3, '100%', $footer . $float_none);
        break;
      case '8-2':
      case '13-2':
        $htm .= $flex;
        $htm .= sprintf($div_n, 1, '50%', 'order:1;');
        $htm .= sprintf($div_n, 2, '100%', 'order:3;');
        $htm .= sprintf($div_n, 3, '50%', 'order:2;');
        break;
      case '8-3':
      case '13-3':
        $htm .= $flex;
        $htm .= sprintf($div_n, 1, '50%', 'order:2;');
        $htm .= sprintf($div_n, 2, '100%', 'order:1;');
        $htm .= sprintf($div_n, 3, '50%', 'order:3;');
        break;
      case '14-2':
        $htm .= sprintf($div_n, 1, '50%', '');
        $htm .= sprintf($div_n, 2, '50%', '');
        $htm .= sprintf($div_n, 3, '50%', $clear);
        $htm .= sprintf($div_n, 4, '50%', '');
        break;
      case '15-2':
        $htm .= sprintf($div_n, 1, '50%', '');
        $htm .= sprintf($div_n, 2, '50%', '');
        $htm .= sprintf($div_n, 3, '33.33%', $clear);
        $htm .= sprintf($div_n, 4, '33.33%', '');
        $htm .= sprintf($div_n, 5, '33.33%', '');
        break;
      case '15-3':
        $htm .= sprintf($div_n, 1, '33.33%', '');
        $htm .= sprintf($div_n, 2, '33.33%', '');
        $htm .= sprintf($div_n, 3, '33.33%', '');
        $htm .= sprintf($div_n, 4, '50%', $clear);
        $htm .= sprintf($div_n, 5, '50%', '');
        break;
      case '15-4':
        $htm .= sprintf($div_n, 1, '100%', '');
        $htm .= sprintf($div_n, 2, '50%', $clear);
        $htm .= sprintf($div_n, 3, '50%', '');
        $htm .= sprintf($div_n, 4, '50%', $clear);
        $htm .= sprintf($div_n, 5, '50%', '');
        break;
      case '15-5':
        $htm .= sprintf($div_n, 1, '50%', '');
        $htm .= sprintf($div_n, 2, '50%', '');
        $htm .= sprintf($div_n, 3, '50%', $clear);
        $htm .= sprintf($div_n, 4, '50%', '');
        $htm .= sprintf($div_n, 5, '100%', $clear);
        break;
    }

    return $htm;
  }

  public function run()
  {
    global $block_styles;

    $media_query = array();
    $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . THEME_NAME . "' and setting_name = 'media_query'");
    while ($item = tep_db_fetch_array($media_query_arr)){
      $media_query[] = $item;
    }

    $items_query = tep_db_query("select id, widget_name, widget_params from " . (\frontend\design\Info::isAdmin() ? TABLE_DESIGN_BOXES_TMP : TABLE_DESIGN_BOXES) . " where block_name = '" . $this->name . "' and theme_name = '" . THEME_NAME . "' order by sort_order");

    $count = tep_db_num_rows($items_query);

    $block = '';
    if ($count > 0 || \frontend\design\Info::isAdmin()){
      $block .= '<div class="block' . ($this->params['type'] ? ' ' . $this->params['type'] : '') . '"' . (\frontend\design\Info::isAdmin() ? ' data-name="' . $this->name . '"' . ($this->params['type'] ? ' data-type="' . $this->params['type'] . '"' : '') . ($this->params['cols'] ? ' data-cols="' . $this->params['cols'] . '"' : '') : '') . ($this->params['tabs'] ? ' id="tab-' . $this->name . '"' : '') . '>';
    }
    if ($count > 0) {
      while ($item = tep_db_fetch_array($items_query)) {
        $widget_array = array();

        $settings = array();
        $visibility = array();
        $settings_query = tep_db_query("select * from " . (\frontend\design\Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where box_id = '" . (int)$item['id'] . "'");
        while ($set = tep_db_fetch_array($settings_query)) {
          if ($set['visibility'] > 0){
            $visibility[$set['visibility']][$set['language_id']][$set['setting_name']] = $set['setting_value'];
          } else {
            $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
          }
        }

        if ($item['widget_name'] == 'Html')$item['widget_name'] = 'Html_box';
        $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];

        //$widget_array['params'] = $item['widget_params'];

        $widget_array['params'] = $this->params['params'];
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

          if ($settings[0]['ajax'] && !\frontend\design\Info::isAdmin()){
            $widget = '
<div class="preloader"></div>
<script type="text/javascript">
  tl(function(){
    $.get("' . tep_href_link('get-widget/one') . '", {
          id: "' . $item['id'] . '",
          action: "' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id . '",
          ' . (count($_GET) > 0 ? str_replace('{', '', str_replace('}', '', json_encode($_GET))) : '') . '
    }, function(d){
      $("#box-' . $item['id'] . '").html(d)
    })
  });
</script>
';
          } else {
            if (is_file(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR .  str_replace('\\', DIRECTORY_SEPARATOR, $item['widget_name']) . '.php')){
              $widget = $widget_name::widget($widget_array);
            } else {
              $widget = '';
            }
            
            if ($ext_widget = \common\helpers\Acl::checkExtension($item['widget_name'], 'run', true)){
                $widget_array = array_merge($widget_array, ['name' => $item['widget_name']]);
                $widget = $ext_widget::widget($widget_array);
            }

          }
          $page_block = Info::pageBlock();
          if ($widget != '' || \frontend\design\Info::isAdmin()) $block .=
            '<div class="box' .
            ($item['widget_name'] == 'BlockBox' || $item['widget_name'] == 'Tabs' || $item['widget_name'] == 'invoice\Container' || $item['widget_name'] == 'email\BlockBox' ? '-block type-'. $settings[0]['block_type'] : '') .
            ($item['widget_name'] == 'Tabs' ? ' tabs' : '') .
            ($settings[0]['style_class'] ? ' '. $settings[0]['style_class'] : '') .
            '" ' .
            ($page_block == 'email' || $page_block == 'packingslip' || $page_block == 'invoice' ? self::styles($settings, true) : '') . ' data-name="' . $item['widget_name'] . '" id="box-' . $item['id'] . '">';

          $style = self::styles($settings);
          $hover = self::styles($visibility[1]);
          $active = self::styles($visibility[2]);
          $before = self::styles($visibility[3]);
          $after = self::styles($visibility[4]);
          if ($style) {
            $block_styles[0] .= '#box-' . $item['id'] . '{' . $style . '}';
          }
          if ($hover) {
            $block_styles[0] .= '#box-' . $item['id'] . ':hover{' . $hover . '}';
          }
          if ($active) {
            $block_styles[0] .= '#box-' . $item['id'] . '.active{' . $active . '}';
          }
          if ($before) {
            $block_styles[0] .= '#box-' . $item['id'] . ':before{' . $before . '}';
          }
          if ($after) {
            $block_styles[0] .= '#box-' . $item['id'] . ':after{' . $after . '}';
          }
          foreach ($media_query as $item2){
            $style = self::styles($visibility[$item2['id']]);
            if ($style){
              $block_styles[$item2['id']] .= '#box-' . $item['id'] . '{' . $style . '}';
            }
            if ($visibility[$item2['id']][0]['only_icon']){
              $block_styles[$item2['id']] .= '#box-' . $item['id'] . ' .no-text {display:none;}';
            }
            if ($visibility[$item2['id']][0]['schema']){
              $block_styles[$item2['id']] .= $this->schema($visibility[$item2['id']][0]['schema'], $item['id']);
            }
          }

          if ($widget == ''){
            if (\frontend\design\Info::isAdmin()) $block .= '<div class="no-widget-name">Here added ' . $item['widget_name'] . ' widget</div>';
          } else {
            $block .= $widget;
          }
          if ($widget != '' || \frontend\design\Info::isAdmin()) $block .= '</div>';

        }


      }
    }
    if ($count > 0 || \frontend\design\Info::isAdmin()){
      $block .= '</div>';
    }

    return $block;
  }




}