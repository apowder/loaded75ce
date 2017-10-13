<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace app\components;

use yii\web\UrlRule;
use Yii;

class TlUrlRule extends UrlRule
{
    public function init()
    {
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }

    public function createUrl($manager, $route, $params)
    {
        if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && (SEARCH_ENGINE_UNHIDE == 'True') ) {
            global $languages_id;
            if ($route === 'catalog/product' && tep_not_null($params['products_id']) && !strstr($params['products_id'], '{') && !strstr($params['products_id'], '}')) {
                $_key = (int)$params['products_id'].'^'.(int)$languages_id;
                static $_lookup_product = array();
                if ( isset($_lookup_product[$_key]) ) {
                  $product = $_lookup_product[$_key];
                }else {
                  $product = tep_db_fetch_array(tep_db_query("select if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name) as products_seo_page_name from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' where p.products_id = '" . (int)$params['products_id'] . "' order by length(if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name)) desc limit 1"));
                  if ( count($_lookup_product)>50 ) $_lookup_product = array();
                  $_lookup_product[$_key] = $product;
                }
                if (tep_not_null($product['products_seo_page_name'])) {
                    $params_array = array();
                    foreach ($params as $key => $value) {
                      if ($key == 'products_id') continue;
                      if (is_array($value)) {
                        for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                          $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                        }
                      } else {
                        $params_array[] = $key . '=' . urlencode($value);
                      }
                    }
                    return $product['products_seo_page_name'] . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                }
            }
            if ($route === 'catalog' || $route === 'catalog/index') {
                if (tep_not_null($params['cPath'])) {
                    $cPath_array = \common\helpers\Categories::parse_category_path($params['cPath']);
// {{
                    if (SEO_URL_FULL_CATEGORIES_PATH == 'True') {
                        $category['categories_seo_page_name'] = '';
                        $categories_array = array($cPath_array[(sizeof($cPath_array)-1)]);
                        \common\helpers\Categories::get_parent_categories($categories_array, $categories_array[0]);
                        $categories_array = array_reverse($categories_array);
                        foreach ($categories_array as $cat_id) {
                            $cat = tep_db_fetch_array(tep_db_query("select if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) as categories_seo_page_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where c.categories_id = '" . (int)$cat_id . "' order by length(if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name)) desc limit 1"));
                            $category['categories_seo_page_name'] .= $cat['categories_seo_page_name'] . '/';
                        }
                        $category['categories_seo_page_name'] = trim($category['categories_seo_page_name'], '/');
                    } else
// }}
                    $category = tep_db_fetch_array(tep_db_query("select if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) as categories_seo_page_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where c.categories_id = '" . (int)$cPath_array[(sizeof($cPath_array)-1)] . "' order by length(if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name)) desc limit 1"));
                    if (tep_not_null($category['categories_seo_page_name'])) {
                        $params_array = array();
                        foreach ($params as $key => $value) {
                          if ($key == 'cPath') continue;
                          if (is_array($value)) {
                            for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                              $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                            }
                          } else {
                            $params_array[] = $key . '=' . urlencode($value);
                          }
                        }
                        return $category['categories_seo_page_name'] . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    }
                } elseif ($params['manufacturers_id'] > 0) {
                    $manufacturer = tep_db_fetch_array(tep_db_query("select mi.manufacturers_seo_name from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where m.manufacturers_id = '" . (int)$params['manufacturers_id'] . "' order by length(mi.manufacturers_seo_name) desc limit 1"));
                    if (tep_not_null($manufacturer['manufacturers_seo_name'])) {
                        $params_array = array();
                        foreach ($params as $key => $value) {
                          if ($key == 'manufacturers_id') continue;
                          if (is_array($value)) {
                            for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                              $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                            }
                          } else {
                            $params_array[] = $key . '=' . urlencode($value);
                          }
                        }
                        return $manufacturer['manufacturers_seo_name'] . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    }
                }
            }
            if ( ($route === 'info' || $route === 'info/index') && $params['info_id'] > 0) {
                $information = tep_db_fetch_array(tep_db_query(
                  "select seo_page_name ".
                  "from " . TABLE_INFORMATION . " where information_id = '" . (int)$params['info_id']  . "' and languages_id = '" . (int)$languages_id . "' ".
                  " AND platform_id='".\common\classes\platform::currentId()."' ".
                  "order by length(seo_page_name) desc limit 1"));
                if (tep_not_null($information['seo_page_name'])) {
                    $params_array = array();
                    foreach ($params as $key => $value) {
                      if ($key == 'info_id') continue;
                      if (is_array($value)) {
                        for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                          $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                        }
                      } else {
                        $params_array[] = $key . '=' . urlencode($value);
                      }
                    }
                    return $information['seo_page_name'] . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                }
            }
            if ($route === 'catalog/advanced-search-result' && is_array($params)) {
                $properties_array = $properties_keys_array = $params_array = array();
                foreach ($params as $key => $value) {
                    if (preg_match("/^pr(\d+)$/", $key, $arr)) {
                        $property = tep_db_fetch_array(tep_db_query("select pd.properties_seo_page_name from " . TABLE_PROPERTIES . " p left join " . TABLE_PROPERTIES_DESCRIPTION . " pd on (p.properties_id = pd.properties_id and pd.language_id = '" . (int)$languages_id . "')  where p.properties_id = '" . (int)$arr[1] . "' order by length(pd.properties_seo_page_name) desc limit 1"));
                        if (tep_not_null($property['properties_seo_page_name'])) {
                            $properties_array[] = $property['properties_seo_page_name'] . '=' . (is_array($value) ? implode(',', $value) : $value);
                            $properties_keys_array[] = $key;
                        }
                    }
                }
                foreach ($params as $key => $value) {
                    if (in_array($key, $properties_keys_array)) continue;
                    if (is_array($value)) {
                      for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                        $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                      }
                    } else {
                      $params_array[] = $key . '=' . urlencode($value);
                    }
                }
                if (count($properties_array) > 0) {
                    return implode(';', $properties_array) . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                }
            }
        } else {
            return parent::createUrl($manager, $route, $params);
        }
        return false;
    }

    public function parseRequest($manager, $request)
    { global $cPath_array, $HTTP_GET_VARS, $current_category_id, $cPath;
        if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && (SEARCH_ENGINE_UNHIDE == 'True') ) {
            global $languages_id;
            
            if (isset($HTTP_GET_VARS['cPath'])) {
              $cPath = $HTTP_GET_VARS['cPath'];
            } elseif (isset($HTTP_GET_VARS['products_id']) && !isset($HTTP_GET_VARS['manufacturers_id'])) {
              $cPath = \common\helpers\Product::get_product_path($HTTP_GET_VARS['products_id']);
            } else {
              $cPath = '';
            }

            if (tep_not_null($cPath)) {
              $cPath_array = \common\helpers\Categories::parse_category_path($cPath);
              $cPath = implode('_', $cPath_array);
              $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
              $query = tep_db_query("select * from " . TABLE_CATEGORIES . " where categories_status = 1 and categories_id = " . (int)$current_category_id);
              if (tep_db_num_rows($query) == 0) {
                $current_category_id = 0;
              }
            } else {
              $current_category_id = 0;
            }    
  
            //$seo_path = str_replace(DIR_WS_HTTP_CATALOG, "", $request->getUrl());
            $seo_path = trim($request->getPathInfo());
            if (!tep_not_null($seo_path)) {
                return false;
            }

            $product = tep_db_fetch_array(tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' where if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name) = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($product['products_id'] > 0) {
                global $products_id, $HTTP_GET_VARS;
                $products_id = $HTTP_GET_VARS['products_id'] = $_GET['products_id'] = $product['products_id'];
                $cPath_array = explode("_", \common\helpers\Product::get_product_path($product['products_id']));
                return ['catalog/product', ['products_id' => $product['products_id']]];
            }
// {{
            if (SEO_URL_FULL_CATEGORIES_PATH == 'True') {
                $cPath_array = array();
                $seo_path_array = explode('/', $seo_path);
                foreach ($seo_path_array as $seo_path_part) {
                    $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) = '" . tep_db_input($seo_path_part) . "' limit 1"));
                    if ($category['categories_id'] > 0) {
                        $cPath_array[] = $category['categories_id'];
                    }
                }
                if (count($cPath_array) > 0 && count($cPath_array) == count($seo_path_array)) {
                    global $current_category_id, $cPath, $HTTP_GET_VARS;
                    $cPath = implode('_', $cPath_array);
                    $HTTP_GET_VARS['cPath'] = $_GET['cPath'] = $cPath;
                    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
                    return ['catalog/index', ['cPath' => $cPath]];
                }
            }
