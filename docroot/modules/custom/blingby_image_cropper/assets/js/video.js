(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.videoCropper = {
    attach: function (context, settings) {

      var setupCoverForCrop = function (image) {
        var $container = $('#crop-container');
        var img = $('<img>');
        img.attr('src', image);
        $container.find('button').remove();
        $container.find('img').croppie('destroy').remove();
        $container.append(img);
        img.croppie({
          viewport: { width: 640, height: 360 },
          boundary: { width: 640, height: 360 }
        });

        var btn = $('<button/>');
        btn.attr('type', 'button');
        btn.text('Save').addClass('btn btn-outline-success');

        btn.on('click', function(){
          img.croppie('result', 'base64', { width: 640, height: 360 }).then(function(data){
            $('[name="files[field_image]"]').val('');
            $('[name="field_image[0][image_content]"]').val(data);
            setupImage(data);
          })
        })
        $container.append(btn);
      }

      var setupImage = function (image) {
        var $container = $('#crop-container');
        $container.find('img').croppie('destroy').remove();
        $container.find('button').remove();
        $container.html('');
        $('[name="files[field_image]"]').val('');
        
        if (image) {
          $('.no-image').hide();
          $('.with-image').show();
          $('.with-image img').attr('src', image);
        } else {
          $('.with-image').hide();
          $('.no-image').show();
        }
      }

      $('.remove-image', context).once('remove-image').each(function(){
        $(this).on('click', function(e){
          e.preventDefault();
          $('[name="field_image[0][image_content]"]').val('');
          $('[name="field_image[0][has_image]"]').val('');
          $('.cover-image .with-image').hide();
          $('.cover-image .no-image').show();
        });
      });

      
      $('#media-container').once('add-croppie').each(function(){
        var $croppie = $('<div id="crop-container"></div>');
        $croppie.insertBefore(this);
      });


      $('[name="files[field_image]"]', context).once('media-image-processed').change(function() {
        var input = this;
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) {   
            setupCoverForCrop(e.target.result);
          };
          reader.readAsDataURL(input.files[0]);
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);