{$this->beginPage()}<!DOCTYPE html>
{use class="yii\helpers\Html"}
{use class="frontend\design\IncludeTpl"}
{use class="Yii"}
{use class="frontend\design\Block"}
{use class="frontend\design\Css"}
{use class="frontend\design\Info"}
{use class="common\widgets\GoogleWidget"}
{use class="common\widgets\WarningWidget"}
<html lang="{Yii::$app->language}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {$smarty.const.TRUSTPILOT_VERIFY_META_TAG}
  <link rel="shortcut icon" href="{Info::themeFile('/icons/favicon.ico')}" type="image/x-icon" />
  <link rel="apple-touch-icon" sizes="57x57" href="{Info::themeFile('/icons/apple-icon-57x57.png')}">
  <link rel="apple-touch-icon" sizes="60x60" href="{Info::themeFile('/icons/apple-icon-60x60.png')}">
  <link rel="apple-touch-icon" sizes="72x72" href="{Info::themeFile('/icons/apple-icon-72x72.png')}">
  <link rel="apple-touch-icon" sizes="76x76" href="{Info::themeFile('/icons/apple-icon-76x76.png')}">
  <link rel="apple-touch-icon" sizes="114x114" href="{Info::themeFile('/icons/apple-icon-114x114.png')}">
  <link rel="apple-touch-icon" sizes="120x120" href="{Info::themeFile('/icons/apple-icon-120x120.png')}">
  <link rel="apple-touch-icon" sizes="144x144" href="{Info::themeFile('/icons/apple-icon-144x144.png')}">
  <link rel="apple-touch-icon" sizes="152x152" href="{Info::themeFile('/icons/apple-icon-152x152.png')}">
  <link rel="apple-touch-icon" sizes="180x180" href="{Info::themeFile('/icons/apple-icon-180x180.png')}">
  <link rel="icon" type="image/png" sizes="192x192"  href="{Info::themeFile('/icons/android-icon-192x192.png')}">
  <link rel="icon" type="image/png" sizes="32x32" href="{Info::themeFile('/icons/favicon-32x32.png')}">
  <link rel="icon" type="image/png" sizes="96x96" href="{Info::themeFile('/icons/favicon-96x96.png')}g">
  <link rel="icon" type="image/png" sizes="16x16" href="{Info::themeFile('/icons/favicon-16x16.png')}">
  <link rel="manifest" href="{Info::themeFile('/icons/manifest.json')}">
  <meta name="msapplication-TileColor" content="#092964">
  <meta name="msapplication-TileImage" content="{Info::themeFile('/icons/ms-icon-144x144.png')}">
  <meta name="theme-color" content="#092964">
	<base href="{$smarty.const.BASE_URL}">
	{Html::csrfMetaTags()}
	<title>{$this->title}</title>

	{$this->head()}

  <script type="text/javascript">
var tl_js = [];var tl_start = false;var tl_include_js = [];var tl_include_loaded = [];var tl = function(a, b){ var script = { };if (typeof a == 'string' && typeof b == 'function'){ script = { 'js': [a],'script': b}} else if (typeof a == 'object' && typeof b == 'function') { script = { 'js': a,'script': b}} else if (typeof a == 'function') { script = { 'script': a}}tl_js.push(script);if (tl_start){ tl_action([script])}};
  </script>

  <style type="text/css">
    {Info::fonts()}
    {if \frontend\design\Info::themeSetting('include_css') == '1'}
    {file_get_contents(Info::themeFile('/css/base.css', 'fs'))|strip}
    {else}
    {file_get_contents(Info::themeFile('/css/basic.css', 'fs'))|strip}
    {file_get_contents(Info::themeFile('/css/style.css', 'fs'))|strip}
    {/if}

