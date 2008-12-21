<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoQueryBuild.php');

class testOffolksoQueryBuild extends UnitTestCase {

  function testBasic () {
    $qq = new folksoQueryBuild();
    $this->assertIsA($qq, folksoQueryBuild);
  }

  function testBuild () {
    $qq = new folksoQueryBuild();
    $bu = $qq->build(array(
                           array('type' => 'common',
                                 'sql' => 'select something')),
                     14, array());
    $this->assertEqual($bu, 'select something');

    $numar = array(
                   array('type' => 'common',
                         'sql' => 'select something from elsewhere where'),
                   array('type' => 'isnum',
                         'sql' => "number is number"));

      $this->assertEqual($qq->build($numar, 14, array()),
                       'select something from elsewhere where number is number');
      $this->assertNotEqual($qq->build($numar, "bob", array()),
                       'select something from elsewhere where number is number');

  }

  function testValRepl () {
    $qq = new folksoQueryBuild();
    $this->assertEqual($qq->valRepl("abc<<<x>>>xyz", "bob", array()),
                       'abcbobxyz');
    $this->assertEqual($qq->valRepl("abc<<<bob>>>xyz", 
                                    "stuff", 
                                    array('bob' => array('func' => 'stuff',
                                                         'value' => 'hector'))),
                       'abchectorxyz');

  }




}


$test = &new testOffolksoQueryBuild();
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