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
   * Shortens a string to a maximum of 300 characters
   */

   /**
    * Checks for keys starting with 'folkso' and adds them to the
    * object. Values longer than 300 characters are shortened to 300
    * characters. 300 is chosen because it is a bit more than 255, the
    * limit for most of the Mysql VARCHAR() arguments.
    */
  private function param_get ($array) {
    $accum = array();
    foreach ($array as $param_key => $param_val) {
      if (substr($param_key, 0, 6) == 'folkso') {
        $to_insert = $param_val;

        if (strstr($param_val, '+')) {
          $to_insert = array_map(  'field_shorten', 
                                   explode('+', $param_val));
        }
        else {
          $to_insert = field_shorten($param_val);
        }
        $accum[$param_key] = $to_insert;
      }
    }
    return $accum;
  }

  /**
   * Returns the method used. In smallcaps, which should be the norm
   * here.
   */
  public function method () {
    return strtolower($this->method);
  }


  public function params () {
    return $this->fk_params;
  }


  /**
   * Note that this returns true for strings AND arrays. The calling
   * function should distinguish if finer grained distinctions are
   * necessary, by using is_single_param or is_multiple_param.
   */
  public function is_param ($str) {
    if ((is_string($this->fk_params[$str])) ||
        ((is_array($this->fk_params[$str])) &&
         (count($this->fk_params[$str]) > 0)))
      {
      return true;
    }
    else {
      return false;
    }
  }

  public function is_single_param ($str) {
    if (is_string($this->fk_params[$str])) {
      return true;
    }
    else {
      return false; 
    }
  }

  /**
   * Note: returns false for an empty array. This makes sense, I
   * think.
   */
  public function is_multiple_param ($str) {
    if ((is_array($this->fk_params[$str])) &&
        (count($this->fk_params[$str]) > 0)) {
      return true;
    }
    else {
      return false;
    }
  }

  }// end class
 function field_shorten ($str) {
   $str = trim($str);

    if ( strlen($str) < 300) {
      return $str;
    }
    else {
      return substr($str, 0, 300);
    }
  }


?>