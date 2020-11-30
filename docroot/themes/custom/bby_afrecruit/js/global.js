/**
 * @file
 * Global utilities.
 *
 */
(function($, Drupal) {

  'use strict';

  $.fn.closeDropdowns = function() {
    $('[data-toggle="dropdown"]').dropdown('hide');
  }

  $.fn.reloadPage = function() {
    window.location.reload();
  }

  Drupal.behaviors.bootstrap_barrio_subtheme = {
    attach: function(context, settings) {
      var position = $(window).scrollTop();
      $(window).scroll(function () {
        if ($(this).scrollTop() > 50) {
          $('body').addClass("scrolled");
        }
        else {
          $('body').removeClass("scrolled");
        }
        var scroll = $(window).scrollTop();
        if (scroll > position) {
          $('body').addClass("scrolldown");
          $('body').removeClass("scrollup");
        } else {
          $('body').addClass("scrollup");
          $('body').removeClass("scrolldown");
        }
        position = scroll;
      });


      $('.iframe-preview-selector a').once('iframe-ps').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        $('.iframe-preview-selector a').addClass('grayed');
        $this.removeClass('grayed');
        $('.iframe-preview iframe').attr('class', '').addClass($this.data('device'));
      });

      $('#copy-link').once('copy-link').on('click', function(e){
        e.preventDefault();
        var $parent = $(this).parents('.modal'),
            $input = $parent.find('input');

          $input.select();
          document.execCommand('copy');
      });


      $('body').once('save-button').on('click', '.squadron-list .save-button', function(e) {
       const squadron = $('input[name="squadron"]').val();
       $('#edit-field-registry-number-0-value').val(squadron);
       Drupal.ajax.instances[1].options.url= '/address-popup/' + squadron;
       $('.ui-dialog-titlebar-close').click();
     });

      $('body').once('address-save-button').on('click', '.address-list .save-button', function(e) {
       $('#edit-field-recruiter-address-0-value').val($('input[name="address"]').val());
       $('.ui-dialog-titlebar-close').click();
     });

     $('body').once('close-button').on('click', '.popup-class .cancel-button', function(e) {
       $('.ui-dialog-titlebar-close').click();
     });

     $('input[name="field_check_squadron[value]"]').once('squadron-check').on('change', function(e) {
         $('.field--name-field-recruiter-address').fadeToggle("slow");
     });

     //show zip codes
     $('.zipcode-click').once('close-zipcode').on('click', function(e) {
       var $this = $(this);

       $this.siblings(".zipcode").toggle();

       if($this.text() == "Collapse") {
         $this.text("Click to see All Zip Codes");
       } else{
         $this.text("Collapse");
       }
     });
   }
 }

 $(window).ready(function() {
  if(!(localStorage.getItem('clicked')))
    $('.squadron-access').once('squadron-access').click();
  });

  $('body').once('squadron-click').on('mousedown', '.squadron-access .ui-dialog-titlebar-close',
    function() {
      localStorage.setItem('clicked', true);
  });

  $(window).ready(function() {
    $('.squadron-access').once('squadron-access').click();
  })
})(jQuery, Drupal);
