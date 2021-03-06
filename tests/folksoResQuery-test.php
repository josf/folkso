<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('dbinit.inc');
require_once('folksoResQuery.php');

class testOffolksoResQuery extends UnitTestCase {


  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
    
  }


  function testBasic ()  {
    $rq = new folksoResQuery();
    $this->assertIsA($rq, folksoResQuery);
    $this->assertIsA($rq->qb, folksoQueryBuild);
    $cl_pop = $rq->cloud_by_popularity(4354, 3);
    $this->assertTrue(is_string($cl_pop));
    $this->assertTrue(strlen($cl_pop) > 300);

    $getag = $rq->getTags(1233, 0, false);
    $this->assertTrue(strlen($getag) > 100);
    $this->assertPattern('/popularity/', $getag);

    $getag2 = $rq->getTags(1233, 10, true);
    $this->assertPattern('/limit\s+10/i', $getag2);
    $this->assertPattern('/te\.meta_id\s+<>\s+1/', $getag2);

  }

  function testCloudbyDate() {
    $rq = new folksoResQuery();
    $sql = $rq->cloud_by_date(4354);
    $this->assertTrue(is_string($sql));
    $this->assertNoPattern('/DESC\sLIMIT/', $sql);
    $this->assertPattern('/4354/', $sql);
    $this->assertNoPattern('/<<</', $sql);
    $sql2 = $rq->cloud_by_date(4354, 5);
    $this->assertPattern('/DESC\sLIMIT/', $sql2);
  }

  function testResEans() {
    $rq = new folksoResQuery();
    $sql = $rq->resEans(102);
    $this->assertTrue(is_string($sql));
    $this->assertNoPattern('/<<</', $sql);
    $this->assertPattern('/=\s+r\.id/', $sql);
    $this->assertPattern('/UNION/',$sql);
    $this->assertPattern('/=\s+r\.id/', $sql);
    $sql2 = $rq->resEans('http://www.fabula.org/actualites/article22843.php');
    $this->assertTrue(is_string($sql2));
    $this->assertNoPattern('/<<</', $sql2);
    $this->assertPattern('/url_whack/', $sql2);

    $this->assertNoPattern('/resource_id\s+=\s+1233/', 
                            $sql2, 
                            'No EAN without option');

    $sql3 = $rq->resEans('http://www.fabula.org/actualites/article22843.php',
                         0,
                         false,
                         true);
  }

  function testBasic_Cloud() {
    $rq = new folksoResQuery();
    $sql = $rq->basic_cloud(4354);

    $this->assertTrue(is_string($sql));
    $this->assertTrue(strlen($sql) > 100);

    $this->assertPattern('/AS\s+metav/', $sql);
    print $sql;
  }

  function testGetTags () {
    $rq = new folksoResQuery();
    $sql = $rq->getTags('http://www.example.com');
    $this->assertTrue(is_string($sql),
                      'No string returned');
    $this->assertTrue(strlen($sql) > 100,
                      'sql output is not long enough');
    $this->assertPattern('/example/',
                         $sql,
                         'Not finding original url in sql');
    $sql_ean = $rq->getTags('http://www.example.com',
                            0, false, true);
    $this->assertPattern('/ean13/',
                         $sql_ean,
                         'Not finding ean13 in sql ' . $sql_ean);
    print '==================' . $sql_ean;
  }
}




$test = &new testOffolksoResQuery();
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

