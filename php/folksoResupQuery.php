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
  public function resremove ($url) {
    $a = array(
               array('type' => 'common',
                     'sql' =>
                     "DELETE FROM r, te \n"
                     . " USING resource r \n"
                     . " LEFT JOIN tagevent te ON r.id = te.resource_id \n"
                     . " WHERE r.uri_raw = <<<x>>> \n")
               );
    $sql = $this->qb->build($a,
                            $url,
                            array());
    return $sql;
  }
}


?>