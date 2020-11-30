(function ($, Drupal, drupalSettings) {

  var media_loading = '<div class="alert alert-light mt-3"><i class="fa fa-spin fa-spinner"></i> Loading...</div>';
  var currentTile = false,
  player = false;

  $.fn.deleteTile = function(tid) {
    $('#tile-form-wrapper input[name="id"]').val(0);
    $('#tile-form-wrapper select[name="plugin"]').val('').trigger('change');
    $('[data-tid="'+tid+'"]').remove();
    var index = drupalSettings.blingby_video.tiles.findIndex(ctile => ctile.id == tid);
    drupalSettings.blingby_video.tiles.splice(index, 1);
  };

  $.fn.sorTiles = function() {
    var tiles = drupalSettings.blingby_video.tiles;

    tiles.sort(function(a, b){
      return (a.time < b.time)? -1 : 1;
    });

    drupalSettings.blingby_video.tiles = tiles;
  };

  $.fn.clearTileForm = function(tile) {

    $('#tile-form-wrapper input[name="id"]').val(0);
    $('#tile-form-wrapper select[name="plugin"]').val('').trigger('change');
    if (tile) {
      var index = drupalSettings.blingby_video.tiles.findIndex(ctile => ctile.id == tile.id);
      if (index > -1) {
        currentTile = false;
        drupalSettings.blingby_video.tiles[index] = tile;
        $.fn.sorTiles();
        var cindex = drupalSettings.blingby_video.tiles.findIndex(ctile => ctile.id == tile.id);
        $('[data-tid]').removeClass('selected');
        $.fn.addTile(tile, tile.id, cindex -1);
      } else {
        $.fn.addTile(tile, 0 , 0, 1);
      }
    }
  };

  $.fn.addTile = function(tile, tid, pindex, is_new) {
    var template = $('.tile-template').clone();
    template.removeClass('tile-template');
    template.find('.tile-title').text(tile.title);
    if (tile.image) {
      template.find('.tile-image img').attr('src', tile.image).removeClass('d-none');
    }

    template.attr('data-tid', tile.id);
    template.attr('data-plugin', tile.plugin);
      
    if (tid) {
      if (pindex > -1) {
        $('[data-tid="'+tid+'"]').remove();
        var ptid = drupalSettings.blingby_video.tiles[pindex].id;
        template.insertAfter('[data-tid="'+ptid+'"]');
      } else {
        $('[data-tid="'+tid+'"]').remove();
        template.insertAfter('.tile-template');
      }
    } else {
      if (is_new) {
        drupalSettings.blingby_video.tiles.push(tile);
        template.insertAfter('.tile-template');
      } else {
        $('#tile-container').append(template);
      }
    }
  }

  function formatSeconds(totalSeconds){
    var hours   = Math.floor(totalSeconds / 3600);
    var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);
    var seconds = totalSeconds - (hours * 3600) - (minutes * 60);
    seconds = Math.round(seconds * 100) / 100;
    var result = (hours < 10 ? "0" + hours : hours);
    result += ":" + (minutes < 10 ? "0" + minutes : minutes);
    result += ":" + (seconds  < 10 ? "0" + seconds : seconds);
    return result+':00';
  }

  Drupal.behaviors.videoEditor = {
    attach: function (context, settings) {
      $('#player-container').once('setup').each(function(){
        player = new videoProvider({
          element: 'player',
          dom: this,
          autoplay: true,
          mute: true,
          id: settings.blingby_video.provider_id,
        });
        player.init();

        player.addEventListener('play', function(){
        });

        player.addEventListener('pause', function(){
        });

        player.addEventListener('timeupdate', function(){
          var ctime = formatSeconds(player.getCurrentTime());
          $('.tile-current-time').text(ctime);
        });
      });

      $('#tile-container').once('tiles-setup').each(function(){
        $.fn.sorTiles();
        var tiles = settings.blingby_video.tiles;
        for(var tid in tiles) {
          var tile = tiles[tid];
          $.fn.addTile(tile);
        }
      });

      $('body').once('setup-timestamp').on('click', '[data-tid] .btn', function(e){
        e.preventDefault();
        player.pause();
        var ctime = formatSeconds(player.getCurrentTime());
        player.play();
        $('input[name="time"]').val(ctime);
        $('button[name="op"]').trigger('mousedown')
      });

      $('body').once('setup-editor').on('click', '[data-tid] a', function(e){
        e.preventDefault();
        var $this = $(this).parent(),
        tid = $this.data('tid'),
        plugin = $this.data('plugin');

        if (currentTile == tid) {
          return
        }

        currentTile = tid;

        $('[data-tid]').removeClass('selected');
        $this.addClass('selected');
        $('#tile-form-wrapper input[name="force"]').val(1);
        $('#tile-form-wrapper input[name="id"]').val(tid);
        $('#tile-form-wrapper select[name="plugin"]').val(plugin).trigger('change');
      });


      $('body').once('setup-back').on('click', 'input.btn-cancel', function(e){
        $('[data-tid]').removeClass('selected');
        currentTile = false;
        $('#tile-form-wrapper input[name="force"]').val(1);
        $('#tile-form-wrapper input[name="id"]').val(0);
        $('#tile-form-wrapper select[name="plugin"]').val('').trigger('change');
      });


      $('body').once('scrapper-item-processed').on('click', '#scrapper-item-box ul label', function (e) {
        var $this = $(this);
        var $container = $('#scrapper-item-box');
        var url = $this.data('url');
        var image = $this.data('image');
         $('[name="url"]').val(url);
        $container.html(media_loading);

        $.ajax({
          type: 'post',
          dataType: 'json',
          url: '/api/scrapper',
          data: {
            image: image
          },
          success: function(data){
            $container.html('');
            if (window.setupTileForCrop) {
              setupTileForCrop(data.image);
            }
          }
        });
      });


      $('input[name="time"]').each(function(){
        $(this).mask('99:99:99:99');
      });

    }
  };
})(jQuery, Drupal, drupalSettings);