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
      'transport' => array(
        self::PAGE => 'Home/transport',
      ),
      'gettransport' => array(
        self::PAGE => 'Home/gettransport',
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
      'cron-getapbd' => array(
        self::PAGE => 'Cron/getApbd',
      ),
      'cron-geteta' => array(
        self::PAGE => 'Cron/eta',
      ),
      'cron-cctv' => array(
        self::PAGE => 'Cron/getCctv',
      ),
      'info-eta' => array(
        self::PAGE => 'Info/eta',
      ),
      'info-source' => array(
        self::PAGE => 'Info/source',
      ),
      'cron-busway-halte' => array(
        self::PAGE => 'Cron/getBuswayHalte',
      ),
      'cron-bus-distance' => array(
        self::PAGE => 'Cron/busDistance',
      ),
      'cron-halte-distance' => array(
        self::PAGE => 'Cron/halteDistance',
      ),
      'process-trayek-umum' => array(
        self::PAGE => 'Process/trayek',
      ),

      // ---- API
      'api-nearby-point' => array(
        self::PAGE => 'Api/nearbyPoint', 
        // list all nearby route and shelter (with busway route)
        // param q = [{lat, lon, radius}]
        // return array( array(lat, lon, type - halte/rute angkot, label) )
      ),
      'api-nearby-route' => array(
        self::PAGE => 'Api/nearbyRoute', 
        // list all nearby route and shelter (with busway route)
        // param q = [{lat, lon, radius}]
        // return array( array(lat, lon, type - halte/rute angkot, label) )
      ),
      'api-get-route' => array(
        self::PAGE => 'Api/getRoute', 
        // list route to be traversed
        // param q = [source{lat, lon}, destination{lat, lon}]
        /* return
          array(
            route1, transit, ... routeN            
          array(
            trayek_name,
            trayek_type,
            trayek_routes => array(0 => (lat, lng), 1=>(lat,lng))
            )
          );
        */
      ),
      'api-eta-busway' => array(
        self::PAGE => 'Api/etaBusway', 
        // get eta from 2 busway shelter
        // param q = [source{halteid}, destination{halteid}]
        // return 
      ),
      'api-get-cctv' => array(
        self::PAGE => 'Api/getCctv', 
        // get cctv from near location
        // param q = [{lat, lon}]
        // return 
      ),
      'api-get-hangout' => array(
        self::PAGE => 'Api/getHangout', 
        // get eta from 2 busway shelter
        // param q = [{lat, lon}]
        // return list of hangout places
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
