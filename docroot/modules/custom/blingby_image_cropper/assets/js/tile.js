(function ($, Drupal, drupalSettings) {

  window.setupTileForCrop = function (image) {

    var $container = $('<div id="crop-container"></div>');
    $container.insertAfter('.form-item-files-image');
    var img = $('<img>');
    img.attr('src', image);
    $container.find('button').remove();
    $container.find('img').croppie('destroy').remove();
    $container.append(img);
    //@TODO move this to config from styles
    img.croppie({
      viewport: { width: 460, height: 180},
      boundary: { width: 600, height: 500 },
      enableOrientation: true,
    });

    var btn = $('<button/>');
    btn.attr('type', 'button');
    btn.text('Save').addClass('btn btn-outline-success');

    btn.on('click', function(){
      img.croppie('result', 'base64', { size: 'viewport'}).then(function(data){
        $('[name="files[image]"]').val('');
        $('[name="image_content"]').val(data);
        setupImage(data);
        $('.group-actions').show();
        $('.js-form-item').show();
      })
    })
    $container.append(btn);

    $('.group-actions').hide();
    $('.js-form-item').hide();
  }

  window.setupImage = function (image) {
    var $container = $('#crop-container');
    $container.remove();

    $('[name="files[image]"]').val('');
    if (image) {
      $('.with-image').show();
      $('.with-image img').attr('src', image);
      $('[name="files[image]').addClass('d-none');
    } else {
      $('.with-image').hide();
      $('[name="files[image]').removeClass('d-none');
    }
  }

  Drupal.behaviors.tileCropper = {
    attach: function (context, settings) {
      $('body').once('remove-image').each(function(){
        $('body').on('click','.remove-image', function(e){
          e.preventDefault();
          $('[name="image_content"]').val('');
          $('[name="has_image"]').val('');
          $('.with-image').hide();
          $('[name="files[image]').removeClass('d-none');
        });
      });

      $('body').once('tile-image-processed').on('change','[name="files[image]"]', function() {
        var input = this;
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) {   
            setupTileForCrop(e.target.result);
          };
          reader.readAsDataURL(input.files[0]);
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);