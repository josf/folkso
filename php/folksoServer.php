<?php

class TagServer {

  // Access stuff
  public $clientUrlRestrict = 'LOC'; //'LOC', 'LIS' or 'ALL'
  public $clientUrlRestrictList = array('127.0.0.1'); //localhost always allowed
  public $allowedClientMethods;
  private $possibleMethods = array('GET', 'get', 'POST', 'post', 'PUT', 'put', 'DELETE', 'delete');
  private $possibleAccessModes = array('LOCAL', 'LIST', 'ALL');
  private $responseObjects = array();


  function __construct ($config) {
    private $conf_keys = array('methods', 'access_mode','access_list' );
    
    // methods
    if ((array_key_exists('methods', $config)) &&
        (is_array($config['methods']))) {
      $this->$allowedClientMethods = array();
      foreach ($config['methods'] as $meth) {
        if (in_array($possibleMethods, $meth)) {
          array_push($this->allowedClientMethods, $meth);
        }
      }
    }
    else {
      $this->allowedClientMethods = array('GET');
    }

    // access mode
    if ((array_key_exists('access_mode')) &&
        (in_array($possibleAccessModes, $config['access_mode']))) {
      $this->clientAccessRestrict = $config['access_mode'];
    }
    else {
      $this->clientAccessRestrict = 'LOC';
    }
        
    // client restrictions
    if (( $this->clientAccessRestrict = 'LIST') &&
        ( array_key_exists($config['access_list'])) &&
        ( is_array($config['access_list']))) { // this should be an erreur instead!
      $this->clientAllowedHost = $config['access_list'];
    }

          
  }

  
  public function addResponseObj (folksoResponse $resp) { //one arg here to indicate
                                           //that at least one is
                                           //necessary
    $objs = func_get_args();
    foreach ($objs as $ob) {
      if ( $ob instanceof folksoResponse ) {
        push($this->responseObjects, $ob);
      }
      else {
        //error
      }
    }
  }

  public function Respond ($request) {
    /*
     * Based on the request received, checks each response object is
     * checked to see if it is equiped to handle the request.
     */

  }


  public function initialChecks () {
    if ((in_array($_SERVER['REQUEST_METHOD'], $this->allowedClientMethods)) &&
        ($this->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']))) {
      // deal with request
    }
    else {
      // send error message
    }
  }
  
  private function validClientAddress ($host, $ip) {
    if ( $this->clientUrlRestrict == 'ALL') {
      return true;
    }
    if (( $this->clientUrlRestrict == 'LOC') &&
        ( $ip == '127.0.0.1' )) {
      return true;
    }
    if (( $this->clientUrlRestrict == 'LIS' ) &&
        (( in_array($ip, $this->clientUrlRestrictList)) ||
         ( in_array($host, $this->clientUrlRestrictList)))) {
      return true;
    }
    return false;
  }


}


?>