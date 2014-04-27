<?php

namespace backend;

use PetakUmpet\Application;
use PetakUmpet\Singleton;
use PetakUmpet\Database\Model;

class CronApplication extends Application {

  private function getJsonFromURL($url)
  {
    // todo, check for valid URL
    $content = file_get_contents($url);
    return json_decode($content);
  }

  private function jsonToArr($jsonData, $key)
  {
    $data = array();
    $relTable = array();

    $n=0;
    if (property_exists($jsonData, $key)) {
      $result = $jsonData->$key;
      foreach ($result as $row) {
        foreach ($row as $k=>$v) {
          $kval = strtolower($k);
          // recurse 1 step if found an array (not recursive yet)
          if (is_array($v)) {
            $relTable[$n][$kval] = array();
            foreach ($v as $vv) {
              $relTable[$n][$kval][] = $vv;
            }
          } else { // otherwise put it in
            $data[$n][$kval]=$v;
          }
        }
        $n++;
      }
    }

    return array($data, $relTable);

  }

  private function blockOutside()
  {
    // some sort of safety measurement
    if ($_SERVER['REMOTE_ADDR'] != 'localhost' && 
          $_SERVER['REMOTE_ADDR'] != '127.0.0.1' &&
            $_SERVER['REMOTE_ADDR'] != '119.235.24.90' &&
              $_SERVER['REMOTE_ADDR'] != '119.235.24.92'
              ) {
      echo (string) 'No Result';
      exit();
    }
  }

  private function processRuteBerangkat($data) 
  {
    $model = new Model('rute_berangkat');
    foreach ($data as $v) {
      $r['halte'] = $v;
      $model->save($r);
    }
  }

  public function etaAction()
  {
    $this->blockOutside();
    $url = $this->request->get('url');
    $checktime = $this->request->get('checktime');
    $srchalte = $this->request->get('srchalte');

    $jsonData = $this->getJsonFromURL($url);
    list($halteData, $relTable) = $this->jsonToArr($jsonData, 'result_halte');
    list($busData, $relTable) = $this->jsonToArr($jsonData, 'result');

    if (count($halteData) > 0) {
      $model = new Model('busway_eta_halte');
      foreach ($halteData as $row) {
        $row['srchalte'] = $srchalte;
        $row['checktime'] = $checktime;
        $model->save($row);
      }
    }

    if (count($busData) > 0) {
      $model = new Model('busway_eta_bus');
      foreach ($busData as $row) {
        $row['srchalte'] = $srchalte;
        $row['checktime'] = $checktime;
        $model->save($row);
      }
    }

    return $this->renderView('Cron/get');
  }

  public function getCctvAction()
  {
    $this->blockOutside();

    $url = "http://itsjakarta.com/its/cctv";
    $table = 'layer_cctv';

    $model = new Model($table);
    $targetUrl = sprintf($url, $k);
    $jsonData = $this->getJsonFromURL($targetUrl);
    list($halteData, $relTable) = $this->jsonToArr($jsonData, 'result');
    
    foreach ($halteData as $row) {
      $row['urladdress'] = 'http://itsjakarta.com/its/camera/?ip=' . $row['ipaddress'];
      $model->save($row);
    }

    return $this->renderView('Cron/get');
  }

  public function getBuswayHalteAction()
  {
    $this->blockOutside();

    $koridors = array('1','2','3','4','5','6','7','8','9','10','11'); 
    $url = "http://api.hackjak.bappedajakarta.go.id/busway/koridor/%s?apiKey=KnFKgQ2ZkS8bAvCRGMXA28RdVufck8BD";
    $table = 'busway_halte';


    $model = new Model($table);
    foreach ($koridors as $k) {
      $targetUrl = sprintf($url, $k);
      $jsonData = $this->getJsonFromURL($targetUrl);
      list($halteData, $relTable) = $this->jsonToArr($jsonData, 'result');
      
      foreach ($halteData as $row) {
        $model->save($row);
      }
    }

    return $this->renderView('Cron/get');
  }

