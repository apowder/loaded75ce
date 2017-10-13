{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{include 'menu.tpl'}



<div class="theme-stylesheet">
  <textarea name="css" id="css" cols="30" rows="10">{$css}</textarea>
  <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.css">
  <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.css">
  <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/addon/dialog/dialog.css">
  <script src="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/xml-hint.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/html-hint.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/mode/xml/xml.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/mode/javascript/javascript.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/mode/css/css.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>

  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/dialog/dialog.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/searchcursor.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/search.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/annotatescrollbar.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/matchesonscrollbar.js"></script>
  <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/jump-to-line.js"></script>

  <div id="code" style="border: 1px solid #ccc"></div>
  <script type="text/javascript">
    var CodeMirrorEditor;
    $(function(){
      CodeMirrorEditor = CodeMirror(document.getElementById("code"), {
        mode: "text/css",
        extraKeys: {
          "Ctrl-Space": "autocomplete",
          "Ctrl-S": function(instance) {
            $.post('design/css-save', { theme_name: '{$theme_name}', css: instance.getValue()}, function(){ });
            return false;
          }
        },
        //lineNumbers: true,
      });
      var htm = $('#css');
      CodeMirrorEditor.setValue(htm.val());
      CodeMirrorEditor.getSearchCursor('gift');
      htm.hide()
    })
  </script>


  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-right">
      <span data-href="{$link_save}" class="btn btn-confirm btn-save-css">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>

  <div class="">
    Ctrl-F / Cmd-F : Start searching<br>
    Ctrl-G / Cmd-G : Find next<br>
    Shift-Ctrl-G / Shift-Cmd-G : Find previous<br>
    Shift-Ctrl-F / Cmd-Option-F : Replace<br>
    Shift-Ctrl-R / Shift-Cmd-Option-F : Replace all<br>
    Alt-F : Persistent search (dialog doesn't autoclose, enter to find next, Shift-Enter to find previous)<br>
    Alt-G : Jump to line<br>
  </div>

</div>


<script type="text/javascript">
  (function(){
    $(function(){
      $('.btn-save-css').on('click', function(){
        var css = $('#css');
        css.val(CodeMirrorEditor.getValue());
        $.post('design/css-save', { theme_name: '{$theme_name}', css: css.val()}, function(){ })
      });


    })
  })(jQuery);
</script>



