<?php

namespace backend;

use PetakUmpet\Application;
use PetakUmpet\Singleton;
use PetakUmpet\Database\Model;

class processApplication extends Application {

  public function nearbyPointAction()
  {
    $q = $this->request->get('q');

    $params = json_decode($q);
    var_dump($params);

    $arr = $params;
     
    echo json_encode($arr);
    exit;
  }
}