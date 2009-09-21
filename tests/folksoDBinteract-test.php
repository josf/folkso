<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoDBinteract.php');
require_once('folksoDBconnect.php');
require('dbinit.inc');

class testOffolksoDBinteract extends  UnitTestCase {
  public $connection;
  public $i;
  public $dbc;
    
  public function setUp () {
    test_db_init();
    $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
  }

  
  public function testDBobjCreation () {
    $this->connection = new folksoDBconnect('localhost','root','hellyes','folksonomie');
    $this->i = new folksoDBinteract($this->connection);

    $this->assertIsA($this->connection, folksoDBconnect, 
                     "DBconnect object creation failed. Expect other problems. [%s]");
    $this->assertIsA($this->i, folksoDBinteract, "Object creation failed: [%s]");
  }

  public function testDBConnectError () {
    $baddb = new folksoDBconnect('localhost', 'bob', '123456', 'nonexistantdb');
  }

  public function testUrl_from_id () {
    $this->assertEqual($this->i->url_from_id(3), 
                       "http://www.fabula.org/actualites/article24402.php");
    $this->assertEqual($this->i->url_from_id(2),
                       0);
  }

  public function testLiveData () {
    $db = new folksoDBinteract($this->dbc);
    /** actually a test of DBconnect...**/
    $this->assertIsA($this->dbc->db_obj(), mysqli, 
                     "Created object is not a mysql object");

    $db->query('select uri_normal as uri from resource where id = 1;'
               .'select uri_raw as raw from resource where id = 1;'
               );


    $this->assertNotEqual($db->result_status,
                          'DBERR',
                          'DBERR status returned. This is wrong.');

    $this->assertEqual($db->result_status, 
                       'OK', 
                       'Result is not ok on resouce id = 1');

    $this->assertNotNull($db->result, 'Null result from query');
    $this->assertIsA($res, mysqli_result, 
                     'Not returning a mysqli result object');



    $this->assertEqual($res->uri, 
                       'example.com/1', 
                       'Incorrect result on resource id = 1');

    $this->assertEqual(count($db->additional_results), 
                       1,
                       'Should have one additional result set');
    $this->assertIsA($db->additional_results[0], 
                     mysqli_result,
                     '2nd result set is not a mysql_result object');

  }
}



$test = &new testOffolksoDBinteract();
$test->run(new HtmlReporter());