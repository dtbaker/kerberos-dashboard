(function ($) {
  'use strict';

  var Cam = function (settings) {
    var config = settings;
    var stream_url = '';
    var $live_preview = null;

    return {
      init: function () {
        // get the settings from the api.
        // this.api( '/api/v1/stream', function(data){
        //   console.log(data);
        // });

      },
      api: function( endpoint, success, error ){
        // We need to proxy to get around the browser security model with OPTIONS checks. Meh.
        $.ajax({
          // url: 'http://' + config.ip + '/api/v1/stream',
          url: 'proxy.php?cam=' + config.name + '&endpoint=' + endpoint,
          type: 'GET',
          dataType: 'json',
          success: success,
          error: function() {
            //alert('Failed to communicate with ' + endpoint);
          },
        });
      },

      show_preview: function ( $container ) {
        $live_preview = $('<div class="webcam__stream"></div>');
        $live_preview.append('<div class="webcam__details"><a href="http://' + config.ip + ':' + config.port + '/settings">' + config.name + '</a></div>');
        setInterval(function() {
          // hunt for recent activity

        },1000);
        $container.append($live_preview);
        var viewer = new MJPEGCANVAS.Viewer({
          div : $live_preview.get(0),
          host : config.ip,
          port : config.streamport,
          width : 640,
          height : 400,
          topic : '',
          interval : 500
        });

      },

      get_recent_events: function () {
        var t = this;
        var events = [];
        // t.api( 'get_recent', function(data){
        t.api( '/api/v1/images/latest_sequence', function(data){
          $live_preview.find('.webcam__events').remove();
          if(data) {
            var $webcam_events = $('<div class="webcam__events"></div>');
            for(var i = 0; i < data.length; i++){
              if( data[i].type == 'image' ) {
                $webcam_events.append($('<div><a href="' + data[i].src + '" target="_blank">(' + data[i].metadata.numberOfChanges + ') ' + data[i].time + '</a></div>'));
              }
            }
            $live_preview.append($webcam_events);
          }
        } );

      },
    }


  };

  var Dashboard = {

    config: {},
    $preview_holder: null,
    cameras: [],

    load_config: function () {
      var t = this;
      $.getJSON("config.json", function (data) {
          t.config = data;
          if (t.config.servers) {
            t.load_servers();
            t.load_previews();
            t.load_events();
          }
        },
      )
    },

    load_servers: function () {
      var t = this;
      for (var i in t.config.servers) {
        if (t.config.servers.hasOwnProperty(i)) {
          var cam = new Cam(t.config.servers[i]);
          cam.init();
          t.cameras.push(cam);
        }
      }
    },
    load_previews: function () {
      var t = this;
      t.$preview_holder = $('.webcams');
      t.$preview_holder.empty();
      for (var c = 0; c < t.cameras.length; c++) {
        t.cameras[c].show_preview( t.$preview_holder );
      }
    },

    load_events: function () {
      var t = this;
      var recent_events = [];
      for (var c = 0; c < t.cameras.length; c++) {
        t.cameras[c].get_recent_events();
      }
      setTimeout(function(){
        t.load_events();
      }, 60000);
    },

    init: function () {

      var t = this;
      t.load_config();

    },
  };

  $(function () {
    Dashboard.init();
  });

}(jQuery));
