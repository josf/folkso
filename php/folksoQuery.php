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
   * @subpackage Tagserv
   */

  /**
   * @package Folkso
   */
class folksoQuery {
  public $tag; // tag id or name
  public $res; // resource id or url

  private $method;

  /**
   * We store the raw server HTTP_ACCEPT data here.
   */
  private $req_content_type;

  /**
   * Once the internal content type has been calculated, we cache it here.
   */
  private $fk_content_type;

  private $fk_params = array(); //will contain only folkso related parameters

  /**
   * Intended to receive as args $_SERVER, $_GET and $_POST. When
   * authorization is added, there will be an authorization argument
   * as well.
   */
  function __construct ($server, $get, $post) {
    $this->method = strtolower($server['REQUEST_METHOD']);
    $this->req_content_type = $server['HTTP_ACCEPT'];
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
    *
    * Slashes are removed from input data by stripslashes, in case
    * magic_quotes_gpc is on, which it probably is.
    */
  private function parse_params ($array) {
      $accum = array();
      $mults = array();
      foreach ($array as $param_key => $param_val) {
          if (substr($param_key, 0, 6) == 'folkso') {
            
            # to avoid XSS -- no html allowed anywhere.
            $param_val = strip_tags($param_val);

            /** in case the dreaded magic_quotes_gpc is on. We will do
                our own escaping, thankyou. **/
            $param_val = stripslashes($param_val);

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
                /* special cases */
                switch ( $param_key) {
                case 'folksopage':
                  $param_val = $this->checkpage($param_val);
                  break;
                case 'folksotag':
                  $this->tag = $param_val;
                  break;
                case 'folksores':
                  $this->res = $param_val;
                  break;
                case 'folksodatatype':
                  $this->req_content_type = $param_val;
                  if ($this->is_valid_datatype($param_val)) {
                    $this->fk_content_type = $param_val;
                  }
                  break;
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
   * Returns the internal content type ($this->fk_content_type). If
   * this variable is undefined, it is calculated from the server
   * variable and then cached.
   *
   */
  public function content_type () {

    if (is_string($this->fk_content_type)) {
      return $this->fk_content_type;
    }
    else {
      $this->fk_content_type 
        = $this->parse_content_type($this->req_content_type);

      return $this->fk_content_type;
    }
  }

  /**
   * Check if string is one of the basic request datatypes.
   */
  private function is_valid_datatype ($str) {
    $valid_types = array('xml', 'html', 'text');
    if (in_array($str, $valid_types)) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Parses the string to see which datatype will become the content
   * type. Returns one of the basic internal datatypes.
   */
  private function parse_content_type($content) {
    $parts = explode(',', $content);
    $returns = array('xml' => 'xml',
                     'xml' => 'xml',
                     'html' => 'html',
                     'xhtml' => 'html',
                     'xhtml-xml' => 'html',
                     'text' => 'text');
    foreach ($parts as $accept) {
      $acc = $accept; // default is to keep the whole thing, just in case.

      // otherwise, get rid of the 'text' or 'application' part...
      if (strpos($accept, '/')) {
        $acc = substr($accept, strpos($accept, '/') + 1);
      }
      return $returns[$acc] ? $returns[$acc] : $acc;
    }
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
