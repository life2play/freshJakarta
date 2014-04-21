<?php

namespace backend;

use PetakUmpet\Application;
use PetakUmpet\Database\Model;

class CronApplication extends Application {

  private function getJsonData()
  {
    $url = $this->request->get('url');
    $resvar = null;
    if ($this->request->get('resvar') !== null) {
      $resvar = $this->request->get('resvar');
    }

    $content = file_get_contents($url);
    $arr = json_decode($content);

    $data = array();
    $relTable = array();

    $n=0;
    if ($resvar !== null) {
      $result = $arr->$resvar;
    } else {
      $result = $arr->result;
    }

    foreach ($result as $row) {
      foreach ($row as $k=>$v) {
        $kval = strtolower($k);
        if (is_array($v)) {
          $relTable[$n][$kval] = array();
          foreach ($v as $vv) {
            $relTable[$n][$kval][] = $vv;
          }
        } else {
          $data[$n][$kval]=$v;
        }
      }
      $n++;
    }

    return array($data, $relTable);

  }

  private function blockOutside()
  {
    // some sort of safety measurement
    if ($_SERVER['REMOTE_ADDR'] != 'localhost' && 
          $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
      echo (string) 'No Result';
      exit();
    }
  }

  public function etaAction()
  {
    $this->blockOutside();
    $table = 'busway_eta';
    $systime = $this->request->get('systime');
    list($data, $relTable) = $this->getJsonData();

    $model = new Model($table);
    foreach ($data as $row) {
      $row['system_check_time'] = $systime;
      $model->save($row);
    }

    return $this->renderView('Cron/get');
  }

  public function getAction()
  {
    $this->blockOutside();
    $table = $this->request->get('table');
    list($data, $relTable) = $this->getJsonData();

    $model = new Model($table);
    foreach ($data as $row) {
      $model->save($row);
    }

    return $this->render();
  }


}