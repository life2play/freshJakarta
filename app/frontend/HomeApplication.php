<?php

namespace frontend;

use PetakUmpet\Application;

class HomeApplication extends Application {

  public function indexAction()
  {    
    return $this->render();
  }

  private function getJsonFromURL($url)
  {
    // todo, check for valid URL
    $content = file_get_contents($url);
    return json_decode(ltrim(rtrim($content)));
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

  public function getNearestAction()
  {
    // $this->blockOutside();
    $url = $this->request->get('url');
    $lat = urlencode('-6.22487');
    $lon = urlencode('106.86669');

    // $url = 'http://buswayapi.apiary.io/busway/halte/near/'.$lat.'/'.$lon.'?apiKey=KnFKgQ2ZkS8bAvCRGMXA28RdVufck8BD&distance=1000&page=1&per_page=10';
    // $content = file_get_contents($url);
    // var_dump($url);
    // $c = json_decode($content);
    // // var_dump($content);
    // // var_dump($c);
    // die;

    $jsonData = $this->getJsonFromURL($url);
    var_dump($jsonData); die;
    // return $this->render();
  }

  public function transportAction()
  {
    return $this->render();
  }

  public function apbdAction()
  {
    return $this->render();
  }
}
