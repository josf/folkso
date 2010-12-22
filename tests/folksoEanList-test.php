<?php


require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoEanList.php');
require_once('folksoFabula.php');

class testOffolksoEanList extends UnitTestCase {

  function testBasic () {
    $e = new folksoEanList(new folksoFabula(), 4159);
    $this->assertIsA($e, 'folksoEanList);'
    $this->assertFalse(is_string($e->xml));
    $e->getData();
    $this->assertTrue(is_string($e->xml));
    $this->assertIsA($e->xml_DOM(), 'DOMDocument);'
    
  }

  function testDC_metalist() {
    $e = new folksoEanList(new folksoFabula(), 4159);
    $s = $e->ean13_dc_metalist();
    $this->assertTrue(is_string($s));
    $this->assertPattern('/<meta/', $s);
    $this->assertPattern('/DC\.Identifier/', $s);
    $this->assertPattern('/DC\.Identifier/', $e->dc_identifier_html);

  }

}


$test = &new testOffolksoEanList();
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
