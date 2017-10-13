{use class="\common\classes\Images"}

<div class="widget box box-no-shadow" style="margin-bottom: 0; border-bottom: 0;">
  <div class="widget-header">
    <h4>{$smarty.const.TEXT_PRODUCT_IMAGES}</h4>
  </div>
  <div class="widget-content" style="padding-bottom: 0;">
    <div class="wrap-prod-gallery">
      <div class="drag-prod-img">
        <div class="upload-container">
          <div class="upload-file-wrap">
            <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES}<br>{$smarty.const.TEXT_OR}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD}</span></div>
            <div class="upload-file"></div>
            <div class="upload-hidden"><input type="hidden" name="image_buffer"/></div>
          </div>
        </div>
      </div>
      <div class="jcarousel-wrapper">
        <div class="jcarousel">
          <ul id="images-listing">
            {foreach $app->controller->view->images as $Key => $Item}
              <li{if $Key == 0} class="active"{/if} prefix="image-box-{$Key}"><span class="handle"><i class="icon-hand-paper-o"></i></span><span><img id="preview-box-{$Key}" src="{$Item['image_name']}" /></span><div onclick="removeImage(this, {$Key});" class="upload-remove"></div></li>
            {/foreach}
          </ul>
        </div>
        <a href="#" class="jcarousel-control-prev"></a>
        <a href="#" class="jcarousel-control-next"></a>
      </div>
      <input type="hidden" value="" name="images_sort_order" id="images_sort_order"/>
    </div>
  </div>
