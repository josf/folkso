<?php
  /**
   * This class provides a unified interface for all of the data
   * pertaining to the HTTP request, including GET and POST
   * parameters, and possible PUT and DELETE parameters should we end
   * up using those methods.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */
class folksoQuery {
  private $method;
  private $content_type;
  private $fk_params = array(); //will contain only folkso related parameters

  /**
   * Intended to receive as args $_SERVER, $_GET and $_POST. When
   * authorization is added, there will be an authorization argument
   * as well.
   */
   function __construct ($server, $get, $post) {
    $this->method = $server['REQUEST_METHOD'];
    $this->content_type = $server['CONTENT_TYPE'];
    if (count($get) > 0) {
      $this->fk_params = array_merge($this->param_get($get), $this->fk_params);;
    }

    if (count($post) > 0) {
      $this->fk_params = array_merge($this->param_get($post), $this->fk_params);
    }
    /** Will add put and delete support here later **/

  }

   /**
    * Checks for keys starting with 'folkso' and adds them to the
    * object. Values longer than 300 characters are shortened to 300
    * characters.
    */
  private function param_get ($array) {
    $accum = array();
    foreach ($array as $param_key => $param_val) {
      if (substr($param_key, 0, 6) == 'folkso') {
        $to_insert = $param_val;
        if (strlen($param_val) > 300) {
          $to_insert = substr($param_val, 0, 300);
        }
        $accum[$param_key] = $to_insert;
      }
    }
    return $accum;
  }

  public function params () {
    return $this->fk_params;
  }

  /**
   * Returns the method used. In smallcaps, which should be the norm
   * here.
   */
  public function method () {
    return strtolower($this->method);
  }

  public function is_param ($str) {
    if (is_string($this->fk_params[$str])) {
      return true;
    }
    else {
      return false;
    }
  }


  }// end class
?>