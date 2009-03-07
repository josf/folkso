<?php
 /**
   *
   * @package Folkso
   * @subpackage Tagserv
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */
require_once('folksoQueryBuild.php');

class folksoResupQuery {
  
  public $qb;
  public function __construct() {
    $this->qb = new folksoQueryBuild();
  }

  /**
   * Update title
   */
  public function resmodtitle ($res, $newtitle) {
    $a = array(
               array(
                     'type' => 'common',
                     'sql' => 
                     "UPDATE"));

    $sql = $this->qb->build($a,
                            $res,
                            array());
    return $sql;
  }


  /**
   * Delete resource (presumably after a 404).
   */
  public function resremove ($res) {
    
  }
}


?>