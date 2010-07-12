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


  public $method;

  /**
   * We store the raw server HTTP_ACCEPT data here.
   */
  public $req_content_type;

  /**
   * Once the internal content type has been calculated, we cache it here.
   */
  public $fk_content_type;

  /*
   * The original form of the content type that was selected. So if,
   * after parsing the Accept header, we choose 'application/xml',
   * $fk_content_type will be 'xml' and $chosenContentType will be
   * 'application/xml'.
   */
  public $chosenContentType;

  /**
   * String. If a valid string is present (ie. 'atom' or 'rss'), and
   * if the folksoResponse object has registered an output variant
   * with an XSLT stylesheet for that type of output, then
   * folksoResponse will run the usual xml output through the
   * stylesheet before returning it.
   */
  public $applyOutput;
  /**
   * String. To be accessed through $q->subType() function. 
   *
   * Replacement for $q->applyOutput.
   */
  public $subType;

  /**
   * @brief folksoQueryAcceptType object.
   */
  public $acceptType;

  /**
   * @brief Associated array of parameter names (without the "folkso" prefix).
   *
   * Fields for which we do not automatically remove all tags with
   * strip_tags. Values should evaluate to true.
   */
  private $tagsAllowed;

  
  private $fk_params = array(); //will contain only folkso related parameters

  /**
   * Intended to receive as args $_SERVER, $_GET and $_POST. When
   * authorization is added, there will be an authorization argument
   * as well.
   */
  function __construct ($server, $get, $post) {
    $this->method = strtolower($server['REQUEST_METHOD']);
    $this->req_content_type = $server['HTTP_ACCEPT'];

    $this->tagsAllowed = array('cv' => true);

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
    * magic_quotes_gpc is on, which it probably is X-(.
    */
  private function parse_params ($array) {
      $accum = array();
      $mults = array();
      foreach ($array as $param_key => $param_val) {
          if (substr($param_key, 0, 6) == 'folkso') {
            $shortParamKey = substr($param_key, 6);
            
            # to avoid XSS -- no html allowed anywhere, except for exceptions.
            if (! (array_key_exists($shortParamKey, $this->tagsAllowed) &&
                   $this->tagsAllowed[$shortParamKey])) {
              $param_val = strip_tags($param_val);
            }

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
                case 'folksofeed':
                  if (($param_val == 'atom') ||
                      ($param_val == 'rss')) {
                    $this->fk_content_type = 'xml';
                    $this->applyOutput = $param_val; //deprecated
                    $this->subType = $param_val; 
                  }
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
    $valid_types = array('xml', 'html', 'text', 'json');
    if (in_array($str, $valid_types)) {
      return true;
    }
    return false;
  }

  /**
   * Parses the string to see which datatype will become the content
   * type. Returns one of the basic internal datatypes.
   *
   * Defaults to 'html' when string is empty.
   *
   * @param $content String Contents of HTTP_ACCEPT 
   */
  public function parse_content_type($content) {
    if (strlen($content) == 0) {
      return 'html';
    }

    $this->acceptType = $this->chooseContentType($this->buildAcceptArray($content));
    if (! $this->acceptType instanceof folksoQueryAcceptType) {
      return 'html';
    }
    $ret = $this->acceptType->fkType();

    $this->chosenContentType = $this->acceptType->accept();
    if ($this->acceptType->subType){ // subtype does not get set until fkType is called.
      $this->applyOuput = $this->acceptType->subType; // deprecated
      $this->subType = $this->acceptType->subType;
    }
    return $ret;
  }

  /**
   * The subType is for variations on xml output, in particular atom
   * and rss, so that additional xslt stylesheets can be called on the 
   * internal XML output.
   */
  public function subType() {
    if (is_string($this->subType)) {
      return $this->subType;
    }
    
    if ($this->acceptType instanceof folksoQueryAcceptType) {
      $this->subType = $this->acceptType->subType;
      return $this->subType;
    }
    else {
      $this->parse_content_type($this->req_content_type);
      if ($this->acceptType instanceof folksoQueryAcceptType) {
        $this->subType = $this->acceptType->subType;
        return $this->subType;
      }
    }
    return '';
  }
  

  /**
   * Parse accept header into an array of folkoQueryAcceptType
   * objects.
   *
   * @param $content String Complete http_accept header
   * @return Array
   */
   public function buildAcceptArray ($content) {
     $acc = array('xml' => array(),
                  'html' => array(),
                  'json' => array(),
                  'text' => array());
     $counter = 0;
    foreach (explode(',', $content) as $accept) {
      $fkAcc = new folksoQueryAcceptType($accept, $counter);
      $counter++;

      if ($fkAcc->fkType()) {
        $acc[$fkAcc->fkType()][] = $fkAcc;
      }
    }
    $acc = array_filter($acc, array($this, 'countTest'));
    return $acc;
   }
  
   /**
    * @brief Choose a content type
    * @param $types Array Output from $q->buildAcceptArray()
    */
    public function chooseContentType ($types) {
    
      if (count($types) == 0) {
        return null;
      }

      /** case 1: only one content category  and one type**/
      if (count($types) == 1)  {
        $keys = array_keys($types);
        $type = $keys[0];
        return $this->selectTypeFromArray($types[$type]);
      }

      // choose best in each category
      $champs = array();
      foreach ($types as $key => $val) {
        $champs[$key] = $this->selectTypeFromArray($val);
      }
      
      /** special case for xml and html **/
      if (($champs['xml'] instanceof folksoQueryAcceptType) &&
          ($champs['html'] instanceof folksoQueryAcceptType) &&
          ($champs['xml']->accept() == 'application/xml') &&
          ($champs['xml']->weight() <= $champs['html'])) {
        return $champs['html'];
      }
      return $this->selectTypeFromArray(array_values($champs));
    }

    /**
     * Choose the "best" type from an array of possible types.
     *
     * @param $types Array An array of 0 or more fkQueryAcceptType objs
     */
     public function selectTypeFromArray ($types) {
       $noParamTypes = array_filter($types, array($this, 'weightOne'));
       if (count($noParamTypes) > 0) {
         usort($noParamTypes, array($this, 'fkQatIndexSorter'));
         return $noParamTypes[0];
       }
       else {
         usort($types, array($this, 'fkQatWeightSorter'));
         return $types[0];         
       }
     }
    
     public function hasWeight(folksoQueryAcceptType $accT) {
       if ($accT->weight() > 0) {
         return true;
       }
       return false;
     }

     public function weightOne(folksoQueryAcceptType $accT){
       if ($accT->weight() >= 1) {
         return true;
       }
       return false;
     }


     public function fkQatWeightSorter(folksoQueryAcceptType $a,
                                       folksoQueryAcceptType $b) {
       if ($a->weight() > $b->weight()) {
         return -1;
       }
       if ($a->weight() == $b->weight()) {
         return 0;
       }
       if ($b->weight() > $a->weight()) {
         return 1;
       }
     }


     public function fkQatIndexSorter(folksoQueryAcceptType $a,
                                      folksoQueryAcceptType $b) {
       // NB: we are sorting lowest first. 
       if ($a->index() == $b->index()) {
         return 0;
       }
       return ($a->index() > $b->index()) ? 1 : -1;
     }


  /**
   * For use in parse_content_type array_filter. Returns true if the
   * array is not empty.
   */
  public function countTest ($arr) {
    if (count($arr) > 0) {
      return true;
    }
    return false;
  }


  public function contentTypeComp ($a, $b) {
    if ((! isset($a['weight']) && (! isset($b['weight'])))) {
      return 0;
    }

    if ($a['weight'] === $b['weight']) {
      return 0;
    }

    if (isset($a['weight']) && (! isset($b['weight']))) {
      return -1;
    }

    if (isset($b['weight']) && (! isset($a['weight']))) {
      return 1;
    }

    if ($a['weight'] > $b['weight']) {
      return -1;
    }
    
    if ($b['weight'] > $a['weight']) {
      return 1;
    }
  }

  public function removeOnWeight($arr) {
    return isset($arr['weight']);
  }

  /**
   * Returns the method used. In smallcaps, which should be the norm
   * here. The method is put in smallcaps on object construction.
   */
  public function method () {
    return $this->method;
  }


/**
 * Whether the method (from $_SERVER['REQUEST_METHOD']) is a write
 * method (POST or DELETE, possibly PUSH) or not.
 * 
 * @return bool True for post, delete, etc. False for get, head.
 */
 public function is_write_method () {
   if (($this->method == 'get') ||
       ($this->method == 'head')) {
     return false;
   }
   return true;
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

/**
 * An AcceptType is one of the items in an HTTP_ACCEPT header,
 * eg. text/html. 
 */
class folksoQueryAcceptType {
  /**
   * @brief The content type as received.
   */
  public $raw;

  /**
   * @brief Map between content type and folkso datatype.
   *
   * Map between the second part of the raw content-type (eg. 'html'
   * in 'text/html') and the internal (folkso) datatype.
   */
  public $equivs;
  
  /**
   * The value of either the "q" or "level" parameter, if present.
   */
  public $weight;

  /**
   * 'html' in 'text/html'
   */
  public $type_part;
  
  /**
   * Index to retain order of content-types in Accept header.
   */
  public $index;

  /**
   * Internal type.
   */
  public $fkType;

  /**
   * Currently either 'atom' or 'rss'.
   */
  public $subType;


  public function __construct($str, $index) {
    $this->raw = trim($str); $this->index = $index;

    $this->equivs = array('xml' => 'xml',
                          'html' => 'html',
                          'xhtml' => 'html',
                          'xhtml-xml' => 'html', //???
                          'xhtml+xml' => 'html',
                          'atom+xml' => 'xml',
                          'rss+xml' => 'xml',
                          'rss' => 'xml', // support 'text/rss'
                          'atom' => 'xml',
                          'json' => 'json',
                          'text' => 'text');
    
  }

  public function type_part () {
    if (is_string($this->type_part)) {
      return $this->type_part;
    }
    $this->type_part = substr($this->accept(), strpos($this->accept(), '/') + 1);
    return $this->type_part;
  }


  public function accept() {
    if (is_string($this->accept)) {
      return $this->accept;
    }
    if (strpos($this->raw, ';')) {
      $this->accept = substr($this->raw, 
                             0,
                             strpos($this->raw, ';'));
    }
    else {
      $this->accept = $this->raw;
    }
    return $this->accept;
  }

  /**
   * @brief Calculate and return the weight, taking into account the q
   * parameter if present.
   *
   * Default weight is 1.
   */
  public function weight() {
    if ($this->weight) {
      return $this->weight;
    }
    $this->weight = 1; 
    if ($this->param()) {
      if (preg_match('/q=(.+)$/', $this->param(), $match)) {
        $this->weight = $match[1];
      }
    }
    return $this->weight;
  }


  public function param () {
    if (strpos($this->raw, ';') === false) {
      return '';
    }
    return substr($this->raw, strpos($this->raw, ';'));
  }

  /**
   * Calculates internal type.
   *
   * Also sets subType when appropriate.
   *
   * @return String One of the internal types: xml, json, text, html
   */
  public function fkType() {
    if ($this->fkType) {
      return $this->fkType;
    }

    if (isset($this->equivs[$this->type_part()])) {
      $this->fkType = $this->equivs[$this->type_part()];
    }

    if ($this->fkType == 'xml') {
      if (preg_match('/atom/', $this->type_part())) {
        $this->subType = 'atom';
      }
      elseif (preg_match('/rss/', $this->type_part())) {
        $this->subType = 'rss';
      }
    }
    return $this->fkType;
  }


  public function index() {
    return $this->index;
  }
}

