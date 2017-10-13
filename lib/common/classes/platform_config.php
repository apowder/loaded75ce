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

class platform_config
{
  protected $id;
  protected $platform;
  protected $platform_address;
  protected $platform_config;
  protected $catalogBaseUrlWithId = false;

  function __construct($platform_id)
  {
    $this->id = $platform_id;
    $this->load();
  }

  public function getId()
  {
    return $this->id;
  }

  protected function load()
  {
    $get_platform_data_r = tep_db_query("SELECT * FROM ".TABLE_PLATFORMS." WHERE platform_id='".(int)$this->id."'");
    if (tep_db_num_rows($get_platform_data_r)>0 ) {
      $this->platform = tep_db_fetch_array($get_platform_data_r);
      if ($this->platform['is_virtual'] == 1) {
          $default_platform = tep_db_fetch_array(tep_db_query(
            "select * from platforms " .
            "where is_default=1 " .
            "LIMIT 1 "
          ));
          $this->platform['platform_url'] = $default_platform['platform_url'];
          $this->platform['platform_url_secure'] = $default_platform['platform_url_secure'];
          $this->platform['ssl_enabled'] = $default_platform['ssl_enabled'];
          if ($this->platform['is_default_contact'] == 1) {
            $this->platform['platform_email_from'] = $default_platform['platform_email_from'];
            $this->platform['platform_email_address'] = $default_platform['platform_email_address'];
            $this->platform['platform_email_extra'] = $default_platform['platform_email_extra'];
          }
      }
      
      if ( empty($this->platform['platform_url_secure']) ) {
        $this->platform['platform_url_secure'] = $this->platform['platform_url'];
      }

      if ($this->platform['is_default_address'] == 1) {
        $get_address_book_r = tep_db_query(
          "SELECT entry_company_vat, ".
          " entry_company as company, ".
          " entry_street_address as street_address, entry_suburb as suburb, ".
          " entry_city as city, entry_postcode as postcode, ".
          " entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id ".
          "FROM ".TABLE_PLATFORMS_ADDRESS_BOOK." ".
          "WHERE platform_id='".intval($default_platform['platform_id'])."' ".
          "ORDER BY IF(is_default=1,0,1) LIMIT 1"
        );
      } else {
        $get_address_book_r = tep_db_query(
          "SELECT entry_company_vat, ".
          " entry_company as company, ".
          " entry_street_address as street_address, entry_suburb as suburb, ".
          " entry_city as city, entry_postcode as postcode, ".
          " entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id ".
          "FROM ".TABLE_PLATFORMS_ADDRESS_BOOK." ".
          "WHERE platform_id='".intval($this->platform['platform_id'])."' ".
          "ORDER BY IF(is_default=1,0,1) LIMIT 1"
        );
      }
      if ( tep_db_num_rows($get_address_book_r)>0 ) {
        $this->platform_address = tep_db_fetch_array($get_address_book_r);
      }

      $get_platform_config_r = tep_db_query("SELECT configuration_key, configuration_value FROM ".TABLE_PLATFORMS_CONFIGURATION." WHERE platform_id='".intval($this->platform['platform_id'])."'");
      if ( tep_db_num_rows($get_platform_config_r)>0 ) {
        while( $_platform_config = tep_db_fetch_array($get_platform_config_r) ){
          $this->platform_config[$_platform_config['configuration_key']] = $_platform_config['configuration_value'];
        }
      }
    }
  }
  
  public function getPlatformAddress(){
    return $this->platform_address;
  }

  public function catalogBaseUrlWithId($use_id = false)
  {
    $this->catalogBaseUrlWithId = $use_id;
  }

  public function isCatalogBaseUrlWithId()
  {
    return $this->catalogBaseUrlWithId;
  }

  public function getPlatformCode()
  {
    return $this->platform['platform_code'];
  }

  public function isVirtual()
  {
      return !!$this->platform['is_virtual'];
  }
  
  public function getCatalogBaseUrl($ssl=false)
  {
    $ssl_status = defined('ENABLE_SSL_CATALOG')?(ENABLE_SSL_CATALOG===true || ENABLE_SSL_CATALOG==='true'):ENABLE_SSL;

    if ( $this->isCatalogBaseUrlWithId() && defined('HTTPS_CATALOG_SERVER') ) {
      $catalog_base = ($ssl && $ssl_status) ? (HTTPS_CATALOG_SERVER . DIR_WS_CATALOG) : (HTTP_CATALOG_SERVER . DIR_WS_CATALOG);
    }else{
      $catalog_base = ($ssl && $this->platform['ssl_enabled'])?('https://' . $this->platform['platform_url_secure'] . '/'):('http://' . $this->platform['platform_url'] . '/');
    }
    return $catalog_base;
  }

