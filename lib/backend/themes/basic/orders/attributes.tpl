{use class="Yii"}

 {if $attributes|count > 0}
     <div id="product-attributes" class="attributes w-line-row-2 w-line-row-22">
  {foreach $attributes as $item}
  <div>
      <div class="edp-line">
          <label>{$item.title}:</label>
          <div>
              <select name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$item.title}" onchange="update_attributes(this.form);"} class="form-control">
                  <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
                  {foreach $item.options as $option}
                      <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                  {/foreach}
              </select>
          </div> 
      </div>
  </div>
  {/foreach}
  </div>
  {/if}