  public function busDistanceAction()
  {
    $q = "select a.id, a.koridorno, a.halteid, b.halteid as pathdest, "
        . "c.lat as haltelat, c.long as haltelong, a.latitude as buslat, "
        . "a.longitude as buslong, a.eta as buseta "
        . "from busway_eta_bus a join busway_halte b on a.koridorno = b.koridorno and a.haltename = b.haltename "
        . "join busway_halte c on a.koridorno = c.koridorno and b.halteid = c.halteid "
        . "where checktime::text like '2014-04-%:15:%'" ;

    $url = "origin=%s&destination=%s";

    $db = Singleton::acquire("\\PetakUmpet\\Database");

    $res = $db->queryFetchAll($q);
    $dt = new Model('busway_eta_interim_speed_distance'); 
    $cc = new Model('cache_distance_query');

    $ccquery = "select distance from cache_distance_query where srclat = ? and srclong = ? and dstlat = ? and dstlong = ?";

    if ($res) {
      foreach ($res as $row) {
        $srclat = round($row['buslat'], 4, PHP_ROUND_HALF_DOWN);
        $srclong = round($row['buslong'], 4, PHP_ROUND_HALF_DOWN);
        $dstlat = round($row['haltelat'], 4, PHP_ROUND_HALF_DOWN);
        $dstlong = round($row['haltelong'], 4, PHP_ROUND_HALF_DOWN);

        $ccdistance = $db->queryFetchOne($ccquery, array($srclat, $srclong, $dstlat, $dstlong));

        if (!$ccdistance || $ccdistance == '' || $ccdistance === null || $ccdistance == 0) {
          $srclatlong = $srclat .'%20'. $srclong;
          $dstlatlong = $dstlat .'%20'. $dstlong;

          $req = "https://maps.googleapis.com/maps/api/directions/json?" . sprintf($url, $srclatlong, $dstlatlong). "&units=metric&sensor=false&key=AIzaSyCyL7UQogftN8YAh2JeC99y0ltxIn2cYSY";
          $dirdata = $this->getJsonFromURL($req);
          $routes = $dirdata->routes;
          $distance = 0;
          foreach($routes as $r) {
            foreach($r->legs as $l) {
              $distance += (int) $l->distance->value;
            }
          }
          $ccd = array(
            'srclat' => $srclat,
            'srclong' => $srclong,
            'dstlat' => $dstlat,
            'dstlong' => $dstlong,
            'distance' => $distance,
          );
          $cc->save($ccd);
        } else {
          var_dump($ccdistance);
          $distance = (integer) $ccdistance;
          echo "Using cached distance<br>";
        }

        $speed = 1;
        if (is_numeric($distance)) {
          $speed = $distance/$row['buseta'];
        }

        $d = array(
          'busway_eta_bus_id' => $row['id'],
          'koridorno' => $row['koridorno'],
          'halteid' => $row['halteid'],
          'busdistance' => $distance,
          'eta' => $row['buseta'],
          'speed' => $speed,
        );
        $dt->save($d);
      }
    }
  }

