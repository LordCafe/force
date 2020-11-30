window.bbobject;
window.uc;
(function($){
  $(document).ready(function(){
    $('[data-blingby-video]').each(function(){
      var $container = $(this), 
        id = $container.data('blingby-video'), 
        preview = $container.data('preview');

      window.uc = Cookies.get('uc') || 0;
      $.ajax({
        url: `/api/video/${id}?preview=${preview}&uc=${window.uc}`,
        dataType: 'json',
        success: function(response) {
          if (!(typeof response.data === 'object' && response.data !== null)) {
            response.data = {}
          }

          window.bbobject = response;
          Cookies.set('uc', window.bbobject.uc);
          for(var i = 0; i < response.css.length; i++) {
            var $css = $('<link>').attr('rel', 'stylesheet').attr('href',response.css[i]);
            $container.append($css);
          }

          for(var i = 0; i < response.js.length; i++) {
            var $js = $('<script>').attr('type', 'text/javascript').attr('src',response.js[i]);
            $container.append($js);
          }
        }
      });
    });
  });
}(jQuery));