<?php
 /**
   *
   * @package Folkso
   * @subpackage Tagserv
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */
require_once('folksoQueryBuild.php');
  /**
   * Queries for use with resource.php.
   */
class folksoResQuery  {

  public $qb;
  public function __construct() {
    $this->qb = new folksoQueryBuild();
  }


  $a = array(
             array(
                   'type' => 'common',
                   'sql' => 
                   "SELECT tagid, tagnorm, tagdisplay, \n"
                   ." CASE \n"
                   ." WHEN rese.rank <= (rese.totaltags * 0.1) THEN 5 \n"
                   ." WHEN rese.rank <= (rese.totaltags * 0.3) THEN 4 \n"
                   ." WHEN rese.rank <= (rese.totaltags * 0.5) THEN 3 \n"
                   ." WHEN rese.rank <= (rese.totaltags * 0.7) THEN 2 \n"
                   ." ELSE 1 \n"
                   ." END \n"
                   ." AS cloudweight \n"
                   ." FROM \n"
                   ." (SELECT ta.id AS tagid, \n"
                   ." ta.tagnorm AS tagnorm, \n"
                   ." ta.tagdisplay AS tagdisplay, \n"
                   ." ta.popularity AS pop, \n"
                   ." COUNT(ta2.id) AS rank, \n"
                   ." (SELECT COUNT(*) FROM tag) AS totaltags  \n"
                   ." FROM tag ta \n"
                   ." RIGHT JOIN tag ta2 ON ta.popularity <= ta2.popularity \n"
                   ." JOIN tagevent te ON ta.id = te.tag_id \n"
                   ." JOIN resource r ON te.resource_id = r.id \n"
                   ." WHERE "),
             array(
                   'type' => 'isnum',
                   'sql' => " (r.id = 16930) \n"),
             array(
                   'type' => 'notnum',
                   'sql' => " (r.uri_normal = url_whack('" . $i->dbescape($res) . "')) "),
             array(
                   'type' => 'commun', 
                   'sql' =>
                   " GROUP BY ta.id) \n"
                   ." AS rese \n"
                   " ORDER BY cloudweight DESC \n"),
             array(
                   'type' => 'taglimit',
                   'sql' => " LIMIT $taglimit \n"));     
                   


    if ($taglimit > 0) {
      $sql .= 
    }

  }


  }