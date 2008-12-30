<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoPage.php');

class testOffolksoPageDataMeta extends  UnitTestCase {


  function testBasic () {
    $m = new folksoPageDataMeta();
    $this->assertIsA($m, folksoPageDataMeta);
    $this->assertTrue(is_array($m->array));
  }


  function testAdding () {
    $m = new folksoPageDataMeta();
    $m->add_principal_keyword("thing");
    $this->assertEqual($m->keywords[0], "thing");
    $m->add_all_tags("thing");
    $this->assertEqual($m->alltags[0], "thing");
    $this->assertPattern('/thing/', $m->meta_textlist());
    $m->add_principal_keyword("other");
    $this->assertEqual($m->keywords[1], "other");
    $this->assertPattern('/other/', $m->meta_textlist());
    $this->assertPattern('/thing/', $m->meta_description_textlist());
    $this->assertPattern('/other/', $m->meta_keywords());
    $this->assertPattern('/<meta/', $m->meta_keywords());
    $this->assertPattern('/,/', $m->meta_textlist());
  }

}//end class


$test = new testOffolksoPageDataMeta();
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