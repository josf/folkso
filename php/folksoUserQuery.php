<?php
 /**
   *
   * @package Folkso
   * @subpackage Tagserv
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   */
require_once('folksoQueryBuild.php');
  /**
   * Queries for use with user.php (or other user oriented libs)
   *
   * These queries are written in something like a little language.
   *
   * These are all static methods. They just return text.
   */
class folksoUserQuery  {

  public $qb;
  public function __construct() {
    $this->qb = new folksoQueryBuild();
  }

  /**
   * @param $tag
   */
  public function resourcesByTag ($tag, $userid) {
     $a = array(
                array('type' => 'common',
                      'sql' =>
                      " select r.id, r.uri_raw, r.title, te.tagtime "
                      ." from resource r \n"
                      ." join tagevent te on r.id = te.resource_id \n"),
                array('type' => 'notnum',
                      'sql' => " join tag t on te.tag_id = t.id "),
                array('type' => 'common',
                      'sql' => 
                      " where \n"
                      ." (te.userid = '<<<uid>>>') \n"
                      ." and "),
                array('type' => 'isnum',
                      'sql' => '(te.tag_id = <<<x>>>)'),
                array('type' => 'notnum',
                      'sql' => "(t.tagnorm = normalize_tag('<<<x>>>'))"),
                array('type' => 'common',
                      'sql' => "order by te.tagtime"));

                return $this->qb->build($a,
                                        $tag,
                                        array('uid' => array('value' => $userid)));
  } 


  /**
   * @param $tag, $userid
   */
   public function addSubscriptionSQL ($tag, $userid) {
     $a = array(
                array('type' => 'common',
                      'sql' =>
                      'insert into user_subscription '
                      .' (userid, tag_id) ' 
                      .' values '
                      ." ('<<<uid>>>', "),
                array('type' => 'isnum',
                      'sql' => '<<<x>>>'),
                array('type' => 'notnum',
                      'sql' => 
                      " (select id from tag where tagnorm = normalize_tag('<<<x>>>')))")
                );

     return $this->qb->build($a,
                             $tag,
                             array('uid' => array('value' => $userid)));
   }
  

   public function singleTagRepresentation($tag) {
     $a = array(
                array('type' => 'common',
                      'sql' =>
                      'select id, tagnorm, tagdisplay from tag '
                      . ' where '
                      .' id = '),
                array('type' => 'isnum',
                      'sql' => '<<<x>>>'),
                array('type' => 'notnum',
                      'sql' =>
                      ' (select id from tag where tagnorm ='
                      ." normalize_tag('<<<x>>>')) "));
     return $this->qb->build($a,
                             $tag,
                             array());


   }

}
  