<?php $T->addJs(array('http://maps.google.com/maps/api/js?sensor=true', 'jquery.ui.map', 'jquery.ui.map.services')) ?>

<?php $T->blockStart('content') ; ?>

<script language="javascript">
var loc_x;
var loc_a;
var loc_b;
var api_key = 'KnFKgQ2ZkS8bAvCRGMXA28RdVufck8BD';

$(function() {
    $("#start, #finish, #cari").blur(function() {      
      var loc = $(this).val();

      $('#map_canvas').gmap('search', { 'address': 'indonesia, jakarta, '+loc }, function(results,isFound) {
        if (isFound) {
          map = $('#map_canvas').gmap('get','map');
          map.panTo(results[0].geometry.location);
          map.setZoom(15);
        }
      });
    });

    var jakarta = new google.maps.LatLng(-6.227550, 106.828308);

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
}); 

function findLocation(location, marker) {
  $('#map_canvas').gmap('search', {'location': location}, function(results, status) {
    if ( status === 'OK' ) {      

      // $.each(results[0].address_components, function(i,v) {
      //   if ( v.types[0] == "administrative_area_level_1" || v.types[0] == "administrative_area_level_2" ) {
      //     // $('#state'+marker.__gm_id).val(v.long_name);
      //     // alert(v.long_name);
      //   } else if ( v.types[0] == "country") {
      //     // $('#country'+marker.__gm_id).val(v.long_name);
      //     // alert(v.long_name);
      //   }
      // });

      // marker.setTitle(results[0].formatted_address);
      // alert(results[0].formatted_address);
      $('#cari').val( results[0].formatted_address );

      loc_x = results[0].geometry.location;      
      // alert( loc_a ); 
      // $('#address'+marker.__gm_id).val(results[0].formatted_address);
      // openDialog(marker);
    }
  });  
}

function findRoute() {
  var current_loc = loc_x;

  $.ajax({
    type: "GET",
    dataType: "json",
    url: "http://buswayapi.apiary.io/busway/halte/near/-6.22487/106.86669?apiKey="+api_key+"&distance=1000&page=1&per_page=10",
    success: function(data) {
      
      var a = JSON.parse(data);
      alert(a);
    }    
  });


  // var xhr = new XMLHttpRequest();
  // xhr.open('GET', 'http://buswayapi.apiary.io/busway/halte/near/-6.22487/106.86669?apiKey='+api_key+'&distance=1000&page=1&per_page=10');
  // xhr.onreadystatechange = function () {
  //   if (this.readyState == 4) {
  //     if (typeof cb !== "undefined") {
  //       cb(this);
  //     }
  //     else {
  //       alert('Status: '+this.status+'\nHeaders: '+JSON.stringify(this.getAllResponseHeaders())+'\nBody: '+this.responseText);
  //     }
  //   }
  // };
  // xhr.send(null);
}
</script>

<section class="container content" style="margin-top: 70px;">
  <div class="row">
    <div class="col-md-9">
      <div id="map_canvas" style="width: 100%; height: 500px;"> </div>
    </div>
    <div class="col-md-3">      
      <div class="input-group has-success">
        <span class="input-group-addon glyphicon glyphicon-search"></span>
        <input type="text" class="form-control" placeholder="Posisi kamu saat ini..." id="cari">        
      </div>
      <p></p>
      <div class="input-group" style="width: 100%;">
        <button type="button" class="btn btn-primary btn-lg btn-block" onclick="findRoute()">Rute di dekatmu</button>      
        <button type="button" class="btn btn-primary btn-lg btn-block">Cari informasi rute</button>      
      </div>
    </div>
  </div>
</section>

<?php $T->blockEnd('content') ; ?>

<!-- 08161110808, 08111770808 <sylvianamurni@yahoo.com> -->