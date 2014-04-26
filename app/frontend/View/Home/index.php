<?php $T->addJs(array('http://maps.google.com/maps/api/js?sensor=true', 'jquery.ui.map', 'jquery.ui.map.services')) ?>

<?php $T->blockStart('content') ; ?>

<script language="javascript">
var markerA = null;
var markerB = null;

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
          if ( status === 'OK' ) {  
            $('#sourcepositionlabel').html( results[0].formatted_address );
          }
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
      markerA = new google.maps.Marker({ map: map, 'bounds': false, draggable: true, 'icon': '/img/bus.png' });
      markerA.setPosition(center);
    } else {
      markerA.setPosition(center);
    }

    google.maps.event.addListener(markerA, 'dragend', function() {    
      findLocation('a', markerA.getPosition());
    });

  } else {
    if (markerB == null) {      
      map = $('#map_canvas').gmap('get', 'map');
      markerB = new google.maps.Marker({ map: map, 'bounds': false, draggable: true, 'icon': '/img/cabin.png' });
      markerB.setPosition(center);
    } else {
      markerB.setPosition(center);
    }

    google.maps.event.addListener(markerB, 'dragend', function() {    
      findLocation('b', markerB.getPosition());
    });
  }

  

  findLocation(type, center);
}

function findLocation(type, location, marker) {
  $('#map_canvas').gmap('search', {'location': location}, function(results, status) {
    if ( status === 'OK' ) { 
      if(type === 'a')  {
        $('#sourcepositionlabel').html( results[0].formatted_address +'&nbsp;<img src="<?php echo $T->getResourceUrl('img/bus.png') ?>">');  
      } else {
        $('#destinationpositionlabel').html( results[0].formatted_address  +'&nbsp;<img src="<?php echo $T->getResourceUrl('img/cabin.png') ?>">');  
      }      
    }
  });  
}

function getNearbyPoint()
{  
  var point = markerA.getPosition();

  $.ajax({
    type: "GET",
    url: "backend/get-nearby-point",
    dataType: "json",
    data: { q: JSON.stringify(point) },
    contentType: "application/json",
    succes: function(data){
      alert(data);
    }
  });
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
        <button type="button" class="btn btn-danger" data-toggle="dropdown" onClick="setPoint('a');">Set berangkat</button>
        <button type="button" class="btn btn-danger" data-toggle="dropdown" onClick="setPoint('b');">Set berhenti</button>
      </div>
      <p>&nbsp;</p>
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
    </div>    

    <div class="col-md-9">
      <div class="col-md-6">
        <div class="alert alert-info">
          <strong>Posisi awal:</strong> <span id="sourcepositionlabel"></span>
        </div>
      </div>
      <div class="col-md-6">
        <div class="alert alert-success">
          <strong>Posisi akhir:</strong> <span id="destinationpositionlabel"></span>
        </div>
      </div>
      <div id="map_canvas" style="width: 100%; height: 500px;"> </div>
    </div>    
  </div>
</section>

<?php $T->blockEnd('content') ; ?>

<!-- 08161110808, 08111770808 <sylvianamurni@yahoo.com> -->