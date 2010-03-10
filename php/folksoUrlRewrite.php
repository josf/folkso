<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage
   * @author Joseph Fahey
   * @copyright 2010 Gnu Public Licence (GPL)
   */

  /**
   * @package Folkso
   */
abstract class folksoUrlRewrite {

  public $firstArg;
  /**
   * Array of param names that do not have predicates. They are
   * translated as "folksoparam=1".
   */
  public $singletons;

  /**
   * Array of param names that take predicates. The following value is
   * assumed to be the predicate. "param/value" becomes "folksoparam
   * => value".
   */
  public $pairs;

  public function transmute($req) {
    $args = $this->splitReq($req);

    /*
     * The array we are going to return
     */
    $qu = array();

    if (! $this->validateArgs($args)) {
      throw new InvalidRequestException('Malformed url: ' . $req);
    }
    $this->argParse($qu, $args);
    return $qu;
  }

  /**
   * Prepares input string by splitting into an array on
   * slashes. Prepends $this->firstArg to the array that get created
   * so that then initial item (eg. folksotag=atag) can be treated as
   * a simple param/value pair. "tag" will usually be stripped off of
   * the URI if /tag is a real directory.
   *
   * @param $req String The uri string.
   */
  public function splitReq ($req) {
    $arr =  explode('/', $req);

    // warning, pass by ref php function!
    array_unshift($arr, $this->firstArg);
    return $arr;
  }


  public function argParse(&$qu, $args) {
    $it = 0;
    while ($it <= count($args)) {
      if ($this->isSingleVal($args[$it])) {
        $this->addSingleton($qu, $args[$it]);
        ++$it;
      }
      elseif ($this->isDoubleVal($args[$it])) {
        $this->addPair($qu, $args[$it], $args[$it + 1]);
        $it = $it + 2;
      }
      elseif ($this->isSpecialVal($args[$it])) {
        $this->addSingleton($qu, $args[$it]);
        $this->addPair($qu, $this->special[$args[$it]], $args[$it + 1]);
        $it = $it + 2;
      }
      else {
        ++$it;
      }
    }
    return $qu;
  }

  public function validateArgs($args) {
    if (isset($this->firstArg) &&
        ($args[0] !== $this->firstArg)) {
      return false;
    }
    return true;
  }
  
  /**
   * @brief Add param/value pair to query array
   *
   * Convenience function. Adds "folkso" to param names if absent.
   *
   * @param $qu Array The array we are building (pass by reference)
   * @param $paramName String Parameter name 
   * @param $value String
   * @return Array
   */
  public function addPair(&$qu, $paramName, $value) {
    if (substr($paramName, 0, 6) != 'folkso') {
      $paramName = 'folkso' . $paramName;
    }
    $qu[$paramName] = $value;
    return $qu;

  }

  public function addSingleton(&$qu, $paramName) {
    if (substr($paramName, 0, 6) != 'folkso') {
      $paramName = 'folkso' . $paramName;
    }
    $qu[$paramName] = 1;
    return $qu;
  }


  /**
   * @brief Test if a param is a singleton param
   *
   * @return Boolean
   * @param $param String
   */
   public function isSingleVal ($param) {
     if (in_array($param, $this->singletons)) {
       return true;
     }
     return false;
   }
  
   
   /**
    * @brief Test if a param is a double param, ie. in $rw->pairs
    * @param $param
    * @return Boolean
    */
    public function isDoubleVal ($param) {
      if (in_array($param, $this->pairs)) {
        return true;
      }
      return false;
    }

    /**
     * Special params have an implied param name for the value, and
     * appear themselves as folksoparam=1. merge/tagtwo yields
     * folksomerge=1&folksotarget=tagtwo, for example.
     * 
     *
     * @param $param String Parameter name
     */
     public function isSpecialVal ($param) {
       if (isset($this->special[$param])) {
         return true;
       }
       return false;
     }
    

}
