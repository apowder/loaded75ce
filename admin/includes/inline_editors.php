<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

if(DEFAULT_WYSIWYG_EDITOR=="tinimce")
{
?>
<script language="JavaScript" type="text/javascript" src="<?=DIR_WS_ADMIN ?>includes/javascript/tinymce/tinymce.js"></script>
<script language="JavaScript1.2" defer="defer">
    function editorGenerate(){
      tinymce.init({
      mode : "textareas",
      editor_selector : "ckeditor",
      theme: "modern",
      element_format : "html",
      width: 700,
      height: 400,
      plugins: [
        "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
        "save table contextmenu directionality emoticons template paste textcolor"
      ],
      content_css: "css/content.css",
      toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
      toolbar2: "link image | print preview media fullpage | forecolor backcolor emoticons",
      style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
      ]
      });
    }
  </script> 
<script language="JavaScript">
initPage = function (){editorGenerate();}
function addEvent(obj, evType, fn) {if (obj.addEventListener) { obj.addEventListener(evType, fn, true); return true; }else if (obj.attachEvent) {  var r = obj.attachEvent("on"+evType, fn);  return r;  }else {  return false; }}
addEvent(window, 'load', initPage);
</script>
<?php
}
if(DEFAULT_WYSIWYG_EDITOR=="ckeditor")
{
?>
<script language="JavaScript" type="text/javascript" src="<?=DIR_WS_ADMIN ?>includes/javascript/ckeditor/ckeditor.js"></script>
<?php
}
?>