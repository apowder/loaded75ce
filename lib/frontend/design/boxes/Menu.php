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

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\helpers\MenuHelper;

class Menu extends Widget
{

  public $params;
  public $settings;
  public $id;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $languages_id, $cPath_array;

    $sab_categories = array();

    $is_menu = false;
    $menu = array();
    $sql = tep_db_query("
            select i.id, i.parent_id, i.link, i.link_id, i.link_type, i.target_blank, i.class, i.sub_categories, i.sort_order, t.title, i.menu_id
            from " . TABLE_MENUS . " m
              inner join " . TABLE_MENU_ITEMS . " i on i.menu_id = m.id and i.platform_id='".\common\classes\platform::currentId()."'
              left join " . TABLE_MENU_TITLES . " t on t.item_id = i.id and t.language_id = " . (int)$languages_id . "
            where
              m.menu_name = '" . $this->settings[0]['params'] . "'
            order by i.sort_order
          ");
    while ($row = tep_db_fetch_array($sql)) {

      if ($row['link_type'] == 'info') {

        if (!$row['title']) {
          $sql1=tep_db_query("SELECT information_id, info_title, page_title from " . TABLE_INFORMATION ." WHERE visible='1' and languages_id =".(int)$languages_id." and information_id='" . $row['link_id'] . "' AND platform_id='".\common\classes\platform::currentId()."' ");
          while($row1=tep_db_fetch_array($sql1)){
            if ($row1['info_title']) $row['title'] = $row1['info_title'];
            elseif ($row1['page_title']) $row['title'] = $row1['page_title'];
          }
        }

        $row['link'] = tep_href_link('info', 'info_id=' . $row['link_id']);

        if (Yii::$app->controller->id == 'info' && $_GET['info_id'] == $row['link_id']){
          $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
        }

      } elseif ($row['link_type'] == 'categories') {

        if (!$row['title']) {
          if ($row['link_id'] == '999999999') {
            //$row['title'] = 'All categories';
            $query = tep_db_fetch_array(tep_db_query("select last_modified from " . TABLE_MENUS . " where id = '" . $row['menu_id'] . "'"));
            $sql3 = tep_db_query(
              "select c.categories_id, c.parent_id, cd.categories_name ".
              "from " . TABLE_CATEGORIES . " c  ".
              " inner join ".TABLE_PLATFORMS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.platform_id='".\common\classes\platform::currentId()."' ".
              " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id  ".
              "where c.date_added > '" . $query['last_modified'] . "' and cd.language_id = '" . $languages_id . "' and cd.affiliate_id=0"
            );
            if (tep_db_num_rows($sql3) > 0){
              while ($item = tep_db_fetch_array($sql3)){
                $new_categories[] = $item;
              }
            }
          } else {
            $sql1 = tep_db_query("SELECT categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " WHERE language_id =" . (int)$languages_id . " and categories_id='" . $row['link_id'] . "'");
            if ($row1 = tep_db_fetch_array($sql1)) {
              $row['title'] = $row1['categories_name'];
            }
          }
          if (count($new_categories) > 0){
            if ($row['link_id'] == 999999999)$current = 0;
            else $current = $row['link_id'];
            foreach ($new_categories as $item){
              if ($item['parent_id'] == $current){
                $count_prod = \common\helpers\Categories::count_products_in_category($item['categories_id']);
                if ($count_prod == 0 && !Info::themeSetting('show_empty_categories')) $r_count = -1;
                else $r_count = $count_prod;
                $menu[] = array(
                  'count' => $r_count,
                  'parent_id' => $row['id'],
                  'link_type' => 'categories',
                  'name' => $item['categories_name'],
                  'link_id' => $item['categories_id'],
                  'new_category' => $item['categories_id'],
                  'title' => $item['categories_name'],
                  'link' => tep_href_link('catalog', 'cPath=' . $item['categories_id']),
                );
              }
            }
          }
        }

        if ($row['sub_categories']){
          $sab_categories[] = $row['id'];
        }
        $count_prod = \common\helpers\Categories::count_products_in_category($row['link_id']);
        if ($count_prod == 0 && !Info::themeSetting('show_empty_categories')) $row['count'] = -1;
        else $row['count'] = $count_prod;

        $row['link'] = tep_href_link('catalog', 'cPath=' . $row['link_id']);

        if (Yii::$app->controller->id == 'catalog'){
          if (is_array($cPath_array)){
            $cp = $cPath_array;
          } else {
            $cp = explode('_', $_GET['cPath']);
          }
          if (in_array($row['link_id'], $cp)) {
            $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
          }
        }

      } elseif ($row['link_type'] == 'custom') {

        if (!$row['title']) {
          if (!$row['link']) {
            $row['title'] = $row['link'];
          }
        }

        if (strpos($row['link'], 'http') !== 0 && strpos($row['link'], '//') !== 0 && $row['link']){
          $arr = explode('?', $row['link']);
          $row['link'] = tep_href_link($arr[0], $arr[1],preg_match('/^(account|checkout)/', $arr[0])?'SSL':'NONSSL');
        }

        if (str_replace('//', '', str_replace('http://', '', str_replace('https://', '', $row['link']))) == $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']){
          $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
        }

      } elseif ($row['link_type'] == 'default'){

            if ($row['link_id'] == '8888886'){
              $row['title'] = $row['title'] ? $row['title'] : TEXT_HOME;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888885'){
              $row['title'] = $row['title'] ? $row['title'] : TEXT_HEADER_CONTACT_US;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'contact' && Yii::$app->controller->action->id == 'index'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888887'){
              if (isset($_SESSION['customer_id'])){
                $row['title'] = $row['title'] ? $row['title'] : TEXT_HEADER_LOGOUT;
              } else {
                $row['title'] = $row['title'] ? $row['title'] : TEXT_SIGN_IN;                
              }
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
            } elseif ($row['link_id'] == '8888888'){
              if (isset($_SESSION['customer_id'])){
                $row['title'] = $row['title'] ? $row['title'] : TEXT_MY_ACCOUNT;
                if (Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'index'){
                  $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                }
              } else {
                $row['title'] = $row['title'] ? $row['title'] : TEXT_MY_ACCOUNT;
                if (Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login'){
                  $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                }
                 $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              }
            } elseif ($row['link_id'] == '8888884'){
              $row['title'] = $row['title'] ? $row['title'] : NAVBAR_TITLE_CHECKOUT;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'index'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888883'){
              $row['title'] = $row['title'] ? $row['title'] : TEXT_HEADING_SHOPPING_CART;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'shopping-cart' && Yii::$app->controller->action->id == 'index'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888882'){
              $row['title'] = $row['title'] ? $row['title'] : NEW_PRODUCTS;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'products_new'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888881'){
              $row['title'] = $row['title'] ? $row['title'] : FEATURED_PRODUCTS;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'featured_products'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888880'){
              $row['title'] = $row['title'] ? $row['title'] : SPECIALS_PRODUCTS;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'specials'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888879'){
              $row['title'] = $row['title'] ? $row['title'] : TEXT_GIFT_CARD;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'gift-card'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888878'){
              $row['title'] = $row['title'] ? $row['title'] : TEXT_ALL_PRODUCTS;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'all-products'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            } elseif ($row['link_id'] == '8888877'){
              $row['title'] = $row['title'] ? $row['title'] : TEXT_SITE_MAP;
              $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
              if (Yii::$app->controller->id == 'sitemap'){
                $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
              }
            }
        }

      if ( \common\classes\platform::activeId() ) {
        $sql1 = tep_db_fetch_array(tep_db_query("SELECT count(*) as total from " . TABLE_CATEGORIES . " c inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' where c.categories_id='" . $row['link_id'] . "' and categories_status = '1'"));
      }else{
        $sql1 = tep_db_fetch_array(tep_db_query("SELECT count(*) as total from " . TABLE_CATEGORIES . " where categories_id='" . $row['link_id'] . "' and categories_status = '1'"));
      }
      $sql2 = tep_db_fetch_array(tep_db_query("SELECT count(*) as total from " . TABLE_INFORMATION . " where information_id='" . $row['link_id'] . "' and visible = '1' AND platform_id='".\common\classes\platform::currentId()."' "));
      if ($row['link_type'] != 'categories' || $sql1['total'] > 0 || $row['link_id'] == '999999999') {
        if ($row['link_type'] != 'info' || $sql2['total'] > 0) {
          $menu[] = $row;
        }
      }

      $is_menu = true;
    }

    $categories = array();
    $categories_join = '';
    if ( \common\classes\platform::activeId() ) {
      $categories_join .= " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }
    $sql = tep_db_query("
            select c.categories_id, c.parent_id, cd.categories_name
            from " . TABLE_CATEGORIES . " c {$categories_join}
              left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on cd.categories_id = c.categories_id and cd.language_id = " . (int)$languages_id . "
            where c.categories_status = 1 and cd.affiliate_id = 0
            order by c.sort_order, cd.categories_name
          ");
    while ($row = tep_db_fetch_array($sql)) {
      $categories[] = $row;
    }


    foreach ($menu as $item){
      if (array_search($item['id'], $sab_categories)){

      }
    }

    $hide_size = array();
    $media_query_arr = tep_db_query("select t.setting_value from " . TABLE_THEMES_SETTINGS . " t, " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " b where b.setting_name = 'hide_menu' and  b.visibility = t.id and  b.box_id = '" . (int)$this->id . "'");
    while ($item = tep_db_fetch_array($media_query_arr)){      
      $hide_size[] = explode('w', $item['setting_value']);
    }
    
    return IncludeTpl::widget(['file' => 'boxes/menu.tpl', 'params' => [
      'menu' => $menu,
      'categories' => $categories,
      'is_menu' => $is_menu,
      'settings' => $this->settings,
      'menu_htm' => $this->menuTree($menu),
      'hide_size' => $hide_size,
      'id' => $this->id,
    ]]);
  }
  
  public function menuTree ($menu, $parent = 0, $ul = true) {
    $htm = '';
    
    foreach ($menu as $item){
      if ($item['parent_id'] == $parent){
        if ($item['link_id'] == 999999999){
          $htm .= $this->menuTree($menu, $item['id'], false);
        } else {
          if ($item['count'] != -1){
            $htm .= '<li' . ($item['class'] ? ' class="' . $item['class'] . '"' : '') . '>';
            if ($item['title']){
              if ($item['link']){
                $htm .= '<a href="' . $item['link'] . '"' . ($item['target_blank'] == 1 ? ' target="_blank"' : '') . '>' . $item['title'] . '</a>';
              } else {
                $htm .= '<span class="no-link">' . $item['title'] . '</span>';
              }
            }
            
            if ($item['link_type'] != 'categories' || $item['sub_categories'] == 1){
              $htm .= $this->menuTree($menu, $item['id']);
            }
            $htm .= '</li>';
          }
        }
      }
    }
    if ($ul && $htm) $htm = '<ul>' . $htm . '</ul>';
    
    return $htm;
  }


}