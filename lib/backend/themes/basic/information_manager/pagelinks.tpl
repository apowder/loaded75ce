<div class="pageLinksWrapper">
<select class="pageLinksDropdown form-control">
    <option value="">None</option>
{foreach $response as $result}
    <option value="{\common\helpers\Seo::get_seo_page_path($result['information_id'], $result['platform_id'])}">{$result['info_title']}</option>
{/foreach}
 </select>
</div>
 <div class="pageLinksButton"><span class="btn btn-primary">{$smarty.const.IMAGE_INSERT}</span></div>

        
 <script type="text/javascript">
   $(document).ready(function(){
	 var oEditor = CKEDITOR.instances.{$smarty.get.id_ckeditor};
	 if(oEditor.mode == 'wysiwyg'){
    $('.pageLinksButton span').click(function(){
        if($('.pageLinksDropdown').val() != ''){            
            oEditor.focus();
            if(oEditor.getSelection().getRanges()[0].collapsed == false){
            var fragment = oEditor.getSelection().getRanges()[0].extractContents();
            var container = CKEDITOR.dom.element.createFromHtml("<a href='"+$('.pageLinksDropdown').val()+"' />", oEditor.document);
            fragment.appendTo(container);
            oEditor.insertElement(container);
            }else{
            var html = "<a href='"+$('.pageLinksDropdown').val()+"'>"+$('.pageLinksDropdown option:selected').text()+"</a>";
            var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
            oEditor.insertElement( newElement );
            }
        }
        $(this).parents('.popup-box-wrap').remove();
    })
		}else{
			$('.pageLinksWrapper').html('{$smarty.const.TEXT_PLEASE_TURN}');
			$('.pageLinksButton').hide();
		}
   })
 </script>