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

    $query = "select id, lat, long as lng, '<strong>Busway</strong> Koridor: ' || koridor ||': '|| haltename || '<br>(Distance: ' || distance || 'm)' as label, 'busway' as type, distance from (select 'B'||b.id AS id, koridor, haltename, lat, long, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte b ) srca ";

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
        return $res['id'];
    }
    return null;
  }

  private function getRouteById($srcTrayekId, $srcId, $dstTrayekId, $dstId)
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');
    $q = "SELECT * FROM routing_paths WHERE point_a = ? AND point_b = ?";
    $params = array($srcTrayekId, $dstTrayekId);

    $res = $db->queryFetchAll($q, $params);
    if ($res) {
    } else {
      return null;
    }
  }

  private function getRouteInOneTrayek($srcTrayekId, $srcId, $dstTrayekId, $dstId)
  {
    if (strstr($srcId, 'B')) {
      // busway
    } else {
      // non-busway
    }
  }

  private function traverseRoute($id) 
  {

  }

  public function getRouteAction()
  {
    $db = Singleton::acquire('\\PetakUmpet\\Database');

    $jsonQ = $this->request->get('q');
    $params = json_decode($jsonQ);
    
    $srcTrayekId = $this->getTrayekId($params['srcid']);
    $dstTrayekId = $this->getTrayekId($params['dstid']);

  }

}