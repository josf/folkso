<?php

  /**
   * A folksoServer object deals with all of the basic interactions
   * that concern all of the actions of the tag server (per URL).
   * Access to the tag server can be allowed or denied by IP
   * address.
   *
   * The primary task of a folksoServer object is to decide which
   * folksoResponse object will handle the incoming request, and then
   * passes $_SERVER, $_GET and$_POST to that folksoResponse
   * object. These objects are stored in the responseObjects array.
   *
   * When authorization is added, folksoServer will handle that as
   * well.
   *
   */

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');

class folksoServer {

  // Access stuff
  public $clientAccessRestrict = 'LOCAL'; //'LOCAL', 'LIST' or 'ALL'
  public $clientAccessRestrictList = array('127.0.0.1'); //localhost always allowed
  public $allowedClientMethods = array('GET');
  
  private $possibleMethods = array('GET', 'get', 'HEAD', 'head','POST', 'post', 'PUT', 'put', 'DELETE', 'delete');
  private $possibleAccessModes = array('LOCAL', 'LIST', 'ALL');
  public $responseObjects = array();


  function __construct ($config) {
     $conf_keys = array('methods', 'access_mode','access_list' );
    
    // methods
    if ((array_key_exists('methods', $config)) &&
        (is_array($config['methods']))) {
      $this->allowedClientMethods = array();
      foreach ($config['methods'] as $meth) {
        if (in_array($meth, $this->possibleMethods)) {
          array_push($this->allowedClientMethods, $meth);
        }
      }
    }

    // access mode
    if ((array_key_exists('access_mode', $config)) &&
        (in_array($config['access_mode'], $this->possibleAccessModes))) {
      $this->clientAccessRestrict = $config['access_mode'];
    }
    else {
      $this->clientAccessRestrict = 'LOCAL';
    }
        
    // client restrictions
    if (( $this->clientAccessRestrict == 'LIST') &&
        ( array_key_exists('access_list', $config)) &&
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
        $this->responseObjects[] = $ob;
      }
      else {
        //error
      }
    }
  }


  public function Respond () {
    /*
     * Based on the request received, checks each response object is
     * checked to see if it is equiped to handle the request.
     */
    if (!($this->valid_method())) {
      // some kind of error
      header('HTTP/1.0 405');
      print "<h1>NOT OK</h1><p>Illegal request method for this resource.</p>";
      return;
    }

    
    if (!($this->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']))) {
      header('HTTP/1.0 403');
      print "Sorry, this not available to you";
      return;
    }



    $q = new folksoQuery($_SERVER, $_GET, $_POST); 

    if (($q->method() <> 'get') &&
        (strlen($_SERVER['PHP_AUTH_DIGEST']) == 0)) {
      header('HTTP/1.0 403');
      print "You must identify yourself to modify this resource";
    }

    $cred = new folksoUserCreds( $_SERVER['PHP_AUTH_DIGEST']);
    /* check each response object and run the response if activatep
     returns true*/

    $repflag = false;
    foreach ($this->responseObjects as $resp) {
      if ( $resp->activatep($q, $cred)) {
        $resp->Respond($q, $cred);
        $repflag = true;
        break;
      }
    }
    if (!$repflag) {
      header('HTTP/1.0 400');
      print "Client did not make a valid query.";
      // default response or error page...
    }
  }
  
  function valid_method () {
    if (in_array($_SERVER['REQUEST_METHOD'], $this->allowedClientMethods)) {
      return true;
    }
    else {
      return false;
    }
  }

  
  function validClientAddress ($host, $ip) {
    if ( $this->clientAccessRestrict == 'ALL') {
      return true;
    }
    if (( $this->clientAccessRestrict == 'LOCAL') &&
        (( $ip == '127.0.0.1' ) or
         ( $ip == '::1'))) {
      return TRUE;
    }
    if (( $this->clientAccessRestrict == 'LIST' ) &&
        (( in_array($ip, $this->clientAccessRestrictList)) ||
         ( in_array($host, $this->clientAccessRestrictList)))) {
      return true;
    }
    return false;
  }


  } //end of class


?>