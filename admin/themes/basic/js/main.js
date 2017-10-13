
$.fn.popUp = function(options){
  var op = jQuery.extend({
    overflow: false,
    box_class: false,
    one_popup: true,
    data: [],
    event: false,
    action: false,
    type: false,
	only_show:false,
    box: '<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>',
    dataType: 'html',
    success: function(data, popup_box){
      var n = $(window).scrollTop();
      $('.pop-up-content:last').html(data);
      $(window).scrollTop(n);
      op.position(popup_box)
    },
    close:  function(){
      $('.pop-up-close').click(function(){
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        return false
      });
      $('.popup-box').on('click', '.btn-cancel', function(){
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        return false
      });
    },
    position: function(popup_box){
      var d = ($(window).height() - $('.popup-box').height()) / 2;
      if (d < 0) d = 30;
      $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
    }
  },options);

  var body = $('body');
  var html = $('html');



  return this.each(function() {
    var _action = '';
    var _event = '';
    if ($(this).context.localName == 'a'){
      if (!op.event) _event = 'click';
      if (!op.action) _action = 'href'
    } else if ($(this).context.localName == 'form') {
      if (!op.event) _event = 'submit';
      if (!op.action) _action = 'action'
    }
    if (op.event == 'show') _event = 'load';

    jQuery(this).on(_event, function(){
      if(op.one_popup){
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap').remove();
      }
      var url = '';
      if (op.action) {
        url = op.action;
      } else {
        url = $(this).attr(_action);
      }

      body.append(op.box);
      var popup_box = $('.popup-box:last');
      if($(this).attr('data-class'))popup_box.addClass($(this).attr('data-class'));
      if (op.box_class) popup_box.addClass(op.box_class);

      op.position(popup_box);
      var position_pp = function(){
        op.position(popup_box)
      };
      $(window).on('window.resize', position_pp);
      popup_box.on('popup.close', function(){
        $(window).off('window.resize', position_pp);
      });
      if (op.event == 'show' && !op.only_show){
        op.success($(this).html(), popup_box);
      }else if (op.event == 'show' && op.only_show){
        op.success(op.data, popup_box);
      } else {
        if ($(this).context.localName == 'form'){
          var _data = $(this).serializeArray();
          _data.push(op.data);
          _data.push({name: 'popup', value: 'true'});
        } else {
          //var _data = $.extend({'ajax': 'true'}, op.data)
          var _data = op.data
        }
        var _type = '';
        if (!op.type && $(this).context.localName == 'form') {
          _type = $(this).attr('method')
        } else {
          _type = 'GET'
        }
        if (op.dataType == 'jsonp') {
          _data = 'encode_date='+base64_encode($.param(_data))
        }
        if (url.search('#') != -1){
          op.success($(url).html(), popup_box);
        }else{
          $.ajax({
            url: url,
            data: _data,
            dataType: op.dataType,
            type: _type,
            crossDomain: false,
            success: function(data){
              op.success(data, popup_box);
            }
          });
        }

      }

      op.close();
      return false
    });
    $(this).trigger('load')

  })
};


function alertMessage(data){
  $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content">' + data + '</div></div></div>');

  var d = ($(window).height() - $('.popup-box').height()) / 2;
  if (d < 0) d = 0;
  $('.popup-box-wrap').css('top', $(window).scrollTop() + d);

  $('.pop-up-close, .around-pop-up').click(function(){
    $('.popup-box-wrap:last').remove();
    return false
  });
  $('.popup-box').on('click', '.btn-cancel', function(){
    $('.popup-box-wrap:last').remove();
    return false
  });
}


