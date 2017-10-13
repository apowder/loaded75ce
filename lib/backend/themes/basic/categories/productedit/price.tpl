{use class="yii\helpers\Html"}
<div class="edp-pc-box after">
  <div class="cbox-left">
    <div class="edp-our-price-box">
      <div class="widget widget-full box box-no-shadow" style="margin-bottom: 0;">
        <div class="widget-header"><h4>{$smarty.const.TEXT_OUR_PRICE}</h4></div>
        <div class="widget-content">
          <div class="tax-cl">
            <label>{$smarty.const.TEXT_PRODUCTS_TAX_CLASS}</label>
            {Html::dropDownList('products_tax_class_id', $pInfo->products_tax_class_id, $app->controller->view->tax_classes, ['onchange'=>'updateGross()',  'class'=>'form-control'])}
          </div>
        </div>
      </div>
      <div class="widget box widget-not-full box-no-shadow" style="margin-bottom: 0; border-top: 0;">
        <div class="widget-content">
          <div class="tabbable tabbable-custom tab-content tab-content-vertical">
            {if $app->controller->view->useMarketPrices == true}
              <ul class="nav nav-tabs">
                {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
                  <li{if $app->controller->view->defaultCurrenciy == $currId} class="active"{/if}><a href="#markettab_{$currId}" data-toggle="tab"><span>{$currTitle}</span></a></li>
                {/foreach}
              </ul>
              {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
                <div class="tab-pane{if $app->controller->view->defaultCurrenciy == $currId} active{/if}" id="markettab_{$currId}">
                  <ul class="nav nav-tabs nav-tabs-vertical">
                    <li class="active"><a href="#markettab_{$currId}_0" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                    {if {$app->controller->view->groups|@count} > 0}
                      {foreach $app->controller->view->groups as $groups_id => $group}
                        <li><a href="#markettab_{$currId}_{$groups_id}" data-toggle="tab"><span>{$group['groups_name']}</span></a></li>
                      {/foreach}
                    {/if}
                  </ul>

                  <div class="tab-content tab-content-vertical">
                    <div class="tab-pane active" id="markettab_{$currId}_0">
                      <div class="our-pr-line after">
                        <div>
                          <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
                          {Html::textInput('products_price_'|cat:$currId, \common\helpers\Product::get_products_price_for_edit($pInfo->products_id, $currId), ['onKeyUp'=>'updateGross()', 'class'=>'form-control','id'=>'base_price'])}
                        </div>
                        <div class="disable-btn supplier-price-cost sbalr">
                          <label>&nbsp;</label>
                          <a href="javascript:void(0)" class="btn" onclick="return chooseSupplierPrice(0)">{$smarty.const.TEXT_PRICE_COST}</a>
                        </div>
                      </div>
                      <div class="our-pr-line after">
                        <div>
                          <label>{$smarty.const.TEXT_NET_PRICE}</label>
                          {Html::textInput('products_price_gross_'|cat:$currId, '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                        </div>
                      </div>
                      <div class="our-pr-line after our-pr-line-check-box dfullcheck">
                        <div>
                          <label>{$smarty.const.TEXT_ENABLE_SALE}</label>
                          {if $app->controller->view->defaultCurrenciy == $currId}
                            <input type="checkbox" value="1" name="specials_status" class="check_sale_prod" {if {$app->controller->view->sale['status'] > 0}} checked="checked" {/if} />
                          {/if}
                        </div>
                      </div>
                      <div class="our-pr-line after div_sale_prod" {if not {$app->controller->view->sale['status'] > 0}} style="display:none;" {/if}>
                        <div>
                          <label class="sale-info">{$smarty.const.TEXT_SALE}:</label>
                          {Html::textInput('specials_price_'|cat:$currId, \common\helpers\Product::get_specials_price($app->controller->view->sale['specials_id']), ['class'=>'form-control','id'=>'base_sale_price'])}
                        </div>
                        {if $app->controller->view->defaultCurrenciy == $currId}
                          <div class="disable-btn">
                            <label>{$smarty.const.TEXT_EXPIRY_DATE}</label>
                            {Html::textInput('specials_expires_date', \common\helpers\Date::datepicker_date($app->controller->view->sale['expires_date']), ['class'=>'datepicker form-control form-control-small'])}
                          </div>
                        {/if}
                      </div>
                      <div class="our-pr-line after our-pr-line-check-box dfullcheck">
                        <div>
                          <label>{$smarty.const.TEXT_ENABLE_POINTSE}</label>
                          {if $app->controller->view->defaultCurrenciy == $currId}
                            <input type="checkbox" value="1" name="bonus_points_status" class="check_points_prod" {if {min($pInfo->bonus_points_price, $pInfo->bonus_points_cost) > 0}} checked="checked" {/if} />
                          {/if}
                        </div>
                      </div>
                      <div class="our-pr-line after div_points_prod" {if not {min($pInfo->bonus_points_price, $pInfo->bonus_points_cost) > 0}} style="display:none;" {/if}>
                        <div>
                          <label>{$smarty.const.TEXT_BONUS_POINT}</label>
                          {Html::textInput('bonus_points_price_'|cat:$currId|cat:'_0', {$pInfo->bonus_points_price}, ['class'=>'form-control'])}
                        </div>
                        <div class="disable-btn">
                          <label>{$smarty.const.TEXT_POINTS_COST}</label>
                          {Html::textInput('bonus_points_cost_'|cat:$currId|cat:'_0', {$pInfo->bonus_points_cost}, ['class'=>'form-control'])}
                        </div>
                      </div>
                      <div class="our-pr-line after our-pr-line-check-box dfullcheck">
                        <div>
                          <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
                          {if $app->controller->view->defaultCurrenciy == $currId}
                            <input type="checkbox" value="1" name="qty_discount_status" class="check_qty_discount_prod" {if {strlen($pInfo->products_price_discount) > 0}} checked="checked" {/if} />
                          {/if}
                        </div>
                      </div>
                      <div class="wrap-quant-discount-{$currId}-0">

                        {if {$app->controller->view->qty_discounts[$currId][0]|@count} > 0}
                          {foreach $app->controller->view->qty_discounts[$currId][0] as $qty => $price}
                            <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                              <div>
                                <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                                {Html::textInput('discount_qty_'|cat:$currId|cat:'_0[]', $qty, ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                              </div><div>
                                <label>{$smarty.const.TEXT_NET}</label>
                                {Html::textInput('discount_price_'|cat:$currId|cat:'_0[]', $price, ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                              </div><div>
                                <label>{$smarty.const.TEXT_GROSS}</label>
                                {Html::textInput('discount_price_gross_'|cat:$currId|cat:'_0[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                              </div>
                              <span class="rem-quan-line"></span>
                            </div>
                          {/foreach}
                        {else}
                          <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                            <div>
                              <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                              {Html::textInput('discount_qty_'|cat:$currId|cat:'_0[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                            </div><div>
                              <label>{$smarty.const.TEXT_NET}</label>
                              {Html::textInput('discount_price_'|cat:$currId|cat:'_0[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                            </div><div>
                              <label>{$smarty.const.TEXT_GROSS}</label>
                              {Html::textInput('discount_price_gross_'|cat:$currId|cat:'_0[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                            </div>
                            <span class="rem-quan-line"></span>
                          </div>
                        {/if}
                      </div>
                      <div class="quant-discount-btn div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                        <span class="btn btn-add-more-{$currId}-0">{$smarty.const.TEXT_AND_MORE}</span>
                      </div>
                    </div>
                    {if {$app->controller->view->groups|@count} > 0}
                      {foreach $app->controller->view->groups as $groups_id => $group}
                        <div class="tab-pane" id="markettab_{$currId}_{$groups_id}">
                          <div class="js_group_price" data-group_id="{$groups_id}" data-group_discount="{$group['groups_discount']}">
                            <div class="our-pr-line after">
                              <label><input type="radio" name="popt_{$currId}_{$groups_id}" value="-2">{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
                              <label><input type="radio" name="popt_{$currId}_{$groups_id}" value="1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $group['groups_name'])}</label>
                              <label><input type="radio" name="popt_{$currId}_{$groups_id}" value="-1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $group['groups_name'])}</label>
                            </div>
                            <div class="js_price_block">
                              <div class="our-pr-line after">
                                <div>
                                  <label>{$smarty.const.TEXT_NET_PRICE}</label>
                                  {Html::textInput('products_groups_prices_'|cat:$currId|cat:'_'|cat:$groups_id, \common\helpers\Product::get_products_price_for_edit($pInfo->products_id, $currId, $groups_id, '-2'), ['onKeyUp'=>'updateGross()', 'class'=>'form-control js_price_input'])}
                                </div>
                                <div class="disable-btn supplier-price-cost">
                                  <label>&nbsp;</label>
                                  <a href="javascript:void(0)" class="btn" onclick="return chooseSupplierPrice({$groups_id})">{$smarty.const.TEXT_PRICE_COST}</a>
                                </div>
                              </div>
                              <div class="our-pr-line after">
                                <div>
                                  <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
                                  {Html::textInput('products_groups_prices_gross_'|cat:$currId|cat:'_'|cat:$groups_id, '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="js_price_dep">
                            <div class="our-pr-line after div_sale_prod js_group_price" {if not {$app->controller->view->sale['status'] > 0}} style="display:none;" {/if} data-group_id="{$groups_id}" data-group_discount="{if $group['apply_groups_discount_to_specials']}{$group['groups_discount']}{else}0{/if}">
                              <label class="sale-info">{$smarty.const.TEXT_SALE}:</label>
                              <div class="our-pr-line after">
                                <label><input type="radio" name="spopt_{$currId}_{$groups_id}" value="-2">{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
                                <label><input type="radio" name="spopt_{$currId}_{$groups_id}" value="1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $group['groups_name'])}</label>
                                <label><input type="radio" name="spopt_{$currId}_{$groups_id}" value="-1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $group['groups_name'])}</label>
                              </div>
                              <div class="js_price_block">
                                {Html::textInput('specials_groups_prices_'|cat:$currId|cat:'_'|cat:$groups_id, \common\helpers\Product::get_specials_price($app->controller->view->sale['specials_id'], 0, $groups_id, '-2'), ['class'=>'form-control js_price_input'])}
                              </div>
                            </div>
                            <div class="our-pr-line after div_points_prod" {if not {min($pInfo->bonus_points_price, $pInfo->bonus_points_cost) > 0}} style="display:none;" {/if}>
                              <div>
                                <label>{$smarty.const.TEXT_BONUS_POINT}</label>
                                {Html::textInput('bonus_points_price_'|cat:$currId|cat:'_'|cat:$groups_id, \common\helpers\Points::get_bonus_points_price($pInfo->products_id, 0, $groups_id, ''), ['class'=>'form-control'])}
                              </div>
                              <div class="disable-btn">
                                <label>{$smarty.const.TEXT_POINTS_COST}</label>
                                {Html::textInput('bonus_points_cost_'|cat:$currId|cat:'_'|cat:$groups_id, \common\helpers\Points::get_bonus_points_cost($pInfo->products_id, 0, $groups_id, ''), ['class'=>'form-control'])}
                              </div>
                            </div>
                            <div class="our-pr-line after our-pr-line-check-box div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                              <div>
                                <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
                              </div>
                            </div>
                            <div class="wrap-quant-discount-{$currId}-{$groups_id}">
                              {if {$app->controller->view->qty_discounts[$currId][$groups_id]|@count} > 0}
                                {foreach $app->controller->view->qty_discounts[$currId][$groups_id] as $qty => $price}
                                  <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                                    <div>
                                      <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                                      {Html::textInput('discount_qty_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', $qty, ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                                    </div><div>
                                      <label>{$smarty.const.TEXT_NET}</label>
                                      {Html::textInput('discount_price_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', $price, ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                                    </div><div>
                                      <label>{$smarty.const.TEXT_GROSS}</label>
                                      {Html::textInput('discount_price_gross_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                                    </div>
                                    <span class="rem-quan-line"></span>
                                  </div>
                                {/foreach}
                              {else}
                                <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                                  <div>
                                    <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                                    {Html::textInput('discount_qty_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                                  </div><div>
                                    <label>{$smarty.const.TEXT_NET}</label>
                                    {Html::textInput('discount_price_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                                  </div><div>
                                    <label>{$smarty.const.TEXT_GROSS}</label>
                                    {Html::textInput('discount_price_gross_'|cat:$currId|cat:'_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                                  </div>
                                  <span class="rem-quan-line"></span>
                                </div>
                              {/if}
                            </div>
                            <div class="quant-discount-btn div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                              <span class="btn btn-add-more-{$currId}-{$groups_id}">{$smarty.const.TEXT_AND_MORE}</span>
                            </div>
                          </div>
                        </div>
                      {/foreach}
                    {/if}
                  </div>

                </div>
              {/foreach}

            {else}
              <ul class="nav nav-tabs nav-tabs-vertical">
                <li class="active"><a href="#tab_3_0" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                {if {$app->controller->view->groups|@count} > 0}
                  {foreach $app->controller->view->groups as $groups_id => $group}
                    <li><a href="#tab_3_{$groups_id}" data-toggle="tab"><span>{$group['groups_name']}</span></a></li>
                  {/foreach}
                {/if}
              </ul>
              <div class="tab-content tab-content-vertical">
                <div class="tab-pane active" id="tab_3_0">
                  <div class="our-pr-line after">
                    <div>
                      <label>{$smarty.const.TEXT_NET_PRICE}</label>
                      {Html::textInput('products_price', \common\helpers\Product::get_products_price_for_edit($pInfo->products_id), ['onKeyUp'=>'updateGross()', 'class'=>'form-control','id'=>'base_price'])}
                    </div>
                    <div class="disable-btn supplier-price-cost sbalr">
                      <label>&nbsp;</label>
                      <a href="javascript:void(0)" class="btn" onclick="return chooseSupplierPrice(0)">{$smarty.const.TEXT_PRICE_COST}</a>
                    </div>
                  </div>
                  <div class="our-pr-line after">
                    <div>
                      <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
                      {Html::textInput('products_price_gross', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                    </div>
                  </div>
                  <div class="our-pr-line after our-pr-line-check-box dfullcheck">
                    <div>
                      <label>{$smarty.const.TEXT_ENABLE_SALE}</label>
                      <input type="checkbox" value="1" name="specials_status" class="check_sale_prod" {if {$app->controller->view->sale['status'] > 0}} checked="checked" {/if} />
                    </div>
                  </div>
                  <div class="our-pr-line after div_sale_prod" {if not {$app->controller->view->sale['status'] > 0}} style="display:none;" {/if}>
                    <div>
                      <label class="sale-info">{$smarty.const.TEXT_SALE}:</label>
                      {Html::textInput('specials_price', \common\helpers\Product::get_specials_price($app->controller->view->sale['specials_id']), ['class'=>'form-control','id'=>'base_sale_price'])}
                    </div>
                    <div class="disable-btn">
                      <label>{$smarty.const.TEXT_EXPIRY_DATE}</label>
                      {Html::textInput('specials_expires_date', \common\helpers\Date::datepicker_date($app->controller->view->sale['expires_date']), ['class'=>'datepicker form-control form-control-small'])}
                    </div>
                  </div>
                  <div class="our-pr-line after our-pr-line-check-box dfullcheck">
                    <div>
                      <label>{$smarty.const.TEXT_ENABLE_POINTSE}</label>
                      <input type="checkbox" value="1" name="bonus_points_status" class="check_points_prod" {if {min($pInfo->bonus_points_price, $pInfo->bonus_points_cost) > 0}} checked="checked" {/if} />
                    </div>
                  </div>
                  <div class="our-pr-line after div_points_prod" {if not {min($pInfo->bonus_points_price, $pInfo->bonus_points_cost) > 0}} style="display:none;" {/if}>
                    <div>
                      <label>{$smarty.const.TEXT_BONUS_POINT}</label>
                      {Html::textInput('bonus_points_price', {$pInfo->bonus_points_price}, ['class'=>'form-control'])}
                    </div>
                    <div class="disable-btn">
                      <label>{$smarty.const.TEXT_POINTS_COST}</label>
                      {Html::textInput('bonus_points_cost', {$pInfo->bonus_points_cost}, ['class'=>'form-control'])}
                    </div>
                  </div>
                  <div class="our-pr-line after our-pr-line-check-box dfullcheck">
                    <div>
                      <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
                      <input type="checkbox" value="1" name="qty_discount_status" class="check_qty_discount_prod" {if {strlen($pInfo->products_price_discount) > 0}} checked="checked" {/if} />
                    </div>
                  </div>
                  <div class="wrap-quant-discount">

                    {if {$app->controller->view->qty_discounts[0]|@count} > 0}
                      {foreach $app->controller->view->qty_discounts[0] as $qty => $price}
                        <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                          <div>
                            <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                            {Html::textInput('discount_qty[]', $qty, ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                          </div><div>
                            <label>{$smarty.const.TEXT_NET}</label>
                            {Html::textInput('discount_price[]', $price, ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                          </div><div>
                            <label>{$smarty.const.TEXT_GROSS}</label>
                            {Html::textInput('discount_price_gross[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                          </div>
                          <span class="rem-quan-line"></span>
                        </div>
                      {/foreach}
                    {else}
                      <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                        <div>
                          <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                          {Html::textInput('discount_qty[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                        </div><div>
                          <label>{$smarty.const.TEXT_NET}</label>
                          {Html::textInput('discount_price[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                        </div><div>
                          <label>{$smarty.const.TEXT_GROSS}</label>
                          {Html::textInput('discount_price_gross[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                        </div>
                        <span class="rem-quan-line"></span>
                      </div>
                    {/if}
                  </div>
                  <div class="quant-discount-btn div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                    <span class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span>
                  </div>
                </div>
                {if {$app->controller->view->groups|@count} > 0}
                  {foreach $app->controller->view->groups as $groups_id => $group}
                    <div class="tab-pane" id="tab_3_{$groups_id}">
                      <div class="js_group_price" data-group_id="{$groups_id}" data-group_discount="{$group['groups_discount']}">
                        <div class="our-pr-line after">
                          <label><input type="radio" name="popt_{$groups_id}" value="-2">{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
                          <label><input type="radio" name="popt_{$groups_id}" value="1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $group['groups_name'])}</label>
                          <label><input type="radio" name="popt_{$groups_id}" value="-1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $group['groups_name'])}</label>
                        </div>
                        <div class="js_price_block">
                          <div class="our-pr-line after">
                            <div>
                              <label>{$smarty.const.TEXT_NET_PRICE}</label>
                              {Html::textInput('products_groups_prices_'|cat:$groups_id, \common\helpers\Product::get_products_price_for_edit($pInfo->products_id, 0, $groups_id, '-2'), ['onKeyUp'=>'updateGross()', 'class'=>'form-control js_price_input'])}
                            </div>
                            <div class="disable-btn supplier-price-cost">
                              <label>&nbsp;</label>
                              <a href="javascript:void(0)" class="btn" onclick="return chooseSupplierPrice({$groups_id})">{$smarty.const.TEXT_PRICE_COST}</a>
                            </div>
                          </div>
                          <div class="our-pr-line after">
                            <div>
                              <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
                              {Html::textInput('products_groups_prices_gross_'|cat:$groups_id, '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="js_price_dep">
                        <div class="our-pr-line after div_sale_prod js_group_price" {if not {$app->controller->view->sale['status'] > 0}} style="display:none;" {/if} data-group_id="{$groups_id}" data-group_discount="{if $group['apply_groups_discount_to_specials']}{$group['groups_discount']}{else}0{/if}">
                          <label class="sale-info">{$smarty.const.TEXT_SALE}:</label>
                          <div class="our-pr-line after">
                            <label><input type="radio" name="spopt_{$groups_id}" value="-2">{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
                            <label><input type="radio" name="spopt_{$groups_id}" value="1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $group['groups_name'])}</label>
                            <label><input type="radio" name="spopt_{$groups_id}" value="-1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $group['groups_name'])}</label>
                          </div>
                          <div class="js_price_block">
                            {Html::textInput('specials_groups_prices_'|cat:$groups_id, \common\helpers\Product::get_specials_price($app->controller->view->sale['specials_id'], 0, $groups_id, '-2'), ['class'=>'form-control js_price_input'])}
                          </div>
                        </div>
                        <div class="our-pr-line after div_points_prod" {if not {min($pInfo->bonus_points_price, $pInfo->bonus_points_cost) > 0}} style="display:none;" {/if}>
                          <div>
                            <label>{$smarty.const.TEXT_BONUS_POINT}</label>
                            {Html::textInput('bonus_points_price_'|cat:$groups_id, \common\helpers\Points::get_bonus_points_price($pInfo->products_id, 0, $groups_id, ''), ['class'=>'form-control'])}
                          </div>
                          <div class="disable-btn">
                            <label>{$smarty.const.TEXT_POINTS_COST}</label>
                            {Html::textInput('bonus_points_cost_'|cat:$groups_id, \common\helpers\Points::get_bonus_points_cost($pInfo->products_id, 0, $groups_id, ''), ['class'=>'form-control'])}
                          </div>
                        </div>
                        <div class="our-pr-line after our-pr-line-check-box div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                          <div>
                            <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
                          </div>
                        </div>
                        <div class="wrap-quant-discount-{$groups_id}">
                          {if {$app->controller->view->qty_discounts[$groups_id]|@count} > 0}
                            {foreach $app->controller->view->qty_discounts[$groups_id] as $qty => $price}
                              <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                                <div>
                                  <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                                  {Html::textInput('discount_qty_'|cat:$groups_id|cat:'[]', $qty, ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                                </div><div>
                                  <label>{$smarty.const.TEXT_NET}</label>
                                  {Html::textInput('discount_price_'|cat:$groups_id|cat:'[]', $price, ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                                </div><div>
                                  <label>{$smarty.const.TEXT_GROSS}</label>
                                  {Html::textInput('discount_price_gross_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                                </div>
                                <span class="rem-quan-line"></span>
                              </div>
                            {/foreach}
                          {else}
                            <div class="quant-discount-line after div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                              <div>
                                <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                                {Html::textInput('discount_qty_'|cat:$groups_id|cat:'[]', '', ['onchange'=>'updateInventoryBox()', 'class'=>'form-control'])}
                              </div><div>
                                <label>{$smarty.const.TEXT_NET}</label>
                                {Html::textInput('discount_price_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateGross()', 'class'=>'form-control'])}
                              </div><div>
                                <label>{$smarty.const.TEXT_GROSS}</label>
                                {Html::textInput('discount_price_gross_'|cat:$groups_id|cat:'[]', '', ['onKeyUp'=>'updateNet()', 'class'=>'form-control'])}
                              </div>
                              <span class="rem-quan-line"></span>
                            </div>
                          {/if}
                        </div>
                        <div class="quant-discount-btn div_qty_discount_prod" {if not {strlen($pInfo->products_price_discount) > 0}} style="display:none;" {/if}>
                          <span class="btn btn-add-more-{$groups_id}">{$smarty.const.TEXT_AND_MORE}</span>
                        </div>
                      </div>
                    </div>
                  {/foreach}
                {/if}
              </div>
            {/if}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="cbox-right">
    <div class="widget box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header"><h4>{$smarty.const.TEXT_SUPPLIER_COST}</h4></div>
      <div class="widget-content" id="suppliers-placeholder">
        {if {$app->controller->view->suppliers|@count} > 0}
          {foreach $app->controller->view->suppliers as $suppliers_id => $supplier}
            {include file="supplierproduct.tpl" sInfo=$supplier}
          {/foreach}
        {/if}
        <div class="ed-sup-btn-box">
          <a href="{Yii::$app->urlManager->createUrl('categories/supplier-select')}" class="btn select_supplier">{$smarty.const.TEXT_SELECT_ADD_SUPPLIER}</a>
        </div>
        <script type="text/javascript">
          $(document).ready(function() {
            $('.select_supplier').popUp({
              box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_SELECT_ADD_SUPPLIER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
            });
          });
        </script>
        <script type="text/javascript">
          $(document).ready(function() {
            $('.js_group_price').on('change_state',function(event, state){
              var $block = $(this);
              var $all_input = $block.find('[name^="products_groups_prices_"]');
              var base_ref = '#base_price';
              if ( $all_input.length==0 ) {
                $all_input = $block.find('[name^="specials_groups_prices_"]');
                base_ref = '#base_sale_price';
              }
              var $main_input = $block.find('.js_price_input');
              //
              var base_val = parseFloat($(base_ref).val()) || 0;
              if ( base_ref=='#base_sale_price' && $(base_ref).val().indexOf('%')!==-1 ) {
                var main_price = $('#base_price').val(),
                        base_percent = parseFloat($(base_ref).val().substring(0,$(base_ref).val().indexOf('%')));
                base_val = main_price - ((base_percent/100)*main_price);
              }
              var new_val = ((100-parseFloat($block.attr('data-group_discount')))/100*base_val);
              //
              var $dep_block = $block.closest('.tab-pane').find('.js_price_dep');
              if (base_ref == '#base_sale_price') $dep_block = $([]);
              if ( parseFloat(state)==-1 ) {
                $all_input.removeAttr('readonly');
                $all_input.removeAttr('disabled');
                $main_input.val('-1');
                $block.find('.js_price_block').hide();
                $dep_block.hide();
              }else if(parseFloat(state)==-2){
                if ( $dep_block.is(':hidden') ) $dep_block.show();
                $all_input.removeAttr('readonly');
                $all_input.removeAttr('disabled');
                $main_input.val(new_val);
                $main_input.trigger('keyup');
                $all_input.attr({ readonly:'readonly',disabled:'disabled' });
                $block.find('.js_price_block').show();
              }else{
                if ( $dep_block.is(':hidden') ) $dep_block.show();
                $all_input.removeAttr('readonly');
                $all_input.removeAttr('disabled');
                if ( parseFloat($main_input.val())<=0 ) {
                  $main_input.val(new_val);
                  $main_input.trigger('keyup');
                }
                $block.find('.js_price_block').show();
              }
            });

            $('.js_group_price [name^="popt_"]').on('click',function(){
              $(this).parents('.js_group_price').trigger('change_state',[$(this).val()]);
              if ( parseFloat($(this).val()) ==-1) {
                $('.js_group_price').find('[name^="s'+this.name+'"]').filter('[value="-1"]').trigger('click');
              }
            });
            $('.js_group_price [name^="spopt_"]').on('click',function(){
              $(this).parents('.js_group_price').trigger('change_state',[$(this).val()]);
            });
            // init on load
            $('.js_group_price').each(function(){
              var $main_input = $(this).find('.js_price_input');
              var switch_name_locate = ($main_input.length>0 && $main_input[0].name.indexOf('specials_groups_prices_')===0)?'spopt_':'popt_';
              var price = parseFloat($main_input.val());
              if (price==-1) {
                $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-1"]').trigger('click');
              }else if (price==-2) {
                $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-2"]').trigger('click');
              }else {
                $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="1"]').trigger('click');
              }
              //$(this).trigger('change_state',[]);
            });
            $('#base_price').on('change',function(){
              $('.js_group_price [name^="popt_"]').filter('[value="-2"]').trigger('click');
            });
            $('#base_sale_price').on('change',function(){
              $('.js_group_price [name^="spopt_"]').filter('[value="-2"]').trigger('click');
            });
          });
        </script>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  //===== Price and Cost START =====//
  var tax_rates = new Array();
  {if {$app->controller->view->tax_classes|@count} > 0}
  {foreach $app->controller->view->tax_classes as $tax_class_id => $tax_class}
  tax_rates[{$tax_class_id}] = {\common\helpers\Tax::get_tax_rate_value($tax_class_id)};
  {/foreach}
  {/if}

  function doRound(x, places) {
    return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
  }

  function getTaxRate() {
    var selected_value = document.forms['product_edit'].products_tax_class_id.selectedIndex;
    var parameterVal = document.forms['product_edit'].products_tax_class_id[selected_value].value;

    if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
      return tax_rates[parameterVal];
    } else {
      return 0;
    }
  }

  {if $app->controller->view->useMarketPrices == true}
  function updateGross() {
    var taxRate = getTaxRate();
    {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
    var grossValue = document.forms['product_edit'].products_price_{$currId}.value;
    if (taxRate > 0) {
      grossValue = grossValue * ((taxRate / 100) + 1);
    }
    document.forms['product_edit'].products_price_gross_{$currId}.value = doRound(grossValue, 6);
    {/foreach}
  }
  function updateNet() {
    var taxRate = getTaxRate();
    {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
    var netValue = document.forms['product_edit'].products_price_gross_{$currId}.value;
    if (taxRate > 0) {
      netValue = netValue / ((taxRate / 100) + 1);
    }
    document.forms['product_edit'].products_price_{$currId}.value = doRound(netValue, 6);
    {/foreach}
  }
  {else}
  function updateGross() {
    var taxRate = getTaxRate();
    var grossValue = document.forms['product_edit'].products_price.value;

    if (taxRate > 0) {
      grossValue = grossValue * ((taxRate / 100) + 1);
    }

    document.forms['product_edit'].products_price_gross.value = doRound(grossValue, 6);

    var arrValue = [];
    $('[name="discount_price[]"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name="discount_price_gross[]"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="inventoryprice_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventorygrossprice_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="inventoryfullprice_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventorygrossfullprice_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="pack_unit_full_prices"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0 && e.value != '') {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name^="pack_unit_full_gross_prices"]').each(function(i, e) {
      if (arrValue[i] == '') {
        e.value = arrValue[i];
      } else {
        e.value = doRound(arrValue[i], 6);
      }
    });

    var arrValue = [];
    $('[name^="packaging_full_prices"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0 && e.value != '') {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name^="packaging_full_gross_prices"]').each(function(i, e) {
      if (arrValue[i] == '') {
        e.value = arrValue[i];
      } else {
        e.value = doRound(arrValue[i], 6);
      }
    });

    var arrValue = [];
    $('[name^="inventory_discount_price_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventory_discount_gross_price_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="inventory_discount_full_price_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventory_discount_full_gross_price_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    {if {$app->controller->view->groups|@count} > 0}
    {foreach $app->controller->view->groups as $groups_id => $group}

    var fieldValue = document.forms['product_edit'].elements['products_groups_prices_{$groups_id}'].value
    if (fieldValue == -1) {
      document.forms['product_edit'].elements['products_groups_prices_gross_{$groups_id}'].value = doRound(fieldValue, 6);
    } else {
      {if \common\helpers\Acl::checkExtension('BusinessToBusiness', 'productBlock')}
          {\common\extensions\BusinessToBusiness\BusinessToBusiness::productBlock($group)}
      {else}
      if (taxRate > 0) {
        fieldValue = fieldValue * ((taxRate / 100) + 1);
      }
      {/if}
      document.forms['product_edit'].elements['products_groups_prices_gross_{$groups_id}'].value = doRound(fieldValue, 6);
    }

    var arrValue = [];
    $('[name="discount_price_{$groups_id}[]"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
      }
    });
    $('[name="discount_price_gross_{$groups_id}[]"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    {/foreach}
    {/if}

    $('[name^="suppliers_id["]').each(function(i, e) {
      updateSupplierPrices(e.value);
    });

    $('[name^="suppliers_id_"]').each(function(i, e) {
      var uprid = e.name.replace('suppliers_id_', '').replace('[' + e.value + ']', '');
      updateSupplierPricesInv(e.value, uprid);
    });
  }

  function updateNet() {
    var taxRate = getTaxRate();
    var netValue = document.forms['product_edit'].products_price_gross.value;

    if (taxRate > 0) {
      netValue = netValue / ((taxRate / 100) + 1);
    }

    document.forms['product_edit'].products_price.value = doRound(netValue, 6);

    var arrValue = [];
    $('[name="discount_price_gross[]"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name="discount_price[]"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="inventorygrossprice_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventoryprice_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="inventorygrossfullprice_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventoryfullprice_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="pack_unit_full_gross_prices"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0 && e.value != '') {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name^="pack_unit_full_prices"]').each(function(i, e) {
      if (arrValue[i] == '') {
        e.value = arrValue[i];
      } else {
        e.value = doRound(arrValue[i], 6);
      }
    });

    var arrValue = [];
    $('[name^="packaging_full_gross_prices"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0 && e.value != '') {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name^="packaging_full_prices"]').each(function(i, e) {
      if (arrValue[i] == '') {
        e.value = arrValue[i];
      } else {
        e.value = doRound(arrValue[i], 6);
      }
    });

    var arrValue = [];
    $('[name^="inventory_discount_gross_price_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventory_discount_price_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    var arrValue = [];
    $('[name^="inventory_discount_full_gross_price_"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name^="inventory_discount_full_price_"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    {if {$app->controller->view->groups|@count} > 0}
    {foreach $app->controller->view->groups as $groups_id => $group}

    var fieldValue = document.forms['product_edit'].elements['products_groups_prices_gross_{$groups_id}'].value
    if (fieldValue == -1) {
      document.forms['product_edit'].elements['products_groups_prices_{$groups_id}'].value = doRound(fieldValue, 6);
    } else {
      {if {$group['groups_is_tax_applicable']} > 0}
      if (taxRate > 0) {
        fieldValue = fieldValue / ((taxRate / 100) + 1);
      }
      {/if}
      document.forms['product_edit'].elements['products_groups_prices_{$groups_id}'].value = doRound(fieldValue, 6);
    }

    var arrValue = [];
    $('[name="discount_price_gross_{$groups_id}[]"]').each(function(i, e) {
      arrValue[i] = e.value;
      if (taxRate > 0) {
        arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
      }
    });
    $('[name="discount_price_{$groups_id}[]"]').each(function(i, e) {
      e.value = doRound(arrValue[i], 6);
    });

    {/foreach}
    {/if}

    $('[name^="suppliers_id["]').each(function(i, e) {
      updateSupplierPrices(e.value);
    });

    $('[name^="suppliers_id_"]').each(function(i, e) {
      var uprid = e.name.replace('suppliers_id_', '').replace('[' + e.value + ']', '');
      updateSupplierPricesInv(e.value, uprid);
    });
  }
  {/if}

  $(document).ready(function() {
    updateGross();
  });

  function currencyFormat(num) {
    var sep_th = {$default_currency['thousands_point']|json_encode};
    var sep_dec = {$default_currency['decimal_point']|json_encode};
    var symbol_right = {$default_currency['symbol_right']|json_encode};
    var symbol_left = {$default_currency['symbol_left']|json_encode};
    var decimal_places = {$default_currency['decimal_places']|json_encode};
    var sign = '';
    if (num < 0) {
      num = Math.abs(num);
      sign = '-';
    }
    num = Math.round(num * Math.pow(10, decimal_places*1)) / Math.pow(10, decimal_places*1); // round
    var s = new String(num);
    p=s.indexOf('.');
    n=s.indexOf(',');
    var j = Math.floor(num);
    var s1 = new String(j);
    if (p>0 || n>0) {
      if (p>0) {
        s = s.replace('.', sep_dec);
      } else {
        s = s.replace(',', sep_dec);
      }
    }
    var j2 = Math.floor(num * 10);
    if (j == num) {
      s = s + sep_dec + '0000';
    } else if (j2 == num * 10) {
      s = s + '000';
    }
    var l = s1.length;
    var n = Math.floor((l-1)/3);
    while (n >= 1) {
      s = s.substring(0, s.indexOf(sep_dec)-(3*n)) + sep_th + s.substring(s.indexOf(sep_dec)-(3*n), s.length);
      n--;
    }
    s = s.substring(0, s.indexOf(sep_dec) + decimal_places * 1 + 1);
    s = sign + symbol_left + s + symbol_right;
    return s;
  }

  function updateSupplierPrices(id) {
    var taxRate = getTaxRate();
    var supplierPrice = doRound(document.forms['product_edit'].elements['suppliers_price[' + id + ']'].value, 6);
    var supplierDiscount = doRound(document.forms['product_edit'].elements['supplier_discount[' + id + ']'].value, 6);
    var supplierSurcharge = doRound(document.forms['product_edit'].elements['suppliers_surcharge_amount[' + id + ']'].value, 6);
    var supplierMargin = doRound(document.forms['product_edit'].elements['suppliers_margin_percentage[' + id + ']'].value, 6);
    var supplierCostPrice = doRound(supplierPrice * (1 - supplierDiscount / 100), 6);

    var calcNetPrice = supplierCostPrice * (1 + supplierMargin / 100) + supplierSurcharge;
    var calcGrossPrice = calcNetPrice * ((taxRate / 100) + 1);
    var calcProfit = calcNetPrice - supplierCostPrice;

    var ourNetPrice = doRound(document.forms['product_edit'].products_price.value, 6);
    var ourGrossPrice = ourNetPrice * ((taxRate / 100) + 1);
    var ourProfit = ourNetPrice - supplierCostPrice;

    $('#supplier_price_' + id).html(currencyFormat(supplierPrice));
    $('#supplier_cost_price_' + id).html(currencyFormat(supplierCostPrice));

    $('#calc_net_price_' + id).html(currencyFormat(calcNetPrice));
    $('#calc_gross_price_' + id).html(currencyFormat(calcGrossPrice));
    $('#calc_profit_' + id).html(currencyFormat(calcProfit));

    $('#our_net_price_' + id).html(currencyFormat(ourNetPrice));
    $('#our_gross_price_' + id).html(currencyFormat(ourGrossPrice));
    $('#our_profit_' + id).html(currencyFormat(ourProfit));

    if (doRound(document.forms['product_edit'].products_price.value, 2) == doRound(calcNetPrice, 2)) {
      $('#calc_div_' + id).hide();
      $('.calc_div_width_' + id).addClass('tab-sup03-width-full');
    } else {
      $('#calc_div_' + id).show();
      $('.calc_div_width_' + id).removeClass('tab-sup03-width-full');
    }

    getCountSuppliersPrices();
  }

  function deleteSupplier(id) {
    $('#suppliers-' + id).remove();
    getCountSuppliersPrices();
  }

  var countSuppliersPrices = 0;
  function getCountSuppliersPrices() {
    countSuppliersPrices = 0;
    $('[name^="suppliers_id["]').each(function(i, e) {
      var supplierPrice = doRound(document.forms['product_edit'].elements['suppliers_price[' + e.value + ']'].value, 6);
      if (supplierPrice > 0) {
        countSuppliersPrices++;
      }
    });
    if (countSuppliersPrices > 0) {
      $('.supplier-price-cost').removeClass('disable-btn');
    } else {
      $('.supplier-price-cost').addClass('disable-btn');
    }
  }

  function chooseSupplierPrice(group_id) {
    if (countSuppliersPrices > 1) {
      $.post('{Yii::$app->urlManager->createUrl('categories/supplier-price')}?gID=' + group_id, $('#save_product_form').serialize(), function(data, status) {
        if (status == "success") {
          $('body').append("<div class='popup-box-wrap' style='top:200px;'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>{$smarty.const.TEXT_SELECT_PRICE_FROM_SUPPLIER}</div><div class='pop-up-content'><div class='popupCategory'>" + data + "</div></div></div></div>");
        } else {
          alert("Request error.");
        }
      },"html");
      return false;
    } else {
      $('[name^="suppliers_id["]').each(function(i, e) {
        var supplierPrice = doRound(document.forms['product_edit'].elements['suppliers_price[' + e.value + ']'].value, 6);
        if (supplierPrice > 0) {
          selectSupplierPrice(e.value, group_id);
        }
      });
    }
  }

  function selectSupplierPrice(id, group_id) {
    var supplierPrice = doRound(document.forms['product_edit'].elements['suppliers_price[' + id + ']'].value, 6);
    if (supplierPrice > 0) {
      var supplierDiscount = doRound(document.forms['product_edit'].elements['supplier_discount[' + id + ']'].value, 6);
      var supplierSurcharge = doRound(document.forms['product_edit'].elements['suppliers_surcharge_amount[' + id + ']'].value, 6);
      var supplierMargin = doRound(document.forms['product_edit'].elements['suppliers_margin_percentage[' + id + ']'].value, 6);
      var supplierCostPrice = doRound(supplierPrice * (1 - supplierDiscount / 100), 6);
      var calcNetPrice = supplierCostPrice * (1 + supplierMargin / 100) + supplierSurcharge;
      if (group_id > 0) {
        var input = document.forms['product_edit'].elements['products_groups_prices_' + group_id];
        $(input).parents('.js_group_price').trigger('change_state',[calcNetPrice]);
        input.value = doRound(calcNetPrice, 6);
      } else {
        document.forms['product_edit'].products_price.value = doRound(calcNetPrice, 6);
      }
      updateGross();
    }
  }

  function deleteSupplierInv(id, uprid) {
    $('#suppliers' + uprid + '-' + id).remove();
//  getCountSuppliersPricesInv(uprid);
  }

  function updateSupplierPricesInv(id, uprid) {
    {literal}
    var uprid_repl = uprid.replace(/{/g, '-').replace(/}/g, '-');
    {/literal}
    var taxRate = getTaxRate();
    var supplierPrice = doRound(document.forms['product_edit'].elements['suppliers_price_' + uprid + '[' + id + ']'].value, 6);
    var supplierDiscount = doRound(document.forms['product_edit'].elements['supplier_discount_' + uprid + '[' + id + ']'].value, 6);
    var supplierSurcharge = doRound(document.forms['product_edit'].elements['suppliers_surcharge_amount_' + uprid + '[' + id + ']'].value, 6);
    var supplierMargin = doRound(document.forms['product_edit'].elements['suppliers_margin_percentage_' + uprid + '[' + id + ']'].value, 6);
    var supplierCostPrice = doRound(supplierPrice * (1 - supplierDiscount / 100), 6);

    var calcNetPrice = supplierCostPrice * (1 + supplierMargin / 100) + supplierSurcharge;
    var calcGrossPrice = calcNetPrice * ((taxRate / 100) + 1);
    var calcProfit = calcNetPrice - supplierCostPrice;

    if ($('#full_add_price').val() > 0) {
      var ourNetPrice = doRound(document.forms['product_edit'].elements['inventoryfullprice_' + uprid + '[0]'].value, 6);
    } else {
      var ourNetPrice = doRound(document.forms['product_edit'].products_price.value, 6) + doRound(document.forms['product_edit'].elements['inventoryprice_' + uprid + '[0]'].value, 6);
    }
    var ourGrossPrice = ourNetPrice * ((taxRate / 100) + 1);
    var ourProfit = ourNetPrice - supplierCostPrice;

    $('#supplier_price_' + uprid_repl + '_' + id).html(currencyFormat(supplierPrice));
    $('#supplier_cost_price_' + uprid_repl + '_' + id).html(currencyFormat(supplierCostPrice));

    $('#calc_net_price_' + uprid_repl + '_' + id).html(currencyFormat(calcNetPrice));
    $('#calc_gross_price_' + uprid_repl + '_' + id).html(currencyFormat(calcGrossPrice));
    $('#calc_profit_' + uprid_repl + '_' + id).html(currencyFormat(calcProfit));

    $('#our_net_price_' + uprid_repl + '_' + id).html(currencyFormat(ourNetPrice));
    $('#our_gross_price_' + uprid_repl + '_' + id).html(currencyFormat(ourGrossPrice));
    $('#our_profit_' + uprid_repl + '_' + id).html(currencyFormat(ourProfit));

    if (doRound(ourNetPrice, 2) == doRound(calcNetPrice, 2)) {
      $('#calc_div_' + uprid_repl + '_' + id).hide();
      $('.calc_div_width_' + uprid_repl + '_' + id).addClass('tab-sup03-width-full');
    } else {
      $('#calc_div_' + uprid_repl + '_' + id).show();
      $('.calc_div_width_' + uprid_repl + '_' + id).removeClass('tab-sup03-width-full');
    }
//  getCountSuppliersPricesInv(uprid);
  }

  $('.check_sale_prod').bootstrapSwitch({
    onSwitchChange: function (element, argument) {
      if (argument) {
        $('.div_sale_prod').show();
      } else {
        $('.div_sale_prod').hide();
      }
      return true;
    },
    onText: "{$smarty.const.SW_ON}",
    offText: "{$smarty.const.SW_OFF}",
    handleWidth: '20px',
    labelWidth: '24px'
  });

  $('.check_points_prod').bootstrapSwitch({
    onSwitchChange: function (element, argument) {
      if (argument) {
        $('.div_points_prod').show();
      } else {
        $('.div_points_prod').hide();
      }
      return true;
    },
    onText: "{$smarty.const.SW_ON}",
    offText: "{$smarty.const.SW_OFF}",
    handleWidth: '20px',
    labelWidth: '24px'
  });

  $('.check_qty_discount_prod').bootstrapSwitch({
    onSwitchChange: function (element, argument) {
      if (argument) {
        $('.div_qty_discount_prod').show();
      } else {
        $('.div_qty_discount_prod').hide();
      }
      return true;
    },
    onText: "{$smarty.const.SW_ON}",
    offText: "{$smarty.const.SW_OFF}",
    handleWidth: '20px',
    labelWidth: '24px'
  });
  //===== Price and Cost END =====//
</script>