{capture name="body"}
    {if !$app->controller->view->only_content}{Block::widget(['name' => 'header', 'params' => ['type' => 'header']])}{/if}
    <div class="{if $app->controller->view->page_layout == 'default'}main-width {/if}main-content">{$content}</div>
    {if !$app->controller->view->only_content}{Block::widget(['name' => 'footer', 'params' => ['type' => 'footer']])}{/if}
{/capture}

    {if Info::isAdmin()}
      {*Info::getStyle(THEME_NAME)*}
    {else}
      {if is_file($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}
        {file_get_contents($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}
      {/if}
    {/if}
    {Info::getStyle(THEME_NAME)}
    {Block::getStyles()}

  </style>

  {if Info::isAdmin()}
    <link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
  {/if}
	{Css::widget()}
</head>

<body class="layout-main {$this->context->id}-{$this->context->action->id} context-{$this->context->id} action-{$this->context->action->id}{if $app->controller->view->page_name} template-{$app->controller->view->page_name}{/if}">
{$this->beginBody()}

{WarningWidget::widget()}

{$smarty.capture.body}

{$this->endBody()}
{GoogleWidget::widget()}

<script type="text/javascript" src="{Info::themeFile('/js/jquery-2.2.0.min.js')}" async></script>
<script type="text/javascript">
  tl(function(){
    $('body').on('reload-frame', function(d, m){ $(this).html(m);});
    $('head').append('<link rel="stylesheet" href="{Info::themeFile("/css/jquery-ui.min.css")}"/>')
  });
</script>
{strip}
  <script type="text/javascript">
    var tlSize = {
      current: [],
      dimensions: [],

      init: function(){
        tlSize.dimensions = {
          {foreach \frontend\design\Info::themeSetting('media_query', 'extend') as $i}
          '{$i}': '{$i}'.split('w'),
          {/foreach}
        };

        $(window).on('layoutChange', tlSize.bodyClass);
        tlSize.resize();
        $(window).on('resize', tlSize.resize);
      },

      resize: function(){
        $.each(tlSize.dimensions, function(key, val){
          var from = val[0]*1;
          var to = val[1];
          if (to) {
            to = to*1
          } else {
            to = 10000
          }
          var data = { };
          var w = window.innerWidth;
          if (!w) {
            w = $(window).width();
          }
          if (from <= w && w <= to) {
            if (!tlSize.current.includes(key)) {
              tlSize.current.push(key);
              tlSize.current = tlSize.sort(tlSize.current);
              data = {
                key: key,
                status: 'in',
                from: from,
                to: to,
                current: tlSize.current
              };
              $(window).trigger('layoutChange', [data]);
              $(window).trigger(key+'in', [data]);
            }
          } else {
            var index = tlSize.current.indexOf(key);
            if (index > -1) {
              tlSize.current.splice(index, 1);
              tlSize.current = tlSize.sort(tlSize.current);
              data = {
                key: key,
                status: 'out',
                from: from,
                to: to,
                current: tlSize.current
              };
              $(window).trigger('layoutChange', [data]);
              $(window).trigger(key+'out', [data]);
            }
          }
        })
      },

      sort: function(arr){
        var v = [];
        var t = [];
        var tmp = [];
        var l = arr.length;
        for (var i = 0; i < l; i++) {
          tmp[i] = '0w0';
          $.each(arr, function (key, val) {
            v = val.split('w');
            v[0] = v[0]*1;
            v[1] = v[1]*1;
            if (!v[1]) {
              v[1] = 10000
            }
            t = tmp[i].split('w');
            t[0] = t[0]*1;
            t[1] = t[1]*1;
            if (t[1] < v[1]) {
              tmp[i] = val
            } else if (t[1] == v[1] && t[0] > v[0]) {
              tmp[i] = val
            }
          });
          var index = arr.indexOf(tmp[i]);
          arr.splice(index, 1);
        }

        return tmp
      },

      bodyClass: function(e, d){
        if (d.status == 'in') {
          $('body').addClass(d.key)
        }
        if (d.status == 'out') {
          $('body').removeClass(d.key)
        }
      }

    };


    var tl_action = function (script) {
      if (typeof jQuery == 'function') {
        tlSize.init();
        tl_start = true;
        var action = function (block) {
          var key = true;
          $.each(block.js, function (j, js) {
            var include_index = tl_include_js.indexOf(js);
            if (include_index == -1 || tl_include_loaded.indexOf(js) == -1) {
              key = false;
            }
          });
          if (key) {
            block.script()
          }
          return key
        };
        $.each(script, function (i, block) {
          if (!action(block)) {
            $.each(block.js, function (j, js) {
              var include_index = tl_include_js.indexOf(js);
              if (include_index == -1) {
                tl_include_js.push(js);
                include_index = tl_include_js.indexOf(js);
                $.ajax({
                  url: js, success: function () {
                    tl_include_loaded.push(js);
                    $(window).trigger('tl_action_' + include_index);
                  }, error: function (a, b, c) {
                    console.error('Error: "' + js + '" ' + c);
                  }
                });
              }
              $(window).on('tl_action_' + include_index, function () {
                action(block)
              })
            })
          }
        })
      } else {
        setTimeout(function () {
          tl_action(script)
        }, 100)
      }
    };
    tl_action(tl_js);

  </script>
{/strip}

</body>
</html>
{$this->endPage()}