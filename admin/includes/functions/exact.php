<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\order;
use common\classes\language;

include_once('xmlparser.php');

if (!defined('PRODUCTS_CRON_LOCK_TIME')) {
  define('PRODUCTS_CRON_LOCK_TIME', 30); //in minutes
}

if (!defined('EXACT_AUTH_ERROR_COUNT_LIMIT')) {
  define('EXACT_AUTH_ERROR_COUNT_LIMIT', 5);
}

if (!defined('EXACT_ORDERNUMBER_SHIFT')) {
  define('EXACT_ORDERNUMBER_SHIFT', 100000);
}

if (!defined('PLATFORM_ID')) {
  define('PLATFORM_ID', \common\classes\platform::currentId());
}

function exact_run_products() {
  global $languages_id;
  $lng = new language();

  $params = array(
    'refresh_token' => EXACT_REFRESH_TOKEN,
    'grant_type' => 'refresh_token',
    'client_id' => EXACT_CLIENT_ID,
    'client_secret' => EXACT_CLIENT_SECRET,
  );
  $response = exact_call_http_url(EXACT_BASE_URL . '/api/oauth2/token', $params);
  $result = json_decode($response);

  if (is_object($result) && tep_not_null($result->refresh_token) && tep_not_null($result->access_token)) {
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($result->refresh_token) . "', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '0', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
    $access_token = $result->access_token;
  } else {
    if (EXACT_AUTH_ERROR_COUNT < EXACT_AUTH_ERROR_COUNT_LIMIT) {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . ((int)EXACT_AUTH_ERROR_COUNT + 1) . "', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
    } else {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
      exact_send_authorization_error_notification();
    }
    if (defined('TEXT_ERROR_AUTHORIZATION_TOKENS')) {
      return TEXT_ERROR_AUTHORIZATION_TOKENS;
    } else {
      return 'Error: Can not get Authorization Tokens';
    }
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////
  $headers = array(
    'Accept: Application/xml',
    'Content-Type: Application/xml',
    'Authorization: Bearer ' . $access_token
  );
  $response = exact_call_http_url(EXACT_BASE_URL . '/docs/XMLDownload.aspx?Topic=VATs' . '&_Division_=' . EXACT_CURRENT_DIVISION, array(), $headers);

  $VATs_array = array();
  $xml_tree = new xml_tree(CHARSET);
  $tree_node = $xml_tree->createTree($response);
  if (is_object($tree_node) && $root_node = $tree_node->item('eExact/VATs')) {
    for ($i = 0; $i < $root_node->itemcount('VAT'); $i++) {
      $VAT_array = array();
      $VAT_node = $root_node->item('VAT', $i);
      $VAT_array['code'] = decode_string($VAT_node->attributes['code']);
      $VAT_array['type'] = decode_string($VAT_node->attributes['type']); // E - excl. I - inlcl.
      for ($k = 0; $k < count($VAT_node->subitems); $k++) {
        $VAT_array[$VAT_node->subitems[$k]->name] = decode_string($VAT_node->subitems[$k]->content);
      }
      $VATs_array[] = $VAT_array;
    }
  }

  if (count($VATs_array) > 0) {
    $default_geo_zone = tep_db_fetch_array(tep_db_query("select tz.geo_zone_id from " . TABLE_TAX_ZONES . " tz left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tz.geo_zone_id = za.geo_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)STORE_COUNTRY . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)STORE_ZONE . "') order by tz.geo_zone_id limit 1"));
    $default_geo_zone_id = $default_geo_zone['geo_zone_id'];

    foreach ($VATs_array as $VAT) {
      if ( !($VAT['code'] > 0) ) continue;
      tep_db_query("replace into " . TABLE_TAX_CLASS . " (tax_class_id, tax_class_title, tax_class_description, date_added) values ('" . (int)$VAT['code'] . "', '" . tep_db_input($VAT['Description']) . "', 'Imported from Exact Online tax class.', now())");
      tep_db_query("replace into " . TABLE_TAX_RATES . " (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, tax_type, date_added) values ('" . (int)$VAT['code'] . "', '" . (int)$default_geo_zone_id . "', '" . (int)$VAT['code'] . "', '1', '" . (float)($VAT['Percentage'] * 100) . "', '" . tep_db_input($VAT['Description']) . "', '" . tep_db_input($VAT['type']) . "', now())");
    }
  }
  //////////////////////////////////////////////////////////////////////////////////////////////////////

  $headers = array(
    'Accept: Application/json',
    'Content-Type: Application/json',
    'Authorization: Bearer ' . $access_token
  );
// {{
  if (tep_not_null(EXACT_NEXT_ITEMS_URL) && strstr(EXACT_NEXT_ITEMS_URL, EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/read/logistics/Items')) {
    $exact_items_url = EXACT_NEXT_ITEMS_URL;
  } else {
    $exact_items_url = EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/read/logistics/Items?$select=ID,Code,IsSalesItem,IsStockItem,IsWebshopItem,ItemGroupDescription,ItemGroupCode,Stock,SalesVatCode,SalesPrice,PictureName,PictureUrl,Description,Notes&$filter=IsWebshopItem+eq+1';
  }
// }}
  $response = exact_call_http_url($exact_items_url, array(), $headers);
  $result = json_decode($response);

  if (is_object($result) && is_array($result->d->results)) {
    $Images = new \common\classes\Images();
    $new_products_count = 0;
    $all_products_array = array();
    foreach ($result->d->results as $item) {
      if (is_object($item)) {
//        print_r($item);

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    if (EXACT_DESCRIPTION_FIELD == 'ExtraDescription') {
      $resp = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/logistics/Items?$select=ID,Code,Class_01,Class_02,Class_03,Class_04,Class_05,ExtraDescription&$filter=ID+eq+guid\'' . urlencode($item->ID) . '\'', array(), $headers);
      $res = json_decode($resp);
      if (is_object($res) && is_object($item_extra = $res->d->results[0])) {
//        print_r($item_extra);
        $products_description = $item_extra->ExtraDescription;
      } else {
        $products_description = $item->Notes;
      }
    } else {
      $products_description = $item->Notes;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    $status = min($item->IsSalesItem, /* $item->IsStockItem, */ $item->IsWebshopItem);
    if ( !($status > 0) ) continue;

    if (tep_not_null($item->ID)) {
      $check = tep_db_fetch_array(tep_db_query("select products_id from " . TABLE_PRODUCTS . " where exact_id = '" . tep_db_input($item->ID) . "'"));
    } elseif (tep_not_null($item->Code)) {
      $check = tep_db_fetch_array(tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_model = '" . tep_db_input($item->Code) . "'"));
    } else {
      continue;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    $top_category = (tep_not_null($item->ItemGroupDescription) ? $item->ItemGroupDescription : $item->ItemGroupCode);
    if (tep_not_null($top_category)) {
      $top_category = substr($top_category, 0, 64);
      $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where /* c.parent_id = '0' and */ c.categories_id = cd.categories_id and cd.categories_name = '" . tep_db_input($top_category) . "'"));
      if ($category['categories_id'] > 0) {
        $top_categories_id = $category['categories_id'];
      } else {
        tep_db_query("insert into " . TABLE_CATEGORIES . " (parent_id, date_added) values ('0', now())");
        $top_categories_id = tep_db_insert_id();
      }
      $seo_name = \common\helpers\Seo::makeSlug($top_category);
      tep_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name, categories_seo_page_name) values ('" . (int)$top_categories_id . "', '" . (int)$languages_id . "', '" . tep_db_input($top_category) . "', '" . tep_db_input($seo_name . '-' . $top_categories_id) . "') on duplicate key update categories_name = '" . tep_db_input($top_category) . "', categories_seo_page_name = '" . tep_db_input($seo_name . '-' . $top_categories_id) . "'");
      tep_db_query("replace into " . TABLE_PLATFORMS_CATEGORIES . " (platform_id, categories_id) values ('" . (int)PLATFORM_ID . "', '" . (int)$top_categories_id . "')");
    } else {
      $top_categories_id = 0;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    if (tep_not_null($manufacturer)) {
      $manufacturer = substr($manufacturer, 0, 32);
      $check_m = tep_db_fetch_array(tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_name = '" . tep_db_input($manufacturer) . "'"));
      if ($check_m['manufacturers_id'] > 0) {
        $manufacturers_id = $check_m['manufacturers_id'];
      } else {
        tep_db_query("insert into " . TABLE_MANUFACTURERS . " (manufacturers_name, date_added) values ('" . tep_db_input($manufacturer) . "', now())");
        $manufacturers_id = tep_db_insert_id();
        tep_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url) values ('" . (int)$manufacturers_id . "', '" . (int)$languages_id . "', '')");
      }
    } else {
      $manufacturers_id = 0;
    }
*/
    ///////////////////////////////////////////////////////////////////////////////////////////////

    $vat = tep_db_fetch_array(tep_db_query("select tax_rate, tax_type from " . TABLE_TAX_RATES . " where tax_rates_id = '" . (int)$item->SalesVatCode . "'"));

    $sql_data_array = array('exact_id' => $item->ID,
                            'products_model' => $item->Code,
                            'products_quantity' => (int)$item->Stock,
//                            'products_weight' => (float)$weight,
                            'products_tax_class_id' => (int)$item->SalesVatCode,
                            'products_price' => (float)($vat['tax_type'] == 'I' ? $item->SalesPrice / (1 + ($vat['tax_rate'] / 100)) : $item->SalesPrice),
//                            'manufacturers_id' => $manufacturers_id,
                            'products_status' => $status);

    $image_updated = false;
    if (tep_not_null($item->PictureName) && tep_not_null($item->PictureUrl)) {
      $item->PictureUrl = preg_replace("/ThumbSize=\d+/", '', $item->PictureUrl);
      $item->PictureUrl = preg_replace("/OptimizeForWeb=\d+/", '', $item->PictureUrl);
      $headers = exact_parse_headers(exact_headers_http_url($item->PictureUrl, array('Authorization: Bearer ' . $access_token)));
      if (!is_file(DIR_FS_CATALOG_IMAGES . basename($item->PictureName)) || $headers['Content-Length'] != filesize(DIR_FS_CATALOG_IMAGES . basename($item->PictureName))) {
        $strDownloadContent = exact_call_http_url($item->PictureUrl, array(), array('Authorization: Bearer ' . $access_token));
        if (tep_not_null($strDownloadContent) && strpos($strDownloadContent, '<head>') === false) {
          $resFile = fopen(DIR_FS_CATALOG_IMAGES . basename($item->PictureName), 'w');
          fwrite($resFile, $strDownloadContent);
          fclose($resFile);
          $image_updated = true;
        }
      }
      $sql_data_array['products_image'] = basename($item->PictureName);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////

    if ($check['products_id']) {
      // Update
      $id = $check['products_id'];

      $update_sql_data = array('products_last_modified' => 'now()');
      $sql_data_array = array_merge($sql_data_array, $update_sql_data);

      tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$id . "'");
    } else {
      // Insert
      $insert_sql_data = array('products_date_added' => 'now()');
      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

      tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
      $id = tep_db_insert_id();
      $new_products_count++;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    $seo_name = \common\helpers\Seo::makeSlug($item->Description);

    $orig_file = $sql_data_array['products_image'];

    $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int)$id . "' and pi.default_image = '1' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '" . tep_db_input($orig_file) . "'"));

    $tmp_name = DIR_FS_CATALOG_IMAGES . $orig_file;

    if (!empty($orig_file) && file_exists($tmp_name) && ($image_updated || !($check['products_images_id'] > 0))) {
      // Delete old default image
      tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$id . "' and default_image = '1'");

      $image_location = DIR_FS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
      if (!file_exists($image_location)) {
        mkdir($image_location, 0777, true);
      }

      $sql_data_array = [];
      $sql_data_array['default_image'] = 1;
      $sql_data_array['image_status'] = 1;
      $sql_data_array['products_id'] = (int)$id;
      tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
      $imageId = tep_db_insert_id();

      $image_location .=  $imageId . DIRECTORY_SEPARATOR;
      if (!file_exists($image_location)) {
        mkdir($image_location, 0777, true);
      }

      $sql_data_array = [];
      $sql_data_array['language_id'] = 0;

      $file_name = $seo_name;
      $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
      $file_name .= '.' . $uploadExtension;
      $sql_data_array['file_name'] = $file_name;

      $hashName = md5($orig_file . '_' . date('dmYHis') . '_' . microtime(true));
      $new_name = $image_location . $hashName;

      copy($tmp_name, $new_name);
      $sql_data_array['hash_file_name'] = $hashName;

      $sql_data_array['orig_file_name'] = $orig_file;

      $product_name = $item->Description;
      $sql_data_array['image_title'] = $product_name;
      $sql_data_array['image_alt'] = $product_name;

      $lang = '';
      $Images->createImages($id, $imageId, $hashName, $file_name, $lang);

      $sql_data_array['products_images_id'] = (int)$imageId;
      $sql_data_array['language_id'] = 0;
      tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    $sql_data_array = array('products_id' => (int)$id,
                            'products_name' => $item->Description,
                            'products_description' => $products_description,
                            'products_seo_page_name' => $seo_name . '-' . $id,
                            'products_url' => '');

    $check = tep_db_fetch_array(tep_db_query("select count(*) as description_exists from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'"));
    if (!$check['description_exists']) {
      $insert_sql_data = array('language_id' => $languages_id);
      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
      tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
    } else {
      tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    tep_db_query("replace into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$id . "', '" . (int)$top_categories_id . "')");
    tep_db_query("replace into " . TABLE_PLATFORMS_PRODUCTS . " (platform_id, products_id) values ('" . (int)PLATFORM_ID . "', '" . (int)$id . "')");

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    if (is_object($lng) && is_array($lng->catalog_languages))
    foreach ($lng->catalog_languages as $language) {
//      print_r($language);

      $headers = array(
        'Accept: Application/json',
        'Content-Type: Application/json',
        'Authorization: Bearer ' . $access_token,
        'CustomDescriptionLanguage: ' . strtoupper($language['code']),
      );
      $resp = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/logistics/Items?$select=ID,ItemGroupDescription,ItemGroupCode,Code,Class_01,Class_02,Class_03,Class_04,Class_05,Description,ExtraDescription,Notes&$filter=ID+eq+guid\'' . urlencode($item->ID) . '\'', array(), $headers);
      $res = json_decode($resp);
      if (is_object($res) && is_object($item_lang = $res->d->results[0])) {
//        print_r($item_lang);

        if ($top_categories_id > 0) {
          $top_category = (tep_not_null($item_lang->ItemGroupDescription) ? $item_lang->ItemGroupDescription : $item_lang->ItemGroupCode);
          if (tep_not_null($top_category)) {
            $top_category = substr($top_category, 0, 64);
            $seo_name = \common\helpers\Seo::makeSlug($top_category);
            tep_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name, categories_seo_page_name) values ('" . (int)$top_categories_id . "', '" . (int)$language['id'] . "', '" . tep_db_input($top_category) . "', '" . tep_db_input($seo_name . '-' . $top_categories_id) . "') on duplicate key update categories_name = '" . tep_db_input($top_category) . "', categories_seo_page_name = '" . tep_db_input($seo_name . '-' . $top_categories_id) . "'");
          }
        }

        $seo_name = \common\helpers\Seo::makeSlug($item_lang->Description);
        if (EXACT_DESCRIPTION_FIELD == 'ExtraDescription') {
          $products_description = $item_lang->ExtraDescription;
        } else {
          $products_description = $item_lang->Notes;
        }
        tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_seo_page_name) values ('" . (int)$id . "', '" . (int)$language['id'] . "', '" . tep_db_input($item_lang->Description) . "', '" . tep_db_input($products_description) . "', '" . tep_db_input($seo_name . '-' . $id) . "') on duplicate key update products_name = '" . tep_db_input($item_lang->Description) . "', products_description = '" . tep_db_input($products_description) . "', products_seo_page_name = '" . tep_db_input($seo_name . '-' . $id) . "'");
      }
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

        $all_products_array[] = $id;
      }
    }

// {{
    if (tep_not_null(EXACT_ITEMS_IDS)) {
      $all_products_array = array_unique(array_merge($all_products_array, array_map('intval', explode(',', EXACT_ITEMS_IDS))));
    }
    if (tep_not_null($result->d->__next) && strstr($result->d->__next, EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/read/logistics/Items')) {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($result->d->__next) . "' where configuration_key = 'EXACT_NEXT_ITEMS_URL'");
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input(implode(',', array_map('intval', $all_products_array))) . "' where configuration_key = 'EXACT_ITEMS_IDS'");
    } else {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'EXACT_NEXT_ITEMS_URL'");
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'EXACT_ITEMS_IDS'");
      // Disable Products
      tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id not in ('" . implode("','", $all_products_array) . "')");
    }
// }}

    if (defined('TEXT_SUCCESS_PRODUCTS_SYNCED')) {
      return sprintf(TEXT_SUCCESS_PRODUCTS_SYNCED, count($all_products_array), (int)$new_products_count);
    } else {
      return sprintf('Success: Total of %s products synced (%s new products)', count($all_products_array), (int)$new_products_count);
    }
  } else {
    if (defined('TEXT_ERROR_ITEMS_LIST')) {
      return TEXT_ERROR_ITEMS_LIST;
    } else {
      return 'Error: Can not get the Items list';
    }
  }
}

function exact_run_products_qty() {
  $params = array(
    'refresh_token' => EXACT_REFRESH_TOKEN,
    'grant_type' => 'refresh_token',
    'client_id' => EXACT_CLIENT_ID,
    'client_secret' => EXACT_CLIENT_SECRET,
  );
  $response = exact_call_http_url(EXACT_BASE_URL . '/api/oauth2/token', $params);
  $result = json_decode($response);

  if (is_object($result) && tep_not_null($result->refresh_token) && tep_not_null($result->access_token)) {
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($result->refresh_token) . "', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '0', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
    $access_token = $result->access_token;
  } else {
    if (EXACT_AUTH_ERROR_COUNT < EXACT_AUTH_ERROR_COUNT_LIMIT) {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . ((int)EXACT_AUTH_ERROR_COUNT + 1) . "', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
    } else {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
      exact_send_authorization_error_notification();
    }
    if (defined('TEXT_ERROR_AUTHORIZATION_TOKENS')) {
      return TEXT_ERROR_AUTHORIZATION_TOKENS;
    } else {
      return 'Error: Can not get Authorization Tokens';
    }
  }

  $qtyproducts = 0;
  $products_query = tep_db_query("select products_id, exact_id from " . TABLE_PRODUCTS . " where products_status = '1' and length(exact_id) > 0");
  while ($products = tep_db_fetch_array($products_query)) {
    $headers = array(
      'Accept: Application/json',
      'Content-Type: Application/json',
      'Authorization: Bearer ' . $access_token
    );
    $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . "/read/logistics/StockPosition?itemId=guid'" . $products['exact_id'] . "'", array(), $headers);
    $result = json_decode($response);

    if (is_object($result) && is_object($result->d[0])) {
      tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int)$result->d[0]->InStock . "' where products_id  = '" . (int)$products['products_id'] . "'");
      $qtyproducts++;
    }
  }

  if (defined('TEXT_SUCCESS_PRODUCTS_STOCK_SYNCED')) {
    return sprintf(TEXT_SUCCESS_PRODUCTS_STOCK_SYNCED, (int)$qtyproducts);
  } else {
    return sprintf('Success: Total of %s products stock synced', (int)$qtyproducts);
  }
}

function exact_run_orders() {
  $payment_map = @unserialize(EXACT_PAYMENT_MAP);
  $shipping_map = @unserialize(EXACT_SHIPPING_MAP);

  $params = array(
    'refresh_token' => EXACT_REFRESH_TOKEN,
    'grant_type' => 'refresh_token',
    'client_id' => EXACT_CLIENT_ID,
    'client_secret' => EXACT_CLIENT_SECRET,
  );
  $response = exact_call_http_url(EXACT_BASE_URL . '/api/oauth2/token', $params);
  $result = json_decode($response);

  if (is_object($result) && tep_not_null($result->refresh_token) && tep_not_null($result->access_token)) {
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($result->refresh_token) . "', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '0', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
    $access_token = $result->access_token;
  } else {
    if (EXACT_AUTH_ERROR_COUNT < EXACT_AUTH_ERROR_COUNT_LIMIT) {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . ((int)EXACT_AUTH_ERROR_COUNT + 1) . "', last_modified = now() where configuration_key = 'EXACT_AUTH_ERROR_COUNT'");
    } else {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '', last_modified = now() where configuration_key = 'EXACT_REFRESH_TOKEN'");
      exact_send_authorization_error_notification();
    }
    if (defined('TEXT_ERROR_AUTHORIZATION_TOKENS')) {
      return TEXT_ERROR_AUTHORIZATION_TOKENS;
    } else {
      return 'Error: Can not get Authorization Tokens';
    }
  }

  $count = 0;
  $orders_xml = '';
  $orders_query = tep_db_query("select orders_id, orders_status from " . TABLE_ORDERS . " o where orders_status in ('" . implode("','", array_map('intval', explode(',', EXACT_ORDER_STATUSES_SYNCED))) . "') and to_days(date_purchased) >= to_days(date_sub(now(), interval 1 day))");
  while ($orders = tep_db_fetch_array($orders_query)) {
// {{
    $orderNumber = EXACT_ORDERNUMBER_SHIFT + $orders['orders_id'];

    $headers = array(
      'Accept: Application/json',
      'Content-Type: Application/json',
      'Authorization: Bearer ' . $access_token
    );
    $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/salesorder/SalesOrders?$select=OrderID,OrderNumber,Status&$filter=OrderNumber+eq+' . ($orderNumber), array(), $headers);
    $result = json_decode($response);

    if (is_object($result) && is_array($result->d->results)) {
      if ($result->d->results[0]->OrderNumber == $orderNumber) {
        continue; // Order already exists in Exact
      }
    }
// }}

    $order = new order($orders['orders_id']);

    $products_xml = '';
    for ($i = 0; $i < count($order->products); $i++) {
      $product = tep_db_fetch_array(tep_db_query("select exact_id, products_tax_class_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$order->products[$i]['id'] . "'"));
      $vat = tep_db_fetch_array(tep_db_query("select tax_rate, tax_type from " . TABLE_TAX_RATES . " where tax_rates_id = '" . (int)$product['products_tax_class_id'] . "'"));
      if ($vat['tax_type'] == 'I' && $vat['tax_rate'] == $order->products[$i]['tax']) {
        $products_price = $order->products[$i]['final_price'] * (1 + ($vat['tax_rate'] / 100)); // I - inlcl.
      } else {
        $products_price = $order->products[$i]['final_price'];
      }
// {{
      $vat_code = '';
      if ($order->products[$i]['tax'] > 0) {
        if ($product['products_tax_class_id'] > 0 && $vat['tax_rate'] == $order->products[$i]['tax']) {
          $vat_code = '' . $product['products_tax_class_id'];
        }
      } else {
        $vat_code = '' . EXACT_0_VAT_CODE; // No Tax
      }
// }}
      $products_xml .= '
      <SalesOrderLine line="' . ($i + 1) . '">
        <Description>' . CDT(encode_string($order->products[$i]['name'])) . '</Description>
        <Item' . (tep_not_null($product['exact_id']) ? ' ID="{' . $product['exact_id'] . '}"' : '') . ' code="' . encode_string($order->products[$i]['model']) . '">
          <Description>' . CDT(encode_string($order->products[$i]['name'])) . '</Description>
        </Item>
        <Quantity>' . (int)($order->products[$i]['qty']) . '</Quantity>
        <UnitPrice>
          <Currency code="' . encode_string($order->info['currency']) . '" />
          <Value>' . round($products_price, 2) . '</Value>
          ' . (tep_not_null($vat_code) ? '<VAT code="' . $vat_code . '" />' : '') . '
          <VATPercentage>' . round($order->products[$i]['tax'] / 100, 2) . '</VATPercentage>
        </UnitPrice>
        <ForeignAmount>
          <Currency code="' . encode_string($order->info['currency']) . '" />
          <Value>' . round($products_price * $order->products[$i]['qty'], 2) . '</Value>
          <Rate>' . round($order->info['currency_value'], 2) . '</Rate>
          <VATBaseAmount>' . round($order->products[$i]['final_price'] * $order->products[$i]['qty'], 2) . '</VATBaseAmount>
          <VATAmount>' . round($order->products[$i]['final_price'] * $order->products[$i]['qty'] * $order->products[$i]['tax'] / 100, 2) . '</VATAmount>
        </ForeignAmount>
        <DiscountPercentage>0</DiscountPercentage>
        <UseDropShipment>0</UseDropShipment>
      </SalesOrderLine>';
    }

    $payment_class = $order->info['payment_class'];
    if (tep_not_null($payment_map[$order->info['platform_id']][$payment_class]['code']) || tep_not_null($payment_map[$order->info['platform_id']][$payment_class]['description'])) {
      $payment = $payment_map[$order->info['platform_id']][$payment_class];
    } else {
      $payment = $payment_map[$order->info['platform_id']]['default'];
    }
    list($shipping_class, ) = explode('_', $order->info['shipping_class']);
    if (tep_not_null($shipping_map[$order->info['platform_id']][$shipping_class]['code']) || tep_not_null($shipping_map[$order->info['platform_id']][$shipping_class]['description'])) {
      $shipping = $shipping_map[$order->info['platform_id']][$shipping_class];
    } else {
      $shipping = $shipping_map[$order->info['platform_id']]['default'];
    }

    $ot_total = 0;
    $discount_amount = 0;
    foreach ($order->totals as $total) {
      if ($total['class'] == 'ot_total') {
        $ot_total = $total['value'];
// {{ Discount
      } elseif (in_array($total['class'], array('ot_coupon', 'ot_gv'))) {
        $discount_amount += abs($total['value']);
      } elseif (in_array($total['class'], array('ot_paymentfee', 'ot_shippingfee'))) {
        if ($total['value'] < 0) {
          $discount_amount += abs($total['value']);
        } else {
          // ?????
        }
// }}
      } elseif ($total['class'] == 'ot_shipping') {
// {{
        $vat_code = '';
        $shipping_price = ($total['value_exc_vat'] > 0 ? $total['value_exc_vat'] : $total['value']);
        if ($total['value_inc_tax'] > $total['value_exc_vat']) {
          if ($total['tax_class_id'] > 0) {
            $vat = tep_db_fetch_array(tep_db_query("select tax_rate, tax_type from " . TABLE_TAX_RATES . " where tax_rates_id = '" . (int)$total['tax_class_id'] . "'"));
            if ($vat['tax_type'] == 'I') {
              $shipping_price = $total['value_inc_tax']; // I - inlcl.
            }
            $vat_code = '' . $total['tax_class_id'];
          }
        } elseif ($total['tax_class_id'] > 0 && $total['value'] == 0) {
          $vat_code = '' . $total['tax_class_id'];
        } else {
          $vat_code = '' . EXACT_0_VAT_CODE; // No Tax
        }
// }}
        $products_xml .= '
      <SalesOrderLine line="' . ($i + 1) . '">
        <Description>' . CDT(encode_string(tep_not_null($shipping['description']) ? $shipping['description'] : strip_tags($total['title']))) . '</Description>
        <Item code="' . encode_string(tep_not_null($shipping['product']) ? $shipping['product'] : strip_tags($total['title'])) . '">
          <Description>' . CDT(encode_string(tep_not_null($shipping['description']) ? $shipping['description'] : strip_tags($total['title']))) . '</Description>
        </Item>
        <Quantity>1</Quantity>
        <UnitPrice>
          <Currency code="' . encode_string($order->info['currency']) . '" />
          <Value>' . round($shipping_price, 2) . '</Value>
          ' . (tep_not_null($vat_code) ? '<VAT code="' . $vat_code . '" />' : '') . '
        </UnitPrice>
        <ForeignAmount>
          <Currency code="' . encode_string($order->info['currency']) . '" />
          <Value>' . round($shipping_price, 2) . '</Value>
          <Rate>' . round($order->info['currency_value'], 2) . '</Rate>
        </ForeignAmount>
        <DiscountPercentage>0</DiscountPercentage>
        <UseDropShipment>0</UseDropShipment>
      </SalesOrderLine>';
        $i++;
      }
    }

    if (!tep_not_null($order->customer['firstname']) || !tep_not_null($order->customer['lastname'])) {
      $names_array = explode(' ', $order->customer['name']);
      if (!tep_not_null($order->customer['firstname'])) $order->customer['firstname'] = $names_array[0];
      if (!tep_not_null($order->customer['lastname'])) $order->customer['lastname'] = $names_array[1] . (tep_not_null($names_array[2]) ? ' ' . $names_array[2] : '');
    }
    if (!tep_not_null($order->delivery['firstname']) || !tep_not_null($order->delivery['lastname'])) {
      $names_array = explode(' ', $order->delivery['name']);
      if (!tep_not_null($order->delivery['firstname'])) $order->delivery['firstname'] = $names_array[0];
      if (!tep_not_null($order->delivery['lastname'])) $order->delivery['lastname'] = $names_array[1] . (tep_not_null($names_array[2]) ? ' ' . $names_array[2] : '');
    }
    if (!is_array($order->customer['country'])) {
      $order->customer['country'] = tep_db_fetch_array(tep_db_query("select countries_id as id, countries_name as title, countries_iso_code_2 as iso_code_2, countries_iso_code_3 as iso_code_3 from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($order->customer['country']) . "' LIMIT 1"));
    }
    if (!is_array($order->delivery['country'])) {
      $order->delivery['country'] = tep_db_fetch_array(tep_db_query("select countries_id as id, countries_name as title, countries_iso_code_2 as iso_code_2, countries_iso_code_3 as iso_code_3 from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($order->delivery['country']) . "' LIMIT 1"));
    }

// {{
    $accountCode = EXACT_ORDERNUMBER_SHIFT + $order->customer['id'];

    $headers = array(
      'Accept: Application/json',
      'Content-Type: Application/json',
      'Authorization: Bearer ' . $access_token
    );
//    $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/crm/Accounts?$select=ID&$filter=trim(Code)+eq+\'' . ($accountCode) . "'", array(), $headers);
    $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/crm/Accounts?$select=ID&$filter=trim(Email)+eq+\'' . trim($order->customer['email_address']) . "'", array(), $headers);
    $result = json_decode($response);

    $accountID = '';
    if (is_object($result) && is_array($result->d->results)) {
      $accountID = $result->d->results[0]->ID;
    }
    if (tep_not_null($accountID)) {
      // Account Exists
      check_delivery_address:
      $headers = array(
        'Accept: Application/json',
        'Content-Type: Application/json',
        'Authorization: Bearer ' . $access_token
      );
      $response = exact_call_http_url(EXACT_BASE_URL . '/api/v1/' . EXACT_CURRENT_DIVISION . '/crm/Addresses?$select=ID,AddressLine1,AddressLine2,City,Postcode,State,Country&$filter=Account+eq+guid\'' . ($accountID) . '\'+and+Type+eq+4', array(), $headers);
      $result = json_decode($response);
      $delivery_address_id = '';
      if (is_object($result) && is_array($result->d->results)) {
        foreach ($result->d->results as $address) {
          if (is_object($address)) {
            if ( trim($address->AddressLine1) == trim($order->delivery['street_address']) &&
                 trim($address->AddressLine2) == trim($order->delivery['suburb']) &&
                 trim($address->Postcode) == trim($order->delivery['postcode']) &&
                 trim($address->City) == trim($order->delivery['city']) &&
//                 trim($address->State) == trim($order->delivery['state']) &&
                 trim($address->Country) == trim($order->delivery['country']['iso_code_2']) ) {
              $delivery_address_id = trim($address->ID);
            }
          }
        }
      }
      if (tep_not_null($delivery_address_id)) {
        // Address Exists
        $address_xml = '
      <OrderedBy ID="{' . $accountID . '}">
      </OrderedBy>
      <DeliverTo ID="{' . $accountID . '}">
        <Name>' . CDT(encode_string(tep_not_null($order->delivery['name']) ? $order->delivery['name'] : $order->delivery['firstname'] . ' ' . $order->delivery['lastname'])) . '</Name>
      </DeliverTo>
      <DeliveryAddress ID="{' . $delivery_address_id . '}">
      </DeliveryAddress>
      <InvoiceTo ID="{' . $accountID . '}">
        <Name>' . CDT(encode_string(tep_not_null($order->customer['name']) ? $order->customer['name'] : $order->customer['firstname'] . ' ' . $order->customer['lastname'])) . '</Name>
      </InvoiceTo>';
      } else {
        // New Address
        $new_address_xml = '<?xml version="1.0" encoding="utf-8"?>
<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-XML.xsd">
  <Accounts>
    <Account ID="{' . $accountID . '}">
      <Address type="DEL" default="0">
        <AddressLine1>' . CDT(encode_string($order->delivery['street_address'])) . '</AddressLine1>
        <AddressLine2>' . CDT(encode_string($order->delivery['suburb'])) . '</AddressLine2>
        <AddressLine3 />
        <PostalCode>' . CDT(encode_string($order->delivery['postcode'])) . '</PostalCode>
        <City>' . CDT(encode_string($order->delivery['city'])) . '</City>
        <State>' . CDT(encode_string($order->delivery['state'])) . '</State>
        <Country code="' . encode_string($order->delivery['country']['iso_code_2']) . '" />
      </Address>
    </Account>
  </Accounts>
  <Topics>
    <Topic code="Accounts" ts_d="0x00000000914E5C82" count="1" pagesize="1000" />
  </Topics>
  <Messages />
</eExact>';
        $headers = array(
          'Accept: Application/xml',
          'Content-Type: Application/xml',
          'Authorization: Bearer ' . $access_token
        );
        $response = exact_call_http_url(EXACT_BASE_URL . '/docs/XMLUpload.aspx?Topic=Accounts' . '&_Division_=' . EXACT_CURRENT_DIVISION, $new_address_xml, $headers);
        goto check_delivery_address;
      }
    } else {
      // New Account
      $address_xml = '
      <OrderedBy code="' . ($accountCode) . '">
        <Name>' . CDT(encode_string(tep_not_null($order->customer['name']) ? $order->customer['name'] : $order->customer['firstname'] . ' ' . $order->customer['lastname'])) . '</Name>
        <Email>' . CDT(encode_string($order->customer['email_address'])) . '</Email>
        <Phone>' . CDT(encode_string($order->customer['telephone'])) . '</Phone>
        <Contact>
          <LastName>' . CDT(encode_string($order->customer['lastname'])) . '</LastName>
          <MiddleName />
          <FirstName>' . CDT(encode_string($order->customer['firstname'])) . '</FirstName>
          <Initials />
          <FullName>' . CDT(encode_string(tep_not_null($order->customer['name']) ? $order->customer['name'] : $order->customer['firstname'] . ' ' . $order->customer['lastname'])) . '</FullName>
          <Email>' . CDT(encode_string($order->customer['email_address'])) . '</Email>
          <Phone>' . CDT(encode_string($order->customer['telephone'])) . '</Phone>
        </Contact>
        <Address default="1">
          <AddressLine1>' . CDT(encode_string($order->customer['street_address'])) . '</AddressLine1>
          <AddressLine2>' . CDT(encode_string($order->customer['suburb'])) . '</AddressLine2>
          <AddressLine3 />
          <PostalCode>' . CDT(encode_string($order->customer['postcode'])) . '</PostalCode>
          <City>' . CDT(encode_string($order->customer['city'])) . '</City>
          <State>' . CDT(encode_string($order->customer['state'])) . '</State>
          <Country code="' . encode_string($order->customer['country']['iso_code_2']) . '" />
        </Address>
        <Address type="DEL">
          <AddressLine1>' . CDT(encode_string($order->delivery['street_address'])) . '</AddressLine1>
          <AddressLine2>' . CDT(encode_string($order->delivery['suburb'])) . '</AddressLine2>
          <AddressLine3 />
          <PostalCode>' . CDT(encode_string($order->delivery['postcode'])) . '</PostalCode>
          <City>' . CDT(encode_string($order->delivery['city'])) . '</City>
          <State>' . CDT(encode_string($order->delivery['state'])) . '</State>
          <Country code="' . encode_string($order->delivery['country']['iso_code_2']) . '" />
        </Address>
      </OrderedBy>
      <DeliverTo code="' . ($accountCode) . '">
        <Name>' . CDT(encode_string(tep_not_null($order->delivery['name']) ? $order->delivery['name'] : $order->delivery['firstname'] . ' ' . $order->delivery['lastname'])) . '</Name>
      </DeliverTo>
      <InvoiceTo code="' . ($accountCode) . '">
        <Name>' . CDT(encode_string(tep_not_null($order->customer['name']) ? $order->customer['name'] : $order->customer['firstname'] . ' ' . $order->customer['lastname'])) . '</Name>
      </InvoiceTo>';
    }
// }}

    $orders_xml .= '
    <SalesOrder salesordernumber="' . ($orderNumber) . '" status="12">
      <OrderDate>' . substr($orders['date_purchased'], 0, 10) . '</OrderDate>
      <Description>' . CDT('Order imported from ' . encode_string(\common\classes\platform::name($order->info['platform_id']))) . '</Description>
      <YourRef>' . CDT(encode_string(\common\classes\platform::name($order->info['platform_id']) . ' #' . $orders['orders_id'])) . '</YourRef>' . $address_xml . '
      <PaymentCondition code="' . encode_string($payment['code']) . '">
        <Description>' . CDT(encode_string($payment['description'])) . '</Description>
      </PaymentCondition>
      <ForeignAmount>
        <Currency code="' . encode_string($order->info['currency']) . '" />
        <Value>' . round($ot_total, 2) . '</Value>
        <Rate>' . round($order->info['currency_value'], 2) . '</Rate>
        <PaymentDiscountAmount>
          <Value>0</Value>
        </PaymentDiscountAmount>
      </ForeignAmount>
      <ShippingMethod code="' . encode_string($shipping['code']) . '">
        <Description>' . CDT(encode_string($shipping['description'])) . '</Description>
      </ShippingMethod>
      <SalesPerson />
      <EntryDiscount>
        ' . (DISPLAY_PRICE_WITH_TAX == 'true' ? '<AmountInclVAT>-' . round($discount_amount, 2) . '</AmountInclVAT>' : '<AmountExclVAT>-' . round($discount_amount, 2) . '</AmountExclVAT>') . '
      </EntryDiscount>' . $products_xml . '
    </SalesOrder>';
    $count++;
  }

  $exact_xml = '<?xml version="1.0" encoding="utf-8"?>
<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-XML.xsd">
  <SalesOrders>' . $orders_xml . '
  </SalesOrders>
  <Topics>
    <Topic code="SalesOrders" ts_d="0x00000000041FC213" count="' . ($count) . '" pagesize="100" />
  </Topics>
  <Messages />
</eExact>';

  $headers = array(
    'Accept: Application/xml',
    'Content-Type: Application/xml',
    'Authorization: Bearer ' . $access_token
  );
  $response = exact_call_http_url(EXACT_BASE_URL . '/docs/XMLUpload.aspx?Topic=SalesOrders' . '&_Division_=' . EXACT_CURRENT_DIVISION, $exact_xml, $headers);

  $messages_array = array();
  $xml_tree = new xml_tree(CHARSET);
  $tree_node = $xml_tree->createTree($response);
  if (is_object($tree_node) && $root_node = $tree_node->item('eExact/Messages')) {
    for ($i = 0; $i < $root_node->itemcount('Message'); $i++) {
      $message_array = array();
      $message_node = $root_node->item('Message', $i);
      $message_array['type'] = decode_string($message_node->attributes['type']);
      for ($k = 0; $k < count($message_node->subitems); $k++) {
        if ($message_node->subitems[$k]->name == 'Topic') {
          $message_array['node'] = decode_string($message_node->subitems[$k]->attributes['node']);
        } else {
          $message_array[$message_node->subitems[$k]->name] = decode_string($message_node->subitems[$k]->content);
        }
      }
      $messages_array[] = $message_array;
    }
  }

  $success_orders = 0;
  foreach ($messages_array as $message) {
    if ($message['node'] == 'SalesOrder' && $message['type'] == '2') {
      $success_orders++;
    }
  }

  if (defined('TEXT_SUCCESS_ORDERS_SYNCED')) {
    return sprintf(TEXT_SUCCESS_ORDERS_SYNCED, (int)$success_orders);
  } else {
    return sprintf('Success: Total of %s orders synced', (int)$success_orders);
  }
}

function exact_call_http_url($url, $postfields = array(), $headers = array()) {
  if ($ch = curl_init()) {
    $url = str_replace('&amp;', '&', $url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (is_array($headers) && count($headers) > 0) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if ( (is_array($postfields) && count($postfields) > 0) ||
         (is_string($postfields) && strlen($postfields) > 0) ) {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    }
    if ( !($response = curl_exec($ch)) ) {
      $response = curl_error($ch);
    }
    curl_close ($ch);
    return $response;
  }
}

function exact_headers_http_url($url, $headers = array()) {
  if ($ch = curl_init()) {
    $url = str_replace('&amp;', '&', $url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (is_array($headers) && count($headers) > 0) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if ( !($response = curl_exec($ch)) ) {
      $response = curl_error($ch);
    }
    curl_close ($ch);
    return $response;
  }
}

function exact_parse_headers($headers_string) {
  $headers_array = array();
  $data = explode("\n", $headers_string);
  $headers_array['status'] = $data[0];
  array_shift($data);
  foreach ($data as $part) {
    $middle = explode(':', $part);
    $headers_array[trim($middle[0])] = trim($middle[1]);
  }
  return $headers_array;
}

function exact_send_authorization_error_notification() {
  $url = HTTP_SERVER . '/admin/exact_online';
  $message = 'Dear Customer,' . "\n\n" .
             'Your Wolq connector authorization token has expired (or been manually revoked).' . "\n\n" .
             'Please go to this link <a href="' . $url . '">' . $url . '</a> in order to activate your Wolq connector again for free.' . "\n\n" .
             'Best regards,' . "\n" . 'Wolq support department' . "\n\n" . 'This email has been generated automatically. Please do not reply to it.';
  \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'Wolq connector authorization token expired', $message, 'Wolq Support', 'support@wolqstore.com');
  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = 'False', last_modified = now() where configuration_key = 'EXACT_CONNECTOR_STATUS'");
}

function get_schedule_next_started($crons_id) {
  $data = tep_db_fetch_array(tep_db_query("select schedule_every_minutes from " . TABLE_EXACT_CRONS . " where exact_crons_id = '" . (int)$crons_id . "'"));
  if ( !($data['schedule_every_minutes'] > 0) ) return '0000-00-00 00:00:00';
  $count = 1;
  do {
    $date = tep_db_fetch_array(tep_db_query("select date_add(now(), interval " . (int)($data['schedule_every_minutes'] * $count) . " minute) as next_started, date_add(now(), interval " . (int)($data['schedule_every_minutes'] * $count) . " minute) > now() as in_future"));
    $count++;
  }
  while (!$date['in_future']);
  return $date['next_started'];
}

function encode_string($data) {
//  return iconv(CHARSET, 'UTF-8', $data);
  return $data;
}

function decode_string($data) {
//  return iconv('UTF-8', CHARSET, $data);
  return $data;
}

function CDT($in_str) {
  return "<![CDATA[" . $in_str . "]]>";
}

?>