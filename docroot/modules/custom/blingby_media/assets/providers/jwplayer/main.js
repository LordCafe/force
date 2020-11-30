var that;

//@TODO this should be able to configure as well.
jwplayer.key = "z3K85kT086VTUTHhS6Gnttn/4pLv7pKNmlKHIfav4alMIBqQ"

var videoProvider = function(params) {
  this.player = false;
  this.params = params;
  this.timer;
}


videoProvider.prototype.init = function() {
  that = this;
  if (!this.params.id || this.params.id == '0') {
    this.params.id = 'R4VF23OZ';
  }


  this.player = jwplayer(this.params.element);
  this.player.setup({
    title: '',
    file: `//content.jwplatform.com/videos/${this.params.id}.mp4`,
    controls: true,
    skin: {
      name: "bekle"
    },
    autostart: this.params.autoplay,
    mute: this.params.mute,
  });

  this.player.on('ready', function(event){
    that.player.on('play', function(){
      that.dispatchEvent('play');
    });

    that.player.on('pause', function(){
      that.dispatchEvent('pause');
    });

    that.player.on('complete', function(){
      that.dispatchEvent('ended');
    });

    that.player.on('time', function(){
      that.dispatchEvent('timeupdate');
    });

  });
}

videoProvider.prototype.addEventListener = function(event, callback) {
  this.params.dom.addEventListener(event, callback);
}

videoProvider.prototype.dispatchEvent = function(event) {
  this.params.dom.dispatchEvent(new Event(event));
}

videoProvider.prototype.play = function() {
  if (!this.player) return;
  this.player.play();
}

videoProvider.prototype.pause = function() {
  if (!this.player) return;
  this.player.pause();
}

videoProvider.prototype.stop = function() {
  if (!this.player) return;
  this.player.stop();
}

videoProvider.prototype.seek = function(time) {
  if (!this.player) return;
  this.player.seek(time);
}

videoProvider.prototype.mute = function() {
  if (!this.player) return;
  this.player.setMute(true);
}

videoProvider.prototype.unMute = function() {
  if (!this.player) return;
  this.player.setMute(false);
}

videoProvider.prototype.isMuted = function() {
  if (!this.player) return false;
  return this.player.getMute();
}

videoProvider.prototype.getCurrentTime = function() {
  if (!this.player) return false;
  return Math.floor(this.player.getPosition());
}