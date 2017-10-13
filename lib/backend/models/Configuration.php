<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models;

use common\helpers\Translation;

class Configuration {

  // Alias function for Store configuration values in the Administration Tool
  public static function tep_cfg_pull_down_country_list() {//$country_id
    $keys = func_get_args();
    eval('list($country_id,) = array(' . $keys[0] . ');');
    
    return tep_draw_pull_down_menu('configuration_value', \common\helpers\Country::get_countries(), $country_id);
  }

  public static function tep_cfg_pull_down_zone_list() {//$zone_id
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');

    return tep_draw_pull_down_menu('configuration_value', \common\helpers\Zones::get_country_zones(STORE_COUNTRY), $zone_id);
  }

  public static function tep_cfg_pull_down_tax_classes() {//$tax_class_id, $key = ''
    $keys = func_get_args();
    eval('list($tax_class_id, $key) = array(' . $keys[0] . ');');
    
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
      'text' => $tax_class['tax_class_title']);
    }

    return tep_draw_pull_down_menu($name, $tax_class_array, $tax_class_id, 'class="form-control"');
  }

  ////
  // Function to read in text area in admin
  public static function tep_cfg_textarea() {//$text
    $keys = func_get_args();
    eval('list($text,) = array(' . $keys[0] . ');');
    
    return tep_draw_textarea_field('configuration_value', false, 35, 5, $text);
  }

  public static function tep_cfg_get_zone_name() {//$zone_id
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');
    $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_id = '" . (int)$zone_id . "'");

    if (!tep_db_num_rows($zone_query)) {
      return $zone_id;
    } else {
      $zone = tep_db_fetch_array($zone_query);
      return $zone['zone_name'];
    }
  }

  public static function tep_cfg_select_multioption_order_statuses() {//$key_value, $key = ''
    global $languages_id;
    
    $keys = func_get_args();
    eval('list($key_value, $key) = array(' . $keys[0] . ');');    

    $string = '';
    $key_values = explode( ", ", $key_value);
    $statuses_array = \common\helpers\Order::get_status();

    for ($i=0; $i<sizeof($statuses_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><label><input type="checkbox" name="' . $name . '" value="' . $statuses_array[$i]['id'] . '"';

      if ( in_array($statuses_array[$i]['id'], $key_values) ) $string .= 'CHECKED';
      $string .= '> ' . $statuses_array[$i]['text'].'</label>';
    }
    $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
    $string .= '<input type="hidden" name="' . $name . '" value="--none--">';
    return $string;
  }  
////

// Alias function for Store configuration values in the Administration Tool
  public static function tep_cfg_select_option() {//$select_array, $key_value, $key=''
    global $languages_id;
    $string = '';
    
    $keys = func_get_args();
    eval('list($select_array, $key_value, $key) = array(' . $keys[0] . ');');
    
    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

      $string .= '<br><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

      if ($key_value == $select_array[$i]) $string .= ' CHECKED';

      $_t = Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $select_array[$i])), 'configuration', $languages_id);
      $_t = (tep_not_null($_t) ? $_t : $select_array[$i]);
      $string .= '> ' . $_t;
    }

    return $string;
  }

  ////
  // Alias function for module configuration keys
  public static function tep_mod_select_option() {//$select_array, $key_name, $key_value
    global $languages_id;
    
    $keys = func_get_args();
    eval('list($select_array, $key_name, $key_value) = array(' . $keys[0] . ');');  
    
    reset($select_array);
    while (list($key, $value) = each($select_array)) {
      if (is_int($key)) $key = $value;
      $string .= '<br><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
      if ($key_value == $key) $string .= ' CHECKED';

      $_t = Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $value)), 'configuration', $languages_id);
      $_t = (tep_not_null($_t) ? $_t : $value);
      
      $string .= '> ' . $value;
    }

    return $string;
  }  
  
  public static function tep_cfg_pull_down_zone_classes() {//$zone_class_id, $key = ''
    $keys = func_get_args();
    eval('list($zone_class_id, $key) = array(' . $keys[0] . ');');   
  
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
    while ($zone_class = tep_db_fetch_array($zone_class_query)) {
      $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
      'text' => $zone_class['geo_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
  }

  public static function tep_cfg_pull_down_order_statuses() {//$order_status_id, $key = ''
    global $languages_id;
    
    $keys = func_get_args();
    eval('list($order_status_id, $key) = array(' . $keys[0] . ');');       

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
    while ($statuses = tep_db_fetch_array($statuses_query)) {
      $statuses_array[] = array('id' => $statuses['orders_status_id'],
      'text' => $statuses['orders_status_name']);
    }

    return tep_draw_pull_down_menu($name, $statuses_array, $order_status_id);
  }  
  
  // Alias function for array of configuration values in the Administration Tool
  public static function tep_cfg_select_multioption() {//$select_array, $key_value, $key = ''
    
    $keys = func_get_args();
    eval('list($select_array, $key_value, $key) = array(' . $keys[0] . ');');   
    
    for ($i=0; $i<sizeof($select_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"';
      $key_values = explode( ", ", $key_value);
      if ( in_array($select_array[$i], $key_values) ) $string .= 'CHECKED';
      $string .= '> ' . $select_array[$i];
    }
    $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
    $string .= '<input type="hidden" name="' . $name . '" value="--none--"';
    return $string;
  }

  //create a select list to display list of themes available for selection
  public static function tep_cfg_pull_down_template_list() {//$template_id, $key = ''
    
    $keys = func_get_args();
    eval('list($template_id, $key) = array(' . $keys[0] . ');');   
    
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $template_query = tep_db_query("select template_id, template_name from " . TABLE_TEMPLATE . " order by template_name");
    while ($template = tep_db_fetch_array($template_query)) {
      $template_array[] = array('id' => $template['template_name'],
      'text' => $template['template_name']);
    }

    return tep_draw_pull_down_menu($name, $template_array, $template_id);
  }

  public static function tep_cfg_get_timezone_name()//$zone_id
  {
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');   
    
    foreach(\common\helpers\System::get_timezones() as $unused => $timezone)
    {
      if($timezone['id'] === $zone_id)
      {
        return $timezone['text'];
      }
    }
    return "";
  }

  public static function tep_cfg_pull_down_timezone_list() {//$zone_id
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');   
    
    return tep_draw_pull_down_menu('configuration_value', \common\helpers\System::get_timezones(), $zone_id);
  }

  public static function tep_cfg_select_download_status() {//$key_value
    
    $keys = func_get_args();
    eval('list($key_value,) = array(' . $keys[0] . ');');   
    
    $select_array = \common\helpers\Order::get_status();
    $key_value_array = explode(',', $key_value);
    for ($i=0; $i<sizeof($select_array); $i++) {
      //$string .= '<br><input type="checkbox" name="' . $select_array[$i]['text'] . '" value="' . $select_array[$i]['id'] . '"';
      $string .= '<br><input type="checkbox" name="configuration_value[]" value="' . $select_array[$i]['id'] . '"';
      for ($j=0;$j<sizeof($key_value_array);$j++) {
        if ($key_value_array[$j] == $select_array[$i][id]) $string .= ' CHECKED';
      }
      $string .= '> ' . $select_array[$i]['text'];
    }
    $string .= '<br><input type="hidden" name="flag" value="exist"';
    return $string;
  }

  public static function tep_cfg_select_user_group(){//$key_value

    $keys = func_get_args();
    eval('list($key_value,) = array(' . $keys[0] . ');');  
    
    $status_array = array();
    $status_array[] = array('id' => '0', 'text' => TEXT_NONE);
    $status_query = tep_db_query("select * from " . TABLE_GROUPS);
    while ($status = tep_db_fetch_array($status_query)){
      $status_array[] = array('id' => $status['groups_id'], 'text' => $status['groups_name']);
    }
    return tep_draw_pull_down_menu('configuration_value', $status_array, $key_value);
  }

  public static function tep_cfg_select_user_edit_group(){//$key_value

    $keys = func_get_args();
    eval('list($key_value,) = array(' . $keys[0] . ');');  
    
    $status_array = array();
    $status_array[] = array('id' => '0', 'text' => TEXT_NONE);
    $status_query = tep_db_query("select * from " . TABLE_GROUPS);
    while ($status = tep_db_fetch_array($status_query)){
      $status_array[] = array('id' => $status['groups_id'], 'text' => $status['groups_name']);
    }
    return tep_draw_pull_down_menu('groups_id', $status_array, $key_value);
  }
  
    public static function time_zones_select()
    {
        $keys = func_get_args();
        eval('list($key_value,) = array(' . $keys[0] . ');');  

        $timeZonesVariants = [];
        foreach( \DateTimeZone::listIdentifiers() as $timeZoneIdent){
            $timeZonesVariants[] = [
                'id' => $timeZoneIdent,
                'text' => $timeZoneIdent,
            ];
        }

        return tep_draw_pull_down_menu('configuration_value', $timeZonesVariants, $key_value);
    }
 
    public static function cfg_true_get_order_status( $list='' ){
      $default = preg_split('/[, ]/', $list, -1, PREG_SPLIT_NO_EMPTY);
      if ( is_array($default) ) {
        foreach( $default as $idx=>$status_id ) {
          $status_name = \common\helpers\Order::get_order_status_name($status_id);
          $default[$idx] = empty($status_name)?$status_id.'?':$status_name; 
        }
        $ret = implode(', ',$default);
      }else{
        $ret = $default;
      }

      return $ret;
    } 
    
    public static function cfg_true_set_order_status( $single=true, $list='', $key = ''  ){
        
      if ( $single == 'true') {
        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
      }else{
        $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value[]');
      }

      $values = \common\helpers\Order::get_status();
      $default = preg_split('/[, ]/', $list, -1, PREG_SPLIT_NO_EMPTY);

      $field = '<select '.($single == 'true'?'':'size="'.min(count($values),5).'" multiple="multiple" ').' name="' . \common\helpers\Output::output_string($name) . '"';
      for ($i=0, $n=sizeof($values); $i<$n; $i++) {
        $field .= '<option value="' . \common\helpers\Output::output_string($values[$i]['id']) . '"';
        if ( in_array($values[$i]['id'],$default)) {
          $field .= ' SELECTED';
        }

        $field .= '>' . \common\helpers\Output::output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
      }
      $field .= '</select>';

      return $field;
    }
}