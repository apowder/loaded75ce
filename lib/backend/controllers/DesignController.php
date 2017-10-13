<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;
use backend\design\Uploads;
use backend\design\Steps;
use common\classes\design;
/**
 *
 */
class DesignController extends Sceleton {
    
    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES'];
    
  /**
   *
   */
  public function actionIndex()
  {
    return '';
  }


  public function actionTheme()
  {
    $params = Yii::$app->request->get();

    $theme = tep_db_fetch_array(tep_db_query("select * from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'"));

    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => $theme['theme_name']);
    $this->view->headingTitle = $theme['theme_name'];
    
    return $this->render('theme.tpl', [
      'theme' => $theme,
    ]);
  }


  public function actionThemeRestore()
  {
    $params = Yii::$app->request->get();
    //echo file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/design.sql');
    //die;

    $boxes_sql = tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($boxes_sql)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
    }

    $boxes_sql1 = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($boxes_sql1)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$item['id'] . "'");
    }

    tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where 	theme_name = '" . tep_db_input($params['theme_name']) . "'");
    tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where 	theme_name = '" . tep_db_input($params['theme_name']) . "'");


    tep_db_query(file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/sql/design_boxes.sql'));
    tep_db_query(file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/sql/design_boxes_settings.sql'));
    tep_db_query(file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/sql/design_boxes_settings_tmp.sql'));
    tep_db_query(file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/sql/design_boxes_tmp.sql'));
    tep_db_query(file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/sql/themes_settings.sql'));
    tep_db_query(file_get_contents(DIR_FS_CATALOG . 'lib/frontend/themes/' . $params['theme_name'] . '/sql/themes_styles.sql'));

    //tep_redirect(tep_href_link('design/theme', 'theme_name=' . $params['theme_name']));

    $this->actionElementsSave();

    return 'ok';
  }

  public function actionThemes()
  {
    $params = Yii::$app->request->post();

    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('design/theme-add') . '" class="create_item menu-ico">' . TEXT_ADD_THEME . '</a>';

    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => BOX_HEADING_THEMES);
    $this->view->headingTitle = BOX_HEADING_THEMES;

    $themes_array = tep_db_query("select * from " . TABLE_THEMES . " order by sort_order");
    $themes = array();
    while ($item = tep_db_fetch_array($themes_array)){
      $themes[] = $item;
    }

    return $this->render('themes.tpl', [
      'themes' => $themes,
    ]);
  }

  public function actionThemeAdd()
  {
    \common\helpers\Translation::init('admin/design');

    $themes = array();
    $query = tep_db_query("select id, theme_name, title from " . TABLE_THEMES . " where install = '1' order by sort_order");
    while ($theme = tep_db_fetch_array($query)){
      $themes[] = $theme;
    }
    
    $this->layout = 'popup.tpl';
    return $this->render('theme-add.tpl', ['themes' => $themes, 'action' => Yii::$app->urlManager->createUrl('design/theme-add-action')]);
  }

  public function actionThemeAddAction()
  {
    \common\helpers\Translation::init('admin/design');
    $params = Yii::$app->request->get();
    $this->layout = false;

    if (!$params['title']) {
      return json_encode(['code' => 1, 'text' => THEME_TITLE_REQUIRED]);
    }

    if (!$params['theme_name']) {
      $name = $params['title'];
      $name = strtolower($name);
      $name = str_replace(' ', '_', $name);
      $name = preg_replace('/[^a-z0-9_-]/', '', $name);
      $params['theme_name'] = $name;
    }
    if (!preg_match("/^[a-z0-9_\-]+$/", $params['theme_name'])) {
      return json_encode(['code' => 1, 'text' => 'Enter only lowercase letters and numbers for theme name']);
    }

    $theme = tep_db_query("select id from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    if (tep_db_num_rows($theme) > 0){
      return json_encode(['code' => 1, 'text' => 'Theme with this name already exist']);
    }

    $query = tep_db_query("select id, sort_order from " . TABLE_THEMES . " where install = '1'");
    while ($theme = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'sort_order' => $theme['sort_order'] + 1,
      );
      tep_db_perform(TABLE_THEMES, $sql_data_array, 'update', " id = '" . $theme['id'] . "'");
    }

    $sql_data_array = array(
      'theme_name' => $params['theme_name'],
      'title' => $params['title'],
      'install' => 1,
      'is_default' => 0,
      'sort_order' => 0,
      'parent_theme' => ($params['parent_theme'] ? $params['parent_theme'] : 0)
    );
    tep_db_perform(TABLE_THEMES, $sql_data_array);


    if ($params['parent_theme']){
      
      $id_array = array();

      $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['parent_theme']) . "'");
      while ($item = tep_db_fetch_array($query)){
        $sql_data_array = array(
          'theme_name' => $params['theme_name'],
          'block_name' => $item['block_name'],
          'widget_name' => $item['widget_name'],
          'widget_params' => $item['widget_params'],
          'sort_order' => $item['sort_order'],
        );
        tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array);
        tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);


        $new_row_id = tep_db_insert_id();
        /*$new_row = tep_db_fetch_array(tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where
            theme_name = '" . $params['theme_name'] . "' and
            block_name = '" . addslashes($item['block_name']) . "' and
            widget_name = '" . addslashes($item['widget_name']) . "' and
            sort_order = '" . addslashes($item['sort_order']) . "'
            "));*/

        $query2 = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
        while ($item2 = tep_db_fetch_array($query2)){
          $sql_data_array = array(
            'box_id' => $new_row_id,
            'setting_name' => $item2['setting_name'],
            'setting_value' => $item2['setting_value'],
            'language_id' => $item2['language_id'],
            'visibility' => $item2['visibility'],
          );
          tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sql_data_array);
          tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array);
        }

        $id_array[$item['id']] = $new_row_id;
      }

      $query = tep_db_query("select id, block_name from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
      while ($item = tep_db_fetch_array($query)){
        preg_match('/[a-z]-([0-9]+)/', $item['block_name'], $matches );
        if ($matches[1]){
          $new_block_name = str_replace($matches[1], $id_array[$matches[1]], $item['block_name']);
          $sql_data_array = array(
            'block_name' => $new_block_name,
          );
          tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array, 'update', " id = '" . $item['id'] . "'");
          tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', " id = '" . $item['id'] . "'");
        }
      }

      $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['parent_theme']) . "'");
      while ($item = tep_db_fetch_array($query)){
        $sql_data_array = array(
          'theme_name' => $params['theme_name'],
          'setting_group' => $item['setting_group'],
          'setting_name' => $item['setting_name'],
          'setting_value' => $item['setting_value'],
        );
        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
      }

      $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['parent_theme']) . "'");
      while ($item = tep_db_fetch_array($query)){
        $sql_data_array = array(
          'theme_name' => $params['theme_name'],
          'selector' => $item['selector'],
          'attribute' => $item['attribute'],
          'value' => $item['value'],
          'visibility' => $item['visibility'],
        );
        tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
        tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array);
      }
      
      
    }
    
    if ($params['landing']) {
        $sql_data_array = array(
          'theme_name' => $params['theme_name'],
          'setting_group' => 'hide',
          'setting_name' => 'landing',
          'setting_value' => '1',
        );
        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
    }

    return json_encode(['code' => 2, 'text' => 'Theme added']);
  }


  public function actionThemeRemove(){

    $params = Yii::$app->request->get();
    $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){

      tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$item['id'] . "'");

    }
    tep_db_query("delete from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    tep_db_query("delete from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");

    return Yii::$app->getResponse()->redirect(array('design/themes'));
  }



  //   admin/design/theme-setting?theme_name=theme-1
  public function actionThemeSetting()
  {
    $params = Yii::$app->request->get();

    $design_boxes = tep_db_query("select * from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");

    $val = '<div style="float: left; width: 50%"><h2>design_boxes</h2><pre>';
    while ($item = tep_db_fetch_array($design_boxes)){
        $val .= var_export([
            'block_name' => $item['block_name'],
            'widget_name' => $item['widget_name'],
            'widget_params' => $item['widget_params'],
            'sort_order' => $item['sort_order'],
        ],true).",\n";
      /*$val .= '
      array(
            \'block_name\' => \'' . $item['block_name'] . '\',
            \'widget_name\' => \'' . $item['widget_name'] . '\',
            \'widget_params\' => \'' . $item['widget_params'] . '\',
            \'sort_order\' => \'' . $item['sort_order'] . '\'
          ),';*/
    }
    $val .= '</pre></div>';

    $design_boxes = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");

    $val .= '<div style="float: left; width: 50%"><h2>design_boxes</h2><pre>';
    while ($item = tep_db_fetch_array($design_boxes)){
        $val .= var_export([
            'setting_group' => $item['setting_group'],
            'setting_name' => $item['setting_name'],
            'setting_value' => $item['setting_value'],
        ],true).",\n";
      /*$val .= '
      array(
            \'setting_group\' => \'' . $item['setting_group'] . '\',
            \'setting_name\' => \'' . $item['setting_name'] . '\',
            \'setting_value\' => \'' . $item['setting_value'] . '\'
          ),';*/
    }
    $val .= '</pre></div>';

    return $val;
  }


  public function actionThemeEdit()
  {
    global $languages_id;
    \common\helpers\Translation::init('admin/design');

    $params = Yii::$app->request->get();

    $language_query = tep_db_fetch_array(tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . $languages_id . "' order by sort_order"));
    $language_code = $language_query['code'];

    $this->topButtons[] = '<span data-href="' . Yii::$app->urlManager->createUrl(['design/theme-save', 'theme_name' => $params['theme_name']]) . '" class="btn btn-confirm btn-save-boxes btn-elements">'.IMAGE_SAVE.'</span> <span class="redo-buttons"></span>';

    $query = tep_db_fetch_array(tep_db_query("select title from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'"));

    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/elements'), 'title' => BOX_HEADING_MAIN_STYLES . ' "' . $query['title'] . '"');
    $this->view->headingTitle = BOX_HEADING_MAIN_STYLES . ' "' . $query['title'] . '"';

    $editable_links = array();
    $editable_links['home'] = tep_href_link('..');

    $not_array = array();

    $bundle_sets_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SETS_PRODUCTS . " sp where sp.sets_id = p.products_id and p.products_status = '1' order by sp.sort_order");
    if ($bundle_sets = tep_db_fetch_array($bundle_sets_query)){
      $editable_links['bundle'] = tep_href_link('../catalog/product?products_id=' . $bundle_sets['products_id']);
      $not_array[] = $bundle_sets['products_id'];
    }
    while ($bundle_sets = tep_db_fetch_array($bundle_sets_query)){
      $editable_links['bundle'] = tep_href_link('../catalog/product?products_id=' . $bundle_sets['products_id']);
      if (!in_array($bundle_sets['products_id'], $not_array)) {
        $not_array[] = $bundle_sets['products_id'];
      }
    }

    $attributes_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id = p.products_id");
    if ($attributes = tep_db_fetch_array($attributes_query)){
      $editable_links['attributes'] = tep_href_link('../catalog/product?products_id=' . $attributes['products_id']);
      if (!in_array($attributes['products_id'], $not_array)) {
        $not_array[] = $attributes['products_id'];
      }
    }
    while ($attributes = tep_db_fetch_array($attributes_query)){
      $editable_links['attributes'] = tep_href_link('../catalog/product?products_id=' . $attributes['products_id']);
      if (!in_array($attributes['products_id'], $not_array)) {
        $not_array[] = $attributes['products_id'];
      }
    }

    if (count($not_array) > 0) {
      $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_status = 1 and products_id not in ('" . implode("','", $not_array) . "')");
      if ($products = tep_db_fetch_array($products_query)) {
        $editable_links['product'] = tep_href_link('../catalog/product?products_id=' . $products['products_id']);
      }
    }

    $categories_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where parent_id != 0 and categories_status = 1");
    if ($categories = tep_db_fetch_array($categories_query)){
      $editable_links['categories'] = tep_href_link('../catalog/index', 'cPath=' . $categories['parent_id']);
    }

    $categories_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p2c.categories_id = c.categories_id and c.categories_status = 1");
    if ($categories = tep_db_fetch_array($categories_query)){
      $editable_links['products'] = tep_href_link('../catalog/index', 'cPath=' . $categories['categories_id']);
    }

    $information_query = tep_db_query("select 	information_id from " . TABLE_INFORMATION . " where visible = 1 AND platform_id='".\common\classes\platform::firstId()."' ");
    if ($information = tep_db_fetch_array($information_query)){
      $editable_links['information'] = tep_href_link('../info/index', 'info_id=' . $information['information_id']);
    }


    $editable_links['cart'] = tep_href_link('../shopping-cart/index');

    $editable_links['success'] = tep_href_link('../checkout/success');

    $editable_links['contact'] = tep_href_link('../contact/index');

    $editable_links['gift'] = tep_href_link('../catalog/gift-card');


    $css = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_name = 'css'"));
    $javascript = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_name = 'javascript'"));

    return $this->render('theme-edit.tpl', [
      'menu' => 'theme-edit',
      'link_save' => Yii::$app->urlManager->createUrl(['design/theme-save', 'theme_name' => $params['theme_name']]),
      'link_cancel' => Yii::$app->urlManager->createUrl(['design/theme-cancel']),
      'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
      'clear_url' => ($params['theme_name'] ? true : false),
      'editable_links' => $editable_links,
      'css' => $css['setting_value'],
      'javascript' => $javascript['setting_value'],
      'language_code' => $language_code
    ]);
  }

  public function actionCss()
  {
    \common\helpers\Translation::init('admin/design');

    $this->topButtons[] = '<span class="btn btn-confirm btn-save-css btn-elements ">' . IMAGE_SAVE . '</span>';

    $params = Yii::$app->request->get();

    $css = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_name = 'css'"));

    return $this->render('css.tpl', [
      'menu' => 'css',
      'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
      'css' => $css['setting_value'],
    ]);
  }

  public function actionJs()
  {
    global $languages_id;
    \common\helpers\Translation::init('admin/design');

    $params = Yii::$app->request->get();

    $this->topButtons[] = '<span class="btn btn-confirm btn-save-javascript btn-elements ">' . IMAGE_SAVE . '</span>';

    $javascript = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_name = 'javascript'"));

    return $this->render('js.tpl', [
      'menu' => 'js',
      'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
      'javascript' => $javascript['setting_value'],
    ]);
  }

  public function actionCssSave()
  {
    $params = Yii::$app->request->post();

    $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_group = 'css'");
    $css_old = tep_db_fetch_array($query);
    $css_old = $css_old['setting_value'];

    if (tep_db_num_rows($query) == 0) {
      $sql_data_array = array(
        'theme_name' => $params['theme_name'],
        'setting_group' => 'css',
        'setting_name' => 'css',
        'setting_value' => $params['css']
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
    } else {
      $sql_data_array = array(
        'setting_value' => $params['css']
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_name = 'css'");
    }
    
    $data = [
      'theme_name' => $params['theme_name'],
      'css_old' => $css_old,
      'css' => $params['css'],
    ];
    Steps::cssSave($data);
    
    return '';

  }


  public function actionJavascriptSave()
  {
    $params = Yii::$app->request->post();

    $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_group = 'javascript'"));

    $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_group = 'javascript'");
    $javascript_old = tep_db_fetch_array($query);
    $javascript_old = $javascript_old['setting_value'];
    
    if (tep_db_num_rows($query) == 0) {
      $sql_data_array = array(
        'theme_name' => $params['theme_name'],
        'setting_group' => 'javascript',
        'setting_name' => 'javascript',
        'setting_value' => $params['javascript']
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
    } else {
      $sql_data_array = array(
        'setting_value' => $params['javascript']
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_name = 'javascript'");
    }
    
    $data = [
      'theme_name' => $params['theme_name'],
      'javascript_old' => $javascript_old,
      'javascript' => $params['javascript'],
    ];
    Steps::javascriptSave($data);
    
    return '';

  }

  public function actionThemeSave()
  {
    $get = Yii::$app->request->get();
    
    Steps::themeSave($get['theme_name']);

    tep_db_query("delete from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
    tep_db_query("INSERT INTO " . TABLE_THEMES_STYLES . " SELECT * FROM " . TABLE_THEMES_STYLES_TMP . " WHERE theme_name = '" . tep_db_input($get['theme_name']) . "'");

    /*tep_db_query("TRUNCATE TABLE " . TABLE_THEMES_STYLES);
    tep_db_query("INSERT " . TABLE_THEMES_STYLES . " SELECT * FROM " . TABLE_THEMES_STYLES_TMP . ";");*/

    return 'Saved';
  }
  
  public function actionThemeCancel()
  {
    $get = Yii::$app->request->get();

    Steps::themeCancel($get['theme_name']);

    tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
    tep_db_query("INSERT INTO " . TABLE_THEMES_STYLES_TMP . " SELECT * FROM " . TABLE_THEMES_STYLES . " WHERE theme_name = '" . tep_db_input($get['theme_name']) . "'");
    
    /*tep_db_query("TRUNCATE TABLE " . TABLE_THEMES_STYLES_TMP);
    tep_db_query("INSERT " . TABLE_THEMES_STYLES_TMP . " SELECT * FROM " . TABLE_THEMES_STYLES . ";");*/

    return 'Canseled';
  }


  public function actionElements()
  {
    \common\helpers\Translation::init('admin/design');

    global $languages_id;
    $this->selectedMenu = array('design', 'elements');
    $params = Yii::$app->request->get();

    $language_query = tep_db_fetch_array(tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . $languages_id . "' order by sort_order"));
    $language_code = $language_query['code'];

    $this->topButtons[] = '<span data-href="' . Yii::$app->urlManager->createUrl(['design/elements-save']) . '" class="btn btn-confirm btn-save-boxes btn-elements">' . IMAGE_SAVE . '</span> <span class="btn btn-preview-2 btn-elements">' . IMAGE_PREVIEW_POPUP . '</span> <span class="btn btn-preview btn-elements">' . IMAGE_PREVIEW . '</span><span class="btn btn-edit btn-elements" style="display: none">' . IMAGE_EDIT . '</span><span class="redo-buttons"></span>';

    $query = tep_db_fetch_array(tep_db_query("select id, title from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'"));

    $_theme_id = (int)$query['id'];
    $platform_select = array();
    $_attached_platform_list_r = tep_db_query("SELECT platform_id FROM ".TABLE_PLATFORMS_TO_THEMES." WHERE theme_id='".$_theme_id."' ");
    if ( tep_db_num_rows($_attached_platform_list_r)>0 ) {
      while( $_attached_platform = tep_db_fetch_array($_attached_platform_list_r) ) {
        foreach (\common\classes\platform::getList() as $_platform_info) {
          if ($_platform_info['id']==$_attached_platform['platform_id']){
            $platform_select[] = $_platform_info;
          }
        }
      }
    }
    if ( count($platform_select)==0 ) {
      $platform_select = \common\classes\platform::getList();
      $platform_select = array_slice($platform_select,0,1);
    }

    //$this->selectedMenu = array('design_controls', 'design/elements');
    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/elements'), 'title' => BOX_HEADING_ELEMENTS . ' "' . $query['title'] . '"');
    $this->view->headingTitle = BOX_HEADING_ELEMENTS . ' "' . $query['title'] . '"';

    $per_platform_links = array();
    foreach( $platform_select as $_platform ) {
      Yii::$app->get('platform')->config($_platform['id'])->catalogBaseUrlWithId(true);

      $editable_links = array(
        'home' => '',
        'product' => '',
        'attributes' => '',
        'bundle' => '',
        'categories' => '',
        'products' => '',
        'information' => '',
        'cart' => '',
        'success' => '',
        'contact' => '',
        'email' => '',
        'invoice' => '',
        'packingslip' => '',
      );
      $editable_links['home'] = tep_catalog_href_link('', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code);

      $settings = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'added_page' and setting_name = 'home'");
      $editable_links_home = array();
      while ($item = tep_db_fetch_array($settings)){
        $page_name = design::pageName($item['setting_value']);
        $editable_links_home[] = array(
          'page_name' => $page_name,
          'page_title' => $item['setting_value'],
          'link' => tep_catalog_href_link('', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code . '&page_name=' . $page_name)
        );
      }

      $not_array = array();

      /*$bundle_sets_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p inner join ".TABLE_PLATFORMS_PRODUCTS." plp on plp.products_id=p.products_id and plp.platform_id='".$_platform['id']."', " . TABLE_SETS_PRODUCTS . " sp where sp.sets_id = p.products_id and p.products_status = '1' order by sp.sort_order");
      if ($bundle_sets = tep_db_fetch_array($bundle_sets_query)) {
        $editable_links['bundle'] = tep_catalog_href_link('catalog/product', 'products_id=' . $bundle_sets['products_id'].'&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
        $not_array[] = $bundle_sets['products_id'];
      }
      while ($bundle_sets = tep_db_fetch_array($bundle_sets_query)) {
        $editable_links['bundle'] = tep_catalog_href_link('catalog/product', 'products_id=' . $bundle_sets['products_id'].'&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
        if (!in_array($bundle_sets['products_id'], $not_array)) {
          $not_array[] = $bundle_sets['products_id'];
        }
      }

      $attributes_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p inner join ".TABLE_PLATFORMS_PRODUCTS." plp on plp.products_id=p.products_id and plp.platform_id='".$_platform['id']."', " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id = p.products_id");
      if ($attributes = tep_db_fetch_array($attributes_query)) {
        $editable_links['attributes'] = tep_catalog_href_link('catalog/product', 'products_id=' . $attributes['products_id'].'&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
        if (!in_array($attributes['products_id'], $not_array)) {
          $not_array[] = $attributes['products_id'];
        }
      }
      while ($attributes = tep_db_fetch_array($attributes_query)) {
        $editable_links['attributes'] = tep_catalog_href_link('catalog/product', 'products_id=' . $attributes['products_id'].'&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
        if (!in_array($attributes['products_id'], $not_array)) {
          $not_array[] = $attributes['products_id'];
        }
      }*/

      if (count($not_array) > 0) {
        $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p inner join ".TABLE_PLATFORMS_PRODUCTS." plp on plp.products_id=p.products_id and plp.platform_id='".$_platform['id']."' where p.products_status = 1 and p.products_id not in ('" . implode("','", $not_array) . "')");
      } else {
        $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p inner join ".TABLE_PLATFORMS_PRODUCTS." plp on plp.products_id=p.products_id and plp.platform_id='".$_platform['id']."' where p.products_status = 1");
      }
      if ($products = tep_db_fetch_array($products_query)) {
        $editable_links['product'] = tep_catalog_href_link('catalog/product', 'products_id=' . $products['products_id'].'&theme_name=' . $params['theme_name'] . '&language=' . $language_code);

        $settings = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'added_page' and setting_name = 'product'");
        $editable_links_product = array();
        while ($item = tep_db_fetch_array($settings)){
          $page_name = design::pageName($item['setting_value']);
          $editable_links_product[] = array(
            'page_name' => $page_name,
            'page_title' => $item['setting_value'] . ' <span class="edit" data-name="' . $item['setting_value'] . '"></span>',
            'link' => tep_catalog_href_link('catalog/product', 'products_id=' . $products['products_id'].'&theme_name=' . $params['theme_name'] . '&language=' . $language_code . '&page_name=' . $page_name)
          );
        }
      }

      $categories_query = tep_db_query("select c.parent_id from " . TABLE_CATEGORIES . " c inner join ".TABLE_PLATFORMS_CATEGORIES." plc on plc.categories_id=c.categories_id and plc.platform_id='".$_platform['id']."' where parent_id != 0 and categories_status = 1");
      if ($categories = tep_db_fetch_array($categories_query)) {
        $editable_links['categories'] = tep_catalog_href_link('catalog/index', 'cPath=' . $categories['parent_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code);

        $settings = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'added_page' and setting_name = 'categories'");
        $editable_links_categories = array();
        while ($item = tep_db_fetch_array($settings)){
          $page_name = design::pageName($item['setting_value']);
          $editable_links_categories[] = array(
            'page_name' => $page_name,
            'page_title' => $item['setting_value'],
            'link' => tep_catalog_href_link('catalog/index', 'cPath=' . $categories['parent_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code . '&page_name=' . $page_name)
          );
        }
      }

      $categories_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c inner join ".TABLE_PLATFORMS_CATEGORIES." plc on plc.categories_id=c.categories_id and plc.platform_id='".$_platform['id']."', " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p2c.categories_id = c.categories_id and c.categories_status = 1");
      while ($categories = tep_db_fetch_array($categories_query)) {
        if (\common\helpers\Categories::products_in_category_count($categories['categories_id']) > 0) {
          if (!\common\helpers\Categories::has_category_subcategories($categories['categories_id'])) {
            $editable_links['products'] = tep_catalog_href_link('catalog/index', 'cPath=' . $categories['categories_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
          }

          $settings = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . $params['theme_name'] . "' and setting_group = 'added_page' and setting_name = 'products'");
          $editable_links_products = array();
          while ($item = tep_db_fetch_array($settings)) {
            $page_name = design::pageName($item['setting_value']);
            $editable_links_products[] = array(
              'page_name' => $page_name,
              'page_title' => $item['setting_value'] . ' <span class="edit" data-name="' . $item['setting_value'] . '"></span>',
              'link' => tep_catalog_href_link('catalog/index', 'cPath=' . $categories['categories_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code . '&page_name=' . $page_name)
            );
          }
        }
      }
      $information_query = tep_db_query("select information_id from " . TABLE_INFORMATION . " where visible = 1 AND platform_id='" . \common\classes\platform::firstId() . "' ");
      if ($information = tep_db_fetch_array($information_query)) {
        $editable_links['information'] = tep_catalog_href_link('info/index', 'info_id=' . $information['information_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
        
        $settings = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'added_page' and (setting_name = 'info' or setting_name = 'custom')");
        $editable_links_info = array();
        while ($item = tep_db_fetch_array($settings)){
          $page_name = design::pageName($item['setting_value']);
          $editable_links_info[] = array(
            'page_name' => $page_name,
            'page_title' => $item['setting_value'],
            'link' => tep_catalog_href_link('info/index', 'info_id=' . $information['information_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code . '&page_name=' . $page_name)      
          );
        }
      }


      $editable_links['cart'] = tep_catalog_href_link('shopping-cart/index', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code);

      $editable_links['success'] = tep_catalog_href_link('checkout/success', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code,'SSL');

      $editable_links['contact'] = tep_catalog_href_link('contact/index', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code);

      $editable_links['email'] = tep_catalog_href_link('email-template', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code);

      $editable_links['gift'] = tep_href_link('../catalog/gift-card', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code);


      $order_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where platform_id='".$_platform['id']."' limit 1");
      if (tep_db_num_rows($order_query) == 0){
        $order_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " limit 1");
      }
      if ($order = tep_db_fetch_array($order_query)) {
        $editable_links['invoice'] = tep_catalog_href_link('email-template/invoice', 'orders_id=' . $order['orders_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
        $editable_links['packingslip'] = tep_catalog_href_link('email-template/packingslip', 'orders_id=' . $order['orders_id'] . '&theme_name=' . $params['theme_name'] . '&language=' . $language_code);
      }

      $editable_links['blog'] = tep_catalog_href_link('blog', 'theme_name=' . $params['theme_name'] . '&language=' . $language_code);
      
      $per_platform_links[ $_platform['id'] ] = $editable_links;

      Yii::$app->get('platform')->config($_platform['id'])->catalogBaseUrlWithId();
    }

    Yii::$app->get('platform')->config(\common\classes\platform::firstId());

    reset($per_platform_links);
    $first_platform_links = current($per_platform_links);
    if ( isset($_COOKIE['page-url']) && !in_array($_COOKIE['page-url'], $first_platform_links) ) {
      setcookie('page-url', null, -1, DIR_WS_ADMIN);
    }
    
    if (\frontend\design\Info::themeSetting('landing', 'hide', $params['theme_name'])) {
        $landing = 1;
    } else {
        $landing = 0;
    }

    return $this->render('elements.tpl', [
      'menu' => 'elements',
      'link_save' => Yii::$app->urlManager->createUrl(['design/elements-save']),
      'link_cancel' => Yii::$app->urlManager->createUrl(['design/elements-cancel']),
      'link_copy' => Yii::$app->urlManager->createUrl(['design/elements-copy']),
      'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
      'clear_url' => ($params['theme_name'] ? true : false),
      'editable_links' => $first_platform_links,
      'per_platform_links' => $per_platform_links,
      'platform_select' => $platform_select,
      'editable_links_home' => $editable_links_home,
      'editable_links_product' => $editable_links_product,
      'editable_links_categories' => $editable_links_categories,
      'editable_links_products' => $editable_links_products,
      'editable_links_info' => $editable_links_info,
      'landing' => $landing
    ]);
  }


  public function actionElementsSave()
  {
    \common\helpers\Translation::init('admin/design');
    $get = tep_db_prepare_input(Yii::$app->request->get());

    $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
    }

    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){

      tep_db_perform(TABLE_DESIGN_BOXES, $item);

      tep_db_query("INSERT INTO " . TABLE_DESIGN_BOXES_SETTINGS . " SELECT * FROM " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " WHERE box_id = '" . (int)$item['id'] . "'");
    }


    /*tep_db_query("TRUNCATE TABLE " . TABLE_DESIGN_BOXES);
    tep_db_query("INSERT " . TABLE_DESIGN_BOXES . " SELECT * FROM " . TABLE_DESIGN_BOXES_TMP . ";");
    tep_db_query("TRUNCATE TABLE " . TABLE_DESIGN_BOXES_SETTINGS);
    tep_db_query("INSERT " . TABLE_DESIGN_BOXES_SETTINGS . " SELECT * FROM " . TABLE_DESIGN_BOXES_SETTINGS_TMP . ";");*/

    Steps::elementsSave($get['theme_name']);

    return '<div class="popup-heading">' . TEXT_NOTIFIC . '</div><div class="popup-content pop-mess-cont">'.MESSAGE_SAVED.'</div>';
  }


  public function actionElementsCancel()
  {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    
    Steps::elementsCancel($get['theme_name']);

    $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$item['id'] . "'");
    }

    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){

      tep_db_perform(TABLE_DESIGN_BOXES_TMP, $item);
      
      tep_db_query("INSERT INTO " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " SELECT * FROM " . TABLE_DESIGN_BOXES_SETTINGS . " WHERE box_id = '" . (int)$item['id'] . "'");
    }


    /*tep_db_query("TRUNCATE TABLE " . TABLE_DESIGN_BOXES_TMP);
    tep_db_query("INSERT " . TABLE_DESIGN_BOXES_TMP . " SELECT * FROM " . TABLE_DESIGN_BOXES . ";");
    tep_db_query("TRUNCATE TABLE " . TABLE_DESIGN_BOXES_SETTINGS_TMP);
    tep_db_query("INSERT " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " SELECT * FROM " . TABLE_DESIGN_BOXES_SETTINGS . ";");*/

    return '<div class="popup-heading">' . TEXT_NOTIFIC . '</div><div class="popup-content pop-mess-cont">Canceled</div>';
  }


  public function actionInvoice()
  {
    $this->selectedMenu = array('design_controls', 'design/invoice');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/invoice'), 'title' => BOX_HEADING_INVOICE);
    $this->view->headingTitle = BOX_HEADING_INVOICE;

    return $this->render('invoice.tpl');
  }


  public function actionBlocksMove()
  {
    $params = Yii::$app->request->post();

    $this->actionBackupAuto($params['theme_name']);

    $i = 1;
    $positions = array();
    if (is_array($params['id'])) foreach ($params['id'] as $item){
      $id = substr($item, 4);
      $sql_data_array = array(
        'block_name' => tep_db_prepare_input($params['name']),
        'sort_order' => $i,
      );
      $i++;
      $positions[] = array_merge(['id' => $id], $sql_data_array);
      $positions_old[] = tep_db_fetch_array(tep_db_query("select id, block_name, sort_order from " . TABLE_DESIGN_BOXES_TMP . " where id='" . (int)$id . "'"));
      tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', "id = '" . (int)$id . "'");
    }

    $data = [
      'positions' => $positions,
      'positions_old' => $positions_old,
      'theme_name' => $params['theme_name'],
    ];
    Steps::blocksMove($data);
    
    return json_encode('');
  }

  public static function deleteBlock($id) {
    $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "' or block_name = 'block-" . tep_db_input($id) . "-2' or block_name = 'block-" . tep_db_input($id) . "-3' or block_name = 'block-" . tep_db_input($id) . "-4' or block_name = 'block-" . tep_db_input($id) . "-5'");
    while ($item = tep_db_fetch_array($query)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . $item['id'] . "'");
      self::deleteBlock($item['id']);
    }
  }

  public function actionBoxDelete()
  {
    $params = tep_db_prepare_input(Yii::$app->request->post());

    $id = substr($params['id'], 4);

    Steps::boxDelete([
      'theme_name' => $params['theme_name'],
      'id' => $id
    ]);

    $this->actionBackupAuto($params['theme_name']);

    tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'");
    tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "'");

    self::deleteBlock($id);
    
    return json_encode('');
  }


  public function actionWidgetsList()
  {
    \common\helpers\Translation::init('admin/design');
    $params = Yii::$app->request->get();

    $widgets = array();
    if ($params['type'] == 'product'){
      $widgets[] = array('name' => 'title', 'title' => PRODUCTS_WIDGETS, 'description' => '', 'type' => 'product');
      $widgets[] = array('name' => 'product\Name', 'title' => TEXT_PRODUCTS_NAME, 'description' => '', 'type' => 'product', 'class' => 'name');
      $widgets[] = array('name' => 'product\Images', 'title' => TEXT_PRODUCTS_IMAGES, 'description' => '', 'type' => 'product', 'class' => 'images');
      $widgets[] = array('name' => 'product\ImagesAdditional', 'title' => TEXT_ADDITIONAL_IMAGES, 'description' => '', 'type' => 'product', 'class' => 'images');
      $widgets[] = array('name' => 'product\Attributes', 'title' => TEXT_PRODUCTS_ATTRIBUTES, 'description' => '', 'type' => 'product', 'class' => 'attributes');
      $widgets[] = array('name' => 'product\Inventory', 'title' => BOX_CATALOG_INVENTORY, 'description' => '', 'type' => 'product', 'class' => 'attributes');
      $widgets[] = array('name' => 'product\Bundle', 'title' => TEXT_PRODUCTS_BUNDLE, 'description' => '', 'type' => 'product', 'class' => 'bundle');
      $widgets[] = array('name' => 'product\InBundles', 'title' => TEXT_PRODUCTS_IN_BUNDLE, 'description' => '', 'type' => 'product', 'class' => 'in-bundles');
      $widgets[] = array('name' => 'product\Price', 'title' => TEXT_PRODUCTS_PRICE, 'description' => '', 'type' => 'product', 'class' => 'price');
      $widgets[] = array('name' => 'product\QuantityDiscounts', 'title' => QUANTITY_DISCOUNTS, 'description' => '', 'type' => 'product', 'class' => 'price');
      $widgets[] = array('name' => 'product\Quantity', 'title' => TEXT_QUANTITY_INPUT, 'description' => '', 'type' => 'product', 'class' => 'quantity');
	    $widgets[] = array('name' => 'product\Stock', 'title' => TEXT_STOCK_INDICATION, 'description' => '', 'type' => 'product', 'class' => 'stock');
      $widgets[] = array('name' => 'product\Buttons', 'title' => TEXT_BUTTONS, 'description' => '', 'type' => 'product', 'class' => 'buttons');
      $widgets[] = array('name' => 'product\Description', 'title' => TEXT_PRODUCTS_DESCRIPTION, 'description' => '', 'type' => 'product', 'class' => 'description');
      $widgets[] = array('name' => 'product\DescriptionShort', 'title' => TEXT_PRODUCTS_DESCRIPTION_SHORT, 'description' => '', 'type' => 'product', 'class' => 'description');
      $widgets[] = array('name' => 'product\Reviews', 'title' => TEXT_PRODUCTS_REVIEWS, 'description' => '', 'type' => 'product', 'class' => 'reviews');
      $widgets[] = array('name' => 'product\Properties', 'title' => TEXT_PRODUCTS_PROPERTIES, 'description' => '', 'type' => 'product', 'class' => 'properties');
      $widgets[] = array('name' => 'product\Model', 'title' => TABLE_HEADING_PRODUCTS_MODEL, 'description' => '', 'type' => 'product', 'class' => 'properties');
      $widgets[] = array('name' => 'product\PropertiesIcons', 'title' => TEXT_PROPERTIES_ICONS, 'description' => '', 'type' => 'product', 'class' => 'properties');
      $widgets[] = array('name' => 'product\AlsoPurchased', 'title' => TEXT_ALSO_PURCHASED, 'description' => '', 'type' => 'product', 'class' => 'also-purchased');
      $widgets[] = array('name' => 'product\CrossSell', 'title' => TEXT_CROSS_SELL_PRODUCTS, 'description' => '', 'type' => 'product', 'class' => 'cross-sell');
      $widgets[] = array('name' => 'product\Brand', 'title' => TEXT_LABEL_BRAND, 'description' => '', 'type' => 'product', 'class' => 'brands');
      $widgets[] = array('name' => 'product\Video', 'title' => TEXT_VIDEO, 'description' => '', 'type' => 'product', 'class' => 'video');
      $widgets[] = array('name' => 'product\Documents', 'title' => TAB_DOCUMENTS, 'description' => '', 'type' => 'product', 'class' => 'description');
      $widgets[] = array('name' => 'product\Configurator', 'title' => TEXT_CONFIGURATOR, 'description' => '', 'type' => 'product', 'class' => 'configurator');
    }
    if ($params['type'] == 'inform'){
      $widgets[] = array('name' => 'title', 'title' => INFOPAGES_WIDGETS, 'description' => '', 'type' => 'inform');
      $widgets[] = array('name' => 'info\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'inform', 'class' => 'title');
      $widgets[] = array('name' => 'info\Content', 'title' => TEXT_CONTENT, 'description' => '', 'type' => 'inform', 'class' => 'content');
    }
    if ($params['type'] == 'catalog'){
      $widgets[] = array('name' => 'title', 'title' => CATALOGS_WIDGETS, 'description' => '', 'type' => 'catalog');
      $widgets[] = array('name' => 'catalog\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'catalog', 'class' => 'title');
      $widgets[] = array('name' => 'catalog\Description', 'title' => TEXT_CATEGORY_DESCRIPTION, 'description' => '', 'type' => 'catalog', 'class' => 'description');
      $widgets[] = array('name' => 'catalog\Image', 'title' => TEXT_CATEGORY_IMAGE, 'description' => '', 'type' => 'catalog', 'class' => 'image');
      $widgets[] = array('name' => 'PagingBar', 'title' => TEXT_PAGING_BAR, 'description' => '', 'type' => 'catalog', 'class' => 'paging-bar');
      $widgets[] = array('name' => 'Listing', 'title' => TEXT_PRODUCT_LISTING, 'description' => '', 'type' => 'catalog', 'class' => 'listing');
      $widgets[] = array('name' => 'ListingFunctionality', 'title' => TEXT_LISTING_FUNCTIONALITY_BAR, 'description' => '', 'type' => 'catalog', 'class' => 'listing-functionality');
      $widgets[] = array('name' => 'Categories', 'title' => TEXT_CATEGORIES, 'description' => '', 'type' => 'catalog', 'class' => 'categories');
      $widgets[] = array('name' => 'Filters', 'title' => TEXT_FILTERS, 'description' => '', 'type' => 'catalog', 'class' => 'filters');
    }
    if ($params['type'] == 'cart'){
      $widgets[] = array('name' => 'title', 'title' => SHOPPING_CART_WIDGETS, 'description' => '', 'type' => 'cart');
      $widgets[] = array('name' => 'cart\ContinueBtn', 'title' => CONTINUE_BUTTON, 'description' => '', 'type' => 'cart', 'class' => 'continue-button');
      $widgets[] = array('name' => 'cart\CheckoutBtn', 'title' => CHECKOUT_BUTTON, 'description' => '', 'type' => 'cart', 'class' => 'checkout-button');
      $widgets[] = array('name' => 'cart\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'cart', 'class' => 'products');
      $widgets[] = array('name' => 'cart\SubTotal', 'title' => SUB_TOTAL_AND_GIFT_WRAP_PRICE, 'description' => '', 'type' => 'cart', 'class' => 'price');
      $widgets[] = array('name' => 'cart\GiftCertificate', 'title' => GIFT_CERTIFICATE, 'description' => '', 'type' => 'cart', 'class' => 'gift-certificate');
      $widgets[] = array('name' => 'cart\DiscountCoupon', 'title' => DISCOUNT_COUPON, 'description' => '', 'type' => 'cart', 'class' => 'discount-coupon');
      $widgets[] = array('name' => 'cart\OrderReference', 'title' => TEXT_ORDER_REFERENCE, 'description' => '', 'type' => 'cart', 'class' => 'order-reference');
      $widgets[] = array('name' => 'cart\GiveAway', 'title' => BOX_CATALOG_GIVE_AWAY, 'description' => '', 'type' => 'cart', 'class' => 'give-away');
      $widgets[] = array('name' => 'cart\UpSell', 'title' => FIELDSET_ASSIGNED_UPSELL_PRODUCTS, 'description' => '', 'type' => 'cart', 'class' => 'up-sell');
      $widgets[] = array('name' => 'cart\ShippingEstimator', 'title' => SHOW_SHIPPING_ESTIMATOR_TITLE, 'description' => '', 'type' => 'cart', 'class' => 'shipping-estimator');
      $widgets[] = array('name' => 'cart\OrderTotal', 'title' => ORDER_PRICE_TOTAL, 'description' => '', 'type' => 'cart', 'class' => 'order-total');
    }
    if ($params['type'] == 'success'){
      $widgets[] = array('name' => 'title', 'title' => CHECKOUT_SUCCESS_WIDGETS, 'description' => '', 'type' => 'success');
      $widgets[] = array('name' => 'success\ContinueBtn', 'title' => CONTINUE_BUTTON, 'description' => '', 'type' => 'cart', 'class' => 'continue-button');
      $widgets[] = array('name' => 'success\PrintBtn', 'title' => PRINT_BUTTON, 'description' => '', 'type' => 'cart', 'class' => 'print-button');
    }
    if ($params['type'] == 'contact'){
      $widgets[] = array('name' => 'title', 'title' => CONTACT_PAGE_WIDGETS, 'description' => '', 'type' => 'contact');
      $widgets[] = array('name' => 'contact\ContactForm', 'title' => CONTACT_FORM, 'description' => '', 'type' => 'contact', 'class' => 'contact-form');
      $widgets[] = array('name' => 'contact\Map', 'title' => TEXT_MAP, 'description' => '', 'type' => 'contact', 'class' => 'map');
      $widgets[] = array('name' => 'contact\Contacts', 'title' => TEXT_CONTACTS, 'description' => '', 'type' => 'contact', 'class' => 'contacts');
      $widgets[] = array('name' => 'contact\StreetView', 'title' => GOOGLE_STREET_VIEW, 'description' => '', 'type' => 'contact', 'class' => 'street-view');
    }

    if ($params['type'] == 'email'){
      $widgets[] = array('name' => 'title', 'title' => TABLE_HEADING_EMAIL_TEMPLATES, 'description' => '', 'type' => 'email');
      $widgets[] = array('name' => 'email\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'email', 'class' => 'title');
      $widgets[] = array('name' => 'email\Date', 'title' => TEXT_CURRENT_DATE, 'description' => '', 'type' => 'email', 'class' => 'date');
      $widgets[] = array('name' => 'email\Content', 'title' => TEXT_CONTENT, 'description' => '', 'type' => 'email', 'class' => 'content');
      $widgets[] = array('name' => 'email\BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'email', 'class' => 'block-box');
      $widgets[] = array('name' => 'Banner', 'title' => TEXT_BANNER, 'description' => '', 'type' => 'email', 'class' => 'banner');
      $widgets[] = array('name' => 'email\Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'email', 'class' => 'logo');
      $widgets[] = array('name' => 'email\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'email', 'class' => 'image');
      $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'email', 'class' => 'text');
      $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'email', 'class' => 'import');
      $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'email', 'class' => 'copyright');
    }

    if ($params['type'] == 'invoice'){
      $widgets[] = array('name' => 'title', 'title' => INVOICE_TEMPLATE, 'description' => '', 'type' => 'invoice');
      $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'invoice', 'class' => 'block-box');
      $widgets[] = array('name' => 'email\Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'email', 'class' => 'logo');
      $widgets[] = array('name' => 'email\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'invoice', 'class' => 'image');
      $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'invoice', 'class' => 'text');
      $widgets[] = array('name' => 'invoice\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'invoice', 'class' => 'products');
      $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => 'store-address');
      $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'invoice', 'class' => 'store-phone');
      $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'invoice', 'class' => 'store-email');
      $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'invoice', 'class' => 'store-site');
      $widgets[] = array('name' => 'invoice\ShippingAddress', 'title' => ENTRY_SHIPPING_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => 'shipping-address');
      $widgets[] = array('name' => 'invoice\BillingAddress', 'title' => TEXT_BILLING_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => 'shipping-address');
      $widgets[] = array('name' => 'invoice\ShippingMethod', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'invoice', 'class' => 'shipping-method');
      $widgets[] = array('name' => 'invoice\AddressQrcode', 'title' => ADDRESS_QRCODE, 'description' => '', 'type' => 'invoice', 'class' => 'address-qrcode');
      $widgets[] = array('name' => 'invoice\OrderBarcode', 'title' => ORDER_BARCODE, 'description' => '', 'type' => 'invoice', 'class' => 'order-barcode');
      $widgets[] = array('name' => 'invoice\CustomerName', 'title' => TEXT_CUSTOMER_NAME, 'description' => '', 'type' => 'invoice', 'class' => 'customer-name');
      $widgets[] = array('name' => 'invoice\CustomerEmail', 'title' => TEXT_CUSTOMER_EMAIL, 'description' => '', 'type' => 'invoice', 'class' => 'customer-email');
      $widgets[] = array('name' => 'invoice\CustomerPhone', 'title' => TEXT_CUSTOMER_PHONE, 'description' => '', 'type' => 'invoice', 'class' => 'customer-phone');
      $widgets[] = array('name' => 'invoice\Totals', 'title' => TRXT_TOTALS, 'description' => '', 'type' => 'invoice', 'class' => 'totals');
      $widgets[] = array('name' => 'invoice\OrderId', 'title' => TEXT_ORDER_ID, 'description' => '', 'type' => 'invoice', 'class' => 'order-id');
      $widgets[] = array('name' => 'invoice\PaymentDate', 'title' => TEXT_PAYMENT_DATE, 'description' => '', 'type' => 'invoice', 'class' => 'payment-date');
      $widgets[] = array('name' => 'invoice\PaymentMethod', 'title' => TEXT_SELECT_PAYMENT_METHOD, 'description' => '', 'type' => 'invoice', 'class' => 'payment-method');
      $widgets[] = array('name' => 'invoice\Container', 'title' => TEXT_CONTAINER, 'description' => '', 'type' => 'invoice', 'class' => 'container');
      $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'invoice', 'class' => 'import');
      $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'invoice', 'class' => 'copyright');
    }

    if ($params['type'] == 'packingslip'){
      $widgets[] = array('name' => 'title', 'title' => TEXT_PACKINGSLIP, 'description' => '', 'type' => 'packingslip');
      $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'packingslip', 'class' => 'block-box');
      $widgets[] = array('name' => 'email\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'packingslip', 'class' => 'image');
      $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'packingslip', 'class' => 'text');
      $widgets[] = array('name' => 'packingslip\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'packingslip', 'class' => 'products');
      $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => 'store-address');
      $widgets[] = array('name' => 'invoice\ShippingMethod', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'packingslip', 'class' => 'shipping-method');
      $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'packingslip', 'class' => 'store-phone');
      $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'packingslip', 'class' => 'store-email');
      $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'packingslip', 'class' => 'store-site');
      $widgets[] = array('name' => 'invoice\ShippingAddress', 'title' => ENTRY_SHIPPING_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => 'shipping-address');
      $widgets[] = array('name' => 'invoice\BillingAddress', 'title' => TEXT_BILLING_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => 'shipping-address');
      $widgets[] = array('name' => 'invoice\AddressQrcode', 'title' => ADDRESS_QRCODE, 'description' => '', 'type' => 'packingslip', 'class' => 'address-qrcode');
      $widgets[] = array('name' => 'invoice\OrderBarcode', 'title' => ORDER_BARCODE, 'description' => '', 'type' => 'packingslip', 'class' => 'order-barcode');
      $widgets[] = array('name' => 'invoice\CustomerName', 'title' => TEXT_CUSTOMER_NAME, 'description' => '', 'type' => 'packingslip', 'class' => 'customer-name');
      $widgets[] = array('name' => 'invoice\CustomerEmail', 'title' => TEXT_CUSTOMER_EMAIL, 'description' => '', 'type' => 'packingslip', 'class' => 'customer-email');
      $widgets[] = array('name' => 'invoice\CustomerPhone', 'title' => TEXT_CUSTOMER_PHONE, 'description' => '', 'type' => 'packingslip', 'class' => 'customer-phone');
      $widgets[] = array('name' => 'invoice\OrderId', 'title' => TEXT_ORDER_ID, 'description' => '', 'type' => 'packingslip', 'class' => 'order-id');
      $widgets[] = array('name' => 'invoice\PaymentMethod', 'title' => TEXT_SELECT_PAYMENT_METHOD, 'description' => '', 'type' => 'packingslip', 'class' => 'payment-method');
      $widgets[] = array('name' => 'invoice\Container', 'title' => TEXT_CONTAINER, 'description' => '', 'type' => 'packingslip', 'class' => 'container');
      $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'packingslip', 'class' => 'import');
      $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'packingslip', 'class' => 'copyright');
    }

    if ($params['type'] == 'gift'){
      $widgets[] = array('name' => 'title', 'title' => TEXT_GIFT_CARD, 'description' => '', 'type' => 'gift');
      $widgets[] = array('name' => 'gift\Form', 'title' => TEXT_FORM, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
      $widgets[] = array('name' => 'gift\AmountView', 'title' => AMOUNT_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
      $widgets[] = array('name' => 'gift\MessageView', 'title' => MESSAGE_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
      $widgets[] = array('name' => 'gift\CodeView', 'title' => CODE_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
      $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'gift', 'class' => 'store-address');
      $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'gift', 'class' => 'store-phone');
      $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'gift', 'class' => 'store-email');
      $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'gift', 'class' => 'store-site');
    }
    
    if ($params['type'] != 'email' && $params['type'] != 'invoice' && $params['type'] != 'packingslip') {
      $widgets[] = array('name' => 'title', 'title' => GENERAL_WIDGETS, 'description' => '', 'type' => 'general');
      $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'general', 'class' => 'block-box');
      $widgets[] = array('name' => 'Tabs', 'title' => TEXT_TABS, 'description' => '', 'type' => 'general', 'class' => 'tabs');
      $widgets[] = array('name' => 'Brands', 'title' => TEXT_BRANDS, 'description' => '', 'type' => 'general', 'class' => 'brands');
      $widgets[] = array('name' => 'Bestsellers', 'title' => TEXT_BESTSELLERS, 'description' => '', 'type' => 'general', 'class' => 'bestsellers');
      $widgets[] = array('name' => 'Banner', 'title' => TEXT_BANNER, 'description' => '', 'type' => 'general', 'class' => 'banner');
      $widgets[] = array('name' => 'SpecialsProducts', 'title' => TEXT_SPECIALS_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => 'specials-products');
      $widgets[] = array('name' => 'FeaturedProducts', 'title' => BOX_CATALOG_FEATURED, 'description' => '', 'type' => 'general', 'class' => 'featured-products');
      $widgets[] = array('name' => 'NewProducts', 'title' => TEXT_NEW_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => 'new-products');
      $widgets[] = array('name' => 'ViewedProducts', 'title' => VIEWED_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => 'viewed-products');
      $widgets[] = array('name' => 'Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'general', 'class' => 'logo');
      $widgets[] = array('name' => 'Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'general', 'class' => 'image');
      $widgets[] = array('name' => 'Video', 'title' => TEXT_VIDEO, 'description' => '', 'type' => 'general', 'class' => 'video');
      $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'general', 'class' => 'text');
      $widgets[] = array('name' => 'InfoPage', 'title' => INFORMATION_PAGES, 'description' => '', 'type' => 'general', 'class' => 'text');
      $widgets[] = array('name' => 'Reviews', 'title' => TEXT_REVIEWS, 'description' => '', 'type' => 'general', 'class' => 'reviews');
      $widgets[] = array('name' => 'Menu', 'title' => TEXT_MENU, 'description' => '', 'type' => 'general', 'class' => 'menu');
      $widgets[] = array('name' => 'Languages', 'title' => TEXT_LANGUAGES_, 'description' => '', 'type' => 'general', 'class' => 'languages');
      $widgets[] = array('name' => 'Currencies', 'title' => TEXT_CURRENCIES, 'description' => '', 'type' => 'general', 'class' => 'currencies');
      $widgets[] = array('name' => 'Search', 'title' => TEXT_SEARCH, 'description' => '', 'type' => 'general', 'class' => 'search');
      $widgets[] = array('name' => 'Cart', 'title' => TEXT_CART, 'description' => '', 'type' => 'general', 'class' => 'cart');
      $widgets[] = array('name' => 'Breadcrumb', 'title' => TEXT_BREADCRUMB, 'description' => '', 'type' => 'general', 'class' => 'breadcrumb');
      $widgets[] = array('name' => 'Compare', 'title' => TEXT_COMPARE, 'description' => '', 'type' => 'general', 'class' => 'compare');
      //$widgets[] = array('name' => 'Address', 'title' => 'Store Address', 'description' => '', 'type' => 'general', 'class' => 'contacts');
      $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'general', 'class' => 'store-address');
      $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'general', 'class' => 'copyright');
      $widgets[] = array('name' => 'Account', 'title' => TEXT_ACCOUNT, 'description' => '', 'type' => 'general', 'class' => 'account');
      $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'general', 'class' => 'import');
      $widgets[] = array('name' => 'BlogSidebar', 'title' => 'Blog Sidebar', 'description' => '', 'type' => 'general', 'class' => 'menu');
      $widgets[] = array('name' => 'BlogContent', 'title' => 'Blog Content', 'description' => '', 'type' => 'general', 'class' => 'content');
      $widgets[] = array('name' => 'Subscribe', 'title' => 'Subscribe', 'description' => '', 'type' => 'general', 'class' => 'account');
    }

    if ($params['type'] == 'index'){
      $widgets[] = array('name' => 'title', 'title' => HOME_PAGE_WIDGETS, 'description' => '', 'type' => 'index');
      $widgets[] = array('name' => 'TopCategories', 'title' => TEXT_CATEGORIES, 'description' => '', 'type' => 'index', 'class' => 'categories');
    }
    //$widgets[] = array('name' => 'Wristband', 'title' => 'wristband', 'description' => '', 'type' => 'general');

    $type = $params['type'];
    $path = DIR_FS_CATALOG . 'lib'
      . DIRECTORY_SEPARATOR . 'backend'
      . DIRECTORY_SEPARATOR . 'design'
      . DIRECTORY_SEPARATOR . 'boxes'
      . DIRECTORY_SEPARATOR . 'include';
    if (file_exists($path)) {
      $dir = scandir($path);
      foreach ($dir as $file) {
        if (file_exists($path . DIRECTORY_SEPARATOR . $file) && is_file($path . DIRECTORY_SEPARATOR . $file)) {
          require $path . DIRECTORY_SEPARATOR . $file;
        }
      }
    }
    
    $widgets = array_merge($widgets, \common\helpers\Acl::getExtensionWidgets($params['type']));

    return json_encode($widgets);
  }


  public function actionBoxAdd()
  {
    $params = tep_db_prepare_input(Yii::$app->request->post());

    $this->actionBackupAuto($params['theme_name']);

    $sql_data_array = array(
      'theme_name' => $params['theme_name'],
      'block_name' => $params['block'],
      'widget_name' => $params['box'],
      'sort_order' => $params['order'],
    );
    tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);
    $id = tep_db_insert_id();
    
    Steps::boxAdd(array_merge($sql_data_array, ['block_id' => $id]));
    
    return json_encode($params);
  }


  public function actionBoxAddSort()
  {
    $params = tep_db_prepare_input(Yii::$app->request->post());

    $this->actionBackupAuto($params['theme_name']);

    $sql_data_array = array(
      'theme_name' => $params['theme_name'],
      'block_name' => $params['block'],
      'widget_name' => $params['box'],
      'sort_order' => $params['order'],
    );
    tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);

    $query_id = tep_db_insert_id();

    $i = 1;
    $sort_arr = array();
    $sort_arr_old = array();
    foreach ($params['id'] as $item){
      if ($item == 'new'){
        $id = $query_id;
      } else {
        $id = substr($item, 4);
      }
      $sql_data_array2 = array(
        'sort_order' => $i,
      );
      $sort_arr[$id] = $i;
      $i++;
      
      $query = tep_db_fetch_array(tep_db_query("select sort_order from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'"));
      $sort_arr_old[$id] = $query['sort_order'];
      
      tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array2, 'update', "id = '" . (int)$id . "'");
    }
    Steps::boxAdd(array_merge($sql_data_array, ['block_id' => $query_id, 'sort_arr' => $sort_arr, 'sort_arr_old' => $sort_arr_old]));
    
    return json_encode($params['order']);
  }


  public function actionEditableList()
  {

    $dir = scandir(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes');
    $widgets = array();
    foreach($dir as $item){
      if (substr($item, -4) == '.php'){
        $widgets[] = substr($item, 0, -4);
      }
    }

    return json_encode($widgets);
  }


  public function actionAddPage()
  {
    \common\helpers\Translation::init('admin/design');
    $params = Yii::$app->request->get();
    
    $this->layout = 'popup.tpl';
    return $this->render('add-page.tpl', [
      'theme_name' => $params['theme_name'],
      'action' => Yii::$app->urlManager->createUrl('design/add-page-action')
    ]);
  }

  public function actionAddPageAction()
  {
    \common\helpers\Translation::init('admin/design');
    $params = Yii::$app->request->get();
    
    $theme_name = tep_db_prepare_input($params['theme_name']);
    $page_name = tep_db_prepare_input($params['page_name']);
    $page_type = tep_db_prepare_input($params['page_type']);

    if ($theme_name) {
      if ($page_name) {

        $count = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page' and setting_name = '" . tep_db_input($page_type) . "' and setting_value = '" . tep_db_input($page_name) . "'"));
        if ($count['total'] == 0) {

          $sql_data_array = array(
            'theme_name' => $theme_name,
            'setting_group' => 'added_page',
            'setting_name' => $page_type,
            'setting_value' => $page_name
          );
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);

          Steps::addPage( array_merge(['id' => tep_db_insert_id()], $sql_data_array));

          return json_encode(['code' => 2, 'text' => PAGE_ADDED]);
        } else {
          return json_encode(['code' => 1, 'text' => THIS_PAGE_ALREADY_EXIST]);
        }
      } else {
        return json_encode(['code' => 1, 'text' => ENTER_PAGE_NAME]);
      }
    } else {
      return json_encode(['code' => 1, 'text' => THEME_UNKNOWN]);
    }
  }

  public function actionAddPageSettings()
  {
    \common\helpers\Translation::init('admin/design');
    $get = Yii::$app->request->get();
    
    $page_type = tep_db_fetch_array(tep_db_query("select setting_name from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_group = 'added_page' and setting_value = '" . tep_db_input($get['page_name']) . "'"));

    $query = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($get['page_name']) . "'");

    $added_page_settings = array();
    while ($item = tep_db_fetch_array($query)){
      $added_page_settings[$item['setting_value']] = true;
    }

    $this->layout = 'popup.tpl';
    return $this->render('add-page-settings.tpl', [
      'theme_name' => $get['theme_name'],
      'page_name' => $get['page_name'],
      'page_type' => $page_type['setting_name'],
      'added_page_settings' => $added_page_settings,
      'action' => Yii::$app->urlManager->createUrl('design/add-page-settings-action')
    ]);
  }

  public function actionAddPageSettingsAction()
  {
    \common\helpers\Translation::init('admin/design');
    $post = Yii::$app->request->post();

    $theme_name = tep_db_prepare_input($post['theme_name']);
    $page_name = tep_db_prepare_input($post['page_name']);

    $settings_old = array();
    $settings = array();
    $query_settings = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "'");
    while ($item = tep_db_fetch_array($query_settings)){
      $settings_old[] = $item;
    }
    
    foreach ($post['added_page_settings'] as $setting => $key){

      $count = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "' and setting_value = '" . tep_db_input($setting) . "'"));
      if ($key && $count['total'] == 0) {
        $sql_data_array = array(
          'theme_name' => $theme_name,
          'setting_group' => 'added_page_settings',
          'setting_name' => $page_name,
          'setting_value' => $setting
        );
        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
      } elseif (!$key && $count['total'] > 0) {

        tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "' and setting_value = '" . tep_db_input($setting) . "'");
      }

      //if ($key) $settings[] = $setting;
      //if ($count['total']) $settings_old[] = $setting;
    }

    $query_settings = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "'");
    while ($item = tep_db_fetch_array($query_settings)){
      $settings[] = $item;
    }
    
    Steps::addPageSettings([
      'theme_name' => $theme_name,
      'page_name' => $page_name,
      'settings_old' => $settings_old,
      'settings' => $settings
    ]);
    
    return json_encode(['code' => 1, 'text' => '']);
  }

  public function actionBoxEdit()
  {
    \common\helpers\Translation::init('admin/design');
    $params = tep_db_prepare_input(Yii::$app->request->get());
    $id = substr($params['id'], 4);

    $settings = array();
    $items_query = tep_db_query("select id, widget_name, widget_params, theme_name from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'");
    $widget_params = '';
    if ($item = tep_db_fetch_array($items_query)) {
      $widget_params = $item['widget_params'];

      $media_query = array();
      $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($item['theme_name']) . "' and setting_name = 'media_query'");
      while ($item1 = tep_db_fetch_array($media_query_arr)){
        $media_query[] = $item1;
      }
      $settings['media_query'] = $media_query;
    }



    $visibility = array();
    $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "'");
    while ($set = tep_db_fetch_array($settings_query)) {
      if ($set['visibility'] == 0){
        $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
      } else {
        $visibility[$set['language_id']][$set['visibility']][$set['setting_name']] = $set['setting_value'];
      }
    }

    $font_added = array();
    $font_added_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($item['theme_name']) . "' and setting_name = 'font_added'");
    while ($item1 = tep_db_fetch_array($font_added_arr)){
      preg_match('/font-family:[ \'"]+([^\'^"^;^}]+)/', $item1['setting_value'], $val);
      $font_added[] = $val[1];
    }
    $settings['font_added'] = $font_added;
    $settings['theme_name'] = $item['theme_name'];


    if (is_file(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $params['name']) . '.php')){
      $widget_name = 'backend\design\boxes\\' .str_replace('\\\\', '\\', $params['name']);
      return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility]);
    } elseif($ext = \common\helpers\Acl::checkExtension($params['name'], 'showSettings', true)){
        $widget_name = 'backend\design\boxes\Def';
        $settings = array_merge($settings, ['class'=> $ext, 'method' => 'showSettings']);
        return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility, 'block_type' => $params['block_type']]);
    }else {
      $widget_name = 'backend\design\boxes\Def';
      return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility, 'block_type' => $params['block_type']]);
    }
  }

  public function saveBoxSettings($id, $language, $key, $val, $visibility = 0)
  {

    if ($val) {

      $theme_name = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'"));

      if ($key == 'background_image' || $key == 'logo' || $key == 'poster'){

        $setting_value = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "' and setting_name = '" . tep_db_input($key) . "' and	language_id='" . $language . "' and visibility = '" . (int)$visibility . "'"));

        if ($val && $setting_value['setting_value'] != $val) {
          $val_tmp = Uploads::move($val, 'themes/' . $theme_name['theme_name'] . '/img');
          if ($val_tmp){
            $val = $val_tmp;
          }
        }
      }

      if (($key == 'video_upload' || $key == 'poster_upload') && $val) {
        $val_tmp = Uploads::move($val, 'themes/' . $theme_name['theme_name'] . '/img');
        if ($val_tmp){
          $val = $val_tmp;
          switch ($key){
            case 'video_upload': $key = 'video'; break;
            case 'poster_upload': $key = 'poster'; break;
          };
        }
      }

      $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "' and setting_name = '" . tep_db_input($key) . "' and	language_id='" . $language . "' and visibility = '" . (int)$visibility . "'"));
      
      if ($total['total'] == 0) {
        $sql_data_array = array(
          'box_id' => $id,
          'setting_name' => $key,
          'setting_value' => $val,
          'language_id' => $language,
          'visibility' => $visibility
        );
        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array);
      } else {
        $sql_data_array = array(
          'setting_value' => $val
        );
        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array, 'update', "box_id = '" . (int)$id . "' and 	setting_name = '" . tep_db_input($key) . "' and	language_id='" . $language . "' and visibility = '" . (int)$visibility . "'");
      }

    } else {
      $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "' and setting_name = '" . tep_db_input($key) . "' and	language_id='" . $language . "' and visibility = '" . (int)$visibility . "'"));

      if ($total['total'] > 0) {
        tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "' and 	setting_name = '" . tep_db_input($key) . "' and	language_id='" . $language . "' and visibility = '" . (int)$visibility . "'");
      }
    }
  }

  public function actionBoxSave()
  {
    $params = tep_db_prepare_input(Yii::$app->request->post());

    $p = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$params['id'] . "'"));
    $this->actionBackupAuto($p['theme_name']);

    $box_settings_old = array();
    $query = tep_db_query("select setting_name, setting_value, language_id, visibility from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$params['id'] . "'");
    while ($item = tep_db_fetch_array($query)){
      $box_settings_old[] = $item;
    }

    if ($params['setting'] || $params['visibility']) {
      for ($i=0; $i<15; $i++){
        if ($params['setting'][0]['sort_hide_' . $i]) {
          $params['setting'][0]['sort_hide_' . $i] = 0;
        } elseif (isset($params['setting'][0]['sort_hide_' . $i])) {
          $params['setting'][0]['sort_hide_' . $i] = 1;
        }
      }

      if ($params['setting'][0]['font_size_dimension'] && !$params['setting'][0]['font_size']) $params['setting'][0]['font_size_dimension'] = 0;
      
      if ($params['setting'][0]['visibility_home']) $params['setting'][0]['visibility_home'] = 0;
      else $params['setting'][0]['visibility_home'] = 1;
      if ($params['setting'][0]['visibility_product']) $params['setting'][0]['visibility_product'] = 0;
      else $params['setting'][0]['visibility_product'] = 1;
      if ($params['setting'][0]['visibility_catalog']) $params['setting'][0]['visibility_catalog'] = 0;
      else $params['setting'][0]['visibility_catalog'] = 1;
      if ($params['setting'][0]['visibility_info']) $params['setting'][0]['visibility_info'] = 0;
      else $params['setting'][0]['visibility_info'] = 1;
      if ($params['setting'][0]['visibility_cart']) $params['setting'][0]['visibility_cart'] = 0;
      else $params['setting'][0]['visibility_cart'] = 1;
      if ($params['setting'][0]['visibility_checkout']) $params['setting'][0]['visibility_checkout'] = 0;
      else $params['setting'][0]['visibility_checkout'] = 1;
      if ($params['setting'][0]['visibility_success']) $params['setting'][0]['visibility_success'] = 0;
      else $params['setting'][0]['visibility_success'] = 1;
      if ($params['setting'][0]['visibility_account']) $params['setting'][0]['visibility_account'] = 0;
      else $params['setting'][0]['visibility_account'] = 1;
      if ($params['setting'][0]['visibility_login']) $params['setting'][0]['visibility_login'] = 0;
      else $params['setting'][0]['visibility_login'] = 1;
      if ($params['setting'][0]['visibility_other']) $params['setting'][0]['visibility_other'] = 0;
      else $params['setting'][0]['visibility_other'] = 1;
      
      if ($params['setting'][0]['show_name']) $params['setting'][0]['show_name'] = 0;
      elseif (isset($params['setting'][0]['show_name'])) $params['setting'][0]['show_name'] = 1;
      if ($params['setting'][0]['show_image']) $params['setting'][0]['show_image'] = 0;
      elseif (isset($params['setting'][0]['show_image'])) $params['setting'][0]['show_image'] = 1;
      if ($params['setting'][0]['show_stock']) $params['setting'][0]['show_stock'] = 0;
      elseif (isset($params['setting'][0]['show_stock'])) $params['setting'][0]['show_stock'] = 1;
      if ($params['setting'][0]['show_description']) $params['setting'][0]['show_description'] = 0;
      elseif (isset($params['setting'][0]['show_description'])) $params['setting'][0]['show_description'] = 1;
      if ($params['setting'][0]['show_model']) $params['setting'][0]['show_model'] = 0;
      elseif (isset($params['setting'][0]['show_model'])) $params['setting'][0]['show_model'] = 1;
      if ($params['setting'][0]['show_properties']) $params['setting'][0]['show_properties'] = 0;
      elseif (isset($params['setting'][0]['show_properties'])) $params['setting'][0]['show_properties'] = 1;
      if ($params['setting'][0]['show_rating']) $params['setting'][0]['show_rating'] = 0;
      elseif (isset($params['setting'][0]['show_rating'])) $params['setting'][0]['show_rating'] = 1;
      if ($params['setting'][0]['show_rating_counts']) $params['setting'][0]['show_rating_counts'] = 0;
      elseif (isset($params['setting'][0]['show_rating_counts'])) $params['setting'][0]['show_rating_counts'] = 1;
      if ($params['setting'][0]['show_price']) $params['setting'][0]['show_price'] = 0;
      elseif (isset($params['setting'][0]['show_price'])) $params['setting'][0]['show_price'] = 1;
      if ($params['setting'][0]['show_buy_button']) $params['setting'][0]['show_buy_button'] = 0;
      elseif (isset($params['setting'][0]['show_buy_button'])) $params['setting'][0]['show_buy_button'] = 1;
      if ($params['setting'][0]['show_qty_input']) $params['setting'][0]['show_qty_input'] = 0;
      elseif (isset($params['setting'][0]['show_qty_input'])) $params['setting'][0]['show_qty_input'] = 1;
      if ($params['setting'][0]['show_view_button']) $params['setting'][0]['show_view_button'] = 0;
      elseif (isset($params['setting'][0]['show_view_button'])) $params['setting'][0]['show_view_button'] = 1;
      if ($params['setting'][0]['show_wishlist_button']) $params['setting'][0]['show_wishlist_button'] = 0;
      elseif (isset($params['setting'][0]['show_wishlist_button'])) $params['setting'][0]['show_wishlist_button'] = 1;
      if ($params['setting'][0]['show_compare']) $params['setting'][0]['show_compare'] = 0;
      elseif (isset($params['setting'][0]['show_compare'])) $params['setting'][0]['show_compare'] = 1;

      if ($params['setting'][0]['show_name_rows']) $params['setting'][0]['show_name_rows'] = 0;
      elseif (isset($params['setting'][0]['show_name_rows'])) $params['setting'][0]['show_name_rows'] = 1;
      if ($params['setting'][0]['show_image_rows']) $params['setting'][0]['show_image_rows'] = 0;
      elseif (isset($params['setting'][0]['show_image_rows'])) $params['setting'][0]['show_image_rows'] = 1;
      if ($params['setting'][0]['show_stock_rows']) $params['setting'][0]['show_stock_rows'] = 0;
      elseif (isset($params['setting'][0]['show_stock_rows'])) $params['setting'][0]['show_stock_rows'] = 1;
      if ($params['setting'][0]['show_description_rows']) $params['setting'][0]['show_description_rows'] = 0;
      elseif (isset($params['setting'][0]['show_description_rows'])) $params['setting'][0]['show_description_rows'] = 1;
      if ($params['setting'][0]['show_model_rows']) $params['setting'][0]['show_model_rows'] = 0;
      elseif (isset($params['setting'][0]['show_model_rows'])) $params['setting'][0]['show_model_rows'] = 1;
      if ($params['setting'][0]['show_properties_rows']) $params['setting'][0]['show_properties_rows'] = 0;
      elseif (isset($params['setting'][0]['show_properties_rows'])) $params['setting'][0]['show_properties_rows'] = 1;
      if ($params['setting'][0]['show_rating_rows']) $params['setting'][0]['show_rating_rows'] = 0;
      elseif (isset($params['setting'][0]['show_rating_rows'])) $params['setting'][0]['show_rating_rows'] = 1;
      if ($params['setting'][0]['show_rating_counts_rows']) $params['setting'][0]['show_rating_counts_rows'] = 0;
      elseif (isset($params['setting'][0]['show_rating_counts_rows'])) $params['setting'][0]['show_rating_counts_rows'] = 1;
      if ($params['setting'][0]['show_price_rows']) $params['setting'][0]['show_price_rows'] = 0;
      elseif (isset($params['setting'][0]['show_price_rows'])) $params['setting'][0]['show_price_rows'] = 1;
      if ($params['setting'][0]['show_buy_button_rows']) $params['setting'][0]['show_buy_button_rows'] = 0;
      elseif (isset($params['setting'][0]['show_buy_button_rows'])) $params['setting'][0]['show_buy_button_rows'] = 1;
      if ($params['setting'][0]['show_qty_input_rows']) $params['setting'][0]['show_qty_input_rows'] = 0;
      elseif (isset($params['setting'][0]['show_qty_input_rows'])) $params['setting'][0]['show_qty_input_rows'] = 1;
      if ($params['setting'][0]['show_view_button_rows']) $params['setting'][0]['show_view_button_rows'] = 0;
      elseif (isset($params['setting'][0]['show_view_button_rows'])) $params['setting'][0]['show_view_button_rows'] = 1;
      if ($params['setting'][0]['show_wishlist_button_rows']) $params['setting'][0]['show_wishlist_button_rows'] = 0;
      elseif (isset($params['setting'][0]['show_wishlist_button_rows'])) $params['setting'][0]['show_wishlist_button_rows'] = 1;
      if ($params['setting'][0]['show_compare_rows']) $params['setting'][0]['show_compare_rows'] = 0;
      elseif (isset($params['setting'][0]['show_compare_rows'])) $params['setting'][0]['show_compare_rows'] = 1;

      if ($params['setting'][0]['show_name_b2b']) $params['setting'][0]['show_name_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_name_b2b'])) $params['setting'][0]['show_name_b2b'] = 1;
      if ($params['setting'][0]['show_image_b2b']) $params['setting'][0]['show_image_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_image_b2b'])) $params['setting'][0]['show_image_b2b'] = 1;
      if ($params['setting'][0]['show_stock_b2b']) $params['setting'][0]['show_stock_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_stock_b2b'])) $params['setting'][0]['show_stock_b2b'] = 1;
      if ($params['setting'][0]['show_description_b2b']) $params['setting'][0]['show_description_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_description_b2b'])) $params['setting'][0]['show_description_b2b'] = 1;
      if ($params['setting'][0]['show_model_b2b']) $params['setting'][0]['show_model_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_model_b2b'])) $params['setting'][0]['show_model_b2b'] = 1;
      if ($params['setting'][0]['show_properties_b2b']) $params['setting'][0]['show_properties_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_properties_b2b'])) $params['setting'][0]['show_properties_b2b'] = 1;
      if ($params['setting'][0]['show_rating_b2b']) $params['setting'][0]['show_rating_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_rating_b2b'])) $params['setting'][0]['show_rating_b2b'] = 1;
      if ($params['setting'][0]['show_rating_counts_b2b']) $params['setting'][0]['show_rating_counts_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_rating_counts_b2b'])) $params['setting'][0]['show_rating_counts_b2b'] = 1;
      if ($params['setting'][0]['show_price_b2b']) $params['setting'][0]['show_price_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_price_b2b'])) $params['setting'][0]['show_price_b2b'] = 1;
      if ($params['setting'][0]['show_buy_button_b2b']) $params['setting'][0]['show_buy_button_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_buy_button_b2b'])) $params['setting'][0]['show_buy_button_b2b'] = 1;
      if ($params['setting'][0]['show_qty_input_b2b']) $params['setting'][0]['show_qty_input_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_qty_input_b2b'])) $params['setting'][0]['show_qty_input_b2b'] = 1;
      if ($params['setting'][0]['show_view_button_b2b']) $params['setting'][0]['show_view_button_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_view_button_b2b'])) $params['setting'][0]['show_view_button_b2b'] = 1;
      if ($params['setting'][0]['show_wishlist_button_b2b']) $params['setting'][0]['show_wishlist_button_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_wishlist_button_b2b'])) $params['setting'][0]['show_wishlist_button_b2b'] = 1;
      if ($params['setting'][0]['show_compare_b2b']) $params['setting'][0]['show_compare_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_compare_b2b'])) $params['setting'][0]['show_compare_b2b'] = 1;
      if ($params['setting'][0]['show_attributes_b2b']) $params['setting'][0]['show_attributes_b2b'] = 0;
      elseif (isset($params['setting'][0]['show_attributes_b2b'])) $params['setting'][0]['show_attributes_b2b'] = 1;


      foreach ($params['setting'] as $language => $set) {
        
        if (strlen($set['video_upload']) > 3) unset($set['video']);
        if (strlen($set['poster_upload']) > 3) unset($set['poster']);
        
        foreach ($set as $key => $val) {
          $this->saveBoxSettings($params['id'], $language, $key, $val);
        }
      }
      
      foreach ($params['visibility'] as $language => $set) {
        foreach ($set as $visibility => $set2) {
          foreach ($set2 as $key => $val) {
            $this->saveBoxSettings($params['id'], $language, $key, $val, $visibility);
          }
        }
      }
    }

    if ($params['uploads'] == '1'){
      if ($params['params'] != ''){

        $file_name = Uploads::move($params['params'], 'themes/' . $p['theme_name'] . '/img');

        $sql_data_array = array(
          'widget_params' => $file_name
        );
        tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', "id = '" . (int)$params['id'] . "'");
      }
    } else {
      $sql_data_array = array(
        'widget_params' => tep_db_prepare_input($params['params'])
      );
      tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', "id = '" . (int)$params['id'] . "'");
    }

    $box_settings = array();
    $query = tep_db_query("select setting_name, setting_value, language_id, visibility from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$params['id'] . "'");
    while ($item = tep_db_fetch_array($query)){
      $box_settings[] = $item;
    }
    
    Steps::boxSave([
      'box_id' => $params['id'],
      'theme_name' => $p['theme_name'],
      'box_settings' => $box_settings,
      'box_settings_old' => $box_settings_old
    ]);

    return json_encode( '');
  }


  public function actionStyleEdit()
  {
    \common\helpers\Translation::init('admin/design');
    $params = tep_db_prepare_input(Yii::$app->request->get());
    $this->actionBackupAuto($params['theme_name']);

    $settings = array();
    $styles_query = tep_db_query("select * from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "'");
    $visibility = array();
    while ($styles_arr = tep_db_fetch_array($styles_query)){
      if (!$styles_arr['visibility']){
        $settings[0][$styles_arr['attribute']] = $styles_arr['value'];
      } else {
        $visibility[0][$styles_arr['visibility']][$styles_arr['attribute']] = $styles_arr['value'];
      }
    }
    $this->layout = 'popup.tpl';



    $media_query = array();
    $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_name = 'media_query'");
    while ($item1 = tep_db_fetch_array($media_query_arr)){
      $media_query[] = $item1;
    }
    $settings['media_query'] = $media_query;


    $font_added = array();
    $font_added_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_name = 'font_added'");
    while ($item1 = tep_db_fetch_array($font_added_arr)){
      preg_match('/font-family:[ \'"]+([^\'^"^;^}]+)/', $item1['setting_value'], $val);
      $font_added[] = $val[1];
    }
    $settings['font_added'] = $font_added;
    $settings['data_class'] = $params['data_class'];
    $settings['theme_name'] = $params['theme_name'];
    $widget_name = 'backend\design\boxes\StyleEdit';
    return $widget_name::widget(['id' => 0, 'params' => '', 'settings' => $settings, 'visibility' => $visibility, 'block_type' => '']);

    /*return $this->render('style-edit.tpl', [
      'data_class' => $params['data_class'],
      'theme_name' => $params['theme_name'],
      'settings' => $styles
    ]);*/
  }

  public function styleSave($styles, $params, $visibility = 0){

    foreach ($styles as $key => $val) {

      $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "' and attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "'"));

      if ($val !== '') {

        if ($key == 'background_image') {
          $setting_value = tep_db_fetch_array(tep_db_query("select ts.value from " . TABLE_THEMES_STYLES_TMP . " ts where ts.theme_name = '" . tep_db_input($params['theme_name']) . "' and ts.selector = '" . tep_db_input($params['data_class']) . "' and ts.attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "'"));

          if ($setting_value['value'] != $val) {
            $val_tmp = Uploads::move($val, 'themes/' . $params['theme_name'] . '/img');
            if ($val_tmp) $val = $val_tmp;
          }
        }

        if ($total['total'] == 0) {
          $sql_data_array = array(
            'theme_name' => $params['theme_name'],
            'selector' => $params['data_class'],
            'attribute' => $key,
            'value' => $val,
            'visibility' => $visibility,
          );
          tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array);
        } else {
          $sql_data_array = array(
            'value' => $val,
          );
          tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array, 'update', "theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "' and attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "'");
        }

      } else {
        if ($total['total'] > 0) {
          tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "' and attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "'");
        }
      }
    }
  }

  public function actionStyleSave()
  {
    $params = tep_db_prepare_input(Yii::$app->request->post());

    $query = tep_db_query("select * from " . TABLE_THEMES_STYLES_TMP . " where selector='" . tep_db_input($params['data_class']) . "' and theme_name='" . tep_db_input($params['theme_name']) . "'");
    $styles_old = [];
    while($item = tep_db_fetch_array($query)){
      $styles_old[] = $item;
    }

    foreach ($params['visibility'][0] as $key => $item){
      $this->styleSave($item, $params, $key);
    }
    $this->styleSave($params['setting'][0], $params);

    $query = tep_db_query("select * from " . TABLE_THEMES_STYLES_TMP . " where selector='" . tep_db_input($params['data_class']) . "' and theme_name='" . tep_db_input($params['theme_name']) . "'");
    $styles = [];
    while($item = tep_db_fetch_array($query)){
      $styles[] = $item;
    }

    $data = [
      'theme_name' => $params['theme_name'],
      'styles_old' => $styles_old,
      'styles' => $styles,
    ];
    Steps::styleSave($data);

    return '';
  }

  public function actionBackups()
  {
    \common\helpers\Translation::init('admin/design');
    $params = tep_db_prepare_input(Yii::$app->request->get());
    
    $query = tep_db_fetch_array(tep_db_query("select title from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'"));

    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => TEXT_BACKUPS . ' "' . $query['title'] . '"');

    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design/backup-add', 'theme_name' => $params['theme_name']]) . '" class="create_item">' . NEW_NEW_BACKUP . '</a>';

    $this->view->headingTitle = TEXT_BACKUPS;

    return $this->render('backups.tpl', [
      'menu' => 'backups',
      'theme_name' => $params['theme_name'],
    ]);
  }
  public function actionBackupsList ()
  {

    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);
    $theme_name = tep_db_prepare_input(Yii::$app->request->get('theme_name', 10));

    if ($length == -1)
      $length = 10000;

    $current_page_number = ($start / $length) + 1;
    $responseList = [];

    if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
      switch ($_GET['order'][0]['column']) {
        case 0:
          $orderBy = "date_added " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
          break;
        case 1:
          $orderBy = "comments " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
          break;
        default:
          $orderBy = "date_added";
          break;
      }
    } else {
      $orderBy = "date_added";
    }

    $orders_status_query_raw = "select * from " . TABLE_DESIGN_BACKUPS . " where theme_name = '" . tep_db_input($theme_name) . "' order by " . $orderBy . " limit " . (int)$_GET['start'] . ", " . (int)$_GET['length'];
    $count = tep_db_num_rows(tep_db_query("select * from " . TABLE_DESIGN_BACKUPS . " where theme_name = '" . tep_db_input($theme_name) . "' order by " . $orderBy));
    //$orders_status_split = new \splitPageResults($current_page_number, $length, $orders_status_query_raw, $query_numrows);
    $orders_status_query = tep_db_query($orders_status_query_raw);

    $query_numrows = 0;
    while ($orders_status = tep_db_fetch_array($orders_status_query)) {

      $short_desc = $orders_status['comments'];
      $short_desc = preg_replace("/<.*?>/", " ", $short_desc);
      if (strlen($short_desc) > 128) {
        $short_desc = substr($short_desc, 0, 122) . '...';
      }

      $responseList[] = array(
        \common\helpers\Date::date_long($orders_status['date_added'], "%d %b %Y / %H:%M:%S"),
        $short_desc . '<input type="hidden" class="backup_id" name="backup_id" value="' . $orders_status['backup_id'] . '">',
      );
      $query_numrows++;
    }

    $response = [
      'draw' => $draw,
      'recordsTotal' => $count,
      'recordsFiltered' => $count,
      'data' => $responseList
    ];
    echo json_encode($response);
  }

  public function actionBackupsActions() {

    $this->layout = false;

    $backup_id = intval(Yii::$app->request->post('backup_id'));
    if (!empty($backup_id)) {
      $query = tep_db_fetch_array(tep_db_query("select comments from " . TABLE_DESIGN_BACKUPS . " where backup_id = '" . $backup_id . "'"));

      echo '<br><div style="font-size: 12px">';
      echo str_replace("\n", '<br>', $query['comments']);
      echo '</div>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo '<button class="btn btn-no-margin" onclick="backupRestore(\'' . $backup_id . '\')">' . IMAGE_RESTORE . '</button>';
      echo '<button class="btn btn-delete" onclick="translateDelete(\'' . $backup_id . '\')">' . IMAGE_DELETE . '</button>';
      echo '</div>';
    }
  }
  public function actionBackupAdd() {
    \common\helpers\Translation::init('admin/design');

    $params = Yii::$app->request->get();

    $this->layout = false;
    return $this->render('add.tpl', [
      'theme_name' => $params['theme_name'],
    ]);
  }

  public function actionElementsCopy() {

    $params = tep_db_prepare_input(Yii::$app->request->post());


    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'theme_name' => $params['theme_new'],
        'block_name' => $item['block_name'],
        'widget_name' => $item['widget_name'],
        'widget_params' => $item['widget_params'],
        'sort_order' => $item['sort_order'],
      );
      tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array);
      
      $new_row = tep_db_fetch_array(tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where
            theme_name = '" . tep_db_input($params['theme_new']) . "' and
            block_name = '" . tep_db_input($item['block_name']) . "' and
            widget_name = '" . tep_db_input($item['widget_name']) . "' and
            sort_order = '" . tep_db_input($item['sort_order']) . "'
            "));

      $query2 = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
      while ($item2 = tep_db_fetch_array($query2)){
        $sql_data_array = array(
          'box_id' => $new_row['id'],
          'setting_name' => $item2['setting_name'],
          'setting_value' => $item2['setting_value'],
          'language_id' => $item2['language_id'],
          'visibility' => $item2['visibility'],
        );
        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sql_data_array);
      }
    }

    return json_encode(array());
  }

  public function actionBackupAuto($theme_name){

    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and 	setting_group = 'hide' and setting_name = 'backup_date'"));

    if (!$query['setting_value'] || \common\helpers\Date::date_long($query['setting_value'], '%Y%m%d') != date("Ymd")){
      $sql_data_array = array(
        'theme_name' => $theme_name,
        'setting_group' => 'hide',
        'setting_name' => 'backup_date',
        'setting_value' => 'now()',
      );
      if ($query['setting_value']){
        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', "theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'hide' and setting_name = 'backup_date'");
      } else {
        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
      }

      $this->actionBackupSubmit($theme_name, 'Auto saved');
    }

  }

  public function actionBackupSubmit($theme_name = '', $comments = '') {

    $params = tep_db_prepare_input(Yii::$app->request->post());

    if (!$params['theme_name']) $params['theme_name'] = $theme_name;

    $sql_data_array = array(
      'date_added' => 'now()',
      'theme_name' => $params['theme_name'],
      'comments' => ($params['comments'] ? $params['comments'] : $comments),
    );
    tep_db_perform(TABLE_DESIGN_BACKUPS, $sql_data_array);

    $backup_id = tep_db_insert_id();
    //$query = tep_db_fetch_array(tep_db_query("select backup_id from " . TABLE_DESIGN_BACKUPS . " order by date_added desc limit 1"));
    //$backup_id = $query['backup_id'];
    
    Steps::backupSubmit([
      'theme_name' => $params['theme_name'],
      'backup_id' => $backup_id,
      'comments' => ($params['comments'] ? $params['comments'] : $comments)
    ]);

    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'backup_id' => $backup_id,
        'box_id' => $item['id'],
        'block_name' => $item['block_name'],
        'widget_name' => $item['widget_name'],
        'widget_params' => $item['widget_params'],
        'sort_order' => $item['sort_order'],
      );
      tep_db_perform(TABLE_DESIGN_BOXES_BACKUPS, $sql_data_array);

      $query2 = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
      while ($item2 = tep_db_fetch_array($query2)){
        $sql_data_array = array(
          'backup_id' => $backup_id,
          'box_id' => $item2['box_id'],
          'setting_name' => $item2['setting_name'],
          'setting_value' => $item2['setting_value'],
          'language_id' => $item2['language_id'],
          'visibility' => $item2['visibility'],
        );
        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_BACKUPS, $sql_data_array);
      }
    }

    $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'backup_id' => $backup_id,
        'setting_group' => $item['setting_group'],
        'setting_name' => $item['setting_name'],
        'setting_value' => $item['setting_value'],
      );
      tep_db_perform(TABLE_THEMES_SETTINGS_BACKUPS, $sql_data_array);
    }

    $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'backup_id' => $backup_id,
        'selector' => $item['selector'],
        'attribute' => $item['attribute'],
        'value' => $item['value'],
        'visibility' => $item['visibility'],
      );
      tep_db_perform(TABLE_THEMES_STYLES_BACKUPS, $sql_data_array);
    }

    return json_encode($sql_data_array);
  }
  
  public static function blocksTree($id, $images = false) {
    $arr = array();

    $query = tep_db_fetch_array(tep_db_query("select widget_name, widget_params, sort_order, block_name from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'"));

    $arr['block_name'] = $query['block_name'];
    $arr['widget_name'] = $query['widget_name'];
    $arr['widget_params'] = $query['widget_params'];
    $arr['sort_order'] = $query['sort_order'];

    $query2 = tep_db_query("
select dbs.setting_name, dbs.setting_value, ts.setting_value as visibility, l.code
from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " dbs left join " . TABLE_THEMES_SETTINGS . " ts on dbs.visibility = ts.id left join " . TABLE_LANGUAGES . " l on dbs.language_id = l.languages_id 
where dbs.box_id = '" . (int)$id . "'
");
    while ($item2 = tep_db_fetch_array($query2)){
      if ($images){
        $item2['setting_value'] = Uploads::addArchiveImages($item2['setting_name'], $item2['setting_value']);
      }
      $arr['settings'][] = array(
        'setting_name' => $item2['setting_name'],
        'setting_value' => $item2['setting_value'],
        'language_id' => ($item2['code'] ? $item2['code'] : 0),
        'visibility' => ($item2['visibility'] ? $item2['visibility'] : 0)
      );
    }

    if ($query['widget_name'] == 'BlockBox' || $query['widget_name'] == 'email\BlockBox' || $query['widget_name'] == 'invoice\Container'){

      $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "'");
      if (tep_db_num_rows($query) > 0){
        while ($item = tep_db_fetch_array($query)){
          $arr['sub_1'][] = self::blocksTree($item['id'], $images);
        }
      }
      $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-2'");
      if (tep_db_num_rows($query) > 0){
        while ($item = tep_db_fetch_array($query)){
          $arr['sub_2'][] = self::blocksTree($item['id'], $images);
        }
      }
      $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-3'");
      if (tep_db_num_rows($query) > 0){
        while ($item = tep_db_fetch_array($query)){
          $arr['sub_3'][] = self::blocksTree($item['id'], $images);
        }
      }
      $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-4'");
      if (tep_db_num_rows($query) > 0){
        while ($item = tep_db_fetch_array($query)){
          $arr['sub_4'][] = self::blocksTree($item['id'], $images);
        }
      }
      $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-5'");
      if (tep_db_num_rows($query) > 0){
        while ($item = tep_db_fetch_array($query)){
          $arr['sub_5'][] = self::blocksTree($item['id'], $images);
        }
      }
    } elseif ($query['widget_name'] == 'Tabs'){

      for($i = 1; $i < 11; $i++) {
        $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-" . $i . "'");
        if (tep_db_num_rows($query) > 0) {
          while ($item = tep_db_fetch_array($query)) {
            $arr['sub_' . $i][] = self::blocksTree($item['id'], $images);
          }
        }
      }
    }

    return $arr;
  }

  public function actionExport() {

    $params = tep_db_prepare_input(Yii::$app->request->get());

    $theme = array();

    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and block_name not like 'block-%'");
    while ($item = tep_db_fetch_array($query)){
      $theme['blocks'][] = self::blocksTree($item['id'], true);
    }

    $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      if ($item['setting_group'] == 'css' && $item['setting_name'] == 'css'){

        preg_match_all("/url\([\'\"]{0,1}([^\)\'\"]+)/", $item['setting_value'], $out, PREG_PATTERN_ORDER);

        $css_img_arr = array();
        foreach ($out[1] as $img){
          if (substr($img, 0, 2) != '//' && substr($img, 0, 4) != 'http'){
            if (!$css_img_arr[$img]){
              $css_img_arr[$img] = Uploads::addArchiveImages('background_image', $img);
            }
          }
        }
        foreach ($css_img_arr as $path => $img){
          $item['setting_value'] = str_replace($path, $img, $item['setting_value']);
        }
      }
      $theme['settings'][] = array(
        'setting_group' => $item['setting_group'],
        'setting_name' => $item['setting_name'],
        'setting_value' => $item['setting_value'],
      );
    }

    $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
    while ($item = tep_db_fetch_array($query)){
      $item['value'] = Uploads::addArchiveImages($item['attribute'], $item['value']);
      $theme['styles'][] = array(
        'selector' => $item['selector'],
        'attribute' => $item['attribute'],
        'value' => $item['value'],
        'visibility' => $item['visibility'],
      );
    }


    $tmp_path = \Yii::getAlias('@webroot');
    $img_path = $tmp_path;
    $tmp_path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    $img_path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    $backup_file = $params['theme_name'];

    $zip = new \ZipArchive();
    if ($zip->open($tmp_path . $backup_file . '.zip', \ZipArchive::CREATE) === TRUE) {

      $zip->addFromString ('theme-tree.json', json_encode($theme));

      foreach (Uploads::$archiveImages as $item){
        if (is_file($img_path . $item['old'])){
          $zip->addFile($img_path . $item['old'], $item['new']);
        }
      }

      $zip->close();
      $backup_file .= '.zip';

      header('Cache-Control: none');
      header('Pragma: none');
      header('Content-type: application/x-octet-stream');
      header('Content-disposition: attachment; filename=' . $backup_file);

      readfile($tmp_path . $backup_file);
      unlink($tmp_path . $backup_file);

    } else {

      header('Content-Type: application/json');
      header("Content-Transfer-Encoding: utf-8");
      header('Content-disposition: attachment; filename="' . $params['theme_name'] . '.json"');
      return json_encode($theme);
    }




  }

  public function actionExportBlock() {

    $params = Yii::$app->request->get();
    $id = intval(substr($params['id'], 4));
    if ($id) {

      $query = tep_db_fetch_array(tep_db_query("select widget_name, theme_name from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . $id . "'"));
      $file_name = $query['theme_name'] . '_' . $query['widget_name'] . '_' . $id;

      header('Content-Type: application/json');
      header("Content-Transfer-Encoding: utf-8");
      header('Content-disposition: attachment; filename="' . $file_name . '.json"');
      return json_encode(self::blocksTree($id));
    }
    return '';
  }

  public function blocksTreeImport($arr, $theme_name, $block_name = '', $sort_order = ''){

    $sql_data_array = array(
      'theme_name' => $theme_name,
      'block_name' => ($block_name ? $block_name : $arr['block_name']),
      'widget_name' => $arr['widget_name'],
      'widget_params' => $arr['widget_params'],
      'sort_order' => ($sort_order ? $sort_order : $arr['sort_order']),
    );
    tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);
    $box_id = tep_db_insert_id();

    if (is_array($arr['settings']) && count($arr['settings']))
    foreach ($arr['settings'] as $item){
      $language_id = 0;
      $key = true;
      if ($item['language_id']){
        $lan_query = tep_db_fetch_array(tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($item['language_id']) . "'"));
        if ($lan_query['languages_id']) {
          $language_id = $lan_query['languages_id'];
        } else {
          $key = false;
        }
      }
      $visibility = 0;
      if ($item['visibility']){
        $vis_query = tep_db_fetch_array(tep_db_query("select id from " . TABLE_THEMES_SETTINGS . " where setting_value = '" . tep_db_input($item['visibility']) . "' and setting_name = 'media_query'"));
        if ($vis_query['id']) {
          $visibility = $vis_query['id'];
        } else {
          $key = false;
        }
      }
      if ($key) {
        if (substr($item['setting_value'], 0, 2) == '$$'){
          $item['setting_value'] = 'themes/' . $theme_name . '/img/' . substr_replace( $item['setting_value'], '', 0, 2);
        }
        $sql_data_array = array(
          'box_id' => $box_id,
          'setting_name' => $item['setting_name'],
          'setting_value' => $item['setting_value'],
          'language_id' => $language_id,
          'visibility' => $visibility,
        );
        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array);
      }
    }

    if ($arr['widget_name'] == 'BlockBox' || $arr['widget_name'] == 'email\BlockBox' || $arr['widget_name'] == 'invoice\Container'){

      if (is_array($arr['sub_1']) && count($arr['sub_1']) > 0){
        foreach ($arr['sub_1'] as $item){
          $this->blocksTreeImport($item, $theme_name, 'block-' . $box_id);
        }
      }
      if (is_array($arr['sub_2']) && count($arr['sub_2']) > 0){
        foreach ($arr['sub_2'] as $item){
          $this->blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-2');
        }
      }
      if (is_array($arr['sub_3']) && count($arr['sub_3']) > 0){
        foreach ($arr['sub_3'] as $item){
          $this->blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-3');
        }
      }
      if (is_array($arr['sub_4']) && count($arr['sub_4']) > 0){
        foreach ($arr['sub_4'] as $item){
          $this->blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-4');
        }
      }
      if (is_array($arr['sub_5']) && count($arr['sub_5']) > 0){
        foreach ($arr['sub_5'] as $item){
          $this->blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-5');
        }
      }
    } elseif ($arr['widget_name'] == 'Tabs'){

      for($i = 1; $i < 11; $i++) {
        if (is_array($arr['sub_' . $i]) && count($arr['sub_1']) > 0){
          foreach ($arr['sub_' . $i] as $item){
            $this->blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-' . $i);
          }
        }
      }
    }
    
    return $box_id;
  }

  public function actionImport() {
    $params = Yii::$app->request->get();
    if ($_FILES['file']['error'] == UPLOAD_ERR_OK  && is_uploaded_file($_FILES['file']['tmp_name'])) {

      $zip = new \ZipArchive();

      $path = \Yii::getAlias('@webroot');
      $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
      $path .= 'themes' . DIRECTORY_SEPARATOR . $params['theme_name'] . DIRECTORY_SEPARATOR;
      if (substr($_FILES['file']['name'], -4) == '.zip' && $zip->open($_FILES['file']['tmp_name'], \ZipArchive::CREATE) === TRUE){
        if (!file_exists($path)){
          mkdir($path);
        }
        $path .= 'img' . DIRECTORY_SEPARATOR;
        if (!file_exists($path)){
          mkdir($path);
        }
        $zip->extractTo ($path);
        $arr = json_decode(file_get_contents($path . 'theme-tree.json'), true);
      } else {
        $arr = json_decode(file_get_contents($_FILES['file']['tmp_name']), true);
      }
      if (is_array($arr) && $params['theme_name']){

        $boxes_sql = tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
        while ($item = tep_db_fetch_array($boxes_sql)){
          tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where id = '" . $item['id'] . "'");
          tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . $item['id'] . "'");
        }
        $boxes_sql1 = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
        while ($item = tep_db_fetch_array($boxes_sql1)){
          tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . $item['id'] . "'");
          tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . $item['id'] . "'");
        }
        tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where 	theme_name = '" . tep_db_input($params['theme_name']) . "'");
        tep_db_query("delete from " . TABLE_THEMES_STYLES . " where 	theme_name = '" . tep_db_input($params['theme_name']) . "'");
        tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where 	theme_name = '" . tep_db_input($params['theme_name']) . "'");

        foreach ($arr['settings'] as $item){
          if ($item['setting_group'] == 'css' && $item['setting_name'] == 'css'){
            $item['setting_value'] = str_replace("$$", 'themes/' . $params['theme_name'] . '/img/', $item['setting_value']);
          }
          $sql_data_array = array(
            'theme_name' => $params['theme_name'],
            'setting_group' => $item['setting_group'],
            'setting_name' => $item['setting_name'],
            'setting_value' => $item['setting_value'],
          );
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
        }

        foreach ($arr['blocks'] as $item){
          $this->blocksTreeImport($item, $params['theme_name']);
        }

        foreach ($arr['styles'] as $item){
          if (substr($item['value'], 0, 2) == '$$'){
            $item['value'] = 'themes/' . $params['theme_name'] . '/img/' . substr_replace( $item['value'], '', 0, 2);
          }
          $sql_data_array = array(
            'theme_name' => $params['theme_name'],
            'selector' => $item['selector'],
            'attribute' => $item['attribute'],
            'value' => $item['value'],
            'visibility' => $item['visibility'],
          );
          tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
          tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array);
        }
        $this->actionThemeSave();
        $this->actionElementsSave();

        return '1';
      }
    }
    return '';
  }

  public function actionImportBlock() {
    $params = Yii::$app->request->get();
    if ($_FILES['file']['error'] == UPLOAD_ERR_OK  && is_uploaded_file($_FILES['file']['tmp_name'])) {
      $arr = json_decode(file_get_contents($_FILES['file']['tmp_name']), true);
      if (is_array($arr)){

        $box_id = substr($params['box_id'], 4);
        $query = tep_db_fetch_array(tep_db_query("select sort_order from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$box_id . "'"));

        $box_id_new = $this->blocksTreeImport($arr, $params['theme_name'], $params['block_name'], $query['sort_order']);

        tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$box_id . "'");
        tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$box_id . "'");
        
        $data = [
          'box_id_old' => $box_id,
          'box_id' => $box_id_new,
          'theme_name' => $params['theme_name'],
        ];
        Steps::importBlock($data);

        return '1';
      }
    }
    return '';
  }
  
  public static function backupRestore($backup_id, $theme_name){

    $boxes_sql = tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "'");
    while ($item = tep_db_fetch_array($boxes_sql)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where id = '" . $item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . $item['id'] . "'");
    }

    $boxes_sql1 = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($theme_name) . "'");
    while ($item = tep_db_fetch_array($boxes_sql1)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . $item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . $item['id'] . "'");
    }

    tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where 	theme_name = '" . tep_db_input($theme_name) . "'");
    tep_db_query("delete from " . TABLE_THEMES_STYLES . " where 	theme_name = '" . tep_db_input($theme_name) . "'");
    tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where 	theme_name = '" . tep_db_input($theme_name) . "'");

    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_BACKUPS . " where backup_id = '" . (int)$backup_id . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'id' => $item['box_id'],
        'theme_name' => $theme_name,
        'block_name' => $item['block_name'],
        'widget_name' => $item['widget_name'],
        'widget_params' => $item['widget_params'],
        'sort_order' => $item['sort_order'],
      );
      tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array);
      tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);
    }

    $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS_BACKUPS . " where backup_id = '" . (int)$backup_id . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'box_id' => $item['box_id'],
        'setting_name' => $item['setting_name'],
        'setting_value' => $item['setting_value'],
        'language_id' => $item['language_id'],
        'visibility' => $item['visibility'],
      );
      tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sql_data_array);
      tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array);
    }

    $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS_BACKUPS . " where backup_id = '" . (int)$backup_id . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'theme_name' => $theme_name,
        'setting_group' => $item['setting_group'],
        'setting_name' => $item['setting_name'],
        'setting_value' => $item['setting_value'],
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
    }

    $query = tep_db_query("select * from " . TABLE_THEMES_STYLES_BACKUPS . " where backup_id = '" . (int)$backup_id . "'");
    while ($item = tep_db_fetch_array($query)){
      $sql_data_array = array(
        'theme_name' => $theme_name,
        'selector' => $item['selector'],
        'attribute' => $item['attribute'],
        'value' => $item['value'],
        'visibility' => $item['visibility'],
      );
      tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
      tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array);
    }
    
  }

  public function actionBackupRestore() {

    $params = Yii::$app->request->post();

    $query = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_DESIGN_BACKUPS . " where backup_id = '" . (int)$params['backup_id'] . "' limit 1"));
    
    self::backupRestore($params['backup_id'], $query['theme_name']);
    
    Steps::backupRestore([
      'theme_name' => $query['theme_name'],
      'backup_id' => $params['backup_id']
    ]);
  }

  public function actionBackupDelete() {
    $params = Yii::$app->request->post();

    tep_db_query("delete from " . TABLE_DESIGN_BACKUPS . " where backup_id = '" . (int)$params['backup_id'] . "'");
    tep_db_query("delete from " . TABLE_DESIGN_BOXES_BACKUPS . " where backup_id = '" . (int)$params['backup_id'] . "'");
    tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_BACKUPS . " where backup_id = '" . (int)$params['backup_id'] . "'");
    tep_db_query("delete from " . TABLE_THEMES_SETTINGS_BACKUPS . " where 	backup_id = '" . (int)$params['backup_id'] . "'");
    tep_db_query("delete from " . TABLE_THEMES_STYLES_BACKUPS . " where 	backup_id = '" . (int)$params['backup_id'] . "'");
  }

  public function actionGallery() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    $htm = '';
    if ($get['theme_name']){
      $files2 = scandir(DIR_FS_CATALOG . 'themes/' . $get['theme_name'] . '/img');
      foreach ($files2 as $item){
        $s = strtolower(substr($item, -3));
        if (!$get['type'] && ($s == 'gif' || $s == 'png' || $s == 'jpg' || $s == 'peg')){
          $htm .= '<div class="item item-themes"><div class="image"><img src="' . DIR_WS_CATALOG . 'themes/' . $get['theme_name'] . '/img/' . $item . '" title="' . $item . '" alt="' . $item . '"></div><div class="name" data-path="themes/' . $get['theme_name'] . '/img/">' . $item . '</div></div>';
        } elseif ($get['type'] == 'video' && ($s == 'mp4' || $s == 'mov')){
          $htm .= '<div class="item item-themes"><div class="image" style="height: 0; overflow: hidden"><img src="' . DIR_WS_CATALOG . 'themes/' . $get['theme_name'] . '/img/' . $item . '"></div><div class="name" style="white-space: normal" data-path="themes/' . $get['theme_name'] . '/img/">' . $item . '</div></div>';
        }
      }
    }
    $files = scandir(DIR_FS_CATALOG . 'images');
    foreach ($files as $item){
      $s = strtolower(substr($item, -3));
      if (!$get['type'] && ($s == 'gif' || $s == 'png' || $s == 'jpg' || $s == 'peg')){
        $htm .= '<div class="item item-general"><div class="image"><img src="' . DIR_WS_CATALOG . 'images/' . $item . '" title="' . $item . '" alt="' . $item . '"></div><div class="name" data-path="images/">' . $item . '</div></div>';
      } elseif ($get['type'] == 'video' && ($s == 'mp4' || $s == 'mov')){
        $htm .= '<div class="item item-general"><div class="image" style="height: 0; overflow: hidden"><img src="' . DIR_WS_CATALOG . 'images/' . $item . '"></div><div class="name" style="white-space: normal" data-path="images/">' . $item . '</div></div>';
      } 
    }
    return $htm;
  }

  public function actionSettings() {
    \common\helpers\Translation::init('admin/design');
    \common\helpers\Translation::init('admin/js');
    $params = tep_db_prepare_input(Yii::$app->request->get());
    $post = tep_db_prepare_input(Yii::$app->request->post(),false);

    $theme = tep_db_fetch_array(tep_db_query("select * from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'"));
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/settings'), 'title' => THEME_SETTINGS . ' "' . $theme['title'] . '"');
    $this->selectedMenu = array('design_controls', 'design/themes');
    
    $this->topButtons[] = '<span class="redo-buttons"></span>';

    if (count($post) > 0){

      foreach ($post['setting'] as $key => $val) {
        $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and attribute = '" . tep_db_input($key) . "' and visibility='0'"));
        $total2 = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and attribute = '" . tep_db_input($key) . "' and visibility='0'"));
        if ($val) {
          if ($key == 'background_image') {
            $setting_value = tep_db_fetch_array(tep_db_query("select ts.value from " . TABLE_THEMES_STYLES_TMP . " ts where ts.theme_name = '" . tep_db_input($params['theme_name']) . "' and ts.selector = 'body' and ts.attribute = '" . tep_db_input($key) . "' and visibility='0'"));

            if ($setting_value['value'] != $val) {
              $val = Uploads::move($val, 'themes/' . $params['theme_name'] . '/img');
            }
          }
          $sql_data_array = array(
            'theme_name' => $params['theme_name'],
            'selector' => 'body',
            'attribute' => $key,
            'value' => $val,
          );
          if ($total['total'] == 0) {
            tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array);
          } else {
            tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array, 'update', "theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and attribute = '" . tep_db_input($key) . "' and visibility='0'");
          }
          if ($total2['total'] == 0) {
            tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
          } else {
            tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array, 'update', "theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and attribute = '" . tep_db_input($key) . "' and visibility='0'");
          }
        } else {
          if ($total['total'] > 0) {
            tep_db_query("delete from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and attribute = '" . tep_db_input($key) . "' and visibility='0'");
            tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and attribute = '" . tep_db_input($key) . "' and visibility='0'");
          }
        }
      }
      //$this->actionThemeSave();


      $them_settings_old = [];
      $query_s = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and (setting_group = 'main' or setting_group = 'extend')");
      while ($item = tep_db_fetch_array($query_s)){
        $them_settings_old[] = $item;
      }
      /*echo '<pre>';
      var_dump($them_settings_old);
      echo '</pre>';
      echo json_encode($them_settings_old);die;*/

      foreach ($post['settings'] as $setting_name => $setting_value){

        $sql_data_array = array(
          'theme_name' => $params['theme_name'],
          'setting_group' => 'main',
          'setting_name' => $setting_name,
          'setting_value' => $setting_value,
        );

        $query = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'main' and setting_name = '" . tep_db_input($setting_name) . "'"));
        if ($query['total'] > 0){
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'main' and setting_name = '" . tep_db_input($setting_name) . "'");
        } else {
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
        }

      }


      foreach ($post['extend'] as $setting_name => $val){
        foreach ($val as $id => $setting_value){

          $sql_data_array = array(
            'setting_value' => $setting_value,
          );
          $query = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'extend' and setting_name = '" . tep_db_input($setting_name) . "' and id = '" . (int)$id . "'"));
          if ($query['total'] > 0){
            tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'extend' and setting_name = '" . tep_db_input($setting_name) . "' and id = '" . (int)$id . "'");
          }
        }
      }

      $them_settings = [];
      $query_s = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and (setting_group = 'main' or setting_group = 'extend')");
      while ($item = tep_db_fetch_array($query_s)){
        $them_settings[] = $item;
      }

      $data = [
        'theme_name' => $params['theme_name'],
        'them_settings_old' => $them_settings_old,
        'them_settings' => $them_settings,
      ];
      Steps::settings($data);
    }

    $query = tep_db_query("select setting_name, setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'main'");

    $settings = array();
    while ($item = tep_db_fetch_array($query)){
      $settings[$item['setting_name']] = $item['setting_value'];
    }

    $styles = array();
    $styles_query = tep_db_query("select * from " . TABLE_THEMES_STYLES_TMP . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and visibility='0'");
    while ($styles_arr = tep_db_fetch_array($styles_query)){
      $styles[$styles_arr['attribute']] = $styles_arr['value'];
    }

    $path = \Yii::getAlias('@webroot');
    $path .= DIRECTORY_SEPARATOR;
    $path .= '..';
    $path .= DIRECTORY_SEPARATOR;
    $path .= 'themes';
    $path .= DIRECTORY_SEPARATOR;
    $path .= $_GET['theme_name'];
    $path .= DIRECTORY_SEPARATOR;
    $path .= 'icons';
    $path .= DIRECTORY_SEPARATOR;
    if (is_file($path . 'favicon-16x16.png')){
      $favicon = '../themes/' . $_GET['theme_name'] . '/icons/favicon-16x16.png';
    } else {
      $favicon = '../themes/basic/icons/favicon-16x16.png';
    }

    return $this->render('settings.tpl', [
      'favicon' => $favicon,
      'menu' => 'settings',
      'settings' => $settings,
      'setting' => $styles,
      'theme_name' => $params['theme_name'],
      'action' => Yii::$app->urlManager->createUrl(['design/settings', 'theme_name' => $params['theme_name']]),
    ]);
  }

  public function actionExtend() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    
    if ($get['remove']){
      Steps::extendRemove(['theme_name' => $get['theme_name'], 'id' => (int)$get['remove']]);
      
      tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where id = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where visibility = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where visibility = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_THEMES_STYLES . " where visibility = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where visibility = '" . (int)$get['remove'] . "'");
    }
    
    if ($get['add']){
      $sql_data_array = array(
        'theme_name' =>$get['theme_name'],
        'setting_group' => 'extend',
        'setting_name' => $get['setting_name'],
        'setting_value' => '',
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
      $added_id = tep_db_insert_id();

      $sql_data_array['id'] = $added_id;
      Steps::extendAdd(['theme_name' => $get['theme_name'], 'data' => $sql_data_array]);
    }

    $query = tep_db_query("select id, setting_name, setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_group = 'extend' and setting_name = '" . tep_db_input($get['setting_name']) . "'");
    $arr = array();
    while ($item = tep_db_fetch_array($query)){
      $arr[] = $item;
    }
    return json_encode($arr);
  }

  public function actionFavicon() {

    if (isset($_FILES['file'])) {
      $path = \Yii::getAlias('@webroot');
      $path .= DIRECTORY_SEPARATOR;
      $path .= '..';
      $path .= DIRECTORY_SEPARATOR;
      $path .= 'themes';
      $path .= DIRECTORY_SEPARATOR;
      $path .= $_GET['theme_name'];
      $path .= DIRECTORY_SEPARATOR;
      $theme = $path;
      $path .= 'icons';
      $path .= DIRECTORY_SEPARATOR;

      //$upload_file = $path . basename($_FILES['file']['name']);

      $arr = explode(".", $_FILES['file']['name']);
      $upload_file = $path . 'uploaded.' . strtolower(end($arr));

      if (!file_exists($path)) {
        if (!file_exists($theme)) {
          mkdir($theme);
        }
        mkdir($path);
      }

      if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {

        //$mime = mime_content_type($upload_file);

        $info = getimagesize($upload_file);
        $mime = $info['mime'];

        if ($mime == 'image/jpeg'){
          $im = imagecreatefromjpeg($upload_file);
        } elseif ($mime == 'image/png'){
          $im = imagecreatefrompng($upload_file);
        } elseif ($mime == 'image/gif'){
          $im = imagecreatefromgif($upload_file);
        }
        if ($im) {
          $w = imagesx($im);
          $h = imagesy($im);

          $icons = [
            ['size' => 57, 'name' => 'apple-icon-57x57.png'],
            ['size' => 60, 'name' => 'apple-icon-60x60.png'],
            ['size' => 72, 'name' => 'apple-icon-72x72.png'],
            ['size' => 76, 'name' => 'apple-icon-76x76.png'],
            ['size' => 114, 'name' => 'apple-icon-114x114.png'],
            ['size' => 120, 'name' => 'apple-icon-120x120.png'],
            ['size' => 144, 'name' => 'apple-icon-144x144.png'],
            ['size' => 152, 'name' => 'apple-icon-152x152.png'],
            ['size' => 180, 'name' => 'apple-icon-180x180.png'],
            ['size' => 192, 'name' => 'android-icon-192x192.png'],
            ['size' => 32, 'name' => 'favicon-32x32.png'],
            ['size' => 96, 'name' => 'favicon-96x96.png'],
            ['size' => 16, 'name' => 'favicon-16x16.png'],
            ['size' => 16, 'name' => 'favicon.ico'],
            ['size' => 144, 'name' => 'ms-icon-144x144.png'],
            ['size' => 36, 'name' => 'android-icon-36x36.png'],
            ['size' => 48, 'name' => 'android-icon-48x48.png'],
            ['size' => 72, 'name' => 'android-icon-72x72.png'],
            ['size' => 96, 'name' => 'android-icon-96x96.png'],
            ['size' => 144, 'name' => 'android-icon-144x144.png'],
            ['size' => 192, 'name' => 'android-icon-192x192.png'],
          ];

          foreach ($icons as $icon){
            $l = $icon['size'];
            if ($w > $h){
              $left = 0 - (($l * ($w/$h)) - $l) / 2;
              $top = 0;
              $width = $l * ($w/$h);
              $height = $l;
            } else {
              $left = 0;
              $top = 0 - (($l * ($h/$w)) - $l) / 2;
              $width = $l;
              $height = $l * ($h/$w);
            }
            $im1 = imagecreatetruecolor($l, $l);
            imagealphablending($im1, false);
            imagesavealpha($im1, true);
            imagecopyresampled($im1, $im, $left, $top, 0, 0, $width, $height, $w, $h);
            imagepng($im1, $path . $icon['name']);
            imagedestroy($im1);
          }

          //png2wbmp($path . 'favicon-16x16.png', $path . 'favicon.ico', 16, 16, 7);

          imagedestroy($im);

          $text = '';
          $response = ['status' => 'ok', 'text' => $text];
        }
      } else {
        $response = ['status' => 'error'];
      }
    }
    echo json_encode($response);
  }


  public function actionDemoStyles() {
    $post = tep_db_prepare_input(Yii::$app->request->post());
    $class = str_replace('\\', '', $post['data_class']);
    $style = $class . '{' . \frontend\design\Block::styles($post['setting']).'}';

    $key_arr = explode(',', $class);
    for ($i = 1; $i < 5; $i++) {
      $add = '';
      switch ($i) {
        case 1: $add = ':hover'; break;
        case 2: $add = '.active'; break;
        case 3: $add = ':before'; break;
        case 4: $add = ':after'; break;
      }
      $selector_arr = array();
      foreach ($key_arr as $item) {
        $selector_arr[] = trim($item) . $add;
      }
      $selector = implode(', ', $selector_arr);
      $params[0] = $post['visibility'][0][$i];
      $style .= $selector . '{' . \frontend\design\Block::styles($params) . '}';
    }

    echo $style;
  }

  public function actionLog() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    \common\helpers\Translation::init('admin/design');
    $this->topButtons[] = '<span class="redo-buttons"></span>';

    $theme = tep_db_fetch_array(tep_db_query("select * from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($get['theme_name']) . "'"));
    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->view->headingTitle = LOG_TEXT . ' "' . $theme['title'] . '"';
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/settings'), 'title' => 'Log "' . $theme['title'] . '"');

    $admins = array();
    $query = tep_db_query("select admin_id, admin_firstname, admin_lastname, admin_email_address from " . TABLE_ADMIN . "");
    while ($item = tep_db_fetch_array($query)){
      $admins[$item['admin_id']] = $item;
    }

    return $this->render('log.tpl', [
      'tree' => Steps::log($get['theme_name']),
      'admins' => $admins,
      'theme_name' => $get['theme_name'],
      'menu' => 'log',
    ]);
  }

  public function actionUndo() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    Steps::undo($get['theme_name']);
  }

  public function actionRedo() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    Steps::redo($get['theme_name'], $get['steps_id']);
  }

  public function actionRedoButtons() {
    \common\helpers\Translation::init('admin/design');
    $get = tep_db_prepare_input(Yii::$app->request->get());

    $redo_query = tep_db_query("select sr.steps_id, sr.event, sr.date_added, sr.admin_id from " . TABLE_THEMES_STEPS . " sr left join " . TABLE_THEMES_STEPS . " sa on sr.parent_id = sa.steps_id where sa.active='1' and sr.theme_name='" . tep_db_input($get['theme_name']) . "'");
    $redo = '';
    while ($item = tep_db_fetch_array($redo_query)){
      $redo .= '<span class="btn btn-redo btn-elements" data-id="' . $item['steps_id'] . '" data-event="' . $item['event'] . '" title="' . Steps::logNames($item['event']) . ' (' . \common\helpers\Date::date_long($item['date_added'], "%d %b %Y / %H:%M:%S") . ')">' . LOG_REDO . '</span>';
    }

    $undo = tep_db_fetch_array(tep_db_query("select steps_id, event, date_added, admin_id from " . TABLE_THEMES_STEPS . " where active='1' and parent_id!='0' and theme_name='" . tep_db_input($get['theme_name']) . "'"));

    if ($undo['steps_id']) {
      $redo .= '<span class="btn btn-undo btn-elements" data-event="' . $undo['event'] . '" title="' . Steps::logNames($undo['event']) . ' (' . \common\helpers\Date::date_long($undo['date_added'], "%d %b %Y / %H:%M:%S") . ')">' . LOG_UNDO . '</span>';
    }

    echo $redo;
  }

  public  function actionStepRestore()
  {
    \common\helpers\Translation::init('admin/design');
    $get = tep_db_prepare_input(Yii::$app->request->get());
    $text = Steps::restore($get['id']);
    if ($text){
      $text = '
<div class="popup-box-wrap pop-mess">
    <div class="around-pop-up"></div>
    <div class="popup-box">
        <div class="pop-up-close pop-up-close-alert"></div>
        <div class="pop-up-content">
            <div class="popup-content pop-mess-cont pop-mess-cont-error">
                ' . $text . '
            </div> 
        </div>  
            <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary">' . TEXT_BTN_OK . '</span></div>
                </div>
    </div>  
<script>
    $(\'body\').scrollTop(0);
    $(\'.pop-mess .pop-up-close-alert, .noti-btn .btn\').click(function () {
        $(this).parents(\'.pop-mess\').remove();
    });
</script>
</div>
';
    }
    return $text;
  }
  
}
