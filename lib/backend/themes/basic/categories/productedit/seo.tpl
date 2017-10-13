
<div class="tabbable tabbable-custom">
  {if count($languages) > 1}
    <ul class="nav nav-tabs">
      {foreach $languages as $lKey => $lItem}
        <li{if $lKey == 0} class="active"{/if}><a href="#tab_9_{$lItem['id']}" class="flag-span" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
      {/foreach}
    </ul>
  {/if}
  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $pDescription  as $dKey => $dItem}
      <div class="tab-pane tab-seo{if $dKey == 0} active{/if}" id="tab_9_{$dItem['id']}">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_SEO_PAGE_NAME}</label>
          {$dItem['products_seo_page_name']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_SELF_SERVICE}</label>
          {$dItem['products_self_service']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PAGE_TITLE}</label>
          {$dItem['products_head_title_tag']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_HEADER_DESCRIPTION}</label>
          {$dItem['products_head_desc_tag']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_KEYWORDS}</label>
          {$dItem['products_head_keywords_tag']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_GOOGLE_PRODUCT_CATEGORY}</label>
          {$dItem['google_product_category']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_GOOGLE_PRODUCT_TYPE}</label>
          {$dItem['google_product_type']}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</label>
          <input type="text" name="products_old_seo_page_name" value="{$pInfo->products_old_seo_page_name}" class="form-control seo-input-field">
          <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
          {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
          <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
          {/if}
          <script>
          $(document).ready(function(){
            $('body').on('click', "#tab_9_{$dItem['id']} .icon-home", function(){
              $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
            });
            $('body').on('click', '#tab_9_{$dItem['id']} .icon-external-link', function(){
              $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
            });
            
            $('input[name=products_old_seo_page_name]').change(function(){
                $('input[name=products_old_seo_page_name]').val($(this).val());
            })
          })
          </script>
        </div>
        {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
           {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderProduct($pInfo->products_id, $dItem['id'])}
        {/if}
      </div>
    {/foreach}
  </div>
</div>