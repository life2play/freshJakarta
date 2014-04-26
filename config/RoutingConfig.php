<?php

namespace PetakUmpet;

class RoutingConfig extends RoutingEngine {

  public function __construct()
  {
    /* routing table goes here */
    $table['frontend'] = array(
      '/' => array(   /* '/' is default route when nothing else is specified */
        self::PAGE => 'Home/index',
      ),
      'home-index' => array(
        self::PAGE => 'Home/index',
      ),
      'rute' => array(
        self::PAGE => 'Rute/edit',
      ),
      'getdata' => array(
        self::PAGE => 'Home/getNearest',
      ),
      'apbd' => array(
        self::PAGE => 'Home/apbd',
      ),

      /* if one wants to customize their 404 page, set like this */
      self::ERROR_404_ROUTE => array(
        self::PAGE => 'Error/err404',
      ),
    );

    $table['backend'] = array(
      'cron-getdata' => array(
        self::PAGE => 'Cron/get',
      ),
      'cron-geteta' => array(
        self::PAGE => 'Cron/eta',
      ),
      'cron-bus-distance' => array(
        self::PAGE => 'Cron/busDistance',
      ),
    );

    /* application path map */
    $map = array(
        '/' => 'frontend',
        '/backend' => 'backend',
      );

    /* don't forget this! ;-) */
    $this->compile($table, $map);
  }

}
