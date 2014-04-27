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

    $query = "select lat, long as lng, '<strong>Busway</strong> Koridor: ' || koridor ||': '|| haltename as label, 'busway' as type from (select koridor, haltename, lat, long, case when ::srclat:: = b.lat and ::srclong:: = b.long then 0 else ( 6371 * acos( cos( radians(::srclat::) ) * cos( radians( b.lat ) ) * cos( radians( b.long ) - radians(::srclong::) ) + sin( radians(::srclat::) ) * sin( radians( b.lat ) ) ) )  end *1000 AS distance from busway_halte b ) src where distance <= :radius ";

    $query = str_replace('::srclat::', $params->k, $query );
    $query = str_replace('::srclong::', $params->A, $query );

    $query_params['radius'] = '1000';

    $res = $db->queryFetchAll($query, $query_params);
    if ($res) {
        $arr['result'] = $res;
        echo json_encode($arr);
    }
    exit;
  }
}