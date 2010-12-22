<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoQueryBuild.php');

class testOffolksoQueryBuild extends UnitTestCase {

  function testBasic () {
    $qq = new folksoQueryBuild();
    $this->assertIsA($qq, 'folksoQueryBuild);'
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
  
  function testBuildForReal() {
    $qq = new folksoQueryBuild();
    $this->assertEqual(
                       $qq->build(
                            array(
                              array('type' => 'common',
                                    'sql' => "select b from something where d = <<<x>>>")),
                            5,
                            array()),
                       'select b from something where d = 5');
    $numnotnum =array(
                      array('type' => 'common',
                            'sql' => 'select b from x'),
                      array('type' => 'isnum',
                            'sql' => 'where d = <<<x>>>'),
                      array('type' => 'notnum',
                            'sql' => 'where v = <<<x>>>'));

    $this->assertEqual(
                       $qq->build($numnotnum, 5, array()),
                       'select b from x where d = 5');
    $this->assertEqual(
                       $qq->build($numnotnum, "bob", array()),
                       'select b from x where v = bob');

    $numstuff =array(
                     array('type' => 'common',
                           'sql' => 'really'),
                      array('type' => 'isnum',
                            'sql' => 'select b from x where d = <<<stuff>>>'),
                      array('type' => 'notnum',
                            'sql' => 'select b from x where v = <<<stuff>>>'));
    $this->assertEqual(
                       $qq->build($numstuff, 5, array('stuff' => array('value' => 66,
                                                                       'func' => ''))),
                       'really select b from x where d = 66');
    $stupidfunk = create_function('$int', 'if ($int < 0) { return true;}');
    $this->assertEqual(
                       $qq->build($numstuff, 5, 
                                  array('stuff' => array('value' => -12,
                                                         'func' => $stupidfunk))),
                       'really select b from x where d = -12');
  }

    function testMultipleConds () {

      /** AND **/
      $subeval = array(
                     array('type' => 'common',
                           'sql' => 'some common stuff'),
                     array('type' => array('AND', 'notnum', 'bob'),
                           'sql' => 'And <<<x>>> hates it.'));
      $qq = new folksoQueryBuild();
      $rq = $qq->build($subeval, 'Robert', array('bob' => array('func' => '',
                                                                'value' => 1)));
      $this->assertPattern('/Robert/', $rq);
      $this->assertPattern('/hates/', $rq);

      /** OR **/
      $subeval2 = array(array('type' => 'common',
                              'sql' => 'some common stuff'),
                        array('type' => array('OR', 'isnum', 'bob'),
                              'sql' => 'And <<<x>>> hates it'));
      $rq2 = $qq->build($subeval, 'Robert', array('bob' => array('func' => '',
                                                                 'value' => '3')));
      $this->assertPattern('/hates/', $rq2);
      /** failing AND  **/
      $subeval3 = array(array('type' => 'common',
                              'sql' => 'some common stuff'),
                        array('type' => array('AND', 'isnum', 'bob'),
                              'sql' => 'And <<<x>>> hates it'));
      $rq3 = $qq->build($subeval, 123, array('bob' => array('func' => '',
                                                                 'value' => '')));
      $this->assertNoPattern('/hates/', $rq3);

      /** failing OR  **/
      $subeval = array(array('type' => 'common',
                              'sql' => 'some common stuff'),
                        array('type' => array('OR', 'isnum', 'bob'),
                              'sql' => 'And <<<x>>> hates it'));
      $rq3 = $qq->build($subeval, 'Robert', array('bob' => array('func' => '',
                                                                 'value' => '')));
      $this->assertNoPattern('/hates/', $rq3);


  }
}


$test = new testOffolksoQueryBuild();
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