$.fn.uploads = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false,
    acceptedFiles: ''
  },options);

  var body = $('body');
  var html = $('html');

  return this.each(function() {

    var _this = $(this);

    var preload = _this.data('preload');
    if (preload == undefined) {
      var img = _this.data('img');
      if (img != undefined) {
        preload = img;
      } else {
        preload = '';
      }
    }
    
    var $TRANSLATE = {'title':'Drop files here', 'button': 'Upload', 'or': 'or'};
    if (typeof $tranlations == 'object'){
       if ($tranlations.hasOwnProperty('FILEUPLOAD_TITLE')) $TRANSLATE.title = $tranlations.FILEUPLOAD_TITLE;
       if ($tranlations.hasOwnProperty('FILEUPLOAD_BUTTON')) $TRANSLATE.button = $tranlations.FILEUPLOAD_BUTTON;
       if ($tranlations.hasOwnProperty('FILEUPLOAD_OR')) $TRANSLATE.or = $tranlations.FILEUPLOAD_OR;
    }

    _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">'+$TRANSLATE.title+'<br>'+$TRANSLATE.or+'<br><span class="btn">'+$TRANSLATE.button+'</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="'+_this.data('name')+'" value="'+preload+'" /></div>\
    </div>');

    var linked = _this.data('linked');
    
    var value = _this.data('value');
    if (value != undefined && value !="") {
        $('.upload-file', _this).html('<div class="dz-details"><img src="'+value+'" /><div onclick="uploadRemove(this, \''+show+'\', \''+linked+'\')" class="upload-remove"></div></div>')
    }

    var url = _this.data('url');
    if (url == undefined || url =="") {
        url = "upload/index";
    }
    
    var show = _this.data('show');
    $('.upload-file', _this).dropzone({
      url: url,
      maxFiles: 1,
      acceptedFiles: option.acceptedFiles,
      uploadMultiple: false,
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.upload-hidden input[type="hidden"]', _this).val('');
          _this.trigger('upload-remove')
          $('.dz-details', _this).remove();
          if (show != undefined) {
            $('#'+show).text(' ');
          }
          if (linked != undefined) {
            $('#'+linked).hide();
          }
        })
      },
      dataType: 'json',
      previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="upload-remove"></div></div>',
      drop: function(){
        $('.upload-file', _this).html('');
      },
      success: function(e, data) {
          if (show != undefined) {
                $('#'+show).text(e.name)
          }
          if (linked != undefined) {
              uploadSuccess(linked, e.name);
            
          }
        _this.trigger('upload')
      }
    });

  })
};



$.popUpConfirm = function(message, func){
  $('body').append('<div class="popup-box-wrap confirm-popup"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content">' +
    '<div class="confirm-text">'+message+'</div>' +
    '<div class="buttons"><span class="btn btn-cancel">Cancel</span><span class="btn btn-default btn-success">Ok</span></div>' +
    '</div></div></div>');

  var popup_box = $('.popup-box');

  var d = ($(window).height() - popup_box.height()) / 2;
  if (d < 0) d = 0;
  $('.popup-box-wrap').css('top', $(window).scrollTop() + d);

  $('.btn-cancel').on('click', function(){
    $('.popup-box-wrap:last').remove();
  });
  $('.btn-success').on('click', function(){
    func();
    $('.popup-box-wrap:last').remove();
  });

};


$.fn.galleryImage = function(baseUrl, type){
  return this.each(function(){
    $(this).on('click', function(){
      var _this = $(this);
      var $TRANSLATE = {'themes_folder':'Files from themes folder', 'general_folder': 'Files from general folder', 'all_files': 'All files'};
      if (typeof $tranlations == 'object'){
        if ($tranlations.hasOwnProperty('TEXT_THEMES_FOLDER')) $TRANSLATE.themes_folder = $tranlations.TEXT_THEMES_FOLDER;
        if ($tranlations.hasOwnProperty('TEXT_GENERAL_FOLDER')) $TRANSLATE.general_folder = $tranlations.TEXT_GENERAL_FOLDER;
        if ($tranlations.hasOwnProperty('TEXT_ALL_FILES')) $TRANSLATE.all_files = $tranlations.TEXT_ALL_FILES;
      }
      var name = $(this).data('name');
      if (name == undefined) name = 'params';      
      var theme_name = $(this).closest('form').find('input[name="theme_name"]').val();
      var filter = '';
      if (!theme_name) {
        theme_name = '';
      } else {
        filter = '<select class="form-control folder-name" name="folder_name"><option value="3">'+$TRANSLATE.themes_folder+'</option><option value="2">Files from general folder</option><option value="1">All files</option></select>';
      }
      $.get(baseUrl + '/design/gallery', { type: type, theme_name: theme_name}, function(d){
        $('body').append('<div class="images-popup"><div class="close"></div><div class="search"><input type="text" class="form-control">'+filter+'</div><div class="image-content">'+d+'</div></div>');
        $('.images-popup .item-general').hide();
        $('.images-popup .item').on('click', function(){
          var img = $('.name', this).text();
          var path = $('.name', this).data('path');
          if (!path) path = '';
          $('input[name="'+name+'"]').val(path + img);
          $('.images-popup').remove();
          $('input[name="uploads"]').remove();
          _this.trigger('choose-image');
          if (name == 'params'){
            $('.show-image').attr('src', baseUrl+'/../' + path + img)
          } else {
            $('.show-image[data-name="'+name+'"]').attr('src', baseUrl+'/../' + path + img).closest('video').get(0).load()
          }
        });
        $('.images-popup .close').on('click', function(){
          $('.images-popup').remove()
        });

        if (!theme_name){
          $('.images-popup .item').show();
          $('.images-popup .item-themes').hide();
        }

        $('.images-popup .search .folder-name').on('change', function(){
          if ($(this).val() == 1){
            $('.images-popup .item').show()
          }
          if ($(this).val() == 2){
            $('.images-popup .item').show();
            $('.images-popup .item-themes').hide();
          }
          if ($(this).val() == 3){
            $('.images-popup .item').show();
            $('.images-popup .item-general').hide();
          }
        })

        $('.images-popup .search input').on('keyup', function(){
          var val = $(this).val();

          $('.images-popup .name').each(function(){
            if ($(this).text().search(val) != -1){
              $(this).parent().show()
            } else {
              $(this).parent().hide()
            }
          });

          if (val == '') $('.images-popup .item').show();

          if ($('.images-popup .search .folder-name').val() == 2){
            $('.images-popup .item-themes').hide();
          }
          if ($('.images-popup .search .folder-name').val() == 3){
            $('.images-popup .item-general').hide();
          }
        })
      })
    })
  })
}

