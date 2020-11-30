var that;

var videoProvider = function(params) {
  this.player = false;
  this.params = params;
  this.timer;
}

videoProvider.prototype.init = function() {
  var tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  that = this;
  window.onYouTubeIframeAPIReady = function() {
    that.player = new YT.Player(that.params.element, {
      videoId: that.params.id,
      events: {
        'onReady': that.onPlayerReady,
        'onStateChange': that.onPlayerStateChange
      }
    });
  }
}

videoProvider.prototype.addEventListener = function(event, callback) {
  this.params.dom.addEventListener(event, callback);
}

videoProvider.prototype.dispatchEvent = function(event) {
  this.params.dom.dispatchEvent(new Event(event));
}

videoProvider.prototype.onPlayerReady  = function(event) {
  if (that.params.mute) {
    that.mute();
  }

  if (that.params.autoplay) {
    that.play();
  }

  that.setupTimer();
}

videoProvider.prototype.onPlayerStateChange  = function(event) {
  switch( event.data ) {
    case YT.PlayerState.ENDED:
      that.dispatchEvent( 'ended' );
      break;

    case YT.PlayerState.PLAYING:
      that.dispatchEvent( 'play' );
      break;

    case YT.PlayerState.PAUSED:
      if ( that.player.getDuration() !== that.player.getCurrentTime() ) {
        that.dispatchEvent( 'pause' );
      }
      break;

    // buffering
    case YT.PlayerState.BUFFERING:
      break;

    // video cued
    case YT.PlayerState.CUED:
      break;
  }
}

videoProvider.prototype.setupTimer = function() {
  this.timer = setInterval(function(){
    that.dispatchEvent( 'timeupdate' );
  }, 250);
}

videoProvider.prototype.clearTimer = function() {
  clearInterval(this.timer);
}


videoProvider.prototype.play = function() {
  if (!this.player) return;
  this.player.playVideo();
}

videoProvider.prototype.pause = function() {
  if (!this.player) return;
  this.player.pauseVideo();
}

videoProvider.prototype.stop = function() {
  if (!this.player) return;
  this.player.stopVideo();
}

videoProvider.prototype.seek = function(time) {
  if (!this.player) return;
  this.player.seekTo(time, true);
}

videoProvider.prototype.mute = function() {
  if (!this.player) return;
  this.player.mute();
}

videoProvider.prototype.unMute = function() {
  if (!this.player) return;
  this.player.unMute();
}

videoProvider.prototype.isMuted = function() {
  if (!this.player) return false;
  return this.player.isMuted();
}

videoProvider.prototype.getCurrentTime = function() {
  if (!this.player) return false;
  return Math.floor(this.player.getCurrentTime());
}