  public function getImagesCdnUrl()
  {
    $cdn_server = '';
    if ( $this->platform['platform_images_cdn_status']!='off' && !empty($this->platform['platform_images_cdn_url']) ) {
      if ($this->platform['platform_images_cdn_status']=='non_ssl' && !\Yii::$app->request->getIsSecureConnection()){
        $cdn_server = rtrim('http://'.$this->platform['platform_images_cdn_url'],'/').'/';
        if ($cdn_server==$this->getCatalogBaseUrl()){
          $cdn_server = '';
        }
      }elseif ( $this->platform['platform_images_cdn_status']=='ssl_supported' ) {
        $cdn_server = rtrim('https://'.$this->platform['platform_images_cdn_url'],'/').'/';
        if ($cdn_server==$this->getCatalogBaseUrl(true)){
          $cdn_server = '';
        }
      }
    }
    return $cdn_server;
  }

  public function getAllowedCurrencies(){
    if (tep_not_null($this->platform['defined_currencies'])){
      return explode(',',$this->platform['defined_currencies']);
    }
    return false;
  }
  
  public function getDefaultCurrency(){
    if (tep_not_null($this->platform['default_currency'])){
      return $this->platform['default_currency'];
    }
    return false;
  }  

  public function getAllowedLanguages(){
    if (tep_not_null($this->platform['defined_languages'])){
      return explode(',',$this->platform['defined_languages']);
    }
    return false;
  }
  
  public function getDefaultLanguage(){
    if (tep_not_null($this->platform['default_language'])){
      return $this->platform['default_language'];
    }
    return false;
  }  
  
  public function checkNeedSocials(){
    return (bool)$this->platform['use_social_login'];
    
  }
  
  public function constant_up(){
    if ( !is_array($this->platform_config) ) return;
    foreach( $this->platform_config as $key=>$val ) {
      if ( !defined($key) ) define($key, $val);
    }
  }

  public function const_value($key, $default='')
  {
    if ( isset($this->platform_config[$key]) ) {
      return $this->platform_config[$key];
    }elseif ( $key=='STORE_NAME' ) {
      return $this->platform['platform_name'];
    }elseif ( $key=='STORE_OWNER' ) {
      return $this->platform['platform_owner'];
    }elseif ( $key=='EMAIL_FROM' ) {
      return $this->platform['platform_email_from'];
    }elseif ( $key=='STORE_OWNER_EMAIL_ADDRESS' ) {
      return $this->platform['platform_email_address'];
    }elseif ( $key=='STORE_ADDRESS' ) {
      if ( function_exists('\common\helpers\Address::address_format') ) {
        $formatted = \common\helpers\Address::address_format(max(1,$this->platform_address['format_id']),$this->platform_address,false,'',"\n");
        $formatted = preg_replace("/\n\s*/ms","\n",$formatted); // remove empty customer name
        return $formatted;
      }
      //return $this->platform_address;
    }elseif( $key=='SEND_EXTRA_ORDER_EMAILS_TO' ) {
      return $this->platform['platform_email_extra'];
    }

    return defined($key)?constant($key):$default;
  }
  

    public function getGoogleShopPlatformId($code)
    {
        $platform_id = 0;
        $configValue = $this->const_value('GOOGLE_BASE_SHOP_PLATFORM_ID');
        if ( preg_match('/^(.*):(\d+)$/', $configValue, $match) && strtolower($code) == strtolower($match[1]) ) {
            $platform_id = (int)$match[2];
        }
        return empty($platform_id)?$this->id:$platform_id;
    }
    
    public function setConfigValue($key, $value)
    {
        if ( (int)$this->id==0 ) return false;
        $platformKeyCheck = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM ".TABLE_PLATFORMS_CONFIGURATION." ".
            "WHERE configuration_key='".tep_db_input($key)."' AND platform_id='".(int)$this->id."'"
        ));
        if ( $platformKeyCheck['c']==0 ) {
            $template_r = tep_db_query(
                "SELECT * ".
                "FROM ".TABLE_CONFIGURATION." ".
                "WHERE configuration_key='".tep_db_input($key)."'"
            );
            if ( tep_db_num_rows($template_r)==0 ) return false;
            $template = tep_db_fetch_array($template_r);
            unset($template['configuration_id']);
            $template['platform_id'] = (int)$this->id;
            tep_db_perform(TABLE_PLATFORMS_CONFIGURATION, $template);
        }
        tep_db_query(
            "UPDATE ".TABLE_PLATFORMS_CONFIGURATION." ".
            "SET configuration_value='".tep_db_input($value)."', last_modified=NOW() ".
            "WHERE configuration_key='".tep_db_input($key)."' AND platform_id='".(int)$this->id."'"
        );
        return true;
    }

}