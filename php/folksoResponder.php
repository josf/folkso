<?php


  /**
   * folksoResponder provides objects to be used as args to the
   * addResponseObj method in folksoServer.php. Each object must
   * contain a test function and an "action" function. The test
   * function is called to determine if the action function should be
   * called or not.
   *
   * Both test and action functions receive folksoQuery objects as
   * mandatory arguments. Test functions must return either true or
   * false. Action functions should provide a document of some sort to
   * return and probably should provide a status code.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

  /**
   * @package Folkso
   */
class folksoResponder {

  public $test_func;
  public $action_func;

  /**
   * The required method for this response. Should always be in
   * lowercase.
   */
  public $method;
  protected $activate_params;

  /**
   * @param string $method The method that this Response will respond
   * to.  
   *
   * @param associative array $params An associative array of
   * arrays. The four possible keys are 'required', 'oneof',
   * 'required_single', and 'required_multiple'. The key or keys
   * present must contain a linear array of field names (not
   * necessarily preceded by 'folkso'). If just one of the fields in
   * the 'oneof' array are present in the query, the query will be
   * considered valid (provided that the other conditions from the
   * 'required*' arrays are met, if present).
   *
   * @param function $action_func This function will be called if this
   * response is activated.
   */
  function __construct ($method, $params, $action_func) {
    $this->method = strtolower($method);
    $this->action_func = $action_func;
    $this->activate_params = $params;
  }

  /**
   * Tests whether this Response object should respond to the request ($q).
   * 
   * First the method is tested.
   * 
   * Using the information in $params passed on object creation, this
   * method tests that information against the query information ($q).
   *
   * @param folksoQuery $q
   * @uses method
   * @return boolean
   */
  function activatep (folksoQuery $q, folksoWsseCreds $cred) {
    if (($q->method() == $this->method) &&
        ($this->param_check($q))) {
      return true;
    }
    else {
      return false;
    }
  }
  
  /**
   * Checks to see if this Response should be used to respond to the query $q.
   *
   * If there are 'exclude' parameters, they are checked first. If any
   * of them are present, param_check returns 'false'.
   *
   * Then we check the 'required' parameters in $this->activate_params
   * and returns false if any of them are missing. If one of the
   * 'oneof' fields is present (and assuming conditions in the other
   * arrays are met), 'true' is returned.
   *
   * @param folksoQuery $q
   * @return boolean
   */
  private function param_check(folksoQuery $q) {

    if (is_array($this->activate_params['exclude'])) {
        foreach ($this->activate_params['exclude'] as $no) {
          if ($q->is_param($no)) {
            return false;
          }
        }
      }
    $all_requireds = array();
    foreach (array($this->activate_params['required'],
                   $this->activate_params['required_single'],
                   $this->activate_params['required_multiple']) as $arr) {
      if (is_array($arr)) {
        $all_requireds = array_merge($all_requireds, $arr);
      }
    }
    
    foreach ($all_requireds as $p) {
      if (!$q->is_param($p)) {
          return false;
      }
    }

    if (is_array($this->activate_params['required_single'])) {
      foreach ($this->activate_params['required_single'] as $p) {
        if (!$q->is_single_param($p)) {
          return false;
        }
      }
    }

    if (is_array($this->activate_params['required_multiple'])) {
      foreach ($this->activate_params['required_multiple'] as $p) {
        if (!$q->is_multiple_param($p)) {
          return false;
        }
      }
    }

    $oneof = false;
    if (is_array($this->activate_params['oneof'])) {
      foreach ($this->activate_params['oneof'] as $p) {
        if ($q->is_param($p)) {
          $oneof = true;
        }
      }
      if (!$oneof) {
        return false;
      }
    }
    return true;
  }

  /**
   * Passes t
   * @param folksoQuery $q
   * @param folksoWsseCreds $cred (this will probably change)
   * @param folkskoDBconnect $dbc (passed in from folksoServer)
   * @return HTTP response. The return value is never used as such,
   * since the action is performed in the server.
   */
  function Respond (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
    $aa = $this->action_func;
    return $aa($q, $cred, $dbc); //action (on DB for example) + return document
                        //+ status. In fact, returned value does not
                        //matter probably.
  }

  function getHttpMethod () {
    return strtolower($this->method);
  }

  /**
   * I think this is deprecated.
   */
  function setHttpMethod ($meth) {
    if ((is_string($meth)) &&
        in_array(strtolower($meth), array('get', 'put', 'post', 'delete'))) {
      $this->method = strtolower($meth);
      return $this->method;
    }
    else {
      //Error
    }
  }

  }// end of class



?>
