
<div class="btn-box-inv-price btn-market after">
  <span class="btn-xl-pr active" id="btn-xl-pr">{$smarty.const.FIELDSET_ASSIGNED_XSELL_PRODUCTS}</span>
  <span class="btn-up-pr" id="btn-up-pr">{$smarty.const.FIELDSET_ASSIGNED_UPSELL_PRODUCTS}</span>
  <span class="btn-gaw-pr" id="btn-gaw-pr">{$smarty.const.FIELDSET_ASSIGNED_AS_GIVEAWAY}</span>
</div>
<div class="xl-pr-box" id="box-xl-pr">
  <div class="after">
    <div class="attr-box attr-box-1">
      <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
        <div class="widget-header">
          <h4>{$smarty.const.FIND_PRODUCTS}</h4>
          <div class="box-head-serch after">
            <input type="search" id="xsell-search-by-products" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
            <button onclick="return false"></button>
          </div>
        </div>
        <div class="widget-content">
          <select id="xsell-search-products" size="25" style="width: 100%; height: 100%; border: none;" ondblclick="addSelectedXSell()">
          </select>
        </div>
      </div>
    </div>
    <div class="attr-box attr-box-2">
      <span class="btn btn-primary" onclick="addSelectedXSell()"></span>
    </div>
    <div class="attr-box attr-box-3">
      <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
        <div class="widget-header">
          <h4>{$smarty.const.FIELDSET_ASSIGNED_PRODUCTS}</h4>
          <div class="box-head-serch after">
            <input type="search" id="search-xp-assigned" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
            <button onclick="return false"></button>
          </div>
        </div>
        <div class="widget-content">
          <table class="table assig-attr-sub-table xsell-products">
            <thead>
            <tr role="row">
              <th></th>
              <th>{$smarty.const.TEXT_IMG}</th>
              <th>{$smarty.const.TEXT_LABEL_NAME}</th>
              <th>{$smarty.const.TEXT_PRICE}</th>
              <th></th>
            </tr>
            </thead>
            <tbody id="xp-assigned">
            {foreach $app->controller->view->xsellProducts as $xKey => $xsell}
              <tr role="row" prefix="xsell-box-{$xsell['xsell_id']}" class="{$xsell['status_class']}">
                <td class="sort-pointer"></td>
                <td class="img-ast img-ast-img">
                  {$xsell['image']}
                </td>
                <td class="name-ast name-ast-xl">
                  {$xsell['products_name']}
                  <input type="hidden" name="xsell_id[]" value="{$xsell['xsell_id']}" />
                </td>
                <td class="ast-price ast-price-xl">
                  {$xsell['price']}
                </td>
                <td class="remove-ast" onclick="deleteSelectedXSell(this)"></td>
              </tr>
            {/foreach}
            </tbody>
          </table>
          <input type="hidden" value="" name="xsell_sort_order" id="xsell_sort_order"/>
        </div>
      </div>
    </div>
  </div>
</div>
{if \common\helpers\Acl::checkExtension('UpSell', 'productBlock')}
    {\common\extensions\UpSell\UpSell::productBlock()}
{else}                           
    {include 'productedit/upsell.tpl'}
{/if}
<div class="gaw-pr-box" id="box-gaw-pr">
  {include 'give-away.tpl'}
