<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes\modules;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

abstract class Module{

    public function getTitle($method = '') {
        return $this->title;
    }

    public function check( $platform_id ) {
    $keys = $this->keys();
    if ( count($keys)==0 || (int)$platform_id==0 ) return 0;

    $check_keys_r = tep_db_query(
      "SELECT configuration_key ".
      "FROM " . TABLE_PLATFORMS_CONFIGURATION . " ".
      "WHERE configuration_key IN('".implode("', '",array_map('tep_db_input',$keys))."') AND platform_id='".(int)$platform_id."'"
    );
    $installed_keys = array();
    while( $check_key = tep_db_fetch_array($check_keys_r) ) {
      $installed_keys[$check_key['configuration_key']] = $check_key['configuration_key'];
    }

    $check_status = isset($installed_keys[$keys[0]])?1:0;

    $install_keys = false;
    foreach( $keys as $idx=>$module_key ) {
      if ( !isset($installed_keys[$module_key]) && $check_status ) {
        // missing key
        if ( !is_array($install_keys) ) $install_keys = $this->get_install_keys($platform_id);
        $this->add_config_key($platform_id, $module_key, $install_keys[$module_key]);
      }
    }

    return $check_status;
  }

  public function install( $platform_id ) {
    $keys = $this->get_install_keys($platform_id);
    if ( count($keys)==0 || (int)$platform_id==0 ) return false;

    foreach($keys as $key=>$data) {
      $this->add_config_key($platform_id, $key, $data);
    }
  }

  protected function add_config_key($platform_id, $key, $data )
  {
    $sql_data = array(
      'platform_id' => (int)$platform_id,
      'configuration_key' => $key,
      'configuration_title' => isset($data['title'])?$data['title']:'',
      'configuration_value' => isset($data['value'])?$data['value']:'',
      'configuration_description' => isset($data['description'])?$data['description']:'',
      'configuration_group_id' => isset($data['group_id'])?$data['group_id']:'6',
      'sort_order' => isset($data['sort_order'])?$data['sort_order']:'0',
      'date_added' => 'now()',
    );
    if ( isset($data['use_function']) ) {
      $sql_data['use_function'] = $data['use_function'];
    }
    if ( isset($data['set_function']) ) {
      $sql_data['set_function'] = $data['set_function'];
    }
    tep_db_perform(TABLE_PLATFORMS_CONFIGURATION, $sql_data);
  }

  public function remove($platform_id) {
    $keys = $this->keys();
    if ( count($keys)>0 && (int)$platform_id!=0 ) {
      tep_db_query(
        "DELETE FROM ".TABLE_PLATFORMS_CONFIGURATION." ".
        "WHERE platform_id='".(int)$platform_id."' AND configuration_key IN('".implode("', '",$keys)."')"
      );
    }
  }

  function keys(){
    return array_keys($this->configure_keys());
  }

  /**
   * @return ModuleStatus
   */
  abstract public function describe_status_key();

  /**
   * @return ModuleSortOrder
   */
  abstract public function describe_sort_key();
  /**
   * @return array
   */

  abstract public function configure_keys();

  public function enable_module($platform_id, $flag){
    $key_info = $this->describe_status_key();
    if ( !is_object($key_info) || !is_a($key_info,'common\classes\modules\ModuleStatus')) return;

    $this->update_config_key(
      $platform_id,
      $key_info->key,
      $flag?$key_info->value_enabled:$key_info->value_disabled
    );
  }

  /**
   * @param $platform_id
   * @return bool
   */
  public function is_module_enabled($platform_id){
    $key_info = $this->describe_status_key();
    if ( !is_object($key_info) || !is_a($key_info,'common\classes\modules\ModuleStatus')) return false;

    return $this->get_config_key($platform_id,$key_info->key)==$key_info->value_enabled;
  }


  public function update_sort_order($platform_id, $new_sort_order){
    $key_info = $this->describe_sort_key();
    if ( !is_object($key_info) || !is_a($key_info,'common\classes\modules\ModuleSortOrder')) return;
    $this->update_config_key($platform_id, $key_info->key, (int)$new_sort_order );
  }

  protected function update_config_key($platform_id, $key, $value){
    tep_db_query(
      "UPDATE ".TABLE_PLATFORMS_CONFIGURATION." ".
      "SET configuration_value='".tep_db_input($value)."', last_modified=NOW() " .
      "WHERE configuration_key='".tep_db_input($key)."' AND platform_id='".(int)$platform_id."'"
    );
  }

  protected function get_config_key($platform_id, $key){
    $get_key_value_r = tep_db_query(
      "SELECT configuration_value ".
      "FROM ".TABLE_PLATFORMS_CONFIGURATION." ".
      "WHERE configuration_key='".tep_db_input($key)."' AND platform_id='".(int)$platform_id."'"
    );
    if ( tep_db_num_rows($get_key_value_r)>0 ) {
      $key_value = tep_db_fetch_array($get_key_value_r);
      return $key_value['configuration_value'];
    }
    return false;
  }

  public function save_config($platform_id, $new_data_array){
    if (is_array($new_data_array)) {
      $module_keys = $this->keys();
      foreach( $new_data_array as $update_key=>$new_value ){
        if ( !in_array($update_key,$module_keys) ) continue;
        $this->update_config_key($platform_id, $update_key, $new_value);
      }
    }
  }

  protected function get_install_keys($platform_id)
  {
    return $this->configure_keys();
  }

}