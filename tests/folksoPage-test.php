<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoPage.php');

class testOffolksoPage extends  UnitTestCase {

  function testBasicTests () {
    $page = new folksoPage();
    $this->assertTrue($page instanceof folksoPage);
    $this->assertPattern('/folksoPage-test.php/', $page->url);
    $this->assertFalse($page->keyword_list());

  }


  function testKeyword_list () {
    $page = new folksoPage(26663);
    $this->assertIsA($page, folksoPage);
    $this->assertEqual($page->url, 26663);
    $this->assertTrue(is_string($page->keyword_list()));
    $this->assertTrue(is_string($page->basic_cloud()));
  }

  function testMetas () {
    $page = new folksoPage(26663);
    $this->assertIsA($page->pdata->resourceMetas(), folksoPageDataMeta);
    $this->assertTrue((is_string($page->pdata->mt->meta_keywords()) &&
                       (strlen($page->pdata->mt->meta_keywords()) > 10)));
    $this->assertPattern('/<meta/', $page->pdata->mt->meta_keywords());
    $this->assertTrue((is_string($page->pdata->mt->meta_textlist()) &&
                       (strlen($page->pdata->mt->meta_textlist()) > 10)));
    $this->assertTrue(is_string($page->DC_description_list()));
  }
}//end class


$test = &new testOffolksoPage();
$test->run(new HtmlReporter());