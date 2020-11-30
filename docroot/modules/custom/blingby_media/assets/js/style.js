(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.videoStyle = {
    attach: function (context, settings) {

      $('.field--widget-field-blingby-style .video-style-selected a').once('selector').on('click', function(e){
        e.preventDefault();
        $(this).parent().next().toggle();
      });

      $('.field--widget-field-blingby-style .item-list li label', context).once('selector').on('click', function(){
        var $this =  $(this),
        container = $this.parents('.field--widget-field-blingby-style');

        $('.field--widget-field-blingby-style .item-list li label').removeClass('selected');
        $this.addClass('selected');

        var option = $this.find('input').val();
        container.find('.form-control').val(option);
        container.find('.video-style-selected a').text($this.find('h5').text());
      });
    }
  }
})(jQuery, Drupal, drupalSettings);