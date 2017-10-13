{use class="frontend\design\Info"}
<div class="js-product-image-set main-image-box">
<div class="images{if $settings[0].align_position == 'horizontal'} additional-horizontal{/if}">

  {if $settings[0].align_position == 'horizontal'}
    <div class="img-holder">
      <img src="{$img}" alt="{$main_image_alt|escape:'html'}" itemprop="image" title="{$main_image_title|escape:'html'}" class="main-image">
    </div>
  {/if}

  {if !$settings[0].hide_additional}
  <div class="additional-images"{if $images_count < 2} style="visibility: hidden; width: 0; height: 0" {/if}>
  {foreach $images as $image_id=>$item}
    {if $item.default == 1}
    <div class="js-product-image" data-image-id="{$image_id}">
      <div class="item"><div>
          <a href="{$item.image.Large.url}" title="{$item.title|escape:'html'}" class="fancybox active" rel="gallery1">
            <img
                src="{$item.image.Small.url}"
                data-med="{$item.image.Medium.url}"
                data-lrg="{$item.image.Large.url}"
                alt="{$item.alt|escape:'html'}"
                title="{$item.title|escape:'html'}"
                class="default"
                >
          </a>
        </div></div>
    </div>
    {/if}
  {/foreach}
  {foreach $images as $image_id=>$item}
    {if $item.default == 0}
    <div class="js-product-image" data-image-id="{$image_id}">
      <div class="item"><div>
          <a href="{$item.image.Large.url}" title="{$item.title|escape:'html'}" class="fancybox" rel="gallery1">
            <img
                src="{$item.image.Small.url}"
                data-med="{$item.image.Medium.url}"
                data-lrg="{$item.image.Large.url}"
                alt="{$item.alt|escape:'html'}"
                title="{$item.title|escape:'html'}"
                >
          </a>
        </div></div>
    </div>
    {/if}
  {/foreach}
  </div>
  {/if}

{if !$settings[0].align_position}
  <div class="img-holder">
    <img src="{$img}" alt="{$main_image_alt|escape:'html'}" itemprop="image" title="{$main_image_title|escape:'html'}" class="main-image">
  </div>
{/if}

</div>

<script type="text/javascript">
  tl([
          '{Info::themeFile('/js/slick.min.js')}',
          '{Info::themeFile('/js/jquery.fancybox.pack.js')}'
  ], function(){

    $('.main-image-box .additional-images').slick({
      {if !$settings[0].align_position}
      vertical: true,
      rows: 3,
      {else}
      slidesToShow: 3,
      {/if}
      infinite: false
    });
    $('.additional-images img').on('click', function(){
      //$('.additional-images img').removeClass('active');
      $('.additional-images .item .active').removeClass('active');
      //$(this).addClass('active');
      var _this = $(this);
      $(this).closest('a').addClass('active');
      $('.main-image').attr({
        'src': $(this).data('med'),
        'data-lrg': $(this).data('lrg'),
        'alt': $(this).attr('alt'),
        'title': $(this).attr('title')
      })
    });
    $('.img-holder img').on('click', function(){
      $('.additional-images .active').closest('a').trigger('click', true);
      return false
    });
    $('.fancybox').on('click', function(a, open){
      if (!open) return false
    }).fancybox({
      nextEffect: 'fade',
      prevEffect: 'fade',
      padding: 10
    });

  })
</script>
</div>