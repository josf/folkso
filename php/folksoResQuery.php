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
   *
   * These queries are written in something like a little language.
   *
   * These are all static methods. They just return text.
   */
class folksoResQuery  {

  public $qb;
  public function __construct() {
    $this->qb = new folksoQueryBuild();
  }

  /** vars: x and taglimit **/

  public function cloud_by_popularity($res, $taglimit = 0) {
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
                     'sql' => " (r.id = <<<x>>>) \n"),
               array(
                     'type' => 'notnum',
                     'sql' => " (r.uri_normal = url_whack('<<<x>>>')) "),
               array(
                     'type' => 'common', 
                     'sql' =>
                     " GROUP BY ta.id) \n"
                     ." AS rese \n"
                     ." ORDER BY cloudweight DESC \n"),
               array(
                     'type' => 'taglimit',
                     'sql' => " LIMIT <<<taglimit>>> \n"));     
    
    $sql = $this->qb->build($a, $res, array('taglimit' => 
                                            array('func' => '',
                                                  'value' => $taglimit)));
    return $sql;
  }

  public function getTags ($res, $limit = 0, $metaonly = false) {
    $q = array(
               array('type' => 'common',
                     'sql' =>
                         "SELECT DISTINCT "
                          ." t.id as id, t.tagdisplay as tagdisplay, " 
                     . " t.tagnorm as tagnorm, t.popularity as popularity, "
                     . " meta.tagdisplay as meta "
                     . " FROM tag t "
                     . " JOIN tagevent te ON t.id = te.tag_id "
                     . " JOIN metatag meta ON te.meta_id = meta.id "
                     . " JOIN resource r ON r.id = te.resource_id "
                     . " WHERE "),
               
               array('type' => 'isnum',
                     'sql' => ' (r.id = <<<x>>>)'),
               array('type' => 'notnum',
                     'sql' => " (r.uri_normal = url_whack('<<<x>>>'))"),

               array('type' => 'metaonly',
                     'sql'=> ' AND (te.meta_id <> 1) '),

               array('type' => 'limit',
                     'sql' => ' LIMIT <<<limit>>> '),

               array('type' => 'common',
                     'sql' =>     " UNION "
                     ." SELECT DISTINCT "
                     ." ean13 AS id, convert(ean13, char) AS tagdisplay, "
                     ." convert(ean13, char) AS tagnorm, 1 AS popularity, 'EAN13' AS meta "
                     ." FROM ean13 "
                     ." WHERE resource_id = "),

               array('type' => 'isnum',
                     'sql' => '<<<x>>>'),

               array('type' => 'notnum',
                     'sql' => 
                     "(SELECT id FROM resource ".
                     "WHERE uri_normal = url_whack('<<<x>>>'))"),

               array('type' => 'common',
                     'sql' =>
                     'ORDER BY popularity DESC '));

    return $this->qb->build($q, 
                            $res, 
                            array('limit' => array('func' => '',
                                                   'value' => $limit),
                                  'metaonly' => array('func' => '',
                                                      'value' => $metaonly)));


  }
  

  }