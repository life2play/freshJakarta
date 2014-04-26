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

  public function getAction()
  {
    $this->blockOutside();
    $this->blockOutside();
    $url = $this->request->get('url');
    $checktime = $this->request->get('checktime');
    $table = $this->request->get('table');

    $jsonData = $this->getJsonFromURL($url);
    list($halteData, $relTable) = $this->jsonToArr($jsonData, 'result');
    
    $model = new Model($table);
    foreach ($halteData as $row) {
      $model->save($row);
    }

    // if (count($relTable) > 0) {
    //   foreach ($relTable as $k => $rows) {
    //     $fname = 'process' . ucfirst($k);
    //     if (is_callable(array($this, $fname))) {
    //       call_user_func(array($this, $fname), $rows);
    //     }
    //   }
    // }

    return $this->render();
  }

  public function busDistanceAction()
  {
    $q = "select a.id, a.koridorno, a.halteid, b.halteid as pathdest, "
        . "c.lat as haltelat, c.long as haltelong, a.latitude as buslat, "
        . "a.longitude as buslong, a.eta as buseta "
        . "from busway_eta_bus a join busway_halte b on a.koridorno = b.koridorno and a.haltename = b.haltename "
        . "join busway_halte c on a.koridorno = c.koridorno and b.halteid = c.halteid limit 1" ;


    $url = "origin=%s&destination=%s";

    $db = Singleton::acquire("\\PetakUmpet\\Database");

    $res = $db->queryFetchAll($q);

    if ($res) {
      foreach ($res as $row) {
        $srclatlong = $row['buslat'].'%20'.$row['buslong'];
        $dstlatlong = $row['haltelat'].'%20'.$row['haltelong'];

        $req = "https://maps.googleapis.com/maps/api/directions/json?" . sprintf($url, $srclatlong, $dstlatlong). "&units=metric&sensor=false&key=AIzaSyCyL7UQogftN8YAh2JeC99y0ltxIn2cYSY";
        $dirdata = $this->getJsonFromURL($req);
        $routes = $dirdata->routes;
        $distance = 0;
        foreach($routes as $r) {
          foreach($r->legs as $l) {
            $distance += (int) $l->distance->value;
          }
        }
        $speed = 1;
        if (is_numeric($distance)) {
          $speed = $distance/$row['buseta'];
        }
        echo $row['id'] . ' speed (meter/menit):' . $speed;
      }
    }

  }


// 0816 111 0808 (Sylviana Murni) Sylviana Murni@yahoo.com
// 08111 77 0808
}