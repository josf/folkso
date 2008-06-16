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
    $this->method = strtolower($server['REQUEST_METHOD']);
    $this->content_type = $server['HTTP_ACCEPT'];
    if (count($get) > 0) {
      $this->fk_params = array_merge($this->parse_params($get), 
                                     $this->fk_params);;
    }

    if (count($post) > 0) {
      $this->fk_params = array_merge($this->parse_params($post), 
                                     $this->fk_params);
    }
    /** Will add put  support here later (maybe) **/
  }


   /**
    *
    * @params array $array : an array made up of $_SERVER etc.
    * 
    * Checks for keys starting with 'folkso' and adds them to the
    * object. Values longer than 300 characters are shortened to 300
    * characters. 300 is chosen because it is a bit more than 255, the
    * limit for most of the Mysql VARCHAR() arguments.
    *
    * Fields ending in three digits are processed differently. Their
    * values are built up into arrays that are then associated with a
    * single parameter name, stripped of the three finale digits.
    */
  private function parse_params ($array) {
      $accum = array();
      $mults = array();
      foreach ($array as $param_key => $param_val) {
          if (substr($param_key, 0, 6) == 'folkso') {
              
              # if fieldname end in 3 digits : folksothing123, we strip off
              # the digits and build up an array of the fields
            if (is_numeric(substr($param_key, -3))) {
                  $new_key = substr($param_key, 0, -3);

                 # for 1st time through
                  if (! isset($mults[$new_key])) {
                      $mults[$new_key] = array();
                  }      
                  array_push($mults[$new_key],
                         $this->field_shorten($param_val));
              }
              else {
                /** What is this for? **/
                  if ( $param_key == 'folksopage' ) {
                      $param_val = $this->checkpage($param_val);
                  }

                  $accum[$param_key] = $this->field_shorten($param_val);
              }
          }
      }

    # If there are multiple fields, put them into $accum
    if (count($mults) > 0){
      foreach ($mults as $mkey => $mval) {
        $accum[$mkey] = $mval;
      }
    }
    return $accum;
  }

/**
  * If the 'folksopage' parameter is present, this function validates
  *  it, makes  sure that it is an integer and that it is not longer
  * than 4 digits. (If it is longer, the first four digits are used.)
  *
  * In case of a malformed field, 0 is returned, which should
  * eliminate the effect of the field.
  *
  * @param mixed $page Ideally this is an integer, but we want to be sure
  * @return integer
  */
  private function checkpage( $page ) {
      if (preg_match('/^\d+$/', $page)) {
          if ( strlen($page) > 4) {
            return substr($page, 1, 4);
      }
          else {
              return $page;    
          }         
      }         
         else { 
             return 0;
         }              
  }     

  /**
   * Simple getter function right now.
   *
   */
  public function content_type () {
    return $this->content_type;
  }

  /**
   * Returns the method used. In smallcaps, which should be the norm
   * here. The method is put in smallcaps on object construction.
   */
  public function method () {
    return $this->method;
  }

  /* This should not be publicly used anymore */
  private function params () {
    return $this->fk_params;
  }

  /**
   * Convience access to parameters. Not necessary to write 'folkso'
   * in front of parameters.
   */
  public function get_param ($str) {
    if (isset($this->fk_params[$str])) {
      return $this->fk_params[$str];
    }
    elseif ((substr($str, 0, 6) <> 'folkso') &&
            (isset($this->fk_params['folkso'.$str]))) {
      return $this->fk_params['folkso' . $str ];
    }
    else {
      return false;
    }
  }


  /**
   * Note that this returns true for strings AND arrays. The calling
   * function should distinguish if finer grained distinctions are
   * necessary, by using is_single_param or is_multiple_param.
   */
  public function is_param ($str) {
    if ((is_string($this->fk_params[$str])) ||
        (is_string($this->fk_params['folkso' . $str])) ||
        ((is_array($this->fk_params[$str])) &&
         (count($this->fk_params[$str]) > 0)) ||
        ((is_array($this->fk_params['folkso' . $str])) &&
         (count($this->fk_params['folkso' . $str]) > 0))) {
      return true;
    }
    else {
      return false;
    }
  }
  /**
   * 
   * Returns true if the parameter exists and is not multiple.
   * 
   * @params string $str A parameter name.
   * @returns boolean
   */
  public function is_single_param ($str) {
    if ((is_string($this->fk_params[$str])) ||
        (is_string($this->fk_params['folkso'.$str]))){
      return true;
    }
    else {
      return false; 
    }
  }

  /**
   * Note: returns false for an empty array. This makes sense, I
   * think.
   *
   * @params string $str A parameter name.
   * @returns boolean
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

  /**
   * Shortens a string to a maximum of 300 characters
   *
   * @param string $str
   * @returns string
   */
  private function field_shorten ($str) {
    $str = trim($str);

    if ( strlen($str) < 300) {
      return $str;
    }
    else {
      return substr($str, 0, 300);
    }
  }

  /**
   * Use is_numeric instead...
   * 
   * @returns boolean
   * @params mixed $param
   */
  public function is_number ($param) {
    if (is_numeric($this->get_param($param))) {
      return true;
    }
    else {
      return false;
    }
  }
  }// end class


?>