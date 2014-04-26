<?php $T->addJs(array('http://maps.google.com/maps/api/js?sensor=false', 'jquery.ui.map', 'jquery.ui.map.services')) ?>

<?php $T->blockStart('content') ; ?>

<script language="javascript">
var api_key = 'KnFKgQ2ZkS8bAvCRGMXA28RdVufck8BD';
var jakarta = new google.maps.LatLng(-6.227550, 106.828308);

$(function() {    
  $('#map_canvas').gmap({'center': jakarta, 'zoom': 12});          
});


function loadMap(table, cols) {
  var map = new google.maps.Map(document.getElementById('map_canvas'), {
    center: jakarta,
    zoom: 12,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });

  var layer = new google.maps.FusionTablesLayer({
    query: {
      select: cols,
      from: table
    },
    map: map
  });
}
</script>

<section class="container content" style="margin-top: 70px;">
  <div class="row">
    <div class="col-md-9" style="">
      <div id="map_canvas" style="width: 100%; height: 500px;"> </div>
    </div>
    <div class="col-md-3" style="">
      <ul>
        <?php foreach($rute as $r): ?>
        <li><a href="#" onclick="loadMap('<?php echo $r['tablename'] ?>', '<?php echo $r['cols'] ?>');"><?php echo $r['name'] ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php $T->blockEnd('content') ; ?>
