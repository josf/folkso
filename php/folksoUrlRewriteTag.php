<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage
   * @author Joseph Fahey
   * @copyright 2010 Gnu Public Licence (GPL)
   */
require_once "folksoUrlRewrite.php";
  /**
   * @package Folkso
   */
class folksoUrlRewriteTag extends folksoUrlRewrite {

  public $firstArg = 'tag';
  public $singletons = array('related', 'fancy', 'newtag', 'byalpha');
  public $pairs = array('tag', 'feed', 'datatype', 'offset');
  public $special = array('merge' => 'target');


}
