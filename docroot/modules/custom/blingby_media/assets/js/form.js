(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.videoForm = {
    attach: function (context, settings) {
      $('label.browse-video').each(function(){
        var $this =  $(this),
        file = $this.parent().find('[type="file"]');

        if (file.length) {
          $this.attr('for',file.attr('id')).show();
        } else {
          $this.hide();
        }
      });
    }
  }
})(jQuery, Drupal, drupalSettings);