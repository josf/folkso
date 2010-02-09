<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoTagQuery.php');

class testOffolksoTagQuery extends  UnitTestCase {

  function testBase () {

    $tq = new folksoTagQuery();
    $this->assertPattern('/101/',
                         $tq->related_tags(101));
    $this->assertPattern('/tag <> 101/',
                         $tq->related_tags('101'));
    $this->assertPattern('/bobness/',
                         $tq->related_tags('bobness'));
    $this->assertPattern("/normalize_tag\('bobness'\)/",
                         $tq->related_tags('bobness'));
    $this->assertPattern("/tagnorm <> normalize_tag\('bobness'\)/",
                         $tq->related_tags('bobness'));
   }

  function testUDeleteTag() {
    $tq = new folksoTagQuery();
    $this->assertPattern('/stuff/',
                         $tq->userDeleteTag('stuff', 'gustav-001-2001'));
    $this->assertPattern('/\'gustav/',
                         $tq->userDeleteTag('stuff', 'gustav-001-2001'));
    $this->assertPattern('/1984/',
                         $tq->userDeleteTag(1984, 'gustav-001-2001'));

  }
}//end class

$test = &new testOffolksoTagQuery();
$test->run(new HtmlReporter());