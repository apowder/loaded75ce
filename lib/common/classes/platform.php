<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

class platform
{
    private $config_pool = array();
    private $active_config_id = false;

  public static function name($platform_id)
  {
    static $_map = false;
    if( !is_array($_map) ) {
      $_map = array();
      foreach( self::getList() as $_platform ) $_map[ $_platform['id'] ] = $_platform['text'];
    }
    return isset($_map[$platform_id])?$_map[$platform_id]:'-';
  }

  public static function getList($withVirtual = true)
  {
    static $db_list = array();
    $cache_key = !!$withVirtual?'T':'F';
    if ( !isset($db_list[$cache_key]) ) {
      $db_list[$cache_key] = array();
      $get_list_r = tep_db_query(
        "SELECT platform_id, platform_name, is_default, is_virtual, need_login, platform_url ".
        "FROM ".TABLE_PLATFORMS." ".
              "WHERE status=1 ".
              ($withVirtual ? '' : 'and is_virtual=0 ') .
        "ORDER BY IF(is_default=1,0,1), sort_order, platform_name ".
        ""
      );
      while( $_list = tep_db_fetch_array($get_list_r) ) {
        $db_list[$cache_key][] = array(
          'id' => $_list['platform_id'],
          'text' => $_list['platform_name'],
          'is_virtual' => $_list['is_virtual'],
          'is_default' => !!$_list['is_default'],
          'need_login' => $_list['need_login'],
          'platform_url' => $_list['platform_url'],
        );
      }
    }
    return $db_list[$cache_key];
  }

    /**
     * @return platform_config
     */
    function getConfig($id)
    {
        if ( !isset($this->config_pool[(int)$id]) ) {
            $this->config_pool[(int)$id] = new platform_config((int)$id);
            if ( !$this->config_pool[(int)$id]->getId() ) {
                $this->config_pool[(int)$id] = new platform_config(platform::defaultId());
            }
        }
        return $this->config_pool[(int)$id];
    }

    /**
     * @return platform_config
     */
    function config($id=null)
    {
        if ( is_numeric($id) ) {
            $this->active_config_id = false;
            foreach( platform::getList() as $check_id ){
                if ($check_id['id']==$id) {
                    $this->active_config_id = (int)$id;
                    break;
                }
            }
        }

        if ( !is_numeric($this->active_config_id) ) {
            $this->active_config_id = platform::currentId();
        }

        if ( !isset($this->config_pool[$this->active_config_id]) ) {
            $this->config_pool[$this->active_config_id] = new platform_config($this->active_config_id);
        }

        return $this->config_pool[$this->active_config_id];
    }

  public static function getProductsAssignList()
  {
    return self::getList();
  }

  public static function getCategoriesAssignList()
  {
    return self::getList();
  }

  public static function defaultId()
  {
    $default_id = 0;
    $platforms = self::getList();
    foreach( $platforms as $platform ) {
      if ( $platform['is_default'] ) {
        $default_id = (int)$platform['id'];
        break;
      }
    }
    return $default_id;
  }

  public static function activeId()
  {
    $platforms = self::getList();
    return ( count($platforms)>1 && defined('PLATFORM_ID') && PLATFORM_ID>0 )?(int)PLATFORM_ID:0;
  }

  public static function currentId()
  {
    return ( defined('PLATFORM_ID') && PLATFORM_ID>0 )?(int)PLATFORM_ID:self::firstId();
  }

  public static function isMulti($withVirtual = true)
  {
    $platforms = self::getList($withVirtual);
    return count($platforms)>1;
  }

  public static function firstId()
  {
    $platforms = self::getList(false);
    return count($platforms)>0?$platforms[0]['id']:0;
  }

  public static function validId($id)
  {
    $is_valid = false;
    $platforms = self::getList(false);
    foreach ($platforms as $platform){
      if ($id == $platform['id']) $is_valid = true;
    }
    if ($is_valid){
      return $id;
    } else {
      return self::currentId();
    }
  }

}