  public function halteDistanceAction()
  {
    $q = "select a.koridor, a.halteid as ahalteid, b.halteid as bhalteid, a.lat as alat, "
        . "a.long as along, b.lat as blat, b.long as blong from busway_halte a, "
        . "busway_halte b where a.halteid <> b.halteid and a.koridor = b.koridor "
        . "and a.halteid::integer < b.halteid::integer order by a.koridor::integer, "
        . "a.halteid::integer, b.halteid::integer";

    $url = "origin=%s&destination=%s";

    $db = Singleton::acquire("\\PetakUmpet\\Database");

    $res = $db->queryFetchAll($q);
    $dt = new Model('busway_halte_real_distance'); 
    $cc = new Model('cache_distance_query');

    $ccquery = "select distance from cache_distance_query where srclat = ? and srclong = ? and dstlat = ? and dstlong = ?";

    if ($res) {
      foreach ($res as $row) {
        $srclat = round($row['alat'], 5, PHP_ROUND_HALF_DOWN);
        $srclong = round($row['along'], 5, PHP_ROUND_HALF_DOWN);
        $dstlat = round($row['blat'], 5, PHP_ROUND_HALF_DOWN);
        $dstlong = round($row['blong'], 5, PHP_ROUND_HALF_DOWN);

        $ccdistance = $db->queryFetchOne($ccquery, array($srclat, $srclong, $dstlat, $dstlong));

        if (!$ccdistance || $ccdistance == '' || $ccdistance === null || $ccdistance == 0) {
          $srclatlong = $srclat .'%20'. $srclong;
          $dstlatlong = $dstlat .'%20'. $dstlong;

          $req = "https://maps.googleapis.com/maps/api/directions/json?" . sprintf($url, $srclatlong, $dstlatlong). "&units=metric&sensor=false&key=AIzaSyCyL7UQogftN8YAh2JeC99y0ltxIn2cYSY";
          $dirdata = $this->getJsonFromURL($req);
          $routes = $dirdata->routes;
          $distance = 0;
          foreach($routes as $r) {
            foreach($r->legs as $l) {
              $distance += (int) $l->distance->value;
            }
          }
          if ($distance != 0) { 
            $ccd = array(
              'srclat' => $srclat,
              'srclong' => $srclong,
              'dstlat' => $dstlat,
              'dstlong' => $dstlong,
              'distance' => $distance,
            );
            $cc->save($ccd);
          }
        } else {
          var_dump($ccdistance);
          $distance = (integer) $ccdistance;
          echo "Using cached distance<br>";
        }

        $row['distance'] = $distance;
        $dt->save($row);
      }
    }

  }


// 0816 111 0808 (Sylviana Murni) Sylviana Murni@yahoo.com
// 08111 77 0808

// ---- NOTES COLLECTIONS processing eta queries
// create table busway_halte_distance as select a.koridorno, a.halteid as ahalte, b.halteid as bhalte, (select sum(prevdistance) from busway_halte where halteid >= a.halteid and halteid < b.halteid)as distance from busway_halte a, busway_halte b where a.koridorno = b.koridorno and a.halteid <> b.halteid and a.halteid < b.halteid order by a.koridorno, a.halteid, b.halteid ;
// select * from busway_halte_distance ;
// delete from busway_halte_distance where distance = 0;
// select b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as speed_between_halte  from busway_halte_distance a, busway_eta_interim_speed_distance b where a.distance >= b.busdistance and b.busdistance <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;
// select b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as eta_between_halte  from busway_halte_distance a, busway_eta_interim_speed_distance b where a.distance >= b.busdistance and b.busdistance <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;
// select b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as eta_between_halte  from busway_halte_distance a, busway_eta_interim_speed_distance b where a.distance >= b.busdistance and b.busdistance <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;
// select c.checktime, b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as eta_between_halte  from busway_halte_distance a join busway_eta_bus c on a.busway_eta_bus_id = c.id, busway_eta_interim_speed_distance b where a.distance >= b.busdistance and b.busdistance <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;
// select c.checktime, b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as eta_between_halte  from busway_halte_distance a,  busway_eta_interim_speed_distance b join busway_eta_bus c on a.busway_eta_bus_id = c.id where a.distance >= b.busdistance and b.busdistance <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;
// select c.checktime, b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as eta_between_halte  from busway_halte_distance a,  busway_eta_interim_speed_distance b join busway_eta_bus c on b.busway_eta_bus_id = c.id where a.distance >= b.busdistance and b.busdistance <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;

// END NOTES

// query to process eta data to eta between haltes
// select c.checktime, b.*, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/b.speed as eta_between_halte  from busway_halte_distance a,  busway_eta_interim_speed_distance b join busway_eta_bus c on b.busway_eta_bus_id = c.id where a.distance >= b.busdistance and b.busdistance <> 0 and b.speed <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid 
// 

// create table busway_koridor_start_finish as select src.*, b.haltename as shname, c.haltename as fhname from (select distinct a.koridor, (select min(halteid) from busway_halte where koridor = a.koridor) as starthalte, (select max(halteid) from busway_halte where koridor = a.koridor) as finishhalte from busway_halte a ) src join busway_halte b on src.koridor = b.koridor and src.starthalte = b.halteid join busway_halte c on src.koridor = c.koridor and src.finishhalte = c.halteid;  
// query for eta (normalized eta and speed)
// create table busway_eta_interim_eta_halte as select c.checktime, case when c.haltename = d.shname then 'upstream' else 'downstream' end as direction, b.koridorno, b.halteid, b.busdistance, b.eta as buseta, case when b.speed>1500 then b.busdistance/1500 else b.eta end as normalized_buseta, b.speed, case when b.speed > 1500 then 1500 else b.speed end AS normalize_speed, a.ahalte, a.bhalte, a.distance as haltedistance, a.distance/(case when b.speed > 1500 then 1500 else b.speed end) as eta_between_halte  from busway_halte_distance a,  busway_eta_interim_speed_distance b join busway_eta_bus c on b.busway_eta_bus_id = c.id join busway_koridor_start_finish d on c.koridorno = d.koridorno where a.distance >= b.busdistance and b.busdistance <> 0 and b.speed <> 0 and a.koridorno=b.koridorno and a.ahalte = b.halteid;
// UPDATES on eta-halte creation:
// select c.checktime, case when c.haltename = d.shname then 'upstream' else 'downstream' end as direction, b.koridorno, b.halteid, b.busdistance, b.eta as buseta, case when b.speed>1500 then b.busdistance/1500 else b.eta end as normalized_buseta, b.speed, case when b.speed > 1500 then 1500 else b.speed end AS normalize_speed, a.ahalteid, a.bhalteid, a.distance as haltedistance, a.distance/(case when b.speed > 1500 then 1500 else b.speed end) as eta_between_halte  from busway_halte_real_distance a,  busway_eta_interim_speed_distance b join busway_eta_bus c on b.busway_eta_bus_id = c.id join busway_koridor_start_finish d on c.koridorno = d.koridor where a.distance >= b.busdistance and b.busdistance <> 0 and b.speed <> 0 and a.koridor=b.koridorno and a.ahalteid = b.halteid
// This one is actually creating 2 tables (busway_eta_interim_eta_halte, busway_eta_final)
// select checktime, direction, koridorno, ahalteid as fromhalte, bhalteid as tohalte, avg(eta_between_halte) as avg_etatime from (select c.checktime, case when c.haltename = d.shname then 'upstream' else 'downstream' end as direction, b.koridorno, b.halteid, b.busdistance, b.eta as buseta, case when b.speed>1500 then b.busdistance/1500 else b.eta end as normalized_buseta, b.speed, case when b.speed > 1500 then 1500 else b.speed end AS normalize_speed, a.ahalteid, a.bhalteid, a.distance as haltedistance, a.distance/(case when b.speed > 1500 then 1500 else b.speed end) as eta_between_halte  from busway_halte_real_distance a,  busway_eta_interim_speed_distance b join busway_eta_bus c on b.busway_eta_bus_id = c.id join busway_koridor_start_finish d on c.koridorno = d.koridor where a.distance >= b.busdistance and b.busdistance <> 0 and b.speed <> 0 and a.koridor=b.koridorno and a.ahalteid = b.halteid ) src group by checktime, direction, koridorno, ahalteid, bhalteid order by koridorno::integer, ahalteid::integer, bhalteid::integer


// query to get intersection point between halte and angkot based on distance
// insert into intersect_angkot_busway (trayek_umum_rute_id, angkotlat, angkotlong, busway_halte_id, buswaylat, buswaylong, distance)  select * from (select a.id as angkot_halte_id, a.lat as angkotlat, a.long as angkotlong, b.id as busway_halte_id, b.lat as buswaylat, b.long as buswaylong,  ( 6371 * acos( cos( radians(a.lat) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(a.long) ) + sin( radians(a.lat) ) * sin( radians( b.lat ) ) ) ) *1000 AS distance  from trayek_umum_rute_processed_5 a, busway_halte b ) src where distance <= 300;
// ===> UPDATED
//     insert into intersect_angkot_busway (trayek_umum_id, trayek_umum_rute_id, angkotlat, angkotlong, busway_koridor_id, busway_halte_id, buswaylat, buswaylong, distance) select * from (select a.trayek_umum_id, a.id, a.lat as angkotlat, a.long as angkotlong, c.id, b.id as busway_halte_id, b.lat as buswaylat, b.long as buswaylong,  ( 6371 * acos( cos( radians(a.lat) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(a.long) ) + sin( radians(a.lat) ) * sin( radians( b.lat ) ) ) ) *1000 AS distance  from trayek_umum_rute a, busway_halte b join busway_koridor c on b.koridor = c.koridor ) src where distance <= 100;

  
// intersect busway busway
// insert into intersect_busway_busway (src_busway_halte_id, dst_busway_halte_id, alat, along, blat, blong, distance) select aid as src_busway_halte_id, bid as dst_busway_halte_id, alat, along, blat, blong, distance from (select a.id as aid, b.id as bid, a.koridor as akoridor, a.halteid as ahalte, a.haltename as ahaltename, b.koridor as bkoridor, b.halteid as bhalte, b.haltename as bhaltename, a.lat as alat, a.long as along, b.lat as blat, b.long as blong, case when a.lat = b.lat and a.long = b.long then 0 else ( 6371 * acos( cos( radians(a.lat) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(a.long) ) + sin( radians(a.lat) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte a, busway_halte b where a.halteid <> b.halteid) src where distance < 500 and akoridor <> bkoridor ;
// ===> UPDATED
//     insert into intersect_busway_busway (src_koridor_id, src_halte_id, dst_koridor_id, dst_halte_id, alat, along, blat, blong, distance) select * from (select c.id as cid, a.id as aid, d.id as did, b.id as bid, a.lat as alat, a.long as along, b.lat as blat, b.long as blong, case when a.lat = b.lat and a.long = b.long then 0 else ( 6371 * acos( cos( radians(a.lat) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(a.long) ) + sin( radians(a.lat) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte a join busway_koridor c on a.koridor = c.koridor, busway_halte b join busway_koridor d on d.koridor=b.koridor where a.halteid <> b.halteid) src where distance <= 500 and cid <> did;

// intersect angkot angkot
// select * from (select a.id as aid, a.trayek_umum_id as atid, b.id as bid, b.trayek_umum_id as btid, a.lat as alat, a.long as along, b.lat as blat, b.long as blong, case when a.lat = b.lat and a.long = b.long then 0 else ( 6371 * acos( cos( radians(a.lat) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(a.long) ) + sin( radians(a.lat) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance  from trayek_umum_rute a, trayek_umum_rute b where a.trayek_umum_id <> b.trayek_umum_id) src where distance <= 200  
// ===> UPDATED
//     insert into intersect_angkot_angkot (src_trayek_umum_id, src_trayek_umum_rute_id, dst_trayek_umum_id, dst_trayek_umum_rute_id, alat, along, blat,blong, distance) select * from (select a.id as aid, a.trayek_umum_id as atid, b.id as bid, b.trayek_umum_id as btid, a.lat as alat, a.long as along, b.lat as blat, b.long as blong, case when a.lat = b.lat and a.long = b.long then 0 else ( 6371 * acos( cos( radians(a.lat) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(a.long) ) + sin( radians(a.lat) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance  from trayek_umum_rute a, trayek_umum_rute b where a.trayek_umum_id <> b.trayek_umum_id) src where distance <= 100 ;

// transport intersection for routing 
// > create table transport_intersection as select 'A'||trayek_umum_id point_a, 'B'||busway_koridor_id as point_b from ( select  distinct trayek_umum_id, busway_koridor_id from intersect_angkot_busway order by trayek_umum_id, busway_koridor_id ) src union select 'A'||src_koridor_id, 'B'||dst_koridor_id from ( select distinct src_koridor_id, dst_koridor_id from intersect_busway_busway order by src_koridor_id, dst_koridor_id ) src union select 'A'||src_trayek_umum_id, 'A'||dst_trayek_umum_id from ( select  distinct src_trayek_umum_id, dst_trayek_umum_id from intersect_angkot_angkot order by src_trayek_umum_id, dst_trayek_umum_id ) src;
}
