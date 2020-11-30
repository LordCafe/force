$('body').on('click', '.tile-link', function(e){
  e.preventDefault();
  var $this = $(this).parent(), tid = $this.data('tid');
  $('.tile-item a').removeClass('active');
  $this.find('a').addClass('active');
  
  $('.video-details-info').hide();
  $('.tile-current').remove();

  var tile = window.bbobject.tiles.find(function(item){
    return item.id == tid;
  });

  $('.video-details').prepend(tile_template({tile: tile}));
});

$('body').on('click', '.tile-close', function(e){
  e.preventDefault();
  $('.tile-current').remove();
  $('.video-details-info').show();
  $('.tile-item a').removeClass('active');
});