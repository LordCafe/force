(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.bbForm = {
    attach: function (context, settings) {
      $('.bform-field-container h2', context).once('toggleField').on('click', function(){
        var $this = $(this),
          $parent = $this.parent();
        $parent.toggleClass('closed');

        if ($parent.hasClass('closed')) {
          var text = $parent.find('.full-width input').val();
          if (text) {
            $this.find('small').text(' - ' + text);
          }
          $this.find('i').attr('class', 'fa fa-angle-up');
        } else {
          $this.find('i').attr('class', 'fa fa-angle-down');
        }

      });
    }
  };
})(jQuery, Drupal, drupalSettings);
