(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.videoEditor = {
    attach: function (context, settings) {

      $('.analytics-filters a').once('filter').on('click', function(e){
        e.preventDefault();
        var $this = $(this),
        filter = $this.data('filter')

        $('.analytics-filters a').removeClass('selected');
        $this.addClass('selected');

        if (filter == 'all') {
          $('.analytics-item').show(); 
        } else if (filter == 'green') {
          $('.analytics-item').show();
          $('.has-yellow').hide();
          $('.has-red').hide();
        } else {
          $('.analytics-item').hide();
          $('.has-'+filter).show();
        }

      });

      $('[data-toggle="tooltip"]').tooltip();

      $('select#form-selection').once('filter').on('change', function(e){
        var $this = $(this),
        fid = $this.val();
        $('.form-detail').hide();
        $('.form-'+fid).show();

      });
    }
  }

})(jQuery, Drupal, drupalSettings);