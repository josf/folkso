<?php


  /**
   * Provide link formatting tools to build URLs for  resources
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
require_once('folksoFabula.php');
require_once('folksoLink.php');
  /**
   * @package Folkso
   */

class folksoResourceLink extends folksoLink {

  public $loc;
  public $identifier;
  public $getlink;
  
   /**
    * Returns a URL for a basic resource.php html query
    *
    * @param $resource URL or id, defaults to whatever was provided on object creation.
    * @param $type String One of the folkso datatypes (html (default), xml, text...)
    * 
    */
  public function getLink ($resource = null, $type = 'html') {
      if ($this->getlink) { 
        return $this->getlink;
      }

      $res = $resource ? $resource : $this->identifier;
      $this->getlink =
        $this->loc->web_url 
        . $this->loc->get_path . 'resource.php/folksores=' 
        . $res 
        . "&folksodatatype=$type";
      return $this->getlink;
    }
   

}