</div>
<div class="box-gallery after">
  {foreach $app->controller->view->images as $Key => $Item}
    <div id="image-box-{$Key}" class="image-box {if $Key == 0}active{else}inactive{/if}">
      <div class="box-gallery-left">
        <div class="edp-our-price-box">
          <div class="widget widget-full box box-no-shadow" style="margin-bottom: 0">
            <div class="widget-content after">
              <div class="status-left st-origin">
                <span>{$smarty.const.TEXT_STATUS}</span>
                <input type="checkbox" value="1" name="image_status[{$Key}]" class="check_bot_switch_on_off"{if $Item['image_status'] == 1} checked="checked"{/if} />
              </div>
              <div class="status-left">
                <span>{$smarty.const.TEXT_DEFAULT_IMG}:</span>
                <input type="radio" value="{$Key}" name="default_image" class="default-images check_bot_switch_on_off"{if $Item['default_image'] == 1} checked="checked"{/if} />
              </div>
            </div>
          </div>
          <div class="widget box widget-not-full box-no-shadow" style="margin-bottom: 0; border-top: 0;">
            <div class="widget-content">
              <div class="tabbable tabbable-custom">
                <ul class="nav nav-tabs nav-tabs-vertical nav-tabs-vertical-lang">
                  <li class="active"><a href="#tab_4_{$Key}_0" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                  {foreach $Item['description'] as $DKey => $DItem}
                    <li><a href="#tab_4_{$Key}_{$DItem['key']}" class="flag-span" data-toggle="tab">{$DItem['logo']}<span>{$DItem['name']}</span></a></li>
                  {/foreach}
                </ul>
                <div class="tab-content tab-content-vertical">
                  <div class="tab-pane active one-img-gal" id="tab_4_{$Key}_0">
                    <div class="one-img-gal-left">
                      <div class="drag-prod-img-2">
                        <div class="upload" data-linked="preview-box-{$Key}" data-name="orig_file_name[{$Key}][0]" data-show="ofn_{$Key}_0" data-value="{$Item['image_name']|escape:'html'}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                      </div>
                    </div>
                    <div class="one-img-gal-right">
                      <div class="our-pr-line">
                        <label>{$smarty.const.TEXT_ORING_NAME}</label>
                        <span id="ofn_{$Key}_0">{$Item['orig_file_name']}&nbsp;</span>
                      </div>
                      <div class="our-pr-line">
                        <label>{$smarty.const.TEXT_IMG_HEAD_TITLE}</label>
                        <input type="text" name="image_title[{$Key}][0]" value="{$Item['image_title']|escape:'html'}" class="form-control" />
                      </div>
                      <div class="our-pr-line">
                        <label>{$smarty.const.TEXT_IMG_ALTER}</label>
                        <input type="text" name="image_alt[{$Key}][0]" value="{$Item['image_alt']|escape:'html'}" class="form-control" />
                      </div>
                      <div class="our-pr-line">
                        <label><input type="checkbox" name="alt_file_name_flag[{$Key}][0]" value="1" {if $Item['alt_file_name'] != ""} checked{/if} class="uniform type-altr-file-name" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                        <input type="text" name="alt_file_name[{$Key}][0]" value="{$Item['alt_file_name']|escape:'html'}" class="form-control type-altr-file-name-input" {if $Item['alt_file_name'] == ""} style="display: none;"{/if} />
                      </div>
                      <div class="our-pr-line">
                        <label><input type="checkbox" name="no_watermark[{$Key}][0]" value="1" {if $Item['no_watermark'] == 1} checked{/if} class="uniform" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                      </div>
                    </div>
                  </div>
                  {foreach $Item['description'] as $DKey => $DItem}
                    <div class="tab-pane one-img-gal" id="tab_4_{$Key}_{$DItem['key']}">
                      <div class="one-img-gal-left">
                        <div class="drag-prod-img-2">
                          <div class="upload" data-name="orig_file_name[{$Key}][{$DItem['id']}]" data-show="ofn_{$Key}_{$DItem['id']}" data-value="{$DItem['image_name']}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                        </div>
                      </div>
                      <div class="one-img-gal-right">
                        <div class="our-pr-line">
                          <label>{$smarty.const.TEXT_ORING_NAME}</label>
                          <span id="ofn_{$Key}_{$DItem['id']}">{$DItem['orig_file_name']}&nbsp;</span>
                        </div>
                        <div class="our-pr-line">
                          <label>{$smarty.const.TEXT_IMG_HEAD_TITLE}</label>
                          <input type="text" name="image_title[{$Key}][{$DItem['id']}]" value="{$DItem['image_title']|escape:'html'}" class="form-control" />
                        </div>
                        <div class="our-pr-line">
                          <label>{$smarty.const.TEXT_IMG_ALTER}</label>
                          <input type="text" name="image_alt[{$Key}][{$DItem['id']}]" value="{$DItem['image_alt']|escape:'html'}" class="form-control" />
                        </div>
                        <div class="our-pr-line">
                          <label><input type="checkbox" name="alt_file_name_flag[{$Key}][{$DItem['id']}]" value="1" {if $DItem['alt_file_name'] != ""} checked{/if} class="uniform type-altr-file-name" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                          <input type="text" name="alt_file_name[{$Key}][{$DItem['id']}]" value="{$DItem['alt_file_name']|escape:'html'}" class="form-control type-altr-file-name-input" {if $DItem['alt_file_name'] == ""} style="display: none;"{/if} />
                        </div>
                        <div class="our-pr-line">
                          <label><input type="checkbox" name="no_watermark[{$Key}][{$DItem['id']}]" value="1" {if $DItem['no_watermark'] == 1} checked{/if} class="uniform" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                        </div>
                      </div>
                    </div>
                  {/foreach}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="box-gallery-right">
        <div class="tabbable tabbable-custom">
          <ul class="nav nav-tabs">
                {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock2')}
                    <li class="active"><a href="#tab_{$Key}_5_1" data-toggle="tab"><span>{$smarty.const.TEXT_ASSIGN_TO_ATTR}</span></a></li>
                {else} 
                    <li class="active"><a href="#tab_{$Key}_5_1" data-toggle="tab"><span class="dis_module">{$smarty.const.TEXT_ASSIGN_TO_ATTR}</span></a></li>
                {/if}
            {if $app->controller->view->showInventory == true}
                {if \common\helpers\Acl::checkExtension('InventortyImages', 'productBlock2')}
                    <li><a href="#tab_{$Key}_5_2" data-toggle="tab"><span>{$smarty.const.TEXT_ASSIGN_TO_INVENT}</span></a></li>
                {else} 
                    <li><a href="#tab_{$Key}_5_2" data-toggle="tab"><span class="dis_module">{$smarty.const.TEXT_ASSIGN_TO_INVENT}</span></a></li>
                {/if}
            {/if}
          </ul>
          <div class="tab-content">
                {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock2')}
                    {\common\extensions\AttributesImages\AttributesImages::productBlock2($Key)}
                {else} 
            <div class="tab-pane active dis_module" id="tab_{$Key}_5_1">
              <div class="box-head-serch after">
                  <input type="search" placeholder="Search by assigned attributes" disabled class="form-control">
                <button onclick="return false"></button>
              </div>

              <div class="w-img-list w-img-list-attr">
                <div class="w-img-list-ul js-option-images">
                  {foreach $app->controller->view->selectedAttributes as $sel_attr_option}
                    <label class="js-option-group-images" data-ov_id="{$sel_attr_option['products_options_id']}">{$sel_attr_option['products_options_name']}</label>
                    <ul class="js-option-group-images" data-ov_id="{$sel_attr_option['products_options_id']}">
                      {foreach $sel_attr_option['values'] as $sel_attr_value}
                        <li class="js-option-value-images" data-ov_pair="{$sel_attr_option['products_options_id']}_{$sel_attr_value['products_options_values_id']}"><label><input type="checkbox" disabled class="uniform" {if Images::checkAttribute($Item['products_images_id'], $sel_attr_option['products_options_id'], $sel_attr_value['products_options_values_id'])}checked{/if} /> {$sel_attr_value['products_options_values_name']}</label></li>
                      {/foreach}
                    </ul>
                  {/foreach}
                </div>
                <div class="w-btn-list" style="display: none;">
                  <span class="btn">{$smarty.const.TEXT_ASSIGN}</span>
                </div>
              </div>
            </div>
                {/if}
            {if $app->controller->view->showInventory == true}
                {if \common\helpers\Acl::checkExtension('InventortyImages', 'productBlock2')}
                    {\common\extensions\InventortyImages\InventortyImages::productBlock2($Key)}
                {else} 
              <div class="tab-pane dis_module" id="tab_{$Key}_5_2">
                <div class="box-head-serch after">
                  <input type="search" placeholder="Search by assigned inventory" class="form-control" disabled>
                  <button onclick="return false"></button>
                </div>
                <div class="w-img-list w-img-list-attr">
                  <div class="w-img-list-ul js_image_inventory" data-image_idx="{$Key}">
                    
                  </div>
                  <div class="w-btn-list" style="display: none;">
                    <span class="btn">{$smarty.const.TEXT_ASSIGN}</span>
                  </div>
                </div>
              </div>
                {/if}
            {/if}
          </div>
        </div>
      </div>
      <input type="hidden" name="products_images_id[{$Key}]" value="{$Item['products_images_id']}" />
      <input type="hidden" id="deleted-image-{$Key}" name="products_images_deleted[{$Key}]" value="0" />
    </div>
  {/foreach}

  <script type="text/javascript">
    var imagesQty = {$app->controller->view->imagesQty};
    var imagepath = "{$app->controller->view->upload_path}";

    function removeImage(obj, key) {
      $("#image-box-"+key).removeClass('active').addClass('inactive');
      $("#deleted-image-"+key).val('1');
      $(obj).parent().remove();
      $('#save_product_form').trigger('image_removed',[key]);
    }

    function uploadRemove(obj, show, linked) {
      $(obj).parent().remove();
      if (show != undefined) {
        $('#'+show).text(' ');
      }
      if (linked != undefined) {
        $('#'+linked).hide();
      }
    }

    function uploadSuccess(linked, name) {
      $('#'+linked).attr('src', imagepath+name);
      $('#'+linked).show();
    }

    $('.upload-container').each(function() {

      var _this = $(this);

      $('.upload-file', _this).dropzone({
        url: "{Yii::$app->urlManager->createUrl('upload/index')}",
        sending:  function(e, data) {
          $('.upload-hidden input[type="hidden"]', _this).val(e.name);
          $('.upload-remove', _this).on('click', function(){
            $('.dz-details', _this).remove()
          })
        },
        dataType: 'json',
        previewTemplate: '<div class="dz-details" style="display: none;"><img data-dz-thumbnail /></div>',
        drop: function(){
          $('.upload-file', _this).html('')
        },
        success: function(e, data) {


          setTimeout(function () {

            //console.log( e.name );
            $("#images-listing").append('<li class="clickable-box-'+imagesQty+'" prefix="image-box-'+imagesQty+'"><span class="handle"><i class="icon-hand-paper-o"></i></span><span><img id="preview-box-'+imagesQty+'" src="'+imagepath+e.name+'" /></span><div onclick="removeImage(this, '+imagesQty+');" class="upload-remove"></div></li>');
            //$("#images-listing").append('<li class="clickable-box-'+imagesQty+'" prefix="image-box-'+imagesQty+'"><span><img src="'+$('img', _this).attr('src')+'" /></span><div onclick="removeImage(this, '+imagesQty+');" class="upload-remove"></div></li>');

            $.get("{Yii::$app->urlManager->createUrl('categories/product-new-image')}", { id: imagesQty, name: e.name }, function(data, status){
              if (status == "success") {
                $(".box-gallery.after").append(data);
                $('.jcarousel').jcarousel('scroll', -1); //TODO: better on first uploaded (if batch upload)
                $('#save_product_form').trigger('new_image_uploaded');
              } else {
                alert("Request error.");
              }
            },"html");



            //$('.upload-file', _this).html('');
            imagesQty++;
          }, 200);

        },
      });

    });


    $(document).ready(function(){
      var $main_form = $('#save_product_form');

      var sync_image_attributes = function(){
        var selected_attr = [];
        var selected_opt = { };

        var $attributes = $main_form.find('select[name="attributes"]');
        var get_id_re = /^products_attributes_id\[(\d+)\]\[(\d+)\]/;
        var check_opt_pass = { };
        $main_form.find('input[name^="products_attributes_id\["]').each(function(){
          var rr = this.name.match(get_id_re);
          if ( rr ){
            var ov_pair = rr[1]+'_'+rr[2];
            selected_attr.push(ov_pair);
            selected_opt[''+rr[1]] = rr[2];
            if ( !check_opt_pass[rr[1]] && $('.js-option-group-images[data-ov_id="'+rr[1]+'"]').length==0 ) {
              $('.js-option-images').each(function(){
                $(this).append('<label class="js-option-group-images" data-ov_id="'+rr[1]+'">'+$attributes.find('optgroup[id="'+rr[1]+'"]').attr('label')+'</label><ul class="js-option-group-images" data-ov_id="'+rr[1]+'"></ul>');
              });
              check_opt_pass[rr[1]] = rr[1];
            }
            if ($('.js-option-value-images[data-ov_pair="'+ov_pair+'"]').length == 0) {
              $('ul.js-option-group-images[data-ov_id="'+rr[1]+'"]').each(function(){
                var key = $(this).parents('.image-box').attr('id').replace('image-box-','');
                {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock2')}
                $(this).append('<li class="js-option-value-images" data-ov_pair="'+ov_pair+'"><label><input type="checkbox" name="image_attr['+key+']['+rr[1]+']['+rr[2]+']" value="1" class="uniform" /> '+$attributes.find('option[value="'+rr[2]+'"]').html()+'</label></li>');
                {else}
                $(this).append('<li class="js-option-value-images" data-ov_pair="'+ov_pair+'"><label><input type="checkbox" disabled name="image_attr['+key+']['+rr[1]+']['+rr[2]+']" value="1" class="uniform" /> '+$attributes.find('option[value="'+rr[2]+'"]').html()+'</label></li>');
                {/if}
              });
            }
          }
        });
        $('.js-option-images').each(function(){
          var $set = $(this);
          var selected_attr_str = '|'+selected_attr.join('|')+'|';
          $('.js-option-group-images',$set).each(function(){
            var $ws = $(this);
            var opt_id = $ws.attr('data-ov_id');
            if ( typeof selected_opt[opt_id] !== 'undefined' ){
              if ($ws.hasClass('hide-default')) $ws.removeClass('hide-default');
              $('.js-option-value-images[data-ov_pair]',$ws).each(function(){
                var $wso = $(this);
                if (selected_attr_str.indexOf($wso.attr('data-ov_pair'))!==-1){
                  // need
                  if ($wso.hasClass('hide-default')) $wso.removeClass('hide-default');
                }else{
                  if (!$wso.hasClass('hide-default')) $wso.addClass('hide-default');
                }
              });
            }else{
              if (!$ws.hasClass('hide-default')) $ws.addClass('hide-default');
            }
          });
        });
      }
      $main_form.on('attributes_changed',sync_image_attributes);
      $main_form.on('new_image_uploaded',sync_image_attributes);
      //sync_image_attributes();
      $main_form.trigger('attributes_changed');
      var rebuild_images_inventory = function(){
        var tpl = $('#new_image_inventory').html();
        $('.js_image_inventory').each(function(){
          var $cont = $(this);
          var image_idx = $cont.attr('data-image_idx');
          var $new_content = $(tpl.replace(/%%img_idx%%/g, image_idx));
          $cont.find('input:checked').each(function(){
            var new_checkbox = $new_content.find('input[value="'+$(this).val()+'"]');
            if ( new_checkbox.length>0 ) {
              new_checkbox[0].checked = true; new_checkbox.attr( 'checked', 'checked' );
            }
          });
          $new_content.find(':radio.uniform, :checkbox.uniform').uniform();
          $cont.html($new_content);
        });
      };
      $main_form.on('inventory_arrived',rebuild_images_inventory);
      $main_form.on('new_image_uploaded',rebuild_images_inventory);


      var sync_images_check_state = function(){
        var checked_ov = { },
                unchecked_ov = { },
                unchecked_inv = { },
                checked_inv = { };
        $('#save_product_form').find('[name^="image_attr"]:checked').each(function(){
          var ids = this.name.match(/\[(\d+)\]\[(\d+)\]\[(\d+)\]/);
          if ( ids ) {
            var img_idx = ids[1]; //img
            var pair = ids[2]+'_'+ids[3]; //img
            if ( typeof checked_ov[pair] === 'undefined' ) checked_ov[pair] = [];
            checked_ov[pair].push( parseInt(img_idx,10) );
          }
        });
        $('#save_product_form').find('[name^="image_attr"]').each(function(){
          var ids = this.name.match(/\[(\d+)\]\[(\d+)\]\[(\d+)\]/);
          if ( ids ) {
            var img_idx = ids[1]; //img
            var pair = ids[2]+'_'+ids[3]; //img
            if ( typeof checked_ov[pair] === 'undefined' ) {
                if ( typeof unchecked_ov[pair] === 'undefined' ) unchecked_ov[pair] = [];
                unchecked_ov[pair].push( parseInt(img_idx,10) );
            }
          }
        });
        $('#save_product_form').find('[name^="image_inventory"]:checked').each(function(){
          var ids = this.name.match(/\[(\d+)\]\[(\d+)\]/);
          if ( ids ) {
            var img_idx = ids[1]; //img
            var uprid = this.value; //img
            if ( typeof checked_inv[uprid] === 'undefined' ) checked_inv[uprid] = [];
            checked_inv[uprid].push( parseInt(img_idx,10) );
          }
        });
        $('#save_product_form').find('[name^="image_inventory"]').each(function(){
          var ids = this.name.match(/\[(\d+)\]\[(\d+)\]/);
          if ( ids ) {
            var img_idx = ids[1]; //img
            var uprid = this.value; //img
            
            if ( typeof checked_inv[uprid] === 'undefined' ) {
                if ( typeof unchecked_inv[uprid] === 'undefined' ) unchecked_inv[uprid] = [];
                unchecked_inv[uprid].push( parseInt(img_idx,10) );
            }
          }
        });
        var sel = $('select[name="divselktr"]');
        for( var pair in checked_ov ) {
          if ( ! checked_ov.hasOwnProperty(pair) ) continue;
          var mc_ov = sel.filter('[data-ov_pair="'+pair+'"]');
          if ( mc_ov.length>0 ) {
            mc_ov.val(checked_ov[pair]); mc_ov.trigger('change');
            mc_ov.multiselect('refresh');
          }
        }
        for( var pair in unchecked_ov ) {
            if ( ! unchecked_ov.hasOwnProperty(pair) ) continue;
          var mc_ov = sel.filter('[data-ov_pair="'+pair+'"]');
          if ( mc_ov.length>0 ) {
            mc_ov.val([]).trigger('change').multiselect('refresh');
          }
        }
        for( var uprid in checked_inv ) {
          if ( ! checked_inv.hasOwnProperty(uprid) ) continue;
          var mc_inv = sel.filter('[data-uprid="'+uprid+'"]');
          if ( mc_inv.length>0 ) {
            mc_inv.val(checked_inv[uprid]); mc_inv.trigger('change');
            mc_inv.multiselect('refresh');
          }
        }
        for( var uprid in unchecked_inv ) {
          if ( ! unchecked_inv.hasOwnProperty(uprid) ) continue;
          var mc_inv = sel.filter('[data-uprid="'+uprid+'"]');
          if ( mc_inv.length>0 ) {
              mc_inv.val([]).trigger('change').multiselect('refresh')
          }
        }
      };
      var sync_images_check_state_back = function(e){
        var $select = $(e.target);
        var selected_now = $select.val(), selected_str;
        if ( $select.attr('data-ov_pair') ) {
          var check_attr = $('[name^="image_attr\["]');
          selected_str = '_'+($.isArray(selected_now)?selected_now.join('_'):selected_now)+'_';
          var pair_name_ending = ']['+($select.attr('data-ov_pair').replace('_',']['))+']';
          check_attr.filter(function(idx, checkbox) {
            if ( checkbox.name.indexOf(pair_name_ending)===-1 ) return false;
            var img_id = checkbox.name.match(/image_attr\[(\d+)\]/);
            if ( img_id ) {
              return (selected_str.indexOf('_'+img_id[1]+'_')===-1?checkbox.checked:!checkbox.checked);
            }
            return false;
          }).trigger('click');
        }
        if ( $select.attr('data-uprid') ) {
          var check_inv = $('[name^="image_inventory\["]');
          selected_str = '_'+($.isArray(selected_now)?selected_now.join('_'):selected_now)+'_';
          check_inv.filter('[value="'+$select.attr('data-uprid')+'"]').filter(function(idx, checkbox) {
            var img_id = checkbox.name.match(/image_inventory\[(\d+)\]/);
            if ( img_id ) {
              return (selected_str.indexOf('_'+img_id[1]+'_')===-1?checkbox.checked:!checkbox.checked);
            }
            return false;
          }).trigger('click');
        }
      };
      $main_form.on('inventory_arrived',function(){
        $('select[name="divselktr"]').on('change', sync_images_check_state_back);
        sync_images_check_state();
      });

      $(document).on('click',function(e){
        if ( e.target && e.target.name ){
          var target_name = e.target.name;
          if ( target_name.indexOf('image_inventory[')===0 || target_name.indexOf('image_attr[')===0 ) {
            sync_images_check_state();
          }
        }
      });
      var rebuild_images = function() {
        var options = '';
        $('#images-listing li[prefix]').each(function () {
          var Key = $(this).attr('prefix').replace('image-box-', ''),
                  src = '';
          var $img = $(this).find('img[id="preview-box-' + Key + '"]');
          if ($img.length > 0) src = $img.attr('src');
          options += '<option value="'+Key+'" image="'+src+'" class="multSelktrImg"> </option>';
        });
        $('select[name="divselktr"]').html(options);
        $('select[name="divselktr"]').multiselect('refresh');
        sync_images_check_state();
      };
      $('#save_product_form').on('new_image_uploaded image_removed', rebuild_images);

    });



  </script>
</div>