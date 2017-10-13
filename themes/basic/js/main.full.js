$.fn.inRow = function(options, col){
  return this.each(function() {
    var heightItem = 0;
    var _this = $(this);
    $.each(options, function(i, option){

      heightItem = 0;
      var row = [];
      var n = 0;
      var j = 0;
      var len = _this.find(option).length;
      _this.find(option).each(function(i){
        row[n] = $(this);
        var col_tmp = col;
        if (i % col == col - 1 && i != 0 || i == len-1){
          if (i == len-1 && len % col != 0){
            col_tmp = len % col
          }
          heightItem = 0;
          for(j = 0; j < col_tmp; j++){
            if(row[j]) {
              row[j].css('min-height', '0');
              if (heightItem < row[j].height()) {
                heightItem = row[j].height();
              }
            }
          }
          for(j = 0; j < col_tmp; j++){
            if (row[j]) {
              row[j].css('min-height', heightItem);
            }
          }
          n = -1;
        }
        n++
      })

    });
  })
};


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
    var delay = (function(){
      var timer = 0;
      return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms || 300);
      };
    })();

    var min = 0;
    var max = 0;
    var step = 0;

    _this.wrap('<span class="qty-box"></span>');
    var qtyBox = _this.closest('.qty-box');
    qtyBox.prepend('<span class="smaller"></span>');
    var smaller = $('.smaller', qtyBox)
    qtyBox.append('<span class="bigger"></span>');
    var bigger = $('.bigger', qtyBox);
    var qty = _this.val();

    _this.on('changeSettings', function(event, skip_check){
      var differ_settings = false;
      var new_min = _this.attr('data-min')?parseInt(_this.attr('data-min'),10):options.min;
      var new_max = _this.attr('data-max')?parseInt(_this.attr('data-max'),10):options.max;
      var new_step = _this.attr('data-step')?parseInt(_this.attr('data-step'),10):options.step;
      if (new_max !== false && min > max){
        new_max = false;
        _this.attr('data-error', 'min > max');
      }
      if (min !== new_min) differ_settings = true;
      if (new_max!==false && max !== new_max) differ_settings = true;
      if (step !== new_step) differ_settings = true;
      min = new_min;
      max = new_max;
      step = new_step;
      if ( differ_settings && !skip_check ) {
        _this.trigger('check_quantity');
      }
    });
    _this.trigger('changeSettings', true);

    _this.on('focus',function(){
        this.select();
    });

    bigger.on('click', function(){
      if (!$(this).hasClass('disabled')) {
        qty = _this.val();
        qty = parseInt(qty,10) + 1;
        _this.trigger('check_quantity', [qty]);
      }
    });

    smaller.on('click', function(){
      if (!$(this).hasClass('disabled')) {
        qty = _this.val();
          if ( qty===(''+min) && (_this.attr('data-zero-init') || (_this.attr('data-min') && _this.attr('data-min')==='0')) ) {
          qty = 0;
        }else {
          qty = parseInt(qty,10) - step;
          if (qty < min) qty = min;
        }
        _this.trigger('check_quantity',[qty]);
      }
    });

    _this.on('check_quantity',function(event, new_value){
      var old_value = parseInt(_this.val(),10);
      var qty = ((new_value===0)?new_value:parseInt(new_value || old_value,10)),
          base_qty = 0,
          zero_allow = !!_this.attr('data-zero-init') || (_this.attr('data-min') && _this.attr('data-min')==='0');
      if ( zero_allow && qty===0 ) {

      }else{
        var result_quantity = Math.max(min, qty, 1);
        if (min > step) {
          base_qty = min;
        }
        if (result_quantity > min && ((result_quantity - base_qty) % step) !== 0) {
          result_quantity = base_qty + ((Math.floor((result_quantity - base_qty) / step) + 1) * step);
        }
        qty = result_quantity;
      }

      if (max !== false ) {
        if ( qty >= max ) {
          qty = max;
          bigger.addClass('disabled');
        }else{
          bigger.removeClass('disabled')
        }
      }
      if (min !== false) {
        if (zero_allow) {
          if (qty>0) {
            smaller.removeClass('disabled');
          }else{
            qty = 0;
            smaller.addClass('disabled');
          }
        }else {
          if ( qty > min ) {
            smaller.removeClass('disabled');
          }else{
            if ( _this.val()!=='' ) qty = min;
            smaller.addClass('disabled');
          }
        }
      }
      _this.val( qty );
      if ( old_value!==qty ) {
        delay(function() {
          _this.trigger('change');
          options.event();
        });
      }
    });

      _this.on('keyup', function(){
        var new_value = _this.val().replace(/[^0-9]/g, '');

        delay(function(){
          _this.trigger('check_quantity',[new_value]);
        }, 500);
      });

      _this.trigger('check_quantity');

    }
  })
};


