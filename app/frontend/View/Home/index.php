<?php $T->addJs(array('http://maps.google.com/maps/api/js?sensor=false', 'jquery.ui.map', 'jquery.ui.map.services')) ?>

<?php $T->blockStart('content') ; ?>

<script language="javascript">
var markerA = null;
var markerB = null;
var markerStart = null;
var markerNearby = [];
var idstart;
var idend;

var ico_start   = '/img/start.png';
var ico_stop    = '/img/finish.png';

var ico_angkot  = '/img/angkot.png';
var ico_bus     = '/img/busstop.png';
var ico_cctv    = '/img/cctv.png';
var ico_taxt    = '/img/taxi.png';

var angkutanPath;

var is_start = true;

$(function() {
  var jakarta = new google.maps.LatLng(-6.227550, 106.828308);
  loc_x = jakarta;

  $('#map_canvas')
    .gmap({'center': jakarta, 'zoom': 12})
    .bind('init', function(event, map) { 
      $(map).rightclick( function(event) {
        $('#map_canvas').gmap('addMarker', {
          'position': event.latLng, 
          'draggable': true, 
          'bounds': false,            
        }, function(map, marker) {
          findLocation(marker.getPosition(), marker);       
        });
      });
    });

  $("#cari").blur(function() {
    var loc = $(this).val();

    $('#map_canvas').gmap('search', { 'address': 'indonesia, jakarta, '+loc }, function(results,isFound) {
      if (isFound) {
        map = $('#map_canvas').gmap('get','map');
        map.panTo(results[0].geometry.location);
        map.setZoom(15);

        $('#map_canvas').gmap('search', {'location': results[0].geometry.location }, function(results, status) {
          // if ( status === 'OK' ) {  
          //   $('#sourcepositionlabel').html( results[0].formatted_address );
          // }
        });
      }
    });
  });
}); 

function setPoint(type) {    
  var center = $('#map_canvas').gmap('get', 'map').getCenter();
  

  if(type === 'a'){        
    if (markerA == null) {      
      map = $('#map_canvas').gmap('get', 'map');
      markerA = new google.maps.Marker({ map: map, 'bounds': false, draggable: true, 'icon': ico_start});
      markerA.setPosition(center);


    } else {
      markerA.setPosition(center);
    }

    var infoWindow = new google.maps.InfoWindow();
    infoWindow.setContent(
        'Klik icon untuk mendapat rute dan pemberhentian terdekat.'
        );

    google.maps.event.addListener(markerA, 'dragend', function() {    
      findLocation('a', markerA.getPosition());
    });    

    google.maps.event.addListener(markerA, 'click', function () {
      getNearbyPoint('a');      
      
      infoWindow.close(map, this);
    });
    infoWindow.open(map, markerA);


    // $("#map_canvas").gmap("openInfoWindow", { "content": 'test' });

  } else {
    if (markerB == null) {      
      map = $('#map_canvas').gmap('get', 'map');
      markerB = new google.maps.Marker({ map: map, 'bounds': false, draggable: true, 'icon': ico_stop });
      markerB.setPosition(center);
    } else {
      markerB.setPosition(center);
    }

    // var infoWindow = new google.maps.InfoWindow();
    // infoWindow.setContent(
    //     'Klik icon untuk mendapat pemberhentian terdekat.'
    //     );

    google.maps.event.addListener(markerB, 'dragend', function() {    
      findLocation('b', markerB.getPosition());
    });

    google.maps.event.addListener(markerB, 'click', function () {
      getNearbyPoint('b');      
      
      // infoWindow.close(map, this);
    });
    // infoWindow.open(map, markerB);
  }

  findLocation(type, center);
}

function findLocation(type, location, marker) {

  $('#map_canvas').gmap('search', {'location': location}, function(results, status) {
    if ( status === 'OK' ) { 
      if(type == 'a' && markerA != null)  {
        $('#sourcepositionlabel').html( results[0].formatted_address +'<hr /><span id="ico_start"><img src="'+ico_start+'"></span><span id="ico_stop"></span>');  
        is_start = false;
      } else {        
        $('#destinationpositionlabel').html( results[0].formatted_address  +'<hr /><img src="'+ico_stop+'">&nbsp;<button type="button" class="btn btn-info" onclick="calculateRoute();">Calculate route</button>');  
        $('#panInfoStop').empty();
        $('#panInfo').append('<span id="panInfoStop"><br /><strong>Set posisi pemberhentian: </strong>'+results[0].formatted_address+'</span>');
      }      
    }
  });  
}

function getNearbyPoint(type)
{  
  if(type == 'b') {
    var point = markerB.getPosition();  
  } else {
    var point = markerA.getPosition();  
  }
  
  
  $.ajax({
    type: "GET",
    url: "backend/api-nearby-point",
    dataType: "json",
    data: { q: JSON.stringify(point) },
    contentType: "application/json",
    success: function(data) {
      callbackDraw(data)
    },
    error: function (err) {
      console.log(err);
    }
  });

  if(type == 'a') {
    getNearbyRoute();
  }
}

