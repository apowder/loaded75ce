<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\components;

use common\helpers\Seo;

class Information {

  public static function form($adgrafics_information, $information_id, $title){
    global $language;
    
    if (file_exists(DIR_WS_LANGUAGES . $language . '/' . 'information.php')) {
      include(DIR_WS_LANGUAGES . $language . '/' . 'information.php');
    }
    
    $dir_listing = array(array('id' => '', 'text' => TEXT_NONE));
    if ($dir = @dir(DIR_FS_CATALOG)) {
      while ($file = $dir->read()) {
        if (!is_dir($module_directory . $file)) {
          if (substr($file, strrpos($file, '.')) == '.php') {
            $dir_listing[] = array('id' => $file, 'text' => $file);
          }
        }
      }
      sort($dir_listing);
      $dir->close();
    }
    
    if(WYSIWYG_EDITOR_POPUP_INLINE=='inline')
    {
      require('includes/inline_editors.php');
    }    
    
    $tabList = $tabLang = array();
    ob_start();
  ?> 
<div class="tab-pane" id="mainTabPane">
<?php
  $page = ob_get_contents();
  ob_end_clean();
  
  $languages = \common\helpers\Language::get_languages();
  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
   ob_start();
    if ($adgrafics_information != 'Added') {
      $edit = self::read_data($information_id, $languages[$i]['id']);
    }
?>
      <div class="tab-page" id="tabDescriptionLanguages_<?php echo $languages[$i]['code']; ?>">

        <script type="text/javascript"><!--
//        mainTabPane.addTabPage( document.getElementById( "tabDescriptionLanguages_<?php echo $languages[$i]['code']; ?>" ) );
        //-->
        </script>  
        <div class="edp-line">
            <label><?php echo TITLE_PAGE_TITLE;?></label>
            <?php echo tep_draw_input_field('page_title[' . $languages[$i]['id'] . '][0]', "$edit[page_title]", 'maxlength=255 class="form-control form-control-small"'); ?>
        </div>
        <div class="edp-line">
            <label><?php echo TEXT_NAME_IN_MENU;?></label>
            <?php echo tep_draw_input_field('info_title[' . $languages[$i]['id'] . '][0]', "$edit[info_title]", 'maxlength=255 class="form-control form-control-small"'); ?>
        </div>
        <div class="edp-line">
            <label><?php echo DESCRIPTION_INFORMATION;?>:</label>
            <?php if(WYSIWYG_EDITOR_POPUP_INLINE=='popup') { ?>
            <?php echo tep_image(DIR_WS_ICONS . 'icon_edit.gif', TEXT_OPEN_WYSIWYG_EDITOR, 16, 16, 'onclick="loadedHTMLAREA(\'edit_info\',\'description[' . $languages[$i]['id'] . '][0]\');"'); ?>
            <?php } ?>
            <?php echo tep_draw_textarea_field('description[' . $languages[$i]['id'] . '][0]', '', '', '', "$edit[description]",'class="form-control ckeditor text-dox-01" id="description[' . $languages[$i]['id'] . '][0]"'); ?>
        </div>
        <div class="edp-line">
            <label><?php echo TITLE_PAGE_TYPE;?></label>
            <?php echo '<div class="edp-line-wra"><label>'.tep_draw_radio_field('page_type[' . $languages[$i]['id'] . ']', 'SSL', $edit['page_type'] == 'SSL') . '&nbsp;' . TEXT_SSL . '</label>&nbsp;&nbsp;&nbsp;<label>' . tep_draw_radio_field('page_type[' . $languages[$i]['id'] . ']', 'NONSSL', ($edit['page_type'] == 'NONSSL' || $edit['page_type'] == '')) . '&nbsp;' . TEXT_NONSSL.'</label></div>';?>
        </div>
     
        
        <table border="0" cellpadding="5" cellspacing="0">
          
          <tr>
            <td class="main" colspan="2">
          </td>
          </tr>
                    
        </table>
       </div>
<?php
    $tabLang[] = array('title' => $languages[$i]['name'],'content' =>ob_get_contents(), 'id' => $languages[$i]['code'], 'active'=> ($i==0 ? true : false));
    ob_end_clean();
  }
  ob_start();
?>       
</div>
<?php    
  $page .= ob_get_contents();
  ob_end_clean();
    