$.fn.validate = function(options){
  op = $.extend({
    onlyCheck: false
  },options);
  return this.each(function() {
    var _this = $(this);
    var message = _this.data('required');
    var pattern = _this.data('pattern');
    var confirmation = _this.data('confirmation');

    var check = function(){
      var error = false;
      if (pattern != undefined){
        if (pattern == 'email'){
          pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        }
        if (_this.val().search(pattern) == -1){
            error = true;
        }
      } else if (confirmation != undefined){
        if (_this.parents('form').find('input[name="'+confirmation+'"]').val() != _this.val()) {
            error = true;
        }
      } else {
        if (_this.val() == 0 || _this.val() == '') {
            error = true;
        }

      }
      if (error){
        if (!_this.hasClass('required-error')) {
          _this.addClass('required-error');
          _this.after('<div class="required-message-wrap"><div class="required-message">' + message + '</div></div>');
          _this.next().find('.required-message').hide().slideDown(300);
          _this.on('keyup', check);
          if (op.onlyCheck) {
            _this.on('change', check);
          }
        }

        return false
      } else {
        _this.removeClass('required-error');
        var this_next = _this.next('.required-message-wrap');
        this_next.find('.required-message').slideUp(300, function(){
          this_next.remove()
        });
        _this.off('keyup', check);
        if (op.onlyCheck) {
            _this.off('change', check);
        }
      }
    };

    if (message != undefined){
        if (op.onlyCheck){
            _this.on('check', check);
        } else {
            _this.on('change', check);
            _this.on('check', check);
            _this.parents('form').on('submit', check)
        }
    }
  })
};