function calculateRoute() {
  point0 = markerA.getPosition();  
  pointA = markerStart.getPosition();  
  pointB = markerB.getPosition();  

  // alert(idstart+'--'+point0+'--'+pointA+'--'+pointB);


  $.ajax({
    type: "GET",
    url: "backend/api-get-route",
    dataType: "json",
    data: { 'srcid' : idstart, 'dstid' : 'B510'},
    contentType: "application/json",
    success: function(data) {
      callbackDrawRoutePath(data);      
    },
    error: function (err) {
      console.log(err);
    }
  });

}

function getNearbyRoute()
{  
  // var point0 = markerA.getPosition();  
  // var pointA = markerStart.getPosition();  
  // var pointB = markerB.getPosition();  
  var point = markerA.getPosition(); 

  // var route = new Array();
  // route[0] = point0;
  // route[1] = pointA;
  // route[2] = pointB;

  $.ajax({
    type: "GET",
    url: "backend/api-nearby-route",
    dataType: "json",
    data: { q: JSON.stringify(point) },
    contentType: "application/json",
    success: function(data) {
      callbackDrawRoute(data);      
    },
    error: function (err) {
      console.log(err);
    }
  });
}

// function drawRoute(data) {
//   map = $('#map_canvas').gmap('get', 'map');


//   var flightPlanCoordinates = [
//     new google.maps.LatLng(37.772323, -122.214897),
//     new google.maps.LatLng(21.291982, -157.821856),
//     new google.maps.LatLng(-18.142599, 178.431),
//     new google.maps.LatLng(-27.46758, 153.027892)
//   ];
//   var flightPath = new google.maps.Polyline({
//     path: flightPlanCoordinates,
//     geodesic: true,
//     strokeColor: '#FF0000',
//     strokeOpacity: 1.0,
//     strokeWeight: 2
//   });

//   flightPath.setMap(map);
// }

function callbackDraw(data) {
  
  var listPoint = '<strong>Pemberhentian terdekat:</strong><br /><ul>';

  $.each(data.result, function (i, r) {
    drawNearByPoint(r);
    listPoint += '<li>'+r.label+'</li>';    
  });
  listPoint += '</ul>';

  $('#panInfo').empty().append(listPoint);
}

function callbackDrawRoute(data) {  
  map = $('#map_canvas').gmap('get', 'map');

  var routeAngkutan = [];  
  var pathAngkutan = [];  

  var listRoute = '<strong>Route terdekat:</strong><br /><ul>';
  var rute = '';
  var color = '';
  $.each(data.result, function (i, r) {

    if(rute != r.name) { 
      color = '#'+(Math.random()*0xFFFFFF<<0).toString(16);
      listRoute += '<li style="background: '+color+';">--- '+r.name+'</li>';       
    }
    drawRoute(r.name, r.type, r.routes, color);

    
    rute = r.name;
  });  
  listRoute += '</ul>';

  $('#panInfo').append(listRoute);
}

function callbackDrawRoutePath(data) {  
  map = $('#map_canvas').gmap('get', 'map');

  var routeAngkutan = [];  
  var pathAngkutan = [];  

  // alert(point0);
  var routeAngkutanPath = [];

  routeAngkutanPath.push(new google.maps.LatLng(point0))   
  routeAngkutanPath.push(new google.maps.LatLng(pointA))   

  var angkutanPath = new google.maps.Polyline({
      path: routeAngkutanPath,
      geodesic: true,
      strokeColor: '#abcdef',
      strokeOpacity: 1.0,
      strokeWeight: 5
    });

  angkutanPath.setMap(map);


  // $.each(data.result, function (i, r) {

  //   if(rute != r.name) { 
  //     color = '#'+(Math.random()*0xFFFFFF<<0).toString(16);
  //     listRoute += '<li style="background: '+color+';">--- '+r.name+'</li>';       
  //   }
  //   drawRoute(r.name, r.type, r.routes, color);

    
  //   rute = r.name;
  // });  
  // listRoute += '</ul>';

  // $('#panInfo').append(listRoute);
}

function drawRoute(name, type, routes, color) {
    map = $('#map_canvas').gmap('get', 'map');

    var routeAngkutanPath = [];
    $.each(routes, function(k, t) {
      routeAngkutanPath.push(new google.maps.LatLng(t.lat, t.lng))  
    });

    var angkutanPath = new google.maps.Polyline({
      path: routeAngkutanPath,
      geodesic: true,
      strokeColor: color,
      strokeOpacity: 1.0,
      strokeWeight: 5
    });

    angkutanPath.setMap(map);
}