  $tabList[] = array(
      'title' => $title,
      'id' => 'mainTabPane',
      'content' => $page,
      'langtabs' => $tabLang,
      'active' => 1
  );
  return $tabList;
  }
  
  public static function browse_information ($where='1') {
    global $languages_id;
    $daftar=tep_db_query("SELECT * FROM " . TABLE_INFORMATION . " WHERE languages_id='".$languages_id."' and affiliate_id = 0 and {$where} ORDER BY v_order");
    $result = array();
    while ($buffer = tep_db_fetch_array($daftar)) {
      $result[]=$buffer;
    }
    return $result;
  }

  public static function read_data ($information_id, $language_id, $platform_id, $affiliate_id = 0) {
    $result = tep_db_fetch_array(tep_db_query("SELECT * FROM " . TABLE_INFORMATION . " WHERE information_id='".$information_id."' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'"));
    return $result;
  }

  public static function add_information($data, $language_id, $platform_id, $affiliate_id = 0) {
    global $insert_id;
    if (!tep_not_null($data['seo_page_name'][$language_id])) {
      $data['seo_page_name'][$language_id] = Seo::makeSlug($data['info_title'][$language_id][$affiliate_id]);
    }
    $query ="INSERT INTO " . TABLE_INFORMATION . " (information_id, visible, v_order, info_title, description, languages_id, page_title, page, scope, seo_page_name, old_seo_page_name, meta_description, meta_key, affiliate_id, page_type) VALUES('" . $insert_id . "', '" . $data['visible'][$language_id] . "', '" . $data['v_order'][$language_id] . "', '" . tep_db_input($data['info_title'][$language_id][$affiliate_id]) . "', '" . tep_db_input($data['description'][$language_id][$affiliate_id]) . "','" . $language_id . "', '" . tep_db_input($data['page_title'][$language_id][$affiliate_id]) . "', '" . $data['page'][$language_id] . "', '" . (is_array($data['scope'][$language_id])?implode(',', $data['scope'][$language_id]):'') . "', '" . tep_db_input($data['seo_page_name'][$language_id]) . "', '" . tep_db_input($data['old_seo_page_name'][$language_id]) . "', '" . tep_db_input($data['meta_description'][$language_id]) . "', '" . tep_db_input($data['meta_key'][$language_id]) . "', '" . $affiliate_id . "', '" . $data['page_type'][$language_id] . "')";
    tep_db_query($query);
    if ($insert_id == ''){
      $insert_id = tep_db_insert_id();
    }
  }

  public static function update_information ($data, $language_id, $platform_id, $affiliate_id = 0) {
    $info_id = $data['information_id'];

    $sql_data = array();
    foreach( array('v_order', 'meta_title', 'info_title', 'description', 'page_title', 'page', 'scope', 'seo_page_name', 'old_seo_page_name', 'meta_description', 'meta_key', 'page_type') as $field ) {
      if ( array_key_exists($field,$data) ) {
        if (isset($data[$field][$language_id][$platform_id]) && !is_array($data[$field][$language_id][$platform_id])) {
          $sql_data[$field] = $data[$field][$language_id][$platform_id];
        }else
        if (isset($data[$field][$language_id][$platform_id][$affiliate_id]) && !is_array($data[$field][$language_id][$platform_id][$affiliate_id])) {
          $sql_data[$field] = $data[$field][$language_id][$platform_id][$affiliate_id];
        }
      }
    }
    if ( isset($data['visible_per_platform']) ) {
      $sql_data['visible'] = isset($data['visible'][$platform_id])?1:0;
    }

    if ( empty($sql_data['seo_page_name']) && !empty($sql_data['info_title']) ) {
      $sql_data['seo_page_name'] = Seo::makeSlug($sql_data['info_title']);
    }

    $check = tep_db_fetch_array(tep_db_query("select count(*) as c from " . TABLE_INFORMATION . " where information_id= '" . $info_id . "' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'"));
    if ( $check['c']>0 ) {
      $sql_data['last_modified'] = 'now()';
      tep_db_perform(TABLE_INFORMATION,$sql_data,'update',"information_id= '" . $info_id . "' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'");
    }else{
      $sql_data['information_id'] = $info_id;
      $sql_data['languages_id'] = $language_id;
      $sql_data['platform_id'] = $platform_id;
      $sql_data['affiliate_id'] = $affiliate_id;
      $sql_data['date_added'] = 'now()';
      tep_db_perform(TABLE_INFORMATION,$sql_data);
      $info_id = tep_db_insert_id();
    }
    return $info_id;
  }
  
  public static function update_no_logged ($data, $language_id, $platform_id, $info_id, $affiliate_id = 0) {

    $sql_data = array();
    $no_logged = 0;
    if (isset($data[$platform_id]) && !is_array($data[$platform_id])) {
      $no_logged = ($data[$platform_id] ? '1' : '0');
    } elseif (isset($data[$platform_id][$affiliate_id]) && !is_array($data[$platform_id][$affiliate_id])) {
      $no_logged = ($data[$platform_id][$affiliate_id] ? '1' : '0');
    }

    tep_db_query("update " . TABLE_INFORMATION . " set no_logged = '" . $no_logged . "' where information_id= '" . $info_id . "' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'");

    return $info_id;
  }
  
  public static function update_visible_status($information_id, $visible, $platform_id=null) {
    if ( is_null($platform_id) ) {
      tep_db_query("update " . TABLE_INFORMATION . " set visible = '" . ($visible ? '1' : '0') . "' where information_id = '" . $information_id . "'");
    }else {
      tep_db_query("update " . TABLE_INFORMATION . " set visible = '" . ($visible ? '1' : '0') . "' where information_id = '" . $information_id . "' and platform_id='".(int)$platform_id."'");
    }
  }
   public static function tep_set_information_visible($information_id, $visible) {
    if ($visible == '1') {
      return tep_db_query("update " . TABLE_INFORMATION . " set visible = '0' where information_id = '" . $information_id . "'");
    } else{
      return tep_db_query("update " . TABLE_INFORMATION . " set visible = '1' where information_id = '" . $information_id . "'");
    }
  }
  
  public static function delete_information ($information_id) {
    tep_db_query("DELETE FROM " . TABLE_INFORMATION . " WHERE information_id='".(int)$information_id."'");
  }

  
}