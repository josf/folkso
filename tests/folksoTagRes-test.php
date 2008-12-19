<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoTagRes.php');
require_once('folksoTagdata.php');
require_once('folksoFabula.php');

class testOffolksoTagRes extends  UnitTestCase {
  public $loc;

  function testObjectCreation () {
    $this->loc = new folksoFabula();

    $tr = new folksoTagRes($this->loc, 5775);
    $this->assertIsA($tr, folksoTagRes);
    $this->assertEqual($tr->url, 5775);
    $this->assertNull($tr->html);
    $this->assertNull($tr->xml);
  }

  function testWithFakeData() {
    $fake = new folksoTagRes($this->loc, "bob");
    $fake_xml = "<xml><inner>Stuff</inner><inner>More stuff</inner></xml";
    $fake->store_new_xml($fake_xml, 200);
    $this->assertEqual($fake->xml, $fake_xml);
    $this->assertEqual($fake->status, 200);
    $this->assertTrue($fake->is_valid());

  }


  function testWithData () {
    $tr = new folksoTagRes($this->loc, 5775);
    $tr->getData();
    $this->assertTrue($tr->is_valid());
    $this->assertNotNull($tr->status);
    $this->assertNotNull($tr->xml);
    $this->assertTrue(is_string($tr->xml));
    $this->assertTrue(strlen($tr->xml) > 200);
    $this->assertIsA($tr->xml_DOM(), DOMDocument);
  }

  function testReslist () {
    $tr = new folksoTagRes($this->loc, 5775);
    $this->assertNull($tr->html);

    $tr->resList();

    /**  making sure that this all works when called from resList() **/
    $this->assertTrue($tr->is_valid());
    $this->assertTrue(strlen($tr->xml) > 200);
    $this->assertIsA($tr->xml_DOM(), DOMDocument);

    $this->assertTrue(is_string($tr->html));
    $this->assertTrue(strlen($tr->html) > 300);

  }

  function testTitle () {
    $tr = new folksoTagRes($this->loc, 5775);
    $this->assertTrue(is_string($tr->title()));
    $this->assertTrue(strlen($tr->title()) > 5);
  }
}


$test = &new testOffolksoTagRes();
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