</div>
<script type="text/javascript">
  function addSelectedXSell() {
    $( 'select#xsell-search-products option:selected' ).each(function() {
      var xsell_id = $(this).val();
      if ( $( 'input[name="xsell_id[]"][value="'+xsell_id+'"]' ).length ) {
        //already exist
      } else {
        $.post("{Yii::$app->urlManager->createUrl('categories/product-new-xsell')}", { 'products_id': xsell_id }, function(data, status){
          if (status == "success") {
            $( ".xsell-products tbody" ).append(data);

          } else {
            alert("Request error.");
          }
        },"html");
      }
    });

    return false;
  }

  function deleteSelectedXSell(obj) {
    $(obj).parent().remove();
    return false;
  }

  function addSelectedUpsell() {
    $( 'select#upsell-search-products option:selected' ).each(function() {
      var upsell_id = $(this).val();
      if ( $( 'input[name="upsell_id[]"][value="'+upsell_id+'"]' ).length ) {
        //already exist
      } else {
        $.post("{Yii::$app->urlManager->createUrl('categories/product-new-upsell')}", { 'products_id': upsell_id }, function(data, status){
          if (status == "success") {
            $( ".upsell-products tbody" ).append(data);
          } else {
            alert("Request error.");
          }
        },"html");
      }
    });

    return false;
  }

  function deleteSelectedUpsell(obj) {
    $(obj).parent().remove();
    return false;
  }

  var color = '#ff0000';
  var phighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }

  var searchHighlightExisting = function(e){
    var $rows = $(e.data.rows_selector);
    var search_term = $(this).val();
    $rows.each(function(){
      var $row = $(this);
      var $value_text = $row.find(e.data.text_selector);
      var search_match = true;

      if ( !$row.data('raw-value') ) $row.data('raw-value', $value_text.html());
      var prop_value = $row.data('raw-value');
      if ( search_term.length>0 ) {
        var searchRe = new RegExp(".*" + (search_term + "").replace(/([.?*+\^\$\[\]\\(){}|-])/g, "\\$1") + ".*", 'i');
        if (searchRe.test(prop_value)) {
          phighlight($value_text, search_term);
        } else {
          $value_text.html(prop_value);
          search_match = false;
        }
      }else{
        $value_text.html(prop_value);
      }

      if ( search_match ) {
        $row.show();
      }else{
        $row.hide();
      }
    });
  }


  $(document).ready(function() {
    $('#search-xp-assigned').on('focus keyup', { rows_selector: '#xp-assigned tr', text_selector: '.name-ast'}, searchHighlightExisting);
    $('#search-up-assigned').on('focus keyup', { rows_selector: '#up-assigned tr', text_selector: '.name-ast'}, searchHighlightExisting);

    $('#xsell-search-by-products').on('focus keyup', function(e) {
      var str = $(this).val();
      $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q="+encodeURIComponent(str)+"&not={$pInfo->products_id}", function( data ) {
        $( "select#xsell-search-products" ).html( data );
        psearch = new RegExp(str, 'i');
        $.each($('select#xsell-search-products').find('option'), function(i, e){
          if (psearch.test($(e).text())){
            phighlight(e, str);
          }
        });
      });
    }).keyup();

    $( ".xsell-products tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
      update: function( event, ui ) {
        var data = $(this).sortable('serialize', { attribute: "prefix" });
        $("#xsell_sort_order").val(data);
      },
    }).disableSelection();

    $('#upsell-search-by-products').on('focus keyup', function(e) {
      var str = $(this).val();
      $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q="+encodeURIComponent(str)+"&not={$pInfo->products_id}", function( data ) {
        $( "select#upsell-search-products" ).html( data );
        psearch = new RegExp(str, 'i');
        $.each($('select#upsell-search-products').find('option'), function(i, e){
          if (psearch.test($(e).text())){
            phighlight(e, str);
          }
        });
      });
    }).keyup();

    $( ".upsell-products tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
      update: function( event, ui ) {
        var data = $(this).sortable('serialize', { attribute: "prefix" });
        $("#upsell_sort_order").val(data);
      },
    }).disableSelection();

    function clickMarketingButton() { // shows/hides appropriate divs
      $('.btn-market span').each(function() {
        var div_id = this.id.replace('btn-', 'box-');
        if ($(this).hasClass('active') ) {
          $('#'+div_id).css('display', 'block');
        }else{
          $('#'+div_id).css('display', 'none');
        }
      });
    }
    clickMarketingButton();
    $('.btn-market span').click(function() {
      $('.btn-market span').removeClass('active');
      $(this).toggleClass('active');
      clickMarketingButton();
    });

  });


</script>