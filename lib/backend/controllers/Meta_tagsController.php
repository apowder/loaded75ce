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
use backend\components\Information;
use \common\helpers\Translation;

/**
 * default controller to handle user requests.
 */
class Meta_tagsController extends Sceleton {
    
    public $acl = ['BOX_HEADING_SEO_CMS', 'BOX_META_TAGS'];
    
    public function __construct($id, $module=null){
      Translation::init('admin/meta-tags');
      parent::__construct($id, $module);
    }
    
	/**
	 * Index action is the default action in a controller.
	 */
	public function actionIndex()
	{
    global $languages_id, $language;
    
    $this->selectedMenu = array('seo_cms', 'meta_tags');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('meta_tags/'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;
    $tabList = array();

//    $data = array();
    $get_ex_values_q = tep_db_query("select * from ".TABLE_META_TAGS." where affiliate_id=0 and platform_id>0");
    if (tep_db_num_rows($get_ex_values_q)>0) {
      while($get_ex_values = tep_db_fetch_array($get_ex_values_q)) {
//        $key1 = $get_ex_values['meta_tags_key'];
//        $key2 = $get_ex_values['platform_id'].'_'.$get_ex_values['language_id'].'_'.$get_ex_values['affiliate_id'];
//        if ( !isset($data[$key1]) ) $data[$key1] = array();
//        $data[$key1][$key2] = $get_ex_values['meta_tags_value'];
        define($get_ex_values['meta_tags_key'].'_'.$get_ex_values['language_id'].'_'.$get_ex_values['platform_id'], $get_ex_values['meta_tags_value']);
      }
    }

    $languages = \common\helpers\Language::get_languages();
    $platforms = \common\classes\platform::getList();
    foreach( $platforms as $_idx=>$_aff ) {
      $platforms[$_idx]['name'] = $_aff['text'];
    }
    $affiliates = $platforms;

    $tabs_data = array(
      array(
        'tab_title' => CATEGORY_DEFAULT_TAGS,
        'id' => 'default_tags',
        'active' => true,
        'input_key' => array(
          'HEAD_TITLE_TAG_ALL', 'HEAD_KEY_TAG_ALL', 'HEAD_DESC_TAG_ALL',
        ),
      ),
      array(
        'tab_title' => CATEGORY_INDEX_TAGS,
        'id' => 'index_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_DEFAULT', 'HEAD_KEY_TAG_DEFAULT', 'HEAD_DESC_TAG_DEFAULT',
        ),
      ),
      array(
        'tab_title' => CATEGORY_PRODUCT_INFO_TAGS,
        'id' => 'product_info_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_PRODUCT_INFO', 'HEAD_KEY_TAG_PRODUCT_INFO', 'HEAD_DESC_TAG_PRODUCT_INFO',
        ),
      ),
      array(
        'tab_title' => CATEGORY_WHATS_NEW_TAGS,
        'id' => 'whats_new_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_WHATS_NEW', 'HEAD_KEY_TAG_WHATS_NEW', 'HEAD_DESC_TAG_WHATS_NEW',
        ),
      ),
      array(
        'tab_title' => CATEGORY_SPECIALS_TAGS,
        'id' => 'specials_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_SPECIALS', 'HEAD_KEY_TAG_SPECIALS', 'HEAD_DESC_TAG_SPECIALS',
        ),
      ),
      array(
        'tab_title' => CATEGORY_PRODUCT_REVIEWS_TAGS,
        'id' => 'product_reviews_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO',
        ),
      )
    );
    foreach( $tabs_data as $idx=>$tab_data ) {
      $tabs_data[$idx]['input_controls'] = array();
      foreach($tab_data['input_key'] as $meta_const_key){
        foreach($platforms as $platform) {
          foreach ($languages as $__language) {
            $control_value = defined($meta_const_key.'_' . $__language['id'] . '_' . $platform['id']) ? constant($meta_const_key.'_' . $__language['id'] . '_' . $platform['id']) : '';
            if (strpos($meta_const_key, 'HEAD_DESC_TAG') !== false) {
              $control = tep_draw_textarea_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . ']', 'soft', '70', '3', $control_value, 'class="form-control"');
            } else {
              $control = tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . ']', $control_value, 'class="form-control"');
            }
            if ( !isset($tabs_data[$idx]['input_controls'][$__language['id'].'_'.$platform['id']]) ) $tabs_data[$idx]['input_controls'][$__language['id'].'_'.$platform['id']] = array();
            $tabs_data[$idx]['input_controls'][$__language['id'].'_'.$platform['id']][] = array(
              'label' => (tep_not_null($c_key = \common\helpers\Translation::getTranslationValue($meta_const_key, 'metatags', $languages_id))? $c_key:(defined($meta_const_key) ? constant($meta_const_key) : '')),
              'control' => $control,
            );
          }
        }
      }
    }

    return $this->render('index', [
      'tabs_data' => $tabs_data,
      'platforms'=>\common\classes\platform::getList(),
      'first_platform_id'=>\common\classes\platform::firstId(),
      'isMultiPlatform'=>\common\classes\platform::isMulti(),
      'languages' => array_map(function($lang){
        $lang['logo'] = $lang['image'];
        return $lang;
      }, $languages),
      'update_form_action' => Yii::$app->urlManager->createUrl('meta_tags/update'),
    ]);
  }
 
  
  public function actionUpdate()
  {
        $this->layout = false;
        $result = false;
        $in_data = tep_db_prepare_input($_POST);
        foreach($in_data as $inskey=>$values) {
         if (is_array($values) && sizeof($values)>0){
           foreach($values as $lang_id=>$insvalue) {
              if (is_array($insvalue) && sizeof($insvalue) > 0){
                foreach ($insvalue as $platform_id => $value){
                  $this->update_value($inskey, $value, $lang_id, $platform_id);
                  $result = true;
                }
              }
           }
         }
        }
        if ($result){
          echo 'ok';
        }
        
  }
  
   private function update_value($inkey, $invalue, $inlang, $platform_id) {
    $inkey = $inkey;
    $invalue = strip_tags($invalue);
    $inlang = intval($inlang);
    if ($inkey != 'x' && $inkey != 'y' && $inlang>0) {
      $ch_ex = tep_db_query("select meta_tags_key from ".TABLE_META_TAGS." where meta_tags_key='".tep_db_input($inkey)."' and language_id='".$inlang."' and platform_id='".(int)$platform_id."' and affiliate_id = '0'");
      if (tep_db_num_rows($ch_ex)>0) {
        // update
        tep_db_query("update ".TABLE_META_TAGS." set meta_tags_value='".tep_db_input($invalue)."' where meta_tags_key='".tep_db_input($inkey)."' and language_id='".$inlang."' and platform_id='".(int)$platform_id."' and affiliate_id = '0'");
      } else {
        // insert
        tep_db_query("insert into ".TABLE_META_TAGS." set meta_tags_value='".tep_db_input($invalue)."', meta_tags_key='".tep_db_input($inkey)."', language_id='".$inlang."', platform_id='".(int)$platform_id."', affiliate_id = '0'");
      }
    }
  }

}