$.fn.popUp = function(options){
  var op = jQuery.extend({
    beforeSend: function(){},
    overflow: false,
    box_class: false,
    one_popup: true,
    data: [],
    event: false,
    action: false,
    type: false,
    box: '<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>',
    dataType: 'html',
    success: function(data, popup_box){
      var n = $(window).scrollTop();
      $('.pop-up-content:last').html(data);
      $(window).scrollTop(n);
      op.position(popup_box)
    },
    close:  function(){
      $('.pop-up-close, .around-pop-up').click(function(){
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
      if (d < 0) d = 0;
      $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
    },
    opened: function(){}
  },options);

  var body = $('body');
  var html = $('html');


  return this.each(function() {
    if ($(this).hasClass('set-popup')){
      return false
    }
    $(this).addClass('set-popup');
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

    jQuery(this).on(_event, function(event){
      event.preventDefault();
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

      if (op.event == 'show'){
        op.success($(this).html(), popup_box);
      } else {
        var _data = {};
        if ($(this).context.localName == 'form'){
          _data = $(this).serializeArray();
          _data.push(op.data);
          _data.push({name: 'popup', value: 'true'});
        } else {
          //_data = $.extend({'ajax': 'true'}, op.data)
          _data = $.extend($.extend(_data, op.data), op.beforeSend());
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
          var _this = $(this);
          $.ajax({
            url: url,
            data: _data,
            dataType: op.dataType,
            type: _type,
            crossDomain: false,
            success: function(data){
              op.success(data, popup_box);
              op.opened(_this);
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



$.fn.radioHolder = function(options){
  options = $.extend({
    holder: 'label'
  },options);

  var _this = this;

  return this.each(function() {
    var item = $(this).closest(options.holder);
    if($(this).is(':checked')){
      item.addClass('active');
    }else{
      item.removeClass('active');
    }
    $(this).on('change', function(){
      _this.each(function(){
        var item = $(this).closest(options.holder);
        if($(this).is(':checked')){
          item.addClass('active');
        }else{
          item.removeClass('active');
        }
      })
    })
  })
};


$.fn.rating = function( method, options ) {
  method = method || 'create';
  // This is the easiest way to have default options.
  var settings = $.extend({
    // These are the defaults.
    limit: 5,
    value: 0,
    glyph: "glyphicon-star",
    coloroff: "#ccc",
    coloron: "#183d78",
    size: "2.0em",
    cursor: "default",
    onClick: function () {},
    endofarray: "idontmatter"
  }, options );
  var style = "";
  style = style + "font-size:" + settings.size + "; ";
  style = style + "cursor:" + settings.cursor + "; ";

  if (method == 'create') {
    //this.html('');	//junk whatever was there

    //initialize the data-rating property
    this.each(function(){
      attr = $(this).attr('data-rating');
      if (attr === undefined || attr === false) { $(this).attr('data-rating',settings.value); }
    })

    //bolt in the glyphs
    for (var i = 0; i < settings.limit; i++){
      this.append('<span data-value="' + (i+1) + '" class="ratingicon glyphicon ' + settings.glyph + '" style="' + style + '" aria-hidden="true"></span>');
    }

    //paint
    this.each(function() { paint($(this)); });

  }
  if (method == 'set') {
    this.attr('data-rating',options);
    this.each(function() { paint($(this)); });
  }
  if (method == 'get') {
    return this.attr('data-rating');
  }
  //register the click events
  this.find("span.ratingicon").click(function() {
    rating = $(this).attr('data-value')
    $(this).parent().attr('data-rating',rating);
    paint($(this).parent());
    settings.onClick.call( $(this).parent() );
  })
  function paint(div) {
    rating = parseInt(div.attr('data-rating'));
    div.find("input").val(rating);	//if there is an input in the div lets set it's value
    div.find("span.ratingicon").each(function(){	//now paint the stars

      var rating = parseInt($(this).parent().attr('data-rating'));
      var value = parseInt($(this).attr('data-value'));
      if (value > rating) {
        $(this).removeClass('coloron')
      } else {
        $(this).addClass('coloron')
      }
    })
  }

};



function alertMessage(data){
  $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content alert-message">' + data + '</div></div></div>');

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

$.fn.hideTab = function(){
  return this.each(function(){
    $(this).css({
      'padding': 0,
      'margin': 0,
      'height':0,
      'overflow': 'hidden'
    })
  })
};
$.fn.showTab = function(){
  return this.each(function(){
    $(this).removeAttr('style')
  })
};


jQuery.cookie = function(name, value, options) {
  if (typeof value != 'undefined') {
    options = options || {};
    if (value === null) {
      value = '';
      options.expires = -1;
    }
    var expires = '';
    if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
      var date;
      if (typeof options.expires == 'number') {
        date = new Date();
        date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
      } else {
        date = options.expires;
      }
      expires = '; expires=' + date.toUTCString();
    }
    var path = options.path ? '; path=' + (options.path) : '';
    var domain = options.domain ? '; domain=' + (options.domain) : '';
    var secure = options.secure ? '; secure' : '';
    document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
  } else { // only name given, get cookie
    var cookieValue = null;
    if (document.cookie && document.cookie != '') {
      var cookies = document.cookie.split(';');
      for (var i = 0; i < cookies.length; i++) {
        var cookie = jQuery.trim(cookies[i]);
        if (cookie.substring(0, name.length + 1) == (name + '=')) {
          cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
          break;
        }
      }
    }
    return cookieValue;
  }
};