// }}
            $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($category['categories_id'] > 0) {
                global $current_category_id, $cPath, $HTTP_GET_VARS;
                $categories = array();
                \common\helpers\Categories::get_parent_categories($categories, $category['categories_id']);
                $categories = array_reverse($categories);
                $cPath = implode('_', $categories);
                if (tep_not_null($cPath)) $cPath .= '_';
                $cPath .= $category['categories_id'];
                $HTTP_GET_VARS['cPath'] = $_GET['cPath'] = $cPath;
                $current_category_id = $category['categories_id'];
                return ['catalog/index', ['cPath' => $cPath]];
            }
            $manufacturer = tep_db_fetch_array(tep_db_query("select m.manufacturers_id from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where mi.manufacturers_seo_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($manufacturer['manufacturers_id'] > 0) {
                global $manufacturers_id, $HTTP_GET_VARS;
                $manufacturers_id = $HTTP_GET_VARS['manufacturers_id'] = $_GET['manufacturers_id'] = $manufacturer['manufacturers_id'];
                return ['catalog/index', ['manufacturers_id' => $manufacturer['manufacturers_id']]];
            }
            $information = tep_db_fetch_array(tep_db_query(
              "select information_id from " . TABLE_INFORMATION . " where seo_page_name = '" . tep_db_input($seo_path) . "' ".
              " AND platform_id='".\common\classes\platform::currentId()."' ".
              "limit 1"
            ));
            if ($information['information_id'] > 0) {
                global $information_id, $HTTP_GET_VARS;
                $information_id = $HTTP_GET_VARS['information_id'] = $_GET['information_id'] = $information['information_id'];
                return ['info/index', ['info_id' => $information['information_id']]];
            }

            $properties_array = array();
            foreach (explode(';', $seo_path) as $seo_property) {
                list($seo_page, $value) = explode('=', $seo_property);
                $property = tep_db_fetch_array(tep_db_query("select p.properties_id from " . TABLE_PROPERTIES . " p left join " . TABLE_PROPERTIES_DESCRIPTION . " pd on (p.properties_id = pd.properties_id and pd.language_id = '" . (int)$languages_id . "')  where pd.properties_seo_page_name = '" . tep_db_input($seo_page) . "' order by p.properties_id limit 1"));
                if ($property['properties_id'] > 0) {
                    $properties_array['pr' . $property['properties_id']] = explode(',', $value);
                }
            }
            if (count($properties_array) > 0) {
                return ['catalog/advanced-search-result', $properties_array];
            }

//{{ old seo urls
            $href = null;
            $product = tep_db_fetch_array(tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' where p.products_old_seo_page_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($product['products_id'] > 0) {
              $href = tep_href_link('catalog/product', 'products_id='.$product['products_id']);
            }
            $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where c.categories_old_seo_page_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($category['categories_id'] > 0) {
              $cPath_new = \common\helpers\Categories::get_path($category['categories_id']);
              $href = tep_href_link('catalog', $cPath_new);
            }
            $manufacturer = tep_db_fetch_array(tep_db_query("select m.manufacturers_id from " . TABLE_MANUFACTURERS . " m where m.manufacturers_old_seo_page_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($manufacturer['manufacturers_id'] > 0) {
              $href = tep_href_link('catalog', 'manufacturers_id=' . $manufacturer['manufacturers_id']);
            }
            $information = tep_db_fetch_array(tep_db_query(
              "select information_id from " . TABLE_INFORMATION . " ".
              "where old_seo_page_name = '" . tep_db_input($seo_path) . "' ".
              " AND platform_id='".\common\classes\platform::currentId()."' ".
              "limit 1"
            ));
            if ($information['information_id'] > 0) {
              $href = tep_href_link('info', 'info_id=' . $information['information_id']);
            }

          if (strpos($seo_path, 'blog/') === 0 || $seo_path == 'blog'){
            $url = parse_url(Yii::$app->request->url);
            return ['blog/index', ['url_path' => substr($seo_path, 5) . '?' . $url['query']]];
          }
          
          $custom = tep_db_fetch_array(tep_db_query("select ts.setting_value from " . TABLE_MENU_ITEMS . " mi inner join " . TABLE_THEMES_SETTINGS . " ts on ts.id=mi.theme_page_id and ts.setting_group = 'added_page' and setting_name='custom' where link = '" . tep_db_input($seo_path) . "' and theme_page_id > 0 and platform_id='" . \common\classes\platform::currentId() . "'"));
          if ($custom){
            return ['info/custom', ['page' => $custom['setting_value'] ]];
          }

          if ($ext = \common\helpers\Acl::checkExtension('SeoRedirects', 'checkRedirect')) {
            $ext::checkRedirect($seo_path);
          }
          if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'checkRedirect')) {
            $ext::checkRedirect($seo_path);
          }

          if (!is_null($href)){
              header("HTTP/1.1 301 Moved Permanently"); 
              header("Location: " . $href); 
              exit();
          }
//}}
        }
        return false;
    }
}