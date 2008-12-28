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
    $page->pdata->prepareMetaData();
    $this->assertIsA($page->pdata->mt, folksoPageDataMeta);
    $this->assertTrue((is_string($page->keyword_list()) &&
                       (strlen($page->keyword_list()) > 10)));
    $this->assertPattern('/<meta/', $page->meta_keywords());
    $this->assertTrue((is_string($page->meta_keywords()) &&
                       (strlen($page->meta_keywords()) > 10)));
    $this->assertTrue(is_string($page->DC_description_list()));
    $this->assertIsA($page->pdata->ptags, folksoTagdata); // inheritance works!
  }

  function testTagRes () {
    $page = new folksoPage(5775);
    $this->assertIsA($page, folksoPage);
    $this->assertEqual($page->url, 5775);
    $html = $page->TagResources();
    $this->assertIsA($page->tr, folksoTagRes);
    $this->assertTrue($page->tr->is_valid());
    $this->assertTrue(strlen($html) > 100);
  }


  function testClouds () {
    $page = new folksoPage('fabula.org/actualites/article20927.php');
    $this->assertIsA($page, folksoPage);
    $cloud = $page->basic_cloud();
    $this->assertTrue(is_string($cloud));
    $this->assertTrue(strlen($cloud) > 200);

    $cl2 = $page->popularity_cloud();
    $this->assertTrue(is_string($cl2));
    $this->assertTrue(strlen($cl2) > 200);
    $this->assertTrue($page->pdata->cloud->is_valid());

    $cl3 = $page->date_cloud();
    $this->assertTrue(is_string($cl3));
    $this->assertTrue(strlen($cl3) > 200);
    $this->assertTrue($page->pdata->cloud->is_valid());
    

  }

  function testEan13 () {
    $page = new folksoPage(4159);
    $this->assertIsA($page, folksoPage);
    $dc = $page->ean13_dc_identifier();
    $this->assertTrue(is_string($dc));
    $this->assertPattern('/<meta\s+/', $dc);
  }

}//end class


$test = new testOffolksoPage();
$test->run(new HtmlReporter());


/**

assertTrue($x) 	Fail if $x is false
assertFalse($x) 	Fail if $x is true
assertNull($x) 	Fail if $x is set
assertNotNull($x) 	Fail if $x not set
assertIsA($x, $t) 	Fail if $x is not the class or type $t
assertNotA($x, $t) 	Fail if $x is of the class or type $t
assertEqual($x, $y) 	Fail if $x == $y is false
assertNotEqual($x, $y) 	Fail if $x == $y is true
assertWithinMargin($x, $y, $m) 	Fail if abs($x - $y) < $m is false
assertOutsideMargin($x, $y, $m) 	Fail if abs($x - $y) < $m is true
assertIdentical($x, $y) 	Fail if $x == $y is false or a type mismatch
assertNotIdentical($x, $y) 	Fail if $x == $y is true and types match
assertReference($x, $y) 	Fail unless $x and $y are the same variable
assertClone($x, $y) 	Fail unless $x and $y are identical copies
assertPattern($p, $x) 	Fail unless the regex $p matches $x
assertNoPattern($p, $x) 	Fail if the regex $p matches $x
expectError($x) 	Swallows any upcoming matching error
assert($e) 	Fail on failed expectation.html object $e

 **/
?>