function drawNearByPoint(data) {

    var loc = new google.maps.LatLng(data.lat, data.lng);
    map = $('#map_canvas').gmap('get', 'map');
    map.setZoom(15);

    var ico = (data.type === 'busway') ? ico_bus : ico_angkot;
    var marker = new google.maps.Marker({
      position: loc,
      map: map,
      'animation': google.maps.Animation.DROP,
      'icon': ico
    });

    var button_point = '<button type="button" class="btn btn-info btn-xs" onclick="setStartPoint(\''+data.id+'\', \''+data.label+'\', \''+ico+'\', '+data.lat+', '+data.lng+');">Set start point.</button>';
    var infoWindow = new google.maps.InfoWindow();
    google.maps.event.addListener(marker, 'click', function () {
        infoWindow.setContent(
          data.label
          +'<br />&nbsp;<br />'+button_point
        );
        infoWindow.open(map, this);
    });

    markerNearby.push(marker);
  }

function setStartPoint(id, label, ico, lat, lng) {

  $('#ico_stop').html('<img src="'+ico+'"> '+label);
  deleteOtherNearbyMarkers();
  
  var loc = new google.maps.LatLng(lat, lng);
  markerStart = new google.maps.Marker({
      position: loc,
      map: map,
      'animation': google.maps.Animation.DROP,
      'icon': ico
    });

  idstart = id;

  var infoWindow = new google.maps.InfoWindow();
  google.maps.event.addListener(markerStart, 'click', function () {
        infoWindow.setContent(label+'--'+id);
        infoWindow.open(map, this);        
    });

  $('#panInfo').empty().html('<strong>Set posisi keberangkatan: </strong>'+label);
  // angkutanPath.setMap(null);

}

function setAllMap(map) {
  for (var i = 0; i < markerNearby.length; i++) {
    markerNearby[i].setMap(map);
  }
}

function clearMarkers() {
  setAllMap(null);
}

function deleteOtherNearbyMarkers() {
  clearMarkers();
  markerNearby = [];
}
</script>

<section class="container content" style="margin-top: 70px;">  
  <div class="row">    
    
    <div class="col-md-3" style="margin-bottom: 10px;"> 
      <div class="input-group has-success">
        <span class="input-group-addon glyphicon glyphicon-search"></span>
        <input type="text" class="form-control" placeholder="Cari lokasi di peta..." id="cari">        
      </div>      

      <p>&nbsp;</p>
      <div class="btn-group btn-block">
        <button type="button" class="btn btn-success" data-toggle="dropdown" onClick="setPoint('a');"  style="width: 50%;">
          Set berangkat 
          <img src="<?php echo $T->getResourceUrl('img/start.png') ?>" class="center-block img-responsive"/>
        </button>

        <button type="button" class="btn btn-danger" data-toggle="dropdown" onClick="setPoint('b');"  style="width: 50%;">
          Set berhenti
          <img src="<?php echo $T->getResourceUrl('img/finish.png') ?>" class="center-block img-responsive"/>
        </button>
      </div>
      <p>&nbsp;</p>
      <?php /*
      <div class="btn-group btn-block ">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="width: 100%;">
          Pencarian rute <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li><a href="#" onclick="getNearbyPoint()">Rute di dekatmu</a></li>
          <li><a href="#">Cari informasi rute</a></li>
          <li class="divider"></li>
          <li><a href="#">Durasi perjalanan busway</a></li>
        </ul>
      </div>
      <div class="btn-group btn-block" style="width: 100%;">
        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" style="width: 100%;">
          Teman perjalanan <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li><a href="#">Berbagi Taxi</a></li>
          <li><a href="#">Berbagi Kendaraan</a></li>
          <li class="divider"></li>
          <li><a href="#">Laporan perjalanan</a></li>
          <li><a href="#">Lihat cctv</a></li>
        </ul>
      </div>      
      <div class="btn-group btn-block">
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" style="width: 100%;">
          Hangout..
        </button>
      </div>
      */ ?>

      <div class="col-md-12" style="min-height: 150px; margin-top: 10px; border: solid #000"> 
        <div id="panInfo">
          <ul>
            <li>Ketikan nama tempat untuk melihat lokasi lebih jelas.</li>
            <li>Klik Set berangkat untuk menentukan posisi</li>
            <li>drag icon pada peta untuk posisi lebih tepat</li>
            <li>klik icon pada peta untuk melihat pemberhentian dan rute terdekat</li>
          </ul>
        </div>
      </div>
    </div>    

    <div class="col-md-9">
      <div class="col-md-6">
        <div class="alert alert-success">
          <strong>Posisi awal:</strong> <span id="sourcepositionlabel"></span>
        </div>
      </div>
      <div class="col-md-6">
        <div class="alert alert-danger">
          <strong>Posisi akhir:</strong> <span id="destinationpositionlabel"></span>
        </div>
      </div>
      <div id="map_canvas" style="width: 100%; height: 500px;"> </div>
    </div>    
  </div>
</section>

<?php $T->blockEnd('content') ; ?>

<!-- 08161110808, 08111770808 <sylvianamurni@yahoo.com> -->
