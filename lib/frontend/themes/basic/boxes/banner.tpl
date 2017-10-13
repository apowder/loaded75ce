{use class="frontend\design\Info"}
  {if !empty($banners)}
<div class="banner">
    {if $banner_type == 'banner' || $banner_type == ''}

      {foreach $banners as $banner}
        {if $banner.banners_html_text && $banner.banner_display == '1'}
          <div class="single_banner">{$banner.banners_html_text}</div>
        {elseif $banner.banners_image && !$banner.banner_display}
            {if $banner.banners_url}
              <div class="single_banner"><a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if}><img src="{$app->request->baseUrl}/images/{$banner.banners_image}" alt=""></a></div>
            {else}
              <div class="single_banner"><span><img src="{$app->request->baseUrl}/images/{$banner.banners_image}" alt=""></span></div>
            {/if}
        {elseif $banner.banners_image && $banner.banner_display == '2'}
          <div class="image-text-banner {$banner.text_position}">
              {if $banner.banners_url}
                <div class="single_banner"><a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if}><img src="{$app->request->baseUrl}/images/{$banner.banners_image}" alt=""></a></div>
              {else}
                <div class="single_banner"><span><img src="{$app->request->baseUrl}/images/{$banner.banners_image}" alt=""></span></div>
              {/if}
              <div class="text-banner"><div class="text-banner-1"><div class="text-banner-2">{$banner.banners_html_text}</div></div></div>
          </div>
        {/if}
      {/foreach}

    {elseif $banner_type == 'carousel'}

      <div class="jcarousel-wrapper">
        <div class="jcarousel">
          <ul>
            {foreach $banners as $banner}
              {if file_exists($app->request->baseUrl|cat:'/images/'|cat:$banner.banners_image)}
                <li>

                  {if $banner.banner_display == '0'}
                    {if $banner.banners_url}
                      <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if}><span class="carousel_img"><img src="{$app->request->baseUrl}/images/{$banner.banners_image}"></span></a>
                    {else}
                      <span class="carousel_img"><img src="{$app->request->baseUrl}/images/{$banner.banners_image}"></span>
                    {/if}
                  {else}
                    <span class="carousel_text">{$banner.banners_html_text}</span>
                  {/if}

                </li>
              {/if}
            {/foreach}
          </ul>
        </div>
        <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
        <a href="#" class="jcarousel-control-next">&rsaquo;</a>
      </div>

      <script type="text/javascript">
        tl('{Info::themeFile('/js/jquery.jcarousel.min.js')}', function(){
          $('head').append('<link rel="stylesheet" href="{Info::themeFile('/css/jcarousel.responsive.css')}"/>');
          var jcarousel = $('.jcarousel');
          jcarousel
                  .on('jcarousel:reload jcarousel:create', function () {
                    var carousel = $(this),
                            width = carousel.innerWidth();
                    carousel.jcarousel('items').css('width', Math.ceil(width) + 'px');
                  })
                  .jcarousel({
                    wrap: 'circular',
                    animation: {$settings.animSpeed}
                  });
          $('.jcarousel-control-prev')
                  .jcarouselControl({
                    target: '-=1'
                  });
          $('.jcarousel-control-next')
                  .jcarouselControl({
                    target: '+=1'
                  });
          $('.jcarousel-pagination')
                  .on('jcarouselpagination:active', 'a', function() {
                    $(this).addClass('active');
                  })
                  .on('jcarouselpagination:inactive', 'a', function() {
                    $(this).removeClass('active');
                  })
                  .on('click', function(e) {
                    e.preventDefault();
                  })
                  .jcarouselPagination({
                    perPage: 1,
                    item: function(page) {
                      return '<a href="#' + page + '">' + page + '</a>';
                    }
                  });


        })
      </script>


    {elseif $banner_type == 'slider'}


      <div class="slider-wrapper"><div id="slider" class="sliderItems">
          {foreach $banners as $banner}

              {if $banner.banner_display == '0'}
                {if $banner.banners_url}
                  <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann"><img src="{$app->request->baseUrl}/images/{$banner.banners_image}"{if $banner.banners_title} alt="{$banner.banners_title}"{/if}></a>
                {else}
                  <img src="{$app->request->baseUrl}/images/{$banner.banners_image}"{if $banner.banners_title} alt="{$banner.banners_title}"{/if} class="imgBann">
                {/if}
              {else}
                <div class="htmlBanText">{$banner.banners_html_text}</div>
              {/if}

          {/foreach}
        </div></div>

      <script type="text/javascript">
        tl('{Info::themeFile('/js/jquery.nivo.slider.pack.js')}', function(){
          $('head').append('<link rel="stylesheet" href="{Info::themeFile('/css/nivo-slider.css')}"/>');
          $('.sliderItems').nivoSlider({
            effect: '{$settings.effect}',
            slices: {$settings.slices},
            boxCols: {$settings.boxCols},
            boxRows: {$settings.boxRows},
            animSpeed: {$settings.animSpeed},
            pauseTime: {$settings.pauseTime},
            directionNav: {$settings.directionNav},
            controlNav: {$settings.controlNav},
            controlNavThumbs: {$settings.controlNavThumbs},
            pauseOnHover: {$settings.pauseOnHover},
            manualAdvance: {$settings.manualAdvance}
          });
        })
      </script>
    {/if}
</div>
  {/if}