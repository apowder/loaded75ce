

{function name=renderCategoriesTree level=0}
<ol class="categories_ul dd-list">
{foreach $items as $item name=foo}
<li class="dd-item dd3-item" data-id="{$item.id}">
    <div class="dd-handle handle">
        <i class="icon-hand-paper-o"></i>
    </div>
    <div class="dd3-content{if $item.id == $app->controller->view->category_id} selected{/if}">
        <span class="cat_li"><span id="{$item.id}" class="cat_text" onClick="changeCategory(this)">{$item.text}</span>
            <a href="{Yii::$app->urlManager->createUrl(['categories/categoryedit', 'categories_id' => $item.id])}" class="edit_cat"><i class="icon-pencil"></i></a>
            <a class="delete_cat" href="{Yii::$app->urlManager->createUrl(['categories/confirmcategorydelete', 'popup' => 1,'categories_id' => $item.id])}"><i class="icon-trash"></i></a>
            {if count($item.child) > 0}<span class="collapse_span"></span>{/if}
        </span>
    </div>
{if count($item.child) > 0}
{call name=renderCategoriesTree items=$item.child level=$level+1}
{/if}
</li>
{/foreach}
</ol>
{/function}

<div class="dd3-content"><span class="cat_li"><span id="0" class="cat_text" onClick="changeCategory(this)">{$smarty.const.TEXT_TOP}</span></span></div>
{call renderCategoriesTree items=$app->controller->view->categoriesTree}
