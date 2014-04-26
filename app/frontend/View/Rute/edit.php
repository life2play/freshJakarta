<?php $T->addJs(array('http://maps.google.com/maps/api/js?sensor=false', 'js/json2', 'jquery.ui.map')) ?>

<?php $T->blockStart('content') ; ?>
<script>
$( document ).ready(function() {
    goma();
    
    // $('#cari').blur(function() {      
    //   var loc = $(this).val();

    //   $('#mappy').gmap('search', { 'address': 'indonesia, jakarta, '+loc }, function(results,isFound) {
    //     if (isFound) {
    //       map = $('#mappy').gmap('get','map');
    //       map.panTo(results[0].geometry.location);
    //       map.setZoom(15);
    //       // , {'zoom': 5}).panTo(results[0].geometry.location);
    //     }
    //   });
    // });
});

var map, ren, ser;
var data = {};
function goma()
{
    map = new google.maps.Map( 
            document.getElementById('mappy'), 
            {
                'zoom':12, 
                'mapTypeId': google.maps.MapTypeId.ROADMAP, 
                'center': new google.maps.LatLng(-6.227550, 106.828308) 
            });

    ren = new google.maps.DirectionsRenderer( {'draggable':true} );
    ren.setMap(map);
    ser = new google.maps.DirectionsService();
    
    ser.route({ 
        'origin': new google.maps.LatLng(-6.227550, 106.90) , 
        'destination':  new google.maps.LatLng(-6.227550, 106.754) , 
        'travelMode': google.maps.DirectionsTravelMode.DRIVING},function(res,sts) {
        if(sts=='OK') ren.setDirections(res);
    })      
}

function save_waypoints()
{
    var w=[],wp;
    var rleg = ren.directions.routes[0].legs[0];
    data.start = {'lat': rleg.start_location.lat(), 'lng':rleg.start_location.lng()}
    data.end = {'lat': rleg.end_location.lat(), 'lng':rleg.end_location.lng()}
    var wp = rleg.via_waypoints 
    for(var i=0;i<wp.length;i++)w[i] = [wp[i].lat(),wp[i].lng()]    
    data.waypoints = w;
    
    var str = JSON.stringify(data)
    alert(str);
    // var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    // jax.open('POST','process.php');
    // jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    // jax.send('command=save&mapdata='+str)
    // jax.onreadystatechange = function(){ if(jax.readyState==4) {
    //     if(jax.responseText.indexOf('bien')+1)alert('Updated');
    //     else alert(jax.responseText)
    // }}
}
</script>
<section class="container content" style="margin-top: 70px;">
  <div class="row">
    <div class="col-md-9" style="">
        <div id="mappy" style="width:100%; height:550px; border:1px solid #cecece; background:#F5F5F5"></div>      
    </div>
    <div class="col-md-3">

        <div class="input-group has-success">
            <span class="input-group-addon glyphicon glyphicon-search"></span>
            <input type="text" class="form-control" placeholder="Cari peta.." id="cari">        
        </div>
        <p>&nbsp;<p />
        <div class="input-group">
            <span class="input-group-addon glyphicon glyphicon-road"></span>
            <input type="text" class="form-control" placeholder="Nama Rute.." id="start">        
        </div>
        <br />
        <select class="form-control">
          <option>TransJakarta</option>
          <option>KRL</option>
          <option>Bus Besar</option>
          <option>Bus Sedang</option>
          <option>Angkot</option>
        </select>
        <p>&nbsp;<p />
        <div class="input-group">
            <button type="button" class="btn btn-primary" onClick="save_waypoints()">Simpan Rute</button>
        </div>
    </div>
  </div>
</section>
<?php $T->blockEnd('content') ; ?>