$.fn.quantity = function(options){
  options = $.extend({
    min: 1,
    max: false,
    step: 1,
    event: function(){}
  },options);

  return this.each(function() {
    var _this = $(this);
    if (!_this.parent().hasClass('qty-box')) {
      var min = 0;
      var max = 0;
      var step = 0;
      if (_this.attr('data-min')) min = parseInt(_this.attr('data-min'),10);
      else min = options.min;
      if (_this.attr('data-max')) max = parseInt(_this.attr('data-max'),10);
      else max = options.max;
      if (_this.attr('data-step')) step = parseInt(_this.attr('data-step'),10);
      else step = options.step;

      if (min !== false && max !== false && min > max){
        _this.attr('data-error', 'min > max');
        return false;
      }
      _this.wrap('<span class="qty-box"></span>');
      var qtyBox = _this.closest('.qty-box');
      qtyBox.prepend('<span class="smaller"></span>');
      var smaller = $('.smaller', qtyBox)
      qtyBox.append('<span class="bigger"></span>');
      var bigger = $('.bigger', qtyBox);
      var qty = _this.val();
      if (max !== false && qty*1 >= max*1){
        bigger.addClass('disabled');
      }
      if (min !== false && qty <= min){
        smaller.addClass('disabled');
      }

      _this.on('changeSettings', function(){
        if (_this.attr('data-min')) min = parseInt(_this.attr('data-min'),10);
        else min = options.min;
        if (_this.attr('data-max')) max = parseInt(_this.attr('data-max'),10);
        else max = options.max;
        if (_this.attr('data-step')) step = parseInt(_this.attr('data-step'),10);
        else step = options.step;
      });

      var delay = (function(){
        var timer = 0;
        return function(callback, ms){
          clearTimeout (timer);
          timer = setTimeout(callback, ms);
        };
      })();

      _this.on('focus',function(){
        this.select();
      });

      bigger.on('click', function(){
        qty = parseInt(_this.val(),10);
        if (!$(this).hasClass('disabled')) {
          qty = qty + step;
          if (max !== false && qty >= max) {
            qty = max;
            bigger.addClass('disabled');
          }
          if (min !== false && qty > min) {
            smaller.removeClass('disabled');
          }
          _this.val(qty).trigger('change');
          options.event();
        }
      });

      smaller.on('click', function(){
        qty = _this.val();
        if (!$(this).hasClass('disabled')) {
          qty = qty - step;
          if (min !== false && qty <= min) {
            qty = min;
            smaller.addClass('disabled');
          }
          if (max !== false && qty < max) {
            bigger.removeClass('disabled');
          }
          _this.val(qty).trigger('change');
          options.event();
        }
      });

      _this.on('check_quantity',function(){
        var qty = parseInt(_this.val(),10);
        if ((qty % step)!=0){
          qty = Math.floor(qty / step)*step + step;
          if (max !== false ) {
            if ( qty >= max ) {
              qty = max;
              bigger.addClass('disabled');
            }else{
              bigger.removeClass('disabled')
            }
          }
          if (min !== false) {
            if ( qty > min ) {
              smaller.removeClass('disabled');
            }else{
              smaller.addClass('disabled');
            }
          }
          _this.val( qty ).trigger('change');
          options.event();
        }
      });

      _this.on('keyup', function(){
        _this.val(_this.val().replace(/[^0-9]/g, ''));

        delay(function(){
          _this.trigger('check_quantity');
        }, 2000);
      });

      if ( _this.val()>0 ) {
        _this.trigger('check_quantity');
      }
    }
  })
};

