<?php

namespace backend;

use PetakUmpet\Application;
use PetakUmpet\Singleton;
use PetakUmpet\Database\Model;

class ApiApplication extends Application {

  public function nearbyPointAction()
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');

    $jsonQ = $this->request->get('q');
    $params = json_decode($jsonQ);

    $query = "select id, lat, long as lng, '<strong>Busway</strong> Koridor: ' || koridor ||': '|| haltename || '<br>(Distance: ' || distance || 'm)' as label, 'busway' as type, distance from (select 'B'||b.id AS id, koridor, haltename, lat, long, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte b ) srca WHERE distance <= :radius ";

    $query  .= "UNION select id, lat, lng, label || '<br>(Distance: ' || distance || 'm)' , type, distance from (select 'A'||b.id AS id, lat, long as lng, a.nama as label, a.jenis as type, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from trayek_umum_rute b join trayek_umum a on b.trayek_umum_id = a.id  ) srcb ";
    $query .= " WHERE distance <= :radius  ORDER BY distance desc ";

    $query = str_replace('::srclat::', $params->k, $query );
    $query = str_replace('::srclong::', $params->A, $query );

    $query_params['radius'] = 500;

    $res = $db->queryFetchAll($query, $query_params);

    $arr = array();
    $arr['result'] = array();
    if ($res) {
        $arr['result'] = $res;
    }

    echo json_encode($arr);
    exit;
  }

  public function nearbyRouteAction()
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');

    $jsonQ = $this->request->get('q');
    $params = json_decode($jsonQ);

    $query = "select koridor from (select koridor, haltename, lat, long, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte b ) src where distance <= :radius ";
    $query = str_replace('::srclat::', $params->k, $query );
    $query = str_replace('::srclong::', $params->A, $query );

    $routeQuery = "SELECT haltename, lat, long from busway_halte where koridor = :koridor";

    $res = $db->queryFetchAll($query, array('radius' => 500));
    $arr = array();
    $n = 0;
    if ($res) {
       foreach ($res as $row) {
         $arr[$n]['name'] = $row['koridor'];
         $arr[$n]['type'] = 'busway';
         $rtRes = $db->queryFetchAll($routeQuery, array('koridor'=>$row['koridor']));
         $rtArr = array();
         if ($rtRes) {
            foreach ($rtRes as $rtRow) {
                $latlng = array('lat' => $rtRow['lat'], 'lng' => $rtRow['long']);
                $rtArr[] = $latlng;
            }
         } 
         $arr[$n]['routes'] = $rtArr;
         $n++;
       } 
       $rset['result'] = $arr;
       echo json_encode($rset);
    }
    exit;
  }

  private function getTrayekId($id) 
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');

    $q = '';
    $params = array();
    if (strstr($id, 'B')) {
      $q = "SELECT b.id FROM busway_halte a JOIN busway_koridor b ON a.koridor = b.koridor WHERE a.id = ?";
      $id = str_replace('B', '', $id);
      $params = array($id);
    } else {
      $q = "SELECT b.id FROM trayek_umum_rute a JOIN trayek_umum b ON a.trayek_umum_id = b.id WHERE a.id = ?";
      $id = str_replace('A', '', $id);
      $params = array($id);
    }
    $res = $db->queryFetchOne($q, $params);
    if ($res) {
        return $res;
    }
    return null;
  }

  private function traverseRoute($srcTrayekId, $srcId, $dstTrayekId, $dstId)
  {
  }

  private function getRoutePath($srcTrayekId, $srcId, $dstTrayekId, $dstId)
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');
    $q = "SELECT * FROM routing_paths WHERE point_a = ? AND point_b = ?";
    $params = array($srcTrayekId, $dstTrayekId);

    $res = $db->queryFetchAll($q, $params);
    if ($res) {
        foreach ($res as $row) {
            $trayekA = $row['path_a'];
            $trayekB = $row['path_b'];
        }
    } 
    return null;
  }

  private function getRouteInOneTrayek($srcTrayekId, $srcId, $dstId, $type)
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');
    $arr = array();

    if ($type == 'B') {
      // busway
      $sid = str_replace('B','', $srcId);
      $did = str_replace('B','', $dstId);

      $bigId = $did; $smallId = $sid;
      if ((int) $sid > (int) $did) {
        $bigId = $sid; $smallId = $did;
      }

      $q = "select 'Koridor-'||b.koridor|| ': ' || b.shname || '-' || b.fhname AS label, a.lat, a.long as lng, a.haltename  from busway_halte a join busway_koridor b on a.koridor = b.koridor WHERE b.id = ? AND a.id >= ? AND a.id <= ?";
      $params = array($srcTrayekId, $smallId, $bigId);

      $res = $db->queryFetchAll($q, $params);
      if ($res) {
        return $res;
      }
    } else {
      $sid = str_replace('A','', $srcId);
      $did = str_replace('A','', $dstId);

      $bigId = $did; $smallId = $sid;
      if ((int) $sid > (int) $did) {
        $bigId = $sid; $smallId = $did;
      }
 
      $q = "select b.nama as label, a.lat, a.long as lng, 'Halte' as nama from trayek_umum_rute a join trayek_umum b on a.trayek_umum_id = b.id where b.id = ? and a.id >=? and a.id <=?";

      $params = array($srcTrayekId, $smallId, $bigId);

      $res = $db->queryFetchAll($q, $params);
      if ($res) {
        return $res;
      }
    }
    return $arr;
  }

  public function getRouteAction()
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');

    $jsonQ = $this->request->get('q');
    $params = json_decode($jsonQ);
    
    $srcId = $this->request->get('srcid');
    $dstId = $this->request->get('dstid');

    $srclat = $this->request->get('srclat');
    $srclong = $this->request->get('srclong');
    $dstlat = $this->request->get('dstlat');
    $dstlong = $this->request->get('dstlong');

    $srcTrayekId = $this->getTrayekId($srcId);
    $dstTrayekId = $this->getTrayekId($dstId);

    if ($srcTrayekId == $dstTrayekId) {
        $type = 'A';
        if (strstr($srcId, 'B')) {
            $type = 'B';
        }
        $arr['result'] = $this->getRouteInOneTrayek($srcTrayekId, $srcId, $dstId, $type);
        echo json_encode($arr);
        exit;
    } else {
        $dstType = 'A';
        if (strstr($dstId, 'B')) {
            $dstType = 'B';
        }
        $arr['result'] = $this->getBuswayToBuswayRoute($srcTrayekId, $srcId, $dstTrayekId, $dstId);
        echo json_encode($arr);
        exit;
    }
    return array();
  }

  public function getCctvAction()
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');

    $q = "SELECT urladdress, latitude, longitude FROM layer_cctv" ;

    $res = $db->queryFetchAll($q);

    $arr['result'] = array();
    if ($res) {
        $arr['result'] = $res;
    }
    echo json_encode($arr);
    exit;
  }

}