<?php
  /**
   * 
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

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
   *
   * @package Folkso
   */
require_once('folksoDBconnect.php');
require_once('folksoQuery.php');
require_once('folksoWsseCreds.php');
require_once('folksoFabula.php');
require_once('folksoResponder.php');
require_once('folksoSession.php');



class folksoServer {

  // Access stuff
  /**
   * Defines access mode. This may not be necessary or should be moved
   * to the folksoResponse level.
   */
  public $clientAccessRestrict = 'LOCAL'; //'LOCAL', 'LIST' or 'ALL'
  public $clientAccessRestrictList = array('127.0.0.1'); //localhost always allowed

  /**
   * Methods to which this server object will respond. Again, this may not be necessary.
   */
  public $allowedClientMethods = array('GET');

  private $possibleMethods = array('GET', 'get', 'HEAD', 'head','POST', 'post', 'PUT', 'put', 'DELETE', 'delete');
  private $possibleAccessModes = array('LOCAL', 'LIST', 'ALL');

  public $responseObjects = array();
  public $authorize_get_fields = array();
  private $conf__keys = array('methods', 
                       'access_mode','access_list', 
                       'authorize_get_fields');
  /**
   * @brief folksoUrlRewrite object
   */
  public $rewrite;


  /**
   * @param array $config
   * Argument is an associative array containing any of the following
   * keys:
   *
   * 1. methods: an array of acceptable http method names.
   * 
   * 2. access_mode: Single string, either 'LOCAL' (access only from
   * localhost), 'LIST' (access only from a list of hosts), or 'ALL'
   * (no per host access restrictions). 
   *
   * 3. access_list: if access_mode is LIST, supply the list of valid
   * hosts or IPs here.
   *
   * 4. authorize_get_fields: Most GET requests do not require
   * authentication/authorization, so we can usually avoid this
   * step. However, certain kinds of GETs may need
   * authentication/authorization, so you can put the field names that
   * _do_ require it in an array here.
   *
   * 5. rewrite: A folksoUrlRewrite object for converting clean URL's to traditional
   * associative arrays
   */
  function __construct ($config) {
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

    if (array_key_exists('rewrite', $config)) {
      $this->rewrite = $config['rewrite'];
    }
  }

  /**
   * Adds a folksoResponse object to the array of response objects.
   * 
   * @param folksoResponse $resp 
   *
   */ 
  public function addResponseObj (folksoResponder $resp) { //one arg here to indicate
    //that at least one is necessary
    $this->responseObjects[] = $resp;
  }

  /**
   * @brief Produce output and send to client.
   *
   * After some initial checks (method, client address, session for
   * write methods), each response object is checked to see if it is
   * equiped to handle the request.
   *
   * @param $req String (optional) url parameters
   *
   * If $req is supplied (presumably through a url rewrite), $req 
   * used instead of $_GET and $_POST.
   * 
   */
  public function Respond ($req = null) {
    if (!($this->valid_method())) {
      // some kind of error
      header('HTTP/1.0 405');
      print "NOT OK. Illegal request method for this resource.";
      return;
    }
    
    if (!($this->validClientAddress($_SERVER['REMOTE_HOST'], 
                                    $_SERVER['REMOTE_ADDR']))) {
      header('HTTP/1.1 403 Forbidden');
      print "Sorry, this is not available to you.";
      return;
    }


    /*
     * Only going to try to rewrite if $this->rw is valid. Wimpy, and
     * probably should be changed later. If there is a $req, then it
     * is time to rewrite.
     */
    if ($req &&
        ($this->rewrite instanceof folksoUrlRewrite)) {
      $q = new folksoQuery($_SERVER, 
                           $this->rewrite->transmute($req),
                           array());
    }
    else {
      $q = new folksoQuery($_SERVER, $_GET, $_POST); 
    }

    $realm = 'folkso';
    $loc = new folksoFabula();
    $dbc = $loc->locDBC();
    $fks = new folksoSession($dbc);

    /**
     * $sid: session ID
     */
    $sid = $_COOKIE['folksosess'] ? $_COOKIE['folksosess'] : $q->get_param('session');;

    try {
      $fks->setSid($sid);
    }
    catch ( badSidException $e) {
      if ($q->is_write_method()) {
        header('HTTP/1.1 403 Login required'); // redirect instead
        print "You must login first. Go to the login page.";
        exit();
      }
    }

    /* check each response object and run the response if activatep
       returns true*/
    $repflag = false;
    if (count($this->responseObjects) === 0) {
      trigger_error("No responseObjects available", E_USER_ERROR);
    }

    /** Walking the response objects **/
    foreach ($this->responseObjects as $resp) {
      if ($resp->activatep($q)) {
        $repflag = true;
        $resp->Respond($q, $dbc, $fks);
        break;
      }
    }

    /** check for no valid response **/
    if (!$repflag) {
      header('HTTP/1.1 400');
      print "Client did not make a valid query. (folksoServer)";
      // default response or error page...
    }
  }

  /**
   * Tests whether the REQUEST_METHOD in $_SERVER is allowed.
   * 
   * @return boolean
   */
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

  /** 
   * "True" means authorization _is_ necessary, false means it isn't. We
   * could check the fields for some GETs here too, to see if this is an
   * individualized GET request. (Or maybe it isn't necessary to do so
   * either.)
   *
   * @param folksoQuery $q
   */
  function is_auth_necessary (folksoQuery $q) {
    return false;
    if (($q->method()== 'get') ||
        ($q->method() == 'head') ||
        ($this->clientAccessRestrict == 'LOCAL')) {
      return false;
    }
    else {
      return true;
    }
  }

} //end of class


?>