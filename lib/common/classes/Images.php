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
use frontend\design\Info;

/**
 * Product Images
 *
 * @property array $data
 */
class Images {

    const watermarkPrefix = [
        'top_left_',
        'top_',
        'top_right_',
        'left_',
        '',
        'right_',
        'bottom_left_',
        'bottom_',
        'bottom_right_',
      ];

    public static function getFSCatalogImagesPath() {
        if (defined('DIR_FS_CATALOG_IMAGES')) {
            return DIR_FS_CATALOG_IMAGES;
        }
        return DIR_FS_CATALOG . DIR_WS_IMAGES;
    }
    
    public static function getWSCatalogImagesPath($use_cdn=false) {
        if (defined('DIR_WS_CATALOG_IMAGES')) {
            return DIR_WS_CATALOG_IMAGES;
        }
        if ($use_cdn) {
          $platform_config = \Yii::$app->get('platform')->config();
          $cdn_server = $platform_config->getImagesCdnUrl();
          if ( !empty($cdn_server) ) {
            return $cdn_server.DIR_WS_IMAGES;
          }
        }
        return /*DIR_WS_HTTP_CATALOG .*/ DIR_WS_IMAGES;
    }

    public function __construct() {
        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $this->createFolder($path);
    }

    public static function checkAttribute($products_images_id = 0, $products_options_id = 0, $products_options_values_id = 0) {
        $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES . " where products_images_id = '" . (int)$products_images_id  . "' and products_options_id = '" . (int)$products_options_id  . "' and products_options_values_id = '" . (int)$products_options_values_id  . "'");
        if (tep_db_num_rows($images_query) >0) {
            return true;
        }
        return false;
    }
    
    public static function getImageExists($productsId = 0, $typeName = 'Thumbnail', $languageId = 0, $imageId = 0) {
        $imagePath = self::getImage($productsId, $typeName, $languageId, $imageId);
        if (empty($imagePath)) {
            return false;
        }
    }

    public static function getImageTypes($type_name=false)
    {
        static $types = false;
        if ( !is_array($types) ) {
            $image_types_query = tep_db_query("select * from " . TABLE_IMAGE_TYPES . " where 1");
            while ($image_types = tep_db_fetch_array($image_types_query)) {
                $types[] = $image_types;
            }
        }
        if ( $type_name!==false ) {
            foreach( $types as $type ) {
                if ( strtolower($type['image_types_name'])==strtolower($type_name) ) {
                    return $type;
                }
            }
            return false;
        }
        return $types;
    }

    public static function getImageList($productsId = 0, $languageId = -1) {
        if ( $languageId<0 ) {
          $languageId = (int)$_SESSION['languages_id'];
        }
        $products_name = \common\helpers\Product::get_products_name($productsId, $languageId);
        $images = [];
        
        $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where products_id = '-1'");
        if ($ext = \common\helpers\Acl::checkExtension('InventortyImages', 'getQuery')) {
            $images_query = $ext::getQuery($productsId);
        }
        if ($ext = \common\helpers\Acl::checkExtension('AttributesImages', 'getQuery')) {
            $images_query = $ext::getQuery($images_query, $productsId);
        }
        if (tep_db_num_rows($images_query) == 0) {
            $productsId = \common\helpers\Inventory::get_prid($productsId);
            $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where image_status = 1 and products_id = '" . (int)$productsId  . "' order by default_image DESC, sort_order");
        }
        while ($images_data = tep_db_fetch_array($images_query)) {
            
            $item = [];

            foreach( self::getImageTypes() as $image_types ) {

                $image = self::getImageUrl($productsId, $image_types['image_types_name'], $languageId, $images_data['products_images_id']);
                if (!empty($image)) {


                    $item[$image_types['image_types_name']] = [
                        'url' => $image,
                        'type' => $image_types['image_types_name'],
                        'x' => $image_types['image_types_x'],
                        'y' => $image_types['image_types_y'],
                    ];
                }
                
                
            }
            
            //$images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where file_name !='' and language_id = '" . (int)$languageId . "' and products_images_id = '" . (int)$images_data['products_images_id']  . "'");
            //$images_description_data = tep_db_fetch_array($images_description_query);
            $images_description = false;
            $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where /*(file_name !='' or alt_file_name!='') and*/ language_id = '" . (int)$languageId . "' and products_images_id = '" . (int)$images_data['products_images_id']  . "'");
            if (tep_db_num_rows($images_description_query) > 0) {
              $images_description = tep_db_fetch_array($images_description_query);
            }
            $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where file_name !='' and products_images_id = '" . (int)$images_data['products_images_id']  . "' and language_id = '0'");
            if (tep_db_num_rows($images_description_query)>0) {
              $images_description_default = tep_db_fetch_array($images_description_query);
              if ($images_description==false) {
                $images_description = $images_description_default;
              }else{
                //merge data
                foreach ( $images_description_default as $_key=>$default_value ) {
                  if ( $_key=='language_id' && (empty($images_description['file_name']) && empty($images_description['alt_file_name'])) ) {
                    $images_description[$_key] = $default_value;
                  }else
                    if ( empty($images_description[$_key]) && !empty($default_value) ) {
                      $images_description[$_key] = $default_value;
                    }
                }
              }
            }
            $images_description_data = $images_description;

            if (count($item) > 0) {
                $images[$images_data['products_images_id']] = [
                    //'id' => $images_description_data['products_images_id'],
                    'image' => $item,
                    'alt' => empty($images_description_data['image_alt'])?$products_name:$images_description_data['image_alt'],
                    'title' => empty($images_description_data['image_title'])?$products_name:$images_description_data['image_title'],
                    'default' => $images_data['default_image'],
                    'sort_order' => 0,
                ];
            }
        }
        return $images;
    }
        
    /**
     * 
     * @param type $productsId
     * @param type $typeName
     * @param type $languageId - if language id set to -1 then find by priority main->1->2->...
     * @param integer $imageId - if image id not set then use default image
     */
    public static function getImageUrl($productsId = 0, $typeName = 'Thumbnail', $languageId = -1, $imageId = 0) {
        if ( $languageId<0 ) {
          $languageId = (int)$_SESSION['languages_id'];
        }
        $uprid = \common\helpers\Inventory::normalize_id($productsId);
        $productsId = \common\helpers\Inventory::get_prid($productsId);

        if ($imageId == 0) {
            $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where products_id = '-1'");
            if ($ext = \common\helpers\Acl::checkExtension('InventortyImages', 'getQuery')) {
                $images_query = $ext::getQuery(tep_db_input($uprid), ' LIMIT 1');
            }
            if ($ext = \common\helpers\Acl::checkExtension('AttributesImages', 'getQuery')) {
                $images_query = $ext::getQuery($images_query, $uprid, ' LIMIT 1');
            }
            if ( tep_db_num_rows($images_query)==0 ) {
                $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where image_status = 1 and products_id = '" . (int)$productsId . "' order by default_image DESC, sort_order LIMIT 1");
            }
        } else {
            $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where image_status = 1 and products_id = '" . (int)$productsId . "' and products_images_id = '" . (int)$imageId . "'");
        }
        if (tep_db_num_rows($images_query) == 0) {
            return Info::themeFile('/img/na.png');
        }
        $images = tep_db_fetch_array($images_query);
        $imageId = $images['products_images_id'];
        
        $image_types = self::getImageTypes($typeName);
        if ( $image_types===false ) {
            return Info::themeFile('/img/na.png');
        }

        $images_description = false;
        $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where /*(file_name !='' or alt_file_name!='') and*/ language_id = '" . (int)$languageId . "' and products_images_id = '" . (int)$imageId  . "'");
        if (tep_db_num_rows($images_description_query) > 0) {
          $images_description = tep_db_fetch_array($images_description_query);
        }
        $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where file_name !='' and products_images_id = '" . (int)$imageId  . "' and language_id = '0'");
        if (tep_db_num_rows($images_description_query)>0) {
          $images_description_default = tep_db_fetch_array($images_description_query);
          if ($images_description==false) {
            $images_description = $images_description_default;
          }else{
            //merge data
            foreach ( $images_description_default as $_key=>$default_value ) {
              if ( $_key=='language_id' && (empty($images_description['file_name']) && empty($images_description['alt_file_name'])) ) {
                $images_description[$_key] = $default_value;
              }else
                if ( empty($images_description[$_key]) && !empty($default_value) ) {
                  $images_description[$_key] = $default_value;
                }
            }
          }
        }
        if ($images_description===false) {
            return Info::themeFile('/img/na.png');
        }

        $language = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            if ($languages[$i]['id'] == $images_description['language_id']) {
                $language = $languages[$i]['code'];
                break;
            }
        }
        
        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $product_image_location = $path . $productsId . DIRECTORY_SEPARATOR . $imageId . DIRECTORY_SEPARATOR;
        $image_location = $product_image_location . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . DIRECTORY_SEPARATOR;
        if (!empty($language)) {
            $image_location .= $language . DIRECTORY_SEPARATOR;
        }

        $imageName = $images_description['file_name'];
        if (!empty($images_description['alt_file_name'])) {
            $imageName = $images_description['alt_file_name'];
        }

        if (file_exists($image_location . $imageName)) {
            $target_image_info = getimagesize($image_location . $imageName);
            $partial_file_name = 'products/' . $productsId . '/' . $imageId . '/' . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . '/' . (!empty($language) ? $language . '/' : '') . $imageName;
            if ($images_description['no_watermark'] == 0 && self::useWaterMark($target_image_info[0], $target_image_info[1]) )
            {
                $watermark_image = self::getWatermarkImage(PLATFORM_ID, $target_image_info[0]);
                if (is_array($watermark_image)) {
                    $watermark_mtime = 0;
                    foreach ($watermark_image as $watermark_image_path) {
                        $watermark_image_mtime = filemtime($watermark_image_path);
                        if ($watermark_image_mtime > $watermark_mtime) {
                            $watermark_mtime = $watermark_image_mtime;
                        }
                    }
                } else {
                    $watermark_mtime = 0;
                }
                //$watermark_mtime = empty($watermark_image)?0:filemtime($watermark_image);
                $cache_path = self::allocateCacheKey(array(
                  'image_size' => $image_types['image_types_x'] . 'x' . $image_types['image_types_y'],
                  'platform_id' => PLATFORM_ID,
                  'watermark_image' => $watermark_image,
                  'watermark_mtime' => $watermark_mtime,
                  'language' => strval($language),
                ));
                if ( is_file( self::getFSCatalogImagesPath().'cached/'.$cache_path. $partial_file_name) ) {
                  $cached_time = filemtime( self::getFSCatalogImagesPath().'cached/'.$cache_path. $partial_file_name);
                  $source_time = self::getFSCatalogImagesPath() . $partial_file_name;
                  if ( $source_time>$cached_time || $watermark_mtime>$cached_time ) {
                    @unlink(self::getFSCatalogImagesPath().'cached/'.$cache_path. $partial_file_name);
                  }
                }
                $imageUrl = self::getWSCatalogImagesPath(true) . 'cached/'.$cache_path. $partial_file_name;
            } else {
                $imageUrl = self::getWSCatalogImagesPath(true) . $partial_file_name;
            }
            return $imageUrl;
        }

        return Info::themeFile('/img/na.png');
        
        // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')
    }
    
    public static function getImage($productsId = 0, $typeName = 'Thumbnail', $languageId = 0, $imageId = 0) {

        $uprid = \common\helpers\Inventory::normalize_id($productsId);
        $productsId = \common\helpers\Inventory::get_prid($productsId);

        if ($imageId == 0) {
            $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where products_id = '-1'");
            if ($ext = \common\helpers\Acl::checkExtension('InventortyImages', 'getQuery')) {
                $images_query = $ext::getQuery(tep_db_input($uprid), ' LIMIT 1');
            }
            if ($ext = \common\helpers\Acl::checkExtension('AttributesImages', 'getQuery')) {
                $images_query = $ext::getQuery($images_query, $uprid, ' LIMIT 1');
            }
            if ( tep_db_num_rows($images_query)==0 ) {
                $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where image_status = 1 and products_id = '" . (int)$productsId . "' order by default_image DESC, sort_order LIMIT 1");
            }
        } else {
            $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where image_status = 1 and products_id = '" . (int)$productsId  . "' and products_images_id = '" . (int)$imageId  . "'");
        }
        if (tep_db_num_rows($images_query) == 0) {
            return '';
        }
        $images = tep_db_fetch_array($images_query);
        $imageId = $images['products_images_id'];

        $image_types = self::getImageTypes($typeName);
        if ( $image_types===false ) {
            return Info::themeFile('/img/na.png');
        }

        $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where (file_name !='' or alt_file_name!='') and language_id = '" . (int)$languageId . "' and products_images_id = '" . (int)$imageId  . "'");
        if (tep_db_num_rows($images_description_query) == 0) {
            $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where file_name !='' and products_images_id = '" . (int)$imageId  . "' and language_id = '0'");
        }
        if (tep_db_num_rows($images_description_query) == 0) {
            return '';
        }
        $images_description = tep_db_fetch_array($images_description_query);
           
        $language = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            if ($languages[$i]['id'] == $images_description['language_id']) {
                $language = $languages[$i]['code'];
                break;
            }
        }
        
        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $product_image_location = $path . $productsId . DIRECTORY_SEPARATOR . $imageId . DIRECTORY_SEPARATOR;
        $image_location = $product_image_location . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . DIRECTORY_SEPARATOR;
        if (!empty($language)) {
            $image_location .= $language . DIRECTORY_SEPARATOR;
        }

        $imageName = $images_description['file_name'];
        if (!empty($images_description['alt_file_name'])) {
            $imageName = $images_description['alt_file_name'];
        }
        if (file_exists($image_location . $imageName)) {
            $partial_file_name = 'products/' . $productsId . '/' . $imageId . '/' . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . '/' . (!empty($language) ? $language . '/' : '') . $imageName;
            if ($images_description['no_watermark'] == 0 && self::useWaterMark($image_types['image_types_x'], $image_types['image_types_y']))
            {
                $watermark_image = self::getWatermarkImage(PLATFORM_ID, $image_types['image_types_x']);
                if (is_array($watermark_image)) {
                    $watermark_mtime = 0;
                    foreach ($watermark_image as $watermark_image_path) {
                        $watermark_image_mtime = filemtime($watermark_image_path);
                        if ($watermark_image_mtime > $watermark_mtime) {
                            $watermark_mtime = $watermark_image_mtime;
                        }
                    }
                } else {
                    $watermark_mtime = 0;
                }
                //$watermark_mtime = empty($watermark_image)?0:filemtime($watermark_image);
                $cache_path = self::allocateCacheKey(array(
                  'image_size' => $image_types['image_types_x'] . 'x' . $image_types['image_types_y'],
                  'platform_id' => PLATFORM_ID,
                  'watermark_image' => $watermark_image,
                  'watermark_mtime' => $watermark_mtime,
                  'language' => strval($language),
                ));
                if ( is_file( self::getFSCatalogImagesPath().'cached/'.$cache_path. $partial_file_name) ) {
                  $cached_time = filemtime( self::getFSCatalogImagesPath().'cached/'.$cache_path. $partial_file_name);
                  $source_time = self::getFSCatalogImagesPath() . $partial_file_name;
                  if ( $source_time>$cached_time || $watermark_mtime>$cached_time ) {
                    @unlink(self::getFSCatalogImagesPath().'cached/'.$cache_path. $partial_file_name);
                  }
                }
                $imageUrl = self::getWSCatalogImagesPath(true) . 'cached/'.$cache_path. $partial_file_name;
            } else {
                $imageUrl = self::getWSCatalogImagesPath(true) . $partial_file_name;
            }
            return tep_image(array(
              'file' => $image_location . $imageName,
              'src' => $imageUrl,
            ), $images_description['image_alt']);
            //return tep_image($imageUrl, $images_description['image_alt']);//$image_types['image_types_x'], $image_types['image_types_y']
            //return $imageUrl;
        }
        
        return '';
        
        // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')
    }
    
    private static function allocateCacheKey($params)
    {
      $platform_id = (int)$params['platform_id'];
      if (is_array($params['watermark_image'])) {
          $watermark_image = serialize($params['watermark_image']);
      } else {
          $watermark_image = '';
      }
      $key_data = array(
        'platform_id' => $platform_id,
        'image_size' => isset($params['image_size'])?$params['image_size']:'',
        'watermark_image' => $watermark_image,
        'watermark_mtime' => isset($params['watermark_mtime'])?$params['watermark_mtime']:'',
      );
      $params = array_diff_key($params, $key_data);
      ksort($params);

      $key_data['extra_params'] = count($params)>0?base64_encode(serialize($params)):'';

      $internal_key = md5(implode('/',$key_data));

      static $lookup = array();
      if ( !isset($lookup[$internal_key]) ) {
        $get_external_key_r = tep_db_query(
          "SELECT external_key ".
          "FROM ".TABLE_IMAGE_CACHE_KEYS." ".
          "WHERE internal_key='{$internal_key}' AND is_valid=1 AND platform_id='{$platform_id}'"
        );
        if ( tep_db_num_rows($get_external_key_r)>0 ) {
          $_external_key = tep_db_fetch_array($get_external_key_r);
          $lookup[$internal_key] = $_external_key['external_key'];
        }else{
          do {
            $external_key = strtoupper(uniqid());
            $check_key = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS c FROM " . TABLE_IMAGE_CACHE_KEYS . " WHERE external_key='" . $external_key . "' "
            ));
          }while($check_key['c']==1);

          $key_data['external_key'] = $external_key;
          $key_data['internal_key'] = $internal_key;
          tep_db_perform(TABLE_IMAGE_CACHE_KEYS, $key_data);
          $lookup[$internal_key] = $external_key;
        }
      }
      $external_key = $lookup[$internal_key];

      return $external_key.'/';
    }

    public static function cacheKeyInvalidateByWatermark($watermark_name, $platform_id=0)
    {
      if ( !empty($watermark_name) ) {
        tep_db_query(
          "UPDATE " . TABLE_IMAGE_CACHE_KEYS . " ".
          "SET is_valid=0 ".
          "WHERE watermark_image='" . tep_db_input($watermark_name) . "' ".
          ($platform_id>0?"AND platform_id='".(int)$platform_id."' ":'')
        );
      }
    }
    public static function cacheKeyInvalidateByPlatformId($platform_id)
    {
      if ( !empty($watermark_name) ) {
        tep_db_query("UPDATE " . TABLE_IMAGE_CACHE_KEYS . " SET is_valid=0 WHERE platform_id='" . (int)$platform_id . "'");
      }
    }

    public static function cacheFlush($deep_check=false)
    {
      /*if ( $deep_check ) {
        $get_valid_keys_r = tep_db_query(
          "SELECT * ".
          "FROM ".TABLE_IMAGE_CACHE_KEYS." ".
          "WHERE is_valid=1"
        );
        if ( tep_db_num_rows($get_valid_keys_r)>0 ) {
          while ($cache_data = tep_db_fetch_array($get_valid_keys_r)) {

          }
        }
      }*/
      $get_invalid_keys_r = tep_db_query("SELECT external_key FROM ".TABLE_IMAGE_CACHE_KEYS." WHERE is_valid=0");
      if ( tep_db_num_rows($get_invalid_keys_r)>0 ) {
        while ($invalid_key = tep_db_fetch_array($get_invalid_keys_r)) {
          $flush_dir = self::getFSCatalogImagesPath() . 'cached/'.$invalid_key['external_key'];
          \yii\helpers\BaseFileHelper::removeDirectory($flush_dir);
          if (!is_dir($flush_dir)) {
            tep_db_query("DELETE FROM ".TABLE_IMAGE_CACHE_KEYS." WHERE external_key='".tep_db_input($invalid_key['external_key'])."'");
          }
        }
      }
    }


  public static function getTypeFromFile($file_name)
    {
      $extension = '';
      if (is_file($file_name) && $image_info = @getimagesize($file_name)){
        switch( $image_info[2] ) {
          case IMAGETYPE_GIF: $extension = 'gif'; break;
          case IMAGETYPE_JPEG: $extension = 'jpg'; break;
          case IMAGETYPE_PNG: $extension = 'png'; break;
          case IMAGETYPE_BMP: $extension = 'bmp'; break;
        }
      }
      return $extension;
    }
    
    // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')

    public function createImages($productsId, $imageId, $hashName, $imageName, $language = '') {
        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $product_image_location = $path . $productsId . DIRECTORY_SEPARATOR;
        $this->createFolder($product_image_location);
        $product_image_location .= $imageId . DIRECTORY_SEPARATOR;
        $this->createFolder($product_image_location);

        $image_types_query = tep_db_query("select * from " . TABLE_IMAGE_TYPES . " order by image_types_id");
        while ($image_types = tep_db_fetch_array($image_types_query)) {
            $image_location = $product_image_location . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . DIRECTORY_SEPARATOR;
            $this->createFolder($image_location);
            if (!empty($language)) {
                $image_location .= $language . DIRECTORY_SEPARATOR;
                $this->createFolder($image_location);
            }
            $this->createImage($product_image_location . $hashName, $image_location . $imageName, $image_types['image_types_x'], $image_types['image_types_y']);
        }
    }

    /**
     * Create image
     * @param string $path
     * @param integer $width
     * @param integer $height
     */
    public function createImage($source_image, $destination_image, $width, $height) {
        self::tep_image_resize($source_image, $destination_image, $width, $height);
    }

    /**
     * Create folder
     * @param string $path
     */
    public function createFolder($path) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            @chmod($path,0777);
        }
    }

    public static function tep_image_resize($image, $t_location, $thumbnail_width, $thumbnail_height) {
        $image = str_replace("/./", "/", str_replace("//", "/", $image));
        $t_location = str_replace("/./", "/", str_replace("//", "/", $t_location));
        $size = @GetImageSize($image);
        if (($thumbnail_width >= $size[0]) && ($thumbnail_height >= $size[1])) {
            if ($image != $t_location) {
                @copy($image, $t_location);
                @chmod($t_location,0666);
            }
            return true;
        }
        if (IMAGE_RESIZE != 'GD' && IMAGE_RESIZE != 'ImageMagick') {
            return false;
        }
        if (IMAGE_RESIZE == 'ImageMagick') {
            if (is_executable(CONVERT_UTILITY)) {
                @exec(CONVERT_UTILITY . ' -thumbnail ' . $thumbnail_width . 'x' . $thumbnail_height . ' ' . $image . ' ' . $t_location);
                @chmod($t_location,0666);
                return true;
            }
            return false;
        } elseif (IMAGE_RESIZE == 'GD') {
            if (function_exists("gd_info")) {
                $scale = @min($thumbnail_width / $size[0], $thumbnail_height / $size[1]);
                $x = $size[0] * $scale;
                $y = $size[1] * $scale;

                switch ($size[2]) {
                    case 1 : // GIF
                        $im = @ImageCreateFromGif($image);
                        break;
                    case 3 : // PNG
                        $im = @ImageCreateFromPng($image);
                        if ($im) {
                            if (function_exists('imageAntiAlias')) {
                                @imageAntiAlias($im, true);
                            }
                            @imageAlphaBlending($im, true);
                            @imageSaveAlpha($im, true);
                        }
                        break;
                    case 2 : // JPEG
                        $im = @ImageCreateFromJPEG($image);
                        break;
                    default :
                        return false;
                }

                if (!$im) {
                    return false;
                }

                $imPic = 0;
                if (function_exists('ImageCreateTrueColor'))
                    $imPic = @ImageCreateTrueColor($x, $y);
                if ($imPic == 0)
                    $imPic = @ImageCreate($x, $y);
                if ($imPic != 0) {
                    @ImageInterlace($imPic, 1);
                    if (function_exists('imageAntiAlias')) {
                        @imageAntiAlias($imPic, true);
                    }
                    @imagealphablending($imPic, false);
                    @imagesavealpha($imPic, true);
                    $transparent = @imagecolorallocatealpha($imPic, 255, 255, 255, 0);
                    for ($i = 0; $i < $x; $i++) {
                        for ($j = 0; $j < $y; $j++) {
                            @imageSetPixel($imPic, $i, $j, $transparent);
                        }
                    }
                    if (function_exists('ImageCopyResampled')) {
                        $resized = @ImageCopyResampled($imPic, $im, 0, 0, 0, 0, $x, $y, $size[0], $size[1]);
                    }
                    if (!$resized) {
                        @ImageCopyResized($imPic, $im, 0, 0, 0, 0, $x, $y, $size[0], $size[1]);
                    }
                } else {
                    return false;
                }
                
                if ($size[2] == 3) {
                    @imagePNG($imPic, $t_location, 9);
                } else {
                    @imageJPEG($imPic, $t_location, 85);
                }
                if (is_file($t_location)) {
                    chmod($t_location,0666);
                    return true;
                }
            }
        }
        return false;
    }

    public static function getPlatformWatermarks($platform_id=false){
      if (DEMO_STORE == 'true') {
        return [
          'watermark300' => 'demo300.png',
          'watermark170' => 'demo170.png',
          'watermark30' => 'demo30.png',
          ];
      }
      static $cached = array();
      $platform_id = ((int)$platform_id>0)?(int)$platform_id:(int)PLATFORM_ID;
      if ( !isset($cached[$platform_id]) ) {
        $cached[$platform_id] = false;
        $check_watermark_query = tep_db_query("SELECT * FROM " . TABLE_PLATFORMS_WATERMARK . " WHERE status=1 AND platform_id='{$platform_id}'");
        if ( tep_db_num_rows($check_watermark_query)>0 ) {
          $cached[$platform_id] = tep_db_fetch_array($check_watermark_query);
        }
      }
      return $cached[$platform_id];
    }

    public static function getWatermarkImage($platform_id, $baseWidth=0){
      $watermarkData = self::getPlatformWatermarks(PLATFORM_ID);
      if ( $watermarkData===false ) return false;

      if ($baseWidth > 299) {
        $watermarkName = 'watermark300';
      } elseif ($baseWidth > 169) {
        $watermarkName = 'watermark170';
      } else {
        $watermarkName = 'watermark30';
      }
      
      $watermarkFilenames = [];
      foreach (self::watermarkPrefix as $prefix) {
          if (isset($watermarkData[$prefix . $watermarkName]) && !empty($watermarkData[$prefix . $watermarkName])) {
                $watermark_filename = self::getFSCatalogImagesPath() . 'stamp' . DIRECTORY_SEPARATOR . $watermarkData[$prefix . $watermarkName];
                if (is_file($watermark_filename)) {
                  $watermarkFilenames[$prefix] =  $watermark_filename;
                }
          }
      }
      if (count($watermarkFilenames) > 0)  {
          return $watermarkFilenames;
      }
      return false;
    }

    public static function useWaterMark($base_width=0, $base_height=0) {
        global $customer_groups_id;
// {{
        if ($_GET['nowatermark'] == 1) {
            return false;
        }
// }}
        if (!defined('PLATFORM_ID')) {
            return false;
        }
        
        if (DEMO_STORE == 'true') {
            return true;
        }

        $watermark_image = self::getWatermarkImage(PLATFORM_ID, $base_width);
        if ($watermark_image === false) {
          return false;
        }

        if ($customer_groups_id > 0) {
            static $group_wm_status = array();
            if ( !isset($group_wm_status[(int)$customer_groups_id]) ) {
              $group_wm_status[(int)$customer_groups_id] = true;
              $groups_check = tep_db_fetch_array(tep_db_query(
                "select count(*) as wm_status from " . TABLE_GROUPS . " where groups_id = '" . (int)$customer_groups_id . "' AND disable_watermark=1"
              ));
              if ( $groups_check['wm_status']>0 ) {
                $group_wm_status[(int)$customer_groups_id] = false;
              }
            }
            if ( !$group_wm_status[(int)$customer_groups_id] ) {
                return false;
            }
        }
        /*if (CONFIG_IMAGE_WATERMARK != 'true') {
            return false;
        }*/
        
        return true;
    }

    public static function applyWatermark($source_image, $watermark_image, $output_file=null)
    {
      $size = @GetImageSize($source_image);

      $output_as = 'png';

      switch ($size[2]) {
        case 1 : // GIF
          $im = @ImageCreateFromGif($source_image);
          break;
        case 3 : // PNG
          $im = @ImageCreateFromPng($source_image);
          if ($im) {
            if (function_exists('imageAntiAlias')) {
              @imageAntiAlias($im, true);
            }
            @imageAlphaBlending($im, true);
            @imageSaveAlpha($im, true);
          }
          break;
        case 2 : // JPEG
          $im = @ImageCreateFromJPEG($source_image);
          $output_as = 'jpg';
          break;
        default :
          return false;
      }
      
      if (is_array($watermark_image)) {
          foreach ($watermark_image as $watermarkPosition => $watermarkImage) {
                $stamp = @imagecreatefrompng($watermarkImage);
                if ($stamp) {
                    switch ($watermarkPosition) {
                        case 'top_left_':
                            imagecopy($im, $stamp, 0, 0, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'top_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, 0, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'top_right_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)), 0, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'left_':
                            imagecopy($im, $stamp, 0, (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case '':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'right_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)), (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'bottom_left_':
                            imagecopy($im, $stamp, 0, (imagesy($im) - imagesy($stamp)), 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'bottom_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, (imagesy($im) - imagesy($stamp)), 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'bottom_right_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)), (imagesy($im) - imagesy($stamp)), 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        default:
                            break;
                    }
                }
          }
      } elseif ( !empty($watermark_image) ) {
        $stamp = @imagecreatefrompng($watermark_image);
        if ($stamp) {
          imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
          //imagecopymerge($im, $stamp, (imagesx($im) - imagesx($stamp))/2, (imagesy($im) - imagesy($stamp))/2, 0, 0, imagesx($stamp), imagesy($stamp), 10);
        }
      }

      if ( is_null($output_file) ) {
        header('Content-type: ' . $size['mime']);//image/png
      }
      if ( $output_file==='string' ) {
        ob_start();
        if ($output_as == 'jpg') {
          imagejpeg($im, null, 85);
        } else {
          imagepng($im, null, 9);
        }
        imagedestroy($im);
        return ob_get_clean();
      }else {
        if ($output_as == 'jpg') {
          imagejpeg($im, $output_file, 85);
        } else {
          imagepng($im, $output_file, 9);
        }
        imagedestroy($im);
      }
    }
    
    public static function waterMark($image = '') {
        $image = str_replace("/./", "/", str_replace("//", "/", $image));
        $image = DIR_FS_CATALOG . $image;
        if (!file_exists($image)) {
            return false;
        }
        
        $size = @GetImageSize($image);
        
        $watermark_image = false;
        if (self::useWaterMark($size[0]) ) {
          $watermark_image = self::getWatermarkImage(PLATFORM_ID, $size[0]);
        }
        self::applyWatermark($image, $watermark_image,'direct');
        
        die();
    }
            
}
