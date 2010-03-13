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
class folksoUrlRewriteResource extends folksoUrlRewrite {

  public $firstArg = 'res';
  public $singletons = array('cloud', 'clouduri', 'delete', 'ean13list',
                             'visit', 'bydate', 'bypop', 'newresource');
  public $pairs = array('res','tag', 'newtitle', 'note', 'newean13', 
                        'ean13',  'oldean13', 'meta', 
                        'datatype');
  public $special = array();

}
