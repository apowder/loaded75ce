<ul class="tabNavigation" id="menuSwitcher">
    <li><a href="{$app->urlManager->createUrl("index")}" class="basic">{$smarty.const.TEXT_EVERYDAY_ACTIVITIES}</a></li>
    <li><a href="#" class="advanced active">{$smarty.const.TEXT_FULL_MENU}</a></li>
</ul>
<ul id="nav">
    <li{if $context->selectedMenu[0] == "index"} class="current"{/if}>
        <a href="{$app->urlManager->createUrl("index")}">
            <i class="icon-dashboard"></i>
            {$smarty.const.TEXT_DASHBOARD}
        </a>
    </li>
    {foreach $context->box_files_list as $item_menu}
    <li{if $context->selectedMenu[0] == $item_menu[0]} class="current"{/if}>
        <a href="javascript:void(0);">
            <i class="icon-{$item_menu[0]}"></i>
            <span>{$item_menu[2]}</span>
        </a>
        <ul class="sub-menu">
        {foreach $item_menu[3] as $item_submenu}
            <li class="{if $context->selectedMenu[1] == $item_submenu[0]}current{/if}{if $item_submenu[4]} dis_module{/if}">
                <a href="{$app->urlManager->createUrl($item_submenu[0])}">
                    <span>{$item_submenu[2]}</span>
                </a>
                {if $item_submenu[3]}
                    <ul class="sub-menu">
                    {foreach $item_submenu[3] as $item_subsubmenu}
                    <li class="{if $context->selectedMenu[2] == $item_subsubmenu[0]}current{/if}{if $item_subsubmenu[3]} dis_module{/if}">
                        <a href="{if $item_subsubmenu[3]}javascript:void(0){else}{$app->urlManager->createUrl($item_subsubmenu[0])}{/if}">
                            <i class="icon-circle"></i>
                            <span>{$item_subsubmenu[2]}</span>
                        </a>
                    </li>
                    {/foreach}
                    </ul>
                {/if}
            </li>
        {/foreach}
        </ul>
    </li>
    {/foreach}
    {if \common\helpers\Acl::rule('BOX_HEADING_FRONENDS')}
    <li{if $context->selectedMenu[0] == "fronends"} class="current"{/if}>
        <a href="{$app->urlManager->createUrl("platforms")}">
            <i class="icon-desktop"></i>
            {$smarty.const.BOX_HEADING_FRONENDS}
        </a>
    </li>
    {/if}
</ul>
