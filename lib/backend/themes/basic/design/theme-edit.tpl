{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{include 'menu.tpl'}


<div class="page-elements">




  <div class="widget box">
    <div class="widget-header">
      <h4>Website style</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/body?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>

  <div class="widget box">
    <div class="widget-header">
      <h4>Main navigation</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#horizontal" data-toggle="tab">Horizontal</a></li>
          <li><a href="#slide-menu" data-toggle="tab">Slide menu</a></li>
          <li><a href="#big-dropdown" data-toggle="tab">Big dropdown</a></li>
          <li><a href="#vertical" data-toggle="tab">Vertical</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="horizontal">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/menu-horizontal?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>
          <div class="tab-pane" id="slide-menu">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/menu-slide-menu?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>
          <div class="tab-pane" id="big-dropdown">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/menu-big-dropdow?is_admin=1&theme_name={$theme_name}&language={$language_code}">
            </div>

          </div>
          <div class="tab-pane" id="vertical">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/menu-vertical?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>

        </div>
      </div>

    </div>
  </div>


  <div class="widget box">
    <div class="widget-header">
      <h4>Secondary navigation</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#horizontal" data-toggle="tab">Horizontal</a></li>
          <li><a href="#vertical" data-toggle="tab">Vertical</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="horizontal">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/menu-horizontal2?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="vertical">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/menu-vertical2?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>

        </div>
      </div>

    </div>
  </div>


  <div class="widget box">
    <div class="widget-header">
      <h4>Tabs</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/tabs?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box">
    <div class="widget-header">
      <h4>Buttons</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/buttons?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box">
    <div class="widget-header">
      <h4>Form elements</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/form-elements?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box">
    <div class="widget-header">
      <h4>Typography</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/typography?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box widget-closed">
    <div class="widget-header">
      <h4>Product Listing</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#columns" data-toggle="tab">Columns</a></li>
          <li><a href="#rows" data-toggle="tab">Rows</a></li>
          <li><a href="#b2b" data-toggle="tab">B2B</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="columns">
            <div class="widget box">
              <div class="widget-header">
                <h4>Type 1</h4>
                <div class="toolbar no-padding">
                  <div class="btn-group">
                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                  </div>
                </div>
              </div>
              <div class="widget-content">

                <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/listing_1?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

              </div>
            </div>

            <div class="widget box">
              <div class="widget-header">
                <h4>Type 2</h4>
                <div class="toolbar no-padding">
                  <div class="btn-group">
                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                  </div>
                </div>
              </div>
              <div class="widget-content">

                <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/listing_2?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

              </div>
            </div>
          </div>
          <div class="tab-pane" id="rows">
                <div class="widget box">
                  <div class="widget-header">
                    <h4>Type 1</h4>
                  </div>
                  <div class="widget-content">

                    <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/listing_1_2?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

                  </div>
                </div>
                <div class="widget box">
                  <div class="widget-header">
                    <h4>Type 2</h4>
                  </div>
                  <div class="widget-content">

                    <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/listing_2_2?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

                  </div>
                </div>
          </div>
          <div class="tab-pane" id="b2b">
            <div class="widget box">
              <div class="widget-header">
                <h4>Type 1</h4>
              </div>
              <div class="widget-content">

                <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/listing_1_3?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>


  <div class="widget box widget-closed">
    <div class="widget-header">
      <h4>Shopping cart</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/shopping-cart?is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>







  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
      <span data-href="{$link_cancel}" class="btn btn-save-boxes">Cancel</span>
    </div>
    <div class="btn-right">
      <span data-href="{$link_save}" class="btn btn-confirm btn-save-boxes">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>

</div>
<script type="text/javascript">
  (function($){
    $(function(){
      $('.btn-save-boxes').on('click', function(){
        $.get($(this).data('href'), { 'theme_name': '{$theme_name}'}, function(d){
          alertMessage(d);
          setTimeout(function(){
            $(window).trigger('reload-frame')
          }, 500)
        })
      });

      $('.info-view:visible').addClass('editable').editTheme({
        theme_name: '{$theme_name}'
      });
      $(window).on('change-visible', function(){
        $('.info-view:not(.editable):visible').addClass('editable').editTheme({
          theme_name: '{$theme_name}'
        });
      });


      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          $(window).trigger('reload-frame');
          $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
            redo_buttons.html(data);
            $(redo_buttons).show();
          })
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          $(window).trigger('reload-frame');
          $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
            redo_buttons.html(data);
            $(redo_buttons).show();
          })
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });


      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href"); // activated tab
        var _frame = $(target + ' iframe');
        var frame = _frame.contents();
        _frame.height($('html', frame).height());

        $(window).trigger('change-visible')
      });

      $('.widget-collapse').on('click', function(){
        $(window).trigger('change-visible');
        setTimeout(function(){
          $(window).trigger('change-visible');
        }, 500)
      })

    })
  })(jQuery);
</script>


