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

    $query = "select lat, long as lng, '<strong>Busway</strong> Koridor: ' || koridor ||': '|| haltename as label, 'busway' as type, distance from (select koridor, haltename, lat, long, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte b ) srca ";

    $query  .= "UNION select lat, lng, label, type, distance from (select lat, long as lng, a.nama as label, a.jenis as type, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from trayek_umum_rute b join trayek_umum a on b.trayek_umum_id = a.id  ) srcb ";
    $query .= " WHERE distance <= :radius  ORDER BY distance desc ";

    $query = str_replace('::srclat::', $params->k, $query );
    $query = str_replace('::srclong::', $params->A, $query );

    $query_params['radius'] = '1000';

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

    $res = $db->queryFetchAll($query, array('radius' => 1000));
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
}