document.addEventListener("keydown", function(e) {
	if($('.content-container form textarea').hasClass('ckeditor')){
		if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
    e.preventDefault();		
		$('.content-container form button').click();
		}    
  }  
}, false);

function filters_height_block() {
    var maxHeight = 0;
    $(".item_filter").each(function () {
        if ($(this).height() > maxHeight)
        {
            maxHeight = $(this).height();
        }
    });
    $(".item_filter").css('min-height', maxHeight);
}

$.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return $.getUrlVars()[name];
  }
});

function choose_platform_filter() {
    setTimeout(function () {
        if ($('.choose_platform span').hasClass('checked')) {
            $('.choose_platform .tl_filters_title').addClass('active_options');
        } else {
            $('.choose_platform .tl_filters_title').removeClass('active_options');
        }
    }, 200);
}

function choose_select_filter(){  
    $('.widget-fixed select').each(function(){
        if($(this).val() != ''){
            $(this).siblings('label').addClass('active_options');
        }else{
            $(this).siblings('label').removeClass('active_options');
        }
    });    
}

function choose_input_filter(){  
    $('.widget-fixed input[type="text"]').each(function(){
        if($(this).val() != ''){
            $(this).siblings('label').addClass('active_options');
        }else{
            $(this).siblings('label').removeClass('active_options');
        }
    });    
}

 $(document).ready(function(){
    $('.tl-all-pages-block ul li a[data-toggle="tab"]').on('click', function () {
        $('.nav-tabs-scroll li.active').removeClass('active');
        $('.nav-tabs-scroll a[href="' + $(this).attr('href') + '"]').parent().addClass('active');
        $('.nav-tabs-scroll').scrollingTabs('scrollToActiveTab');
    });

    $('.nav-tabs-scroll a[data-toggle="tab"]').on('click', function () {
        $('.tl-all-pages-block ul li.active').removeClass('active');
        $('.tl-all-pages-block ul li a[href="' + $(this).attr('href') + '"]').parent().addClass('active');
    });

    $('.nav-tabs-scroll').scrollingTabs().on('ready.scrtabs', function () {
        $('.tab-content').show();
    });
    if($('.widget').hasClass('widget-fixed')){
        var height_head = $('.header.navbar-fixed-top').height() + $('.top_header').height();        
        $(window).scroll(function(){
            if($(window).scrollTop() > height_head){
                $('.widget-fixed .widget-header').addClass('widget-fixed-top');
                $('.widget-fixed .widget-header.widget-fixed-top').css('top', height_head);
                $('.widget-fixed .widget-header.widget-fixed-top').css('width', $('.content-container').width());
                $(window).resize(function() {
                    $('.widget-fixed .widget-header.widget-fixed-top').css('width', $('.content-container').width());
                });
            }else{
                $('.widget-fixed .widget-header').removeClass('widget-fixed-top');
                $('.widget-fixed .widget-header').css('top', 'auto');
                $('.widget-fixed .widget-header').css('width', '100%');
            }
        });
    }
    
    if($('.widget-content > form > div').hasClass('wrap_filters')){
        filters_height_block();
        $('.toolbar').click(function(){
            filters_height_block();
            var filters_status = $.getUrlVar('fs');
            if(filters_status == 'open'){
                $('input[name="fs"]').val('closed');
            }else{
                $('input[name="fs"]').val('open');
            }
            setFilterState();
        });
    }
    
    var filters_status = $.getUrlVar('fs');
    if(filters_status == 'open'){
        $('.widget.box').removeClass('widget-closed');
        $('.widget.box .widget-header .toolbar.no-padding .btn i').removeClass('icon-angle-up');
        $('.widget.box .widget-header .toolbar.no-padding .btn i').addClass('icon-angle-down');
        filters_height_block();
    }
    
    /* Choose options filters */
    choose_select_filter();
    choose_platform_filter();
    choose_input_filter();
    $('.choose_platform input[type="checkbox"]').click(function(){
        choose_platform_filter();
    });
    $('.widget-fixed select').change(function(){
        choose_select_filter();
    });
    $('.widget-fixed input[type="text"]').change(function(){
        choose_input_filter();
    });
    
    /* End choose options filters */
    
 });