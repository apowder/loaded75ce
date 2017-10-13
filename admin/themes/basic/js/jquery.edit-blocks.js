(function(){
  var frame_height = 1000;
  var history = [];
  var history_i = 0;
  var newWin = false;

  var popUpPosition = function(){
    var d = ($(window).height() - $('.popup-box').height()) / 2;
    if (d < 50) d = 50;
    $('.popup-box-wrap').css('top', $(window).scrollTop() + d)
  };

  var saveSettings = function() {
    var values = $(this).serializeArray();
    values = values.concat(
      $('input[type=checkbox]:not(:checked)', this).map(function() {
        return { "name": this.name, "value": 0}
      }).get()
    );
    values = values.concat(
      $('.visibility input[disabled]', this).map(function() {
        return { "name": this.name, "value": 1}
      }).get()
    );
    $.post('design/box-save', values, function(){ });
    setTimeout(function(){
      $(window).trigger('reload-frame')
    }, 300);
    return false
  };

  $.fn.infoView = function(options){
    var op = jQuery.extend({
      page_url: '',
      page_id: '288',
      na: '',
      remove_class: '',
      theme_name: 'theme-1',
      clear_url: false
    },options);

    var applyBlocks = function(){

      var _frame = $('#info-view');
      var frame = _frame.contents();

      $('a', frame).removeAttr('href');
      $('form', frame).removeAttr('action').on('submit', function(){return false});

      if (op.remove_class.length > 0) {
        $('.' + op.remove_class, frame).each(function () {
          $(this).removeClass(op.remove_class)
        });
      }


      $('*[data-block]', frame).each(function(){
        if ($(this).html() === '') {
          $(this).html('<span class="iv-editing">empty field</span>')
        }
      });

      $('.block', frame).append('<span class="add-box add-box-single">Add Widget</span>');
      $('.block .block > .add-box', frame).remove();

      $('.block[data-cols]', frame).append('<span class="add-box add-box-single">Add Widget</span>');

      $('.box-block', frame).append('<span class="menu-widget">' +
        '<span class="add-box">Add Widget</span>' +
        '<span class="edit-box" title="Edit Block"></span>' +
        '<span class="handle" title="Move block"></span>' +
        '<span class="export" title="Export Block"></span>' +
        '<span class="remove-box" title="Remove Widget"></span>' +
        '</span>');
      $('.box-block > .menu-widget', frame).each(function(){
        var box = $(this).parent();
        $(this).css({
          'margin-left': box.css('padding-left'),
          'bottom': $(this).css('bottom').replace("px", "") * 1 + box.css('padding-bottom').replace("px", "")*1
        })
      });
      $('.box-block.type-1 > .menu-widget', frame).each(function(){
        var box = $(this).parent();
        $(this).css({
          'margin-left': 0,
          'left': (box.width() - $('> .block', box).width())/2 - 12
        })
      });

      $('.box', frame).append('<span class="menu-widget">' +
        '<span class="edit-box" title="Edit Widget"></span>' +
        '<span class="handle" title="Move block"></span>' +
        '<span class="export" title="Export Block"></span>' +
        '<span class="remove-box" title="Remove Widget"></span>' +
        '</span>');


      $('.block .remove-box', frame).on('click', function(){
        var blocks = {};
        var _this = $(this).closest('div[id]');
        blocks['name'] =  _this.data('name');
        blocks['theme_name'] =  op.theme_name;
        blocks['id'] = _this.attr('id');
        $.post('design/box-delete', blocks, function(){
          _this.remove();
          if (newWin && typeof newWin.location.reload == 'function') newWin.location.reload($.cookie('page-url')+'&is_admin=1');
        }, 'json');
      });


      $('.block .add-box', frame).on('click', function(){
        var this_block = $(this).closest('div[id]').find('> div');
        if ($(this).hasClass('add-box-single')){
          this_block = $(this).parent();
        }
        var block_name = this_block.data('name');

        $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box widgets"><div class="pop-up-close"></div><div class="pop-up-content widgets"><div class="preloader"></div></div></div></div>');
        $('.around-pop-up, .pop-up-close').on('click', function(){
          $('.popup-box-wrap').remove()
        });

        var page = 'default';
        if ($('body', frame).hasClass('catalog-index')) page = 'category';
        var block_type =  '';
        if (this_block.data('type')) {
          block_type = this_block.data('type');
        } else {
          this_block.closest('div[data-type]').each(function(){
            block_type = $(this).data('type')
          })
        }
        $.get('design/widgets-list', {page: page, type: block_type}, function(data){
          $('.pop-up-content')
            .append('<div class="box-group box-group-product"></div>')
            .append('<div class="box-group box-group-inform"></div>')
            .append('<div class="box-group box-group-catalog"></div>')
            .append('<div class="box-group box-group-cart"></div>')
            .append('<div class="box-group box-group-success"></div>')
            .append('<div class="box-group box-group-contact"></div>')
            .append('<div class="box-group box-group-general"></div>')
            .append('<div class="box-group box-group-email"></div>')
            .append('<div class="box-group box-group-invoice"></div>')
            .append('<div class="box-group box-group-packingslip"></div>')
            .append('<div class="box-group box-group-gift"></div>')
            .append('<div class="box-group box-group-index"></div>');
          $.each(data, function(i, item){
            $('.pop-up-content .preloader').remove();
            if (item.name == 'title'){
              $('.pop-up-content .box-group-'+item.type).prepend('<div class="title">' + item.title + '</div>');
            } else {
              $('.pop-up-content .box-group-'+item.type).append('<div class="widget-item ico-' + item.class + '" data-name="' + item.name + '">' + item.title + '</div>');
            }

            popUpPosition();
          });
          $('.box-group').each(function(){
            if ($(this).text().length == 0){
              $(this).remove()
            }
          });


          $('.pop-up-content div[data-name]').on('click', function(){
            var data = {
              'theme_name': op.theme_name,
              'block': block_name,
              'box': $(this).data('name'),
              'order': $('> div', this_block).length + 1
            };
            $.post('design/box-add', data, function(){
              $(window).trigger('reload-frame')
            }, 'json');
          })
        }, 'json');

        popUpPosition();
      });



      $('.menu-widget .edit-box', frame).off('click').on('click', function(){
        var this_block = $(this).closest('div[id]');
        var block_id = this_block.attr('id');
        var block_name = this_block.data('name');
        var block_type = '';
        this_block.closest('div[data-type]').each(function(){
          block_type = $(this).data('type')
        });

        $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box widget-settings"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>');
        $('.around-pop-up, .pop-up-close').on('click', function(){
          $('.popup-box-wrap').remove()
        });

        $.get('design/box-edit', {id: block_id, name: block_name, block_type: block_type}, function(data){
          $('.pop-up-content').html(data);
          $('#box-save').on('submit', saveSettings);
          $('.popup-buttons .btn-cancel').on('click', function(){
            $('.popup-box-wrap').remove()
          });
          popUpPosition();
        });

        popUpPosition();
      });


      $('.menu-widget .export', frame).off('click').on('click', function(){
        window.location="admin/design/export-block?id=" + $(this).closest('div[id]').attr('id')
      });

      $('.import-box', frame).each(function(){
        var block_name = $(this).closest('div[data-name]').parent().closest('div[data-name]').data('name');
        var box_id = $(this).parent().attr('id');
        $(this).dropzone({
          url: 'design/import-block?theme_name=' + op.theme_name + '&block_name=' + block_name + '&box_id=' + box_id,
          success: function(){
            $(window).trigger('reload-frame')
          }
        })
      });


      var type_box = '';
      $('.block[data-type]', frame).each(function(){
        var type = $(this).data('type');
        if (type != 'header' && type != 'footer'){
          type_box = type
        }
      });
      $('body', frame).prepend('<div class="widgets-list"></div>');

      var widgets_list = $('.widgets-list', frame);

      $(window).on('scroll', function(){
        if ($(window).scrollTop() > 91) {
          widgets_list.css('top', $(window).scrollTop() - 91)
        } else {
          widgets_list.css('top', 0)
        }
      });

      if ($.cookie('closed_widgets') == 1){
        widgets_list.addClass('closed')
      }
      widgets_list.on('click', '.close-widgets', function(){
        if (widgets_list.hasClass('closed')){
          widgets_list.removeClass('closed');
          $.cookie('closed_widgets', 0)
        } else {
          widgets_list.addClass('closed');
          $.cookie('closed_widgets', 1)
        }
      });
      $.get('design/widgets-list', {type: type_box}, function(data) {
        widgets_list.html('<div class="close-widgets"></div>')
          .append('<div class="box-group box-group-product"></div>')
          .append('<div class="box-group box-group-inform"></div>')
          .append('<div class="box-group box-group-catalog"></div>')
          .append('<div class="box-group box-group-cart"></div>')
          .append('<div class="box-group box-group-success"></div>')
          .append('<div class="box-group box-group-contact"></div>')
          .append('<div class="box-group box-group-general"></div>')
          .append('<div class="box-group box-group-email"></div>')
          .append('<div class="box-group box-group-invoice"></div>')
          .append('<div class="box-group box-group-packingslip"></div>')
          .append('<div class="box-group box-group-gift"></div>')
          .append('<div class="box-group box-group-index"></div>');
        $.each(data, function (i, item) {
          if (item.name == 'title') {
            $('.widgets-list .box-group-' + item.type, frame).prepend('<div class="title">' + item.title + '</div>');
          } else {
            $('.widgets-list .box-group-' + item.type, frame).append('<div class="widget-item ico-' + item.class + '" data-name="' + item.name + '" title="' + item.title + '">' + item.title + '</div>');
          }

        });
        $('.widgets-list .box-group', frame).each(function () {
          if ($(this).text().length == 0) {
            $(this).remove()
          }
        });


        var sort_update = function( event, ui ) {
          var _this = $(this);

          if (ui.item.hasClass('widget-item')) {
            var block = _this.data('name');

            var data = {
              'theme_name': op.theme_name,
              'box': ui.item.data('name'),
              'block': block,
              'order': $('> div', this).length
            };
            data['id'] = {};
            $('> div', this).each(function(i){
              if ($(this).hasClass('widget-item')){
                data.id[i] = 'new';
              } else {
                data.id[i] = $(this).attr('id');
              }
            });
            $.post('design/box-add-sort', data, function(){
              $(window).trigger('reload-frame')
            }, 'json');

          } else {
            var blocks = {};
            blocks['name'] =  _this.data('name');
            blocks['theme_name'] =  op.theme_name;
            blocks['id'] = {};
            $('> div', this).each(function(i){
              blocks.id[i] = $(this).attr('id');
            });
            $.post('design/blocks-move', blocks, function(){
              if (newWin && typeof newWin.location.reload == 'function') {
                newWin.location.reload($.cookie('page-url')+'&is_admin=1');
              }
            }, 'json')
          }
        };
        var sort_scroll;
        $( ".block" , frame).sortable({
          connectWith: ".block",
          items: '> div.box, > div.box-block',
          cursor: 'move',
          handle: '.handle',
          update: sort_update,
          revert: true,
          tolerance: "pointer",
          scroll: false,
          sort: function(event, ui){
            var top = ui.offset.top + ui.item.height() + _frame.offset().top - $(window).scrollTop();
            if (top < 150) {
              clearInterval(sort_scroll);
              sort_scroll = setInterval(function () {
                var s = $(window).scrollTop() - 20;
                if (s < 0) s = 0;
                $(window).scrollTop(s)
              }, 100)
            } else if (top > $(window).height() - 40) {
              clearInterval(sort_scroll);
              sort_scroll = setInterval(function () {
                var s = $(window).scrollTop() + 20;
                if (s < 0) s = 0;
                $(window).scrollTop(s)
              }, 100)
            } else {
              clearInterval(sort_scroll);
            }
          }
        });
        $( ".widgets-list", frame).sortable({
          connectWith: $(".block", frame),
          items: '.widget-item',
          forcePlaceholderSize: false,
          helper: function(e,li) {
            copyHelper = li.clone().insertAfter(li);
            return li.clone();
          },
          stop: function() {
            copyHelper && copyHelper.remove();
          },
          update: function( event, ui){
            if (ui.item.parent().hasClass('box-group')){
              return false;
            }
          }
        });
        $( ".block" , frame).sortable({
          handle: '.handle',
          receive: function(e,ui) {
            copyHelper= null;
          }
        });
      }, 'json');


      var update_height = function(){
        var h = $('body', frame).height();
        if (frame_height > 999 && (frame_height < h || frame_height > h+200) && h > 1000){
          _frame.animate({'height': h + 150});
          frame_height = h + 150;
        }

      };
      update_height();
      setTimeout(update_height, 1000);
      setTimeout(update_height, 3000);
      setTimeout(update_height, 5000);
    };

    var main = function() {

      var body = $('body');

      if (op.clear_url) $.cookie('page-url', '');
      if ($.cookie('page-url') == undefined || $.cookie('page-url') == 'undefined' || $.cookie('page-url') == ''){
        var url = op.page_url;
        $.cookie('page-url', url);
        history[history_i] = url;
      } else {
        url = $.cookie('page-url');
      }
      op.page_url = url;

      $('.js-catalog_url_set li[data-href="'+$.cookie('page-url')+'"]').each(function(){
        $('.js-catalog_url_set li').removeClass('active');
        $(this).addClass('active')
      });

      $(this).html('<iframe src="' + op.page_url + '" width="100%" height="1000" frameborder="no" id="info-view"></iframe>');
      var _frame = $('#info-view');
      _frame.height($(window).height() - 150);
      _frame.on('load', function(){

        var frame = _frame.contents();
        $('body', frame).addClass('edit-blocks');

        applyBlocks();


        $('.js-catalog_url_set li').off('click').on('click', function(){
          console.log(1111);
          if ($(this).hasClass('add-page')){
            $('<a href="design/add-page"></a>').popUp({
              data: {theme_name: op.theme_name}
            }).trigger('click');
          } else {
            var url = $(this).data('href');
            if (!url) {
              url = op.page_url;
              url = url.replace('http:', '');
            }
            $.cookie('page-url', url);
            _frame.attr('src', url);

            $('.js-catalog_url_set li').removeClass('active');
            $(this).addClass('active')
          }
        });



        $('.btn-preview').on('click', function(){
          $('.btn-edit').show();
          $('.btn-preview').hide();
          $('body', frame).removeClass('edit-blocks');
          $('body', frame).addClass('view-blocks');
        });
        $('.btn-edit').on('click', function(){
          $('.btn-preview').show();
          $('.btn-edit').hide();
          $('body', frame).addClass('edit-blocks');
          $('body', frame).removeClass('view-blocks');
        });

        $('.btn-preview-2').on('click', function(){
          newWin = window.open($.cookie('page-url')+'&is_admin=1', "Preview", "left=0,top=0,width=1200,height=900,location=no");
        });

      });

      $(window).off('reload-frame').on('reload-frame', function(){
        if (newWin && typeof newWin.location.reload == 'function') {
          newWin.location.reload($.cookie('page-url')+'&is_admin=1');
        }
        $('.popup-box-wrap').remove();

        var _frame = $('#info-view');
        _frame.parent().css('position', 'relative');
        _frame.attr('id', 'info-view-1');
        _frame.css({
          'position': 'relative',
          'z-index': 2
        });
        _frame.after('<iframe src="' + $.cookie('page-url') + '" width="100%" height="'+_frame.height()+'" frameborder="no" id="info-view"></iframe>');
        var _frame_new = $('#info-view');
        _frame_new.css({
          'position': 'absolute',
          'left': '0',
          'top': '0'
        });
        _frame_new.on('load', function(){
          var frame = _frame_new.contents();
          $('body', frame).addClass('edit-blocks');
          applyBlocks();
          setTimeout(function(){
            _frame.remove();
            _frame_new.css({
              'position': 'relative'
            });
          }, 100);


          $('.js-catalog_url_set li').off('click').on('click', function(){
            console.log(2222);
            if ($(this).hasClass('add-page')){
              $('<a href="design/add-page"></a>').popUp({
                data: {theme_name: op.theme_name}
              }).trigger('click');
            } else {
              var url = $(this).data('href');
              if (!url) {
                url = op.page_url;
                url = url.replace('http:', '');
                url = url.replace('https:', '');
              }
              $.cookie('page-url', url);
              _frame_new.attr('src', url);

              $('.js-catalog_url_set li').removeClass('active');
              $(this).addClass('active')
            }
          });
        });
      })
    };

    return this.each(main)
  };

})(jQuery);