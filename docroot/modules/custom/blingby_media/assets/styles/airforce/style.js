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

  if (!window.bbobject.history.includes(tile.id)) {
    window.bbobject.history.push(tile.id);

    $('.main-modal-tiles').append(
     `<div class="tile-item">
        <img src="${tile.image}">
        <span>${tile.title}</span>
      </div>`
    );
  }

  $('.video-details').prepend(tile_template({tile: tile}));
  $('.main-container').addClass('current-open');

  if (tile.form) {
    $('.tile-form .tile-form-options label').on('click', function(){
       var $parent = $(this),
           $container = $parent.parent();
        $container.find('label').removeClass('selected');
        $parent.addClass('selected');
    });


    $('.tile-form .tile-form-field').each(function(){
      var $this = $(this),
          index = $this.data('index'),
          field = tile.form.fields[index],
          value = window.bbobject.data[field.key];

      if (value) {
        if (field.type == 'checkbox' || field.type == 'radio') {
          $this.find(`[name="${field.key}"][value="${value}"]`).prop('checked', true).parent().addClass('selected');
        } else {
          $this.find(`[name="${field.key}"]`).val(value);
        }
      } else {
        if (field.key == 'fullname' && (window.bbobject.data['firstname'] && window.bbobject.data['lastname'])) {
          var fullname = `${window.bbobject.data['firstname']} ${window.bbobject.data['lastname']}`;
          $this.find(`[name="${field.key}"]`).val(fullname);
        }
      }
    });


    $('.tile-form .tile-form-actions button').on('click', function(e){

      var $container = $('.tile-form');

      if (window.trace) {

        var form = {
          id: tile.form.id,
          fields: {}
        }

        for (var index in tile.form.fields) {
          var field = tile.form.fields[index],
          value = false;

          if (field.type == 'checkbox' || field.type == 'radio') {
            value = $container.find(`[name="${field.key}"]:checked`).val();
          } else {
            value = $container.find(`[name="${field.key}"]`).val();
          }

          form.fields[field.key] = value;
          window.bbobject.data[field.key] = value;
        }

        window.traceForm(
          window.bbobject.uc,
          form
        );
      }

      e.preventDefault();
      $container.find('.tile-form-field').hide();
      $container.find('.tile-form-actions').hide();
      $container.find('.tile-form-completed').show();
    })
  }


  if (tile.plugin == 'contact') {

    if (window.bbobject.data['zipcode']) {
      $('.tile-current--contact-zipcode input').val(window.bbobject.data['zipcode']);
    }

    $('.tile-action-map').on('click', function(e){
      e.preventDefault();
      var $this = $(this);
      $this.hide();
      $('.tile-current--contact-zipcode').hide();
      $('.tile-current--details').hide();
      $('.tile-action-contact').show();

      $('.tile-current--title').text('YOUR LOCAL RECRUITMENT CENTER');
    });

    $('.tile-action-contact').on('click', function(e){
      e.preventDefault();

      if (!window['local_recruiter']) {
        var zipcode = $('.tile-current--contact-zipcode input').val();
        $.get('/api/recruiter/'+zipcode, function(data){
          window['local_recruiter'] = data.recruiter;
           $('.tile-action-contact').trigger('click');
        });
      } else {
        var $this = $(this);
        $this.hide();
        $('.tile-current--contact-zipcode').hide();
        $('.tile-action-map').show();
        $('.tile-current--details').show();

        $('.tile-current--title').text('CONTACT YOUR RECRUITER');

        $('.tile-current--description').html(`
          Hi, I am Staff ${window.local_recruiter.title} ${window.local_recruiter.fullname}! I am your local recuiter, contact me directly.<br><br>
          Phone #: ${window.local_recruiter.phone}<br><br>
          Email: ${window.local_recruiter.email}
        `);
      }
    });

  }

});


$('body').on('click', '.form-open', function(e){
  e.preventDefault();
  $('.main-container').addClass('main-form-open');
});

$('body').on('click', '.form-close', function(e){
  e.preventDefault();
  $('.main-container').removeClass('main-form-open');
});


$('body').on('click', '.video-history', function(e){
  $('.main-modal').toggle();
});

$('body').on('click', '#change-tiles', function(e){
  e.preventDefault();
  var $this = $(this);
  $('.main-container').toggleClass('show-tiles');

  if ($this.text() == 'T') {
    $this.text('Q');
  } else {
    $this.text('T');
  }
});

$('body').on('click', '.tile-close', function(e){
  e.preventDefault();
  $('.main-container').removeClass('current-open');
  $('.tile-current').remove();
  $('.video-details-info').show();
  $('.tile-item a').removeClass('active');
});




