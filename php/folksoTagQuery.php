<?php 
/**
 * @package Folkso
 * @subpackage Tagserv
 * @author Joseph Fahey
 * @copyright 2009 Gnu Public Licence (GPL)
 */
require_once('folksoQueryBuild.php');

class folksoTagQuery {
  public $qb;
  public function __construct() {
    $this->qb = new folksoQueryBuild();
  }

  /**
   * @param $tag
   */
   public function related_tags ($tag) {
   
     $a = array(
                array(
                      'type' => 'common',
                      'sql' =>
                      'select tag as tagid, t.tagnorm as tagnorm, t.tagdisplay as display, cnt as popularity '
                      .' from '
                      .' (select te.tag_id as tag, count(te.resource_id) as cnt '
                      .' from '
                      .' tagevent te '
                      .' where te.resource_id in '
                      .' (select te2.resource_id from tagevent te2 '
                      .' from '
                      .' tagevent te2 '),
                array(
                      'type' => 'isnum',
                      'sql' => 
                      ' where '
                      .' te2.tag_id = <<<x>>> '),
                array(
                      'type' => 'notnum',
                      'sql' =>
                      ' join tag tt on tt.id = te2.tag_id '
                      ." where tt.tagnorm = normalize_tag('<<<x>>>')  "),
                array(
                      'type' => 'common',
                      'sql' => 
                      ' ) group by te.tag_id ) as work '
                      . ' join tag t on t.id = work.tag '
                      . ' where cnt > 1 '
                      )
                );
     return $this->qb->build($a, $tag, array());
   }
  

}