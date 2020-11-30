setTimeout(function() {

  Handlebars.registerHelper('if_eq', function(a, b, opts) {
    if (a == b) {
      return opts.fn(this);
    } else {
      return opts.inverse(this);
    }
  });

  window.template = Handlebars.compile(window.bbobject.style.video);
  window.tile_template = Handlebars.compile(window.bbobject.style.tile);

  $('[data-blingby-video]').append(template({
    video: window.bbobject.video,
    tiles: window.bbobject.tiles,
    form: window.bbobject.form,
  }));

  var player = new videoProvider({
    element: 'player',
    dom: this,
    autoplay: true,
    mute: false,
    id: window.bbobject.video.provider_id,
  });

  player.init();

  player.addEventListener('play', function(){
    if (window.trace) {
      window.traceEvent(
        window.bbobject.uc,
       'video_played',
        window.bbobject.video,
        player.getCurrentTime(),
      );
    }
  });

  player.addEventListener('pause', function(){
    if (window.trace) {
      window.traceEvent(
        window.bbobject.uc,
       'video_paused',
        window.bbobject.video,
        player.getCurrentTime(),
      );
    }
  });

  player.addEventListener('ended', function(){
    if (window.trace) {
      window.traceEvent(
        window.bbobject.uc,
       'video_stopped',
        window.bbobject.video,
        player.getCurrentTime(),
      );
    }
  });

  //form
  if (window.bbobject.form) {

    $('.form-step:not(.form-completed)').each(function(){
      var $this = $(this),
          index = $this.data('index'),
          field = window.bbobject.form.fields[index],
          value = window.bbobject.data[field.key];

      if (value) {
        if (field.type == 'checkbox' || field.type == 'radio') {
          $this.find(`[name="${field.key}"][value="${value}"]`).prop('checked', true).parent().addClass('selected');
        } else {
          $this.find(`[name="${field.key}"]`).val(value);
        }

        $(`[data-field="${field.key}"]`).text(value+', ');
      }
    });

    $('.form-step').first().css('display', 'flex');
    $('.form-pagination [data-key="0"]').addClass('active');

    $('.form-step .form-options label').on('click', function(){
       var $parent = $(this),
            $container = $parent.parent();
        $container.find('label').removeClass('selected');
        $parent.addClass('selected');
    });

    $('.form-back').on('click', function(e) {
      var $this = $(this),
          $parent = $this.parent(),
          index = $parent.data('index'),
          $prev = $parent.prev();

      $parent.hide();
      $prev.css('display', 'flex');
      $(`.form-pagination [data-key="${index}"]`).removeClass('active');
    });

    $('.form-action').on('click', function(e) {
      var $this = $(this),
          value = false,
          $parent = $this.parent(),
          index = $parent.data('index'),
          field = window.bbobject.form.fields[index],
          last = (window.bbobject.form.fields.length - index == 1),
          $next = $parent.next();

      if (field.type == 'text' || field.type == 'textarea' || field.type == 'select' || field.type == 'date') {
        value = $parent.find(`[name="${field.key}"]`).val();
      } else if (field.type == 'checkbox' || field.type == 'radio') {
        value = $parent.find(`[name="${field.key}"]:checked`).val();
      }

      if (field.key == 'email' && !isEmail(value)) {
        $('.message.error')
          .css('opacity', 1)
          .html('Please provide valid email')
          .stop( true )
          .finish()
          .animate({
              opacity: 0
            },
            2000, function() {
              $(this)
                .html('')
                .css('opacity', 1);
            }
          );
        return;
      }

      if (value) {
        value = value.trim();
        window.bbobject.data[field.key] = value;
        if (window.trace) {
          window.traceEvent(
            window.bbobject.uc,
           'field_filled',
            {
              id: 0,
              title: field.label,
            },
            player.getCurrentTime(),
          );

          window.traceData(
            window.bbobject.uc,
            field.key,
            value
          );
        }

        $(`[data-field-${field.key}]`).hide();
        $(`[data-field-${field.key}="${value}"].should-display`).show();

        $(`[data-field="${field.key}"]`).text(value+', ');
        $parent.hide();
        $next.css('display', 'flex');
        if (last) {

          if (window.trace) {
            window.traceForm(
              window.bbobject.uc,
              {id: window.bbobject.form.id}
            );
          }

          $('.form-pagination').hide();
        } else {
          $(`.form-pagination [data-key="${index+1}"]`).addClass('active');
        }
      }
    });

    if ( $.isFunction($.fn.mask) ) {
      let phoneField = $('.form-label-phone input');
      phoneField.prop('placeholder', '+1 (222) 222-2222');
      phoneField.mask('+1 (000) 000-0000');
    }


  }

  $('body').on('mouseover', '[data-tid]', function(){
    var $this = $(this),
        tid = $this.data('tid');

    if (window.trace) {
      var tile = window.bbobject.tiles.find(function(item){
        return item.id == tid;
      });

      window.traceEvent(
        window.bbobject.uc,
       'tile_over',
        tile,
        player.getCurrentTime(),
      );
    }
  });

  $('body').on('click', '[data-tid]', function(){
    var $this = $(this),
        tid = $this.data('tid');

    if (window.trace) {
      var tile = window.bbobject.tiles.find(function(item){
        return item.id == tid;
      });

      window.traceEvent(
        window.bbobject.uc,
       'tile_clicked',
        tile,
        player.getCurrentTime(),
      );
    }
  });

  $('body').on('mouseover', '[data-cta-id]', function(){
    var $this = $(this),
        tid = $this.data('cta-id');

    if (window.trace) {
      var tile = window.bbobject.tiles.find(function(item){
        return item.id == tid;
      });

      window.traceEvent(
        window.bbobject.uc,
       'tile_cta_over',
        tile,
        player.getCurrentTime(),
      );
    }
  });

  $('body').on('click', '[data-cta-id]', function(){
    var $this = $(this),
        tid = $this.data('cta-id');

    if (window.trace) {
      var tile = window.bbobject.tiles.find(function(item){
        return item.id == tid;
      });

      window.traceEvent(
        window.bbobject.uc,
       'tile_cta_clicked',
        tile,
        player.getCurrentTime(),
      );
    }
  });

  player.addEventListener('timeupdate', function(){
    var ctime = player.getCurrentTime();

    var tiles = window.bbobject.tiles.filter(function(item){
      return (ctime > item.time && !item.displayed);
    });

    for(var i in tiles) {
      tiles[i].displayed = 1;

      var $elem = $(`.tile-item[data-tid="${tiles[i].id}"]`);
      var condition = tiles[i]['params']['condition'];

      if (condition) {
        var current_value = window.bbobject.data[condition.key];
        if (current_value == condition['value'] || (condition['value'] == 'empty' && !current_value)) {
          $elem.show();
        } else {
          $elem.addClass('should-display');
        }
      } else {
        $elem.show();
      }
    }
  });

  function isEmail(email) {
    let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }

}, 1000);








