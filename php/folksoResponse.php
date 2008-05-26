<?php

  /**
   * folksoResponse provides objects to be used as args to the
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
   */

 
class folksoResponse {
  public $httpMethod = 'GET';
  public $test_func;
  public $action_func;
  public $method;
  protected $activate_params;

  /**
   * @param string $method The method that this Response will respond
   * to.
   * @param associative array $params An arra
   * @param function $action_func This function will be called if this
   * response is activated.
   */
  function __construct ($method, $params, $action_func) {
    $this->method = strtolower($method);
    $this->action_func = $action_func;
    $this->activate_params = $params;
  }

  function activatep (folksoQuery $q) {
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
   * First check the 'required' parameters in $this->activate_params
   * and returns false if any of them are missing.
   *
   * @param folksoQuery $q
   * @return boolean
   */
  function param_check(folksoQuery $q) {

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


  function Respond (folksoQuery $query, folksoWsseCreds $cred, folksoDBconnect $dbc) {
    $aa = $this->action_func;
    return $aa($query, $cred, $dbc); //action (on DB for example) + return document
                        //+ status. In fact, returned value does not
                        //matter probably.
  }

  function getHttpMethod () {
    return $this->httpMethod;
  }

  function setHttpMethod ($meth) {
    if ((is_string($meth)) &&
        in_array(strtolower($meth), array('get', 'put', 'post', 'delete'))) {
      $this->httpMethod = $meth;
      return $this->httpMethod;
    }
    else {
      //Error
    }
  }

  }// end of class



?>