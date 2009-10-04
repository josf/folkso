<?php

  /**
   * Provide link formatting tools to build URLs for tags and resources
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
require_once('folksoFabula.php');
  /**
   * @package Folkso
   */
abstract class folksoLink {
  /**
   * The original data supplied on object creation. Either a numeric
   * id or a string (url or tag name)
   */
  public $identifier;
  public $getlink;
  /**
   * @param $resource
   * @param $loc (optional) folksoLocal object
   */
  public function __construct ($resource, folksoLocal $loc = null) {
     $this->identifier = $resource;

     if ($loc instanceof folksoLocal) {
       $this->loc = $loc;
     }
     else {
       $this->loc = new folksoFabula();
     }
   }

  abstract public function getLink();

}