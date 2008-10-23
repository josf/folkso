<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoPage.php');

class testOffolksoPage extends  UnitTestCase {

  function testResourceMetas () {
    
    $page = new folksoPage();
    $this->assertTrue($page instanceof folksoPage);

    $r = $page->resourceMetas(7525);
    $this->assertEqual($r['status'], 200);
    $this->assertTrue(is_string($r['result']));
  }
}//end class


$test = &new testOffolksoPage();
$test->run(new HtmlReporter());