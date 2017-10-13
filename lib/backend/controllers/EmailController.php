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

/**
 * default controller to handle user requests.
 */
class EmailController extends Sceleton  {

    public function actionIndex() {
        global $language;
        
        \common\helpers\Translation::init('admin/email/templates');
        
        $this->view->headingTitle = HEADING_TITLE;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('email/'), 'title' => HEADING_TITLE);
        
        $customers = array();
        $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
        $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
        $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
        $mail_query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " " . ((tep_session_is_registered('login_affiliate'))?" where affiliate_id = '" . $login_id . "'":''). " order by customers_lastname");
        while($customers_values = tep_db_fetch_array($mail_query)) {
          $customers[] = array('id' => $customers_values['customers_email_address'],
                               'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')');
        }

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }
        
        return $this->render('index', ['customers' => $customers]);
    }
    
    public function actionTemplates() {
        
        \common\helpers\Acl::checkAccess(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_TRANSLATION_EMAIL_TEMPLATES']);
        
      global $language;
      
      $this->selectedMenu = array('design_controls', 'email/templates');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('email/templates'), 'title' => HEADING_TITLE);
      
      
      $this->view->headingTitle = HEADING_TITLE;

      $this->view->groupsTable = array(
        array(
          'title' => TABLE_HEADING_EMAIL_TEMPLATES,
          'not_important' => 1
        ),
      );
      
      $this->view->filters = new \stdClass();
      $this->view->filters->row = (int)$_GET['row'];

      $this->view->insertTemplate = \common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_TRANSLATION_EMAIL_TEMPLATES', 'INSERT_EMAIL_TEMPLATES']);
      
      if($this->view->insertTemplate == true){
          $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('email/template-edit').'" class="create_item">'.IMAGE_INSERT.'</a>';
      }      
      
      $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $this->render('templates', array('messages' => $messages));
      
    }

    public function actionTemplatesList()
    {
      global $languages_id;
      \common\helpers\Translation::init('admin/email/templates');

      $draw   = Yii::$app->request->get( 'draw', 1 );
      $start  = Yii::$app->request->get( 'start', 0 );
      $length = Yii::$app->request->get( 'length', 10 );

      $responseList = array();
      if( $length == -1 ) $length = 10000;
      $query_numrows = 0;

      //TODO search
      $search_condition = '';
      if( isset( $_GET['search']['value'] ) && tep_not_null( $_GET['search']['value'] ) ) {
        $keywords         = tep_db_input( tep_db_prepare_input( $_GET['search']['value'] ) );
        $search_condition = "AND email_templates_key like '%" . $keywords . "%' ";
      }

      if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
        switch( $_GET['order'][0]['column'] ) {
          case 0:
            $orderBy = "email_templates_key " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] ));
            break;
          case 1:
            $orderBy = "email_template_type " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] ));
            break;
          default:
            $orderBy = "email_templates_key, email_template_type";
            break;
        }
      } else {
        $orderBy = "email_templates_key, email_template_type";
      }


      $groups_query_raw = "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where 1 {$search_condition} group by email_templates_key order by {$orderBy}";

      $current_page_number = ( $start / $length ) + 1;
      $_split              = new \splitPageResults( $current_page_number, $length, $groups_query_raw, $query_numrows, 'email_templates_key' );
      $groups_query     = tep_db_query( $groups_query_raw );
      while( $email_templates = tep_db_fetch_array( $groups_query ) ) {
        $name_key = 'TEXT_EMAIL_'.str_replace(' ','_',strtoupper($email_templates['email_templates_key']));
        $email_templates['email_templates_key'] = ( defined($name_key)?constant($name_key):$email_templates['email_templates_key'] );

        $responseList[] = array(

          '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['email/template-edit', 'tpl_id' => $email_templates['email_templates_id']]) . '">'.$email_templates['email_templates_key'] . '<input class="cell_identify" type="hidden" value="' . $email_templates['email_templates_id'] . '"></div>',

        );
      }

      $response = array(
        'draw'            => $draw,
        'recordsTotal'    => $query_numrows,
        'recordsFiltered' => $query_numrows,
        'data'            => $responseList
      );
      echo json_encode( $response );

    }

    function actionTemplatepreedit( $item_id = NULL ){
      $this->layout = false;
      \common\helpers\Translation::init('admin/email/templates');

      if( $item_id === NULL )
        $item_id = (int) Yii::$app->request->post( 'item_id' );


      $get_template_r = tep_db_query("select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id='".(int)$item_id."'");
      if ( tep_db_num_rows($get_template_r)>0 ) {
        $etInfo = new \objectInfo( tep_db_fetch_array($get_template_r) );
        $item_id = intval($etInfo->email_templates_id);
        ?>
        <div class="or_box_head or_box_head_no_margin"><?php
          $name_key = 'TEXT_EMAIL_'.str_replace(' ','_',strtoupper($etInfo->email_templates_key));
          echo ( defined($name_key)?constant($name_key):$etInfo->email_templates_key );
          ?></div>
        <div class="row_or_wrapp">
        </div>
        <div class="btn-toolbar btn-toolbar-order">
          <a class="btn btn-process-order btn-edit btn-primary" href="<?php echo  \Yii::$app->urlManager->createUrl(['email/template-edit', 'tpl_id' => $etInfo->email_templates_id]); ?>"><?=IMAGE_EDIT?></a>
<?php
if (\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_TRANSLATION_EMAIL_TEMPLATES', 'DELETE_EMAIL_TEMPLATES'])) {
?>          
          <button onclick="return deleteItemConfirm(<?php echo $item_id ?>)" class="btn btn-delete btn-no-margin btn-process-order "><?php echo IMAGE_DELETE ?></button>
<?php
}
?>          
        </div>
        <?php
        //<button class="btn btn-delete" onclick="return previewItem( <_?php echo $item_id; ?_>)"><_?=IMAGE_PREVIEW?_><!--</button>-->
      }
    }
    
    public function actionConfirmitemdelete()
    {
        \common\helpers\Translation::init('admin/email/templates');
        $this->layout = false;
        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $get_template_r = tep_db_query("select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id='".(int)$item_id."'");
        if ( tep_db_num_rows($get_template_r)>0 ) {
            $etInfo = new \objectInfo( tep_db_fetch_array($get_template_r) );
            $item_id = intval($etInfo->email_templates_id);
        
        echo '<div class="or_box_head">' . TEXT_HEADING_DELETE . '</div>';
        echo tep_draw_form('groups', 'email/templates', '', 'post', 'id="item_delete" onsubmit="return deleteItem();"');
        echo '<div class="row_fields">' . TEXT_DELETE_INTRO . '</div>';
        //echo '<div class="row_fields"><b>' . $etInfo->groups_name . '</b></div>';
        echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()"></div>';
        echo tep_draw_hidden_field( 'item_id', $item_id );
        echo '</form>';

        }
    }
    
    public function actionItemdelete()
    {
        $this->layout = false;
     
        $template_id   = (int) Yii::$app->request->post( 'item_id' );
        
        $html_id = false;
        $text_id = false;

        $info = tep_db_fetch_array(tep_db_query(
          "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id='".(int)$template_id."'"
        ));
        if ($info['email_template_type']=='html'){
          $html_id = (int)$info['email_templates_id'];
        }else{
          $text_id = (int)$info['email_templates_id'];
        }
        $info2 = tep_db_fetch_array(tep_db_query(
          "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " ".
          "where email_templates_key='".tep_db_input($info['email_templates_key'])."' AND email_template_type='".($info['email_template_type']=='html'?'plaintext':'html')."' " /// !!!!!! A-A-A-A-A-A-A-A-A-A-A-A-A-A-A-A!!!
        ));
        if ($info2['email_template_type']=='html'){
          $html_id = (int)$info2['email_templates_id'];
        }else{
          $text_id = (int)$info2['email_templates_id'];
        }
        
        tep_db_query("delete from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id = '" . (int)$html_id . "'");
        tep_db_query("delete from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id = '" . (int)$text_id . "'");
        tep_db_query("delete from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where email_templates_id = '" . (int)$html_id . "'");
        tep_db_query("delete from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where email_templates_id = '" . (int)$text_id . "'");
        
    }

    function actionTemplateEdit( $item_id = NULL )
    {
      $this->selectedMenu = array('design_controls', 'email/templates');
      
      \common\helpers\Translation::init('admin/email/templates');

      $template_id = (int)Yii::$app->request->get('tpl_id', $item_id);

      $html_id = false;
      $text_id = false;

      $info = tep_db_fetch_array(tep_db_query(
        "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id='".(int)$template_id."'"
      ));
      if ($info['email_template_type']=='html'){
        $html_id = (int)$info['email_templates_id'];
      }else{
        $text_id = (int)$info['email_templates_id'];
      }
      $info2 = tep_db_fetch_array(tep_db_query(
        "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " ".
        "where email_templates_key='".tep_db_input($info['email_templates_key'])."' AND email_template_type='".($info['email_template_type']=='html'?'plaintext':'html')."' " /// !!!!!! A-A-A-A-A-A-A-A-A-A-A-A-A-A-A-A!!!
      ));
      if ($info2['email_template_type']=='html'){
        $html_id = (int)$info2['email_templates_id'];
      }else{
        $text_id = (int)$info2['email_templates_id'];
      }

      $cDescriptionHtml = [];
      $cDescriptionText = [];
      $platforms = \common\classes\platform::getList(false);

      $languages = \common\helpers\Language::get_languages();

      foreach ($platforms as $platform) {
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $languages[$i]['logo'] = $languages[$i]['image'];
          $cDescriptionHtml[$platform['id']][$i] = array();
          $cDescriptionHtml[$platform['id']][$i]['code'] = $languages[$i]['code'];
          if ($html_id) {
            $cDescriptionHtml[$platform['id']][$i]['email_templates_subject'] = tep_draw_input_field(
              'email_templates_subject[' . $platform['id'] . '][html][' . $languages[$i]['id'] . ']',
              \common\helpers\Mail::get_email_templates_subject((int)$html_id, $languages[$i]['id'], $platform['id']),
              'class="form-control"'
            );
            $cDescriptionHtml[$platform['id']][$i]['email_templates_body'] = tep_draw_textarea_field(
              'email_templates_body[' . $platform['id'] . '][html][' . $languages[$i]['id'] . ']',
              'soft', '70', '15',
              \common\helpers\Mail::get_email_templates_body((int)$html_id, $languages[$i]['id'], $platform['id']),
              'class="' . ($info['email_template_type'] == 'html' ? 'ckeditor' : '') . ' form-control" id="htmldesc' . $platform['id'] . '_' . $languages[$i]['id'] . '"'
            );
            $cDescriptionHtml[$platform['id']][$i]['c_link'] = 'htmldesc' . $platform['id'] . '_' . $languages[$i]['id'];
          } else {
            $cDescriptionHtml[$platform['id']][$i]['email_templates_subject'] = tep_draw_input_field(
              'email_templates_subject[' . $platform['id'] . '][html][' . $languages[$i]['id'] . ']',
              '',
              'class="form-control"'
            );
            $cDescriptionHtml[$platform['id']][$i]['email_templates_body'] = tep_draw_textarea_field(
              'email_templates_body[' . $platform['id'] . '][html][' . $languages[$i]['id'] . ']',
              'soft', '70', '15',
              '',
              'class="ckeditor form-control" id="htmldesc' . $platform['id'] . '_' . $languages[$i]['id'] . '"'
            );
            $cDescriptionHtml[$platform['id']][$i]['c_link'] = 'htmldesc' . $platform['id'] . '_' . $languages[$i]['id'];
          }
          $cDescriptionText[$platform['id']][$i] = array();
          $cDescriptionText[$platform['id']][$i]['code'] = $languages[$i]['code'];
          if ($text_id) {
            $cDescriptionText[$platform['id']][$i]['email_templates_subject'] = tep_draw_input_field(
              'email_templates_subject[' . $platform['id'] . '][plaintext][' . $languages[$i]['id'] . ']',
              \common\helpers\Mail::get_email_templates_subject((int)$text_id, $languages[$i]['id'], $platform['id']),
              'class="form-control"'
            );
            $cDescriptionText[$platform['id']][$i]['email_templates_body'] = tep_draw_textarea_field(
              'email_templates_body[' . $platform['id'] . '][plaintext][' . $languages[$i]['id'] . ']',
              'soft', '70', '15',
              \common\helpers\Mail::get_email_templates_body((int)$text_id, $languages[$i]['id'], $platform['id']),
              'class="form-control" id="textdesc' . $platform['id'] . '_' . $languages[$i]['id'] . '"'
            );
            $cDescriptionText[$platform['id']][$i]['c_link'] = 'textdesc' . $languages[$i]['id'];
          } else {
            $cDescriptionText[$platform['id']][$i]['email_templates_subject'] = tep_draw_input_field(
              'email_templates_subject[' . $platform['id'] . '][plaintext][' . $languages[$i]['id'] . ']',
              '',
              'class="form-control"'
            );
            $cDescriptionText[$platform['id']][$i]['email_templates_body'] = tep_draw_textarea_field(
              'email_templates_body[' . $platform['id'] . '][plaintext][' . $languages[$i]['id'] . ']',
              'soft', '70', '15',
              '',
              'class="form-control" id="textdesc' . $platform['id'] . '_' . $languages[$i]['id'] . '"'
            );
            $cDescriptionText[$platform['id']][$i]['c_link'] = 'textdesc' . $platform['id'] . '_' . $languages[$i]['id'];
          }
        }
      }

      $this->view->headingTitle = $info['email_templates_key'];
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('email/templates'), 'title' => HEADING_TITLE);

      $name_key = 'TEXT_EMAIL_'.str_replace(' ','_',strtoupper($info['email_templates_key']));
      $info['email_templates_key'] = ( defined($name_key)?constant($name_key):$info['email_templates_key'] );

      if ($template_id == 0) {
          $info['email_templates_key'] = tep_draw_input_field(
                'email_templates_key',
                '',
                'required class="form-control" placeholder="'.TEXT_EMAIL_TEMPLATE_KEY.'"'
            );
      }

      return $this->render('templates-edit', array(
        'email_templates_key' => $info['email_templates_key'],
        'languages' => $languages,
        'cDescriptionHtml' => $cDescriptionHtml,
        'cDescriptionText' => $cDescriptionText,
        'email_templates_id' => (int)$template_id,
        'platforms' => $platforms,
        'isMultiPlatforms' => \common\classes\platform::isMulti(),
        'default_platform_id' => \common\classes\platform::defaultId(),
      ));
    }

    function actionTemplatesKeys()
    {
        \common\helpers\Translation::init('keys');
        
        $this->layout = false;
        
        $keysList = [];
        $keysList[] = ['text' => BOX_CONFIGURATION_MYSTORE, 'child' => [
            '##STORE_NAME##',
            '##HTTP_HOST##',
            '##STORE_OWNER_EMAIL_ADDRESS##',
        ]];
        $keysList[] = ['text' => BOX_CUSTOMERS_CUSTOMERS, 'child' => [
            '##CUSTOMER_EMAIL##',
            '##CUSTOMER_FIRSTNAME##',
            '##NEW_PASSWORD##',
            '##USER_GREETING##',
        ]];
        $keysList[] = ['text' => BOX_CUSTOMERS_ORDERS, 'child' => [
            '##ORDER_NUMBER##',
            '##ORDER_DATE_LONG##',
            '##ORDER_DATE_SHORT##',
            '##BILLING_ADDRESS##',
            '##DELIVERY_ADDRESS##',
            '##PAYMENT_METHOD##',
            '##ORDER_COMMENTS##',
            '##NEW_ORDER_STATUS##',
            '##ORDER_TOTALS##',
            '##PRODUCTS_ORDERED##',
            '##ORDER_INVOICE_URL##',
        ]];
        $keysList[] = ['text' => BOX_HEADING_GV_ADMIN, 'child' => [
            '##COUPON_AMOUNT##',
            '##COUPON_NAME##',
            '##COUPON_DESCRIPTION##',
            '##COUPON_CODE##',
        ]];
        
        
//        $keysList[] = ['id' => 1,'type' => 'item','text' => '&nbsp;&nbsp;Firstname'];
//        $keysList[] = ['id' => 2,'type' => 'item','text' => '&nbsp;&nbsp;Lastname'];
//        $keysList[] = ['id' => 3,'type' => 'group','text' => BOX_CUSTOMERS_ORDERS];
        
        echo '<div class="pageLinksWrapper">';
        echo '<select name="key" class="form-control">';
        foreach ($keysList as $keys) {
            echo '<optgroup label="' . htmlspecialchars($keys['text']) . '">' . "\n";
            foreach ($keys['child'] as $key => $value) {
                 echo '<option value="' . $value . '">' . (defined($value) ? constant($value) : $value) . '</option>';
            }
            echo '</optgroup>';
        }
        echo '</select>';
        //'<div class="pageLinksWrapper">'.tep_draw_pull_down_menu('category_id', $keysList, '', 'class="form-control"') . 
        echo '</div>';
        
        ?>

            <div class="pageLinksButton">
                <button class="btn btn-no-margin"><?php echo IMAGE_INSERT;?></button>
            </div>
<script type="text/javascript">
  (function($){
    $(function(){
      var oEditor = CKEDITOR.instances.<?php echo $_GET['id_ckeditor']?>;
      if (oEditor != undefined) {
      if(oEditor.mode == 'wysiwyg') {
      $('.pageLinksButton .btn').click(function(){
        if($('select[name="key"]').val() != ''){
            oEditor.focus();
            if(oEditor.getSelection().getRanges()[0].collapsed == false){
                var fragment = oEditor.getSelection().getRanges()[0].extractContents();
                var container = CKEDITOR.dom.element.createFromHtml($('select[name="key"]').val(), oEditor.document);
                //fragment.appendTo(container);
                //oEditor.insertElement(container);
                var html = $('select[name="key"]').val();  
                oEditor.insertHtml(html);
            } else {
                
                var html = $('select[name="key"]').val();  
                oEditor.insertHtml(html);
                //var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                //oEditor.insertElement( newElement );
            }
        }
        $(this).parents('.popup-box-wrap').remove();
      })
        } else {
            $('.pageLinksWrapper').html('<?php echo TEXT_PLEASE_TURN;?>');
            $('.pageLinksButton').hide();
        }
      } else {
        $('.pageLinksButton .btn').click(function(){
            if($('select[name="key"]').val() != ''){
                var html = $('select[name="key"]').val(); 
                insertAtCaret('<?php echo $_GET['id_ckeditor']?>', html)
            }
            $(this).parents('.popup-box-wrap').remove();
        })
      }
    })
  })(jQuery)
</script>
        <?php
        
    }
    
    function actionTemplatesSave()
    {
      $this->layout = false;

      \common\helpers\Translation::init('admin/email/templates');

      $template_id = (int)Yii::$app->request->post('email_templates_id');

      $html_id = false;
      $text_id = false;

      $info = tep_db_fetch_array(tep_db_query(
        "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id='".(int)$template_id."'"
      ));
      if ($info['email_template_type']=='html'){
        $html_id = (int)$info['email_templates_id'];
      }else{
        $text_id = (int)$info['email_templates_id'];
      }
      $info2 = tep_db_fetch_array(tep_db_query(
        "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " ".
        "where email_templates_key='".$info['email_templates_key']."' AND email_template_type='".($info['email_template_type']=='html'?'plaintext':'html')."' " /// !!!!!! A-A-A-A-A-A-A-A-A-A-A-A-A-A-A-A!!!
      ));
      if ($info2['email_template_type']=='html'){
        $html_id = (int)$info2['email_templates_id'];
      }else{
        $text_id = (int)$info2['email_templates_id'];
      }

      if ($html_id == 0) {
          tep_db_perform(TABLE_EMAIL_TEMPLATES, array(
              'email_templates_key' => Yii::$app->request->post('email_templates_key'),
              'email_template_type' => 'html',
            ));
          $template_id = $html_id = tep_db_insert_id();
      }

      if ($text_id == 0) {
          tep_db_perform(TABLE_EMAIL_TEMPLATES, array(
              'email_templates_key' => Yii::$app->request->post('email_templates_key'),
              'email_template_type' => 'plaintext',
            ));
          $text_id = tep_db_insert_id();
          if ($html_id == 0) {
              $template_id = $text_id;
          }
      }

      $platforms = \common\classes\platform::getList(false);

      $languages = \common\helpers\Language::get_languages();
      foreach ($platforms as $platform) {
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {

          if ($html_id && isset($_POST['email_templates_subject'][$platform['id']]['html'])) {
            $update_template_id = $html_id;
            $email_templates_subject = tep_db_prepare_input($_POST['email_templates_subject'][$platform['id']]['html'][$languages[$i]['id']]);
            $email_templates_body = tep_db_prepare_input($_POST['email_templates_body'][$platform['id']]['html'][$languages[$i]['id']]);

            $check = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS echeck FROM " . TABLE_EMAIL_TEMPLATES_TEXTS . " " .
              "WHERE email_templates_id='" . (int)$update_template_id . "' AND language_id='" . (int)$languages[$i]['id'] . "' AND affiliate_id=0 and platform_id = '" . $platform['id'] . "'"
            ));
            if ($check['echeck'] > 0) {
              tep_db_perform(TABLE_EMAIL_TEMPLATES_TEXTS, array(
                'email_templates_subject' => $email_templates_subject,
                'email_templates_body' => $email_templates_body,
              ), 'update', "email_templates_id='" . (int)$update_template_id . "' AND language_id='" . (int)$languages[$i]['id'] . "' AND affiliate_id=0 and platform_id = '" . $platform['id'] . "'");
            } else {
              tep_db_perform(TABLE_EMAIL_TEMPLATES_TEXTS, array(
                'email_templates_id' => (int)$update_template_id,
                'language_id' => (int)$languages[$i]['id'],
                'affiliate_id' => 0,
                'email_templates_subject' => $email_templates_subject,
                'email_templates_body' => $email_templates_body,
                'platform_id' =>  $platform['id'],
              ));
            }
          }

          if ($text_id && isset($_POST['email_templates_subject'][$platform['id']]['plaintext'])) {
            $update_template_id = $text_id;
            $email_templates_subject = tep_db_prepare_input($_POST['email_templates_subject'][$platform['id']]['plaintext'][$languages[$i]['id']]);
            $email_templates_body = tep_db_prepare_input($_POST['email_templates_body'][$platform['id']]['plaintext'][$languages[$i]['id']]);

            $check = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS echeck FROM " . TABLE_EMAIL_TEMPLATES_TEXTS . " " .
              "WHERE email_templates_id='" . (int)$update_template_id . "' AND language_id='" . (int)$languages[$i]['id'] . "' AND affiliate_id=0  and platform_id = '" . $platform['id'] . "'"
            ));
            if ($check['echeck'] > 0) {
              tep_db_perform(TABLE_EMAIL_TEMPLATES_TEXTS, array(
                'email_templates_subject' => $email_templates_subject,
                'email_templates_body' => $email_templates_body,
              ), 'update', "email_templates_id='" . (int)$update_template_id . "' AND language_id='" . (int)$languages[$i]['id'] . "' AND affiliate_id=0 and platform_id = '" . $platform['id'] . "'");
            } else {
              tep_db_perform(TABLE_EMAIL_TEMPLATES_TEXTS, array(
                'email_templates_id' => (int)$update_template_id,
                'language_id' => (int)$languages[$i]['id'],
                'affiliate_id' => 0,
                'email_templates_subject' => $email_templates_subject,
                'email_templates_body' => $email_templates_body,
                'platform_id' =>  $platform['id'],
              ));
            }
          }

        }
      }
      echo '<script> window.location.replace("'. Yii::$app->urlManager->createUrl(['email/template-edit', 'tpl_id' => $template_id]) . '");</script>';
      //return $this->actionTemplateEdit( (int)$template_id );
    }

    function actionTemplatePreview( $item_id = NULL )
    {
      $this->layout = false;
  
      \common\helpers\Translation::init('admin/email/templates');

      $template_id = \Yii::$app->request->post('item_id', $item_id);

      $info = tep_db_fetch_array(tep_db_query(
        "select email_templates_id, email_templates_key, email_template_type from " . TABLE_EMAIL_TEMPLATES . " where email_templates_id='".(int)$template_id."'"
      ));

      ?>
      <?php echo \common\helpers\Mail::get_email_templates_subject((int)$template_id, $_SESSION['languages_id']); ?>
      <hr>
      <?php
      if ( $info['email_template_type']=='html' ) {
        echo \common\helpers\Mail::get_email_templates_body((int)$template_id, $_SESSION['languages_id']);
      }else{
        echo nl2br(\common\helpers\Mail::get_email_templates_body((int)$template_id, $_SESSION['languages_id']));
      }
      ?>
      <?php      
    }

  }
