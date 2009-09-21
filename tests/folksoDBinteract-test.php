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

  public function testMultiQuery () {
    $db = new folksoDBinteract($this->dbc);
    /** actually a test of DBconnect...**/
    $this->assertIsA($this->dbc->db_obj(), mysqli, 
                     "Created object is not a mysql object");

    $db->sp_query('select uri_normal as uri from resource where id = 1;'
               .'select uri_raw as raw from resource where id = 1;'
               );


    $this->assertNotEqual($db->result_status,
                          'DBERR',
                          'DBERR status returned. This is wrong.');

    $this->assertEqual($db->result_status, 
                       'OK', 
                       'Result is not ok on resouce id = 1');

    $this->assertNotNull($db->result, 'Null result from query');
    $this->assertIsA($db->result, mysqli_result, 
                     'Not returning a mysqli result object');
    $this->assertTrue(count($db->result_array) == 1,
                      'Incorrect number of elements in $i->result_array');

    $this->assertEqual($db->result_array[0]->uri, 'example.com/1');


    $this->assertEqual(count($db->additional_results), 
                       1,
                       'Should have one additional result set');
    $this->assertIsA($db->additional_results[0], 
                     mysqli_result,
                     '2nd result set is not a mysql_result object');

  }

  public function testLiveQueries () {
    $i = new folksoDBinteract($this->dbc);
    $this->assertIsA($i, folksoDBinteract, 'Problem with object creation');
    $this->assertFalse($i->db_error(),
                       'Database connection error'
                       );

    $sql = "select uri_normal as uri from resource where id = 1";
    $i->query($sql);

    $this->assertIsA($i->result, 
                     mysqli_result, 
                     '$i->result is not a mysqli_result object'
                     );

    $res = $i->result->fetch_object();
    $this->assertEqual($res->uri,
                       'example.com/1',
                       'Incorrect data from simple query: "' . $res->uri . '"'
                       );

    $ii = new folksoDBinteract($this->dbc);
    $ii->query("select uri_normal as uri from resource where id = 9999999");
    $this->assertEqual($ii->result_status, 
                       'NOROWS',
                       'Not reporting NOROWS for dataless query'
                       );

    $iii = new folksoDBinteract($this->dbc);
    $this->assertTrue($iii->resourcep('http://example.com/1'),
                      'Not reporting existence of resource');

  }
}



$test = &new testOffolksoDBinteract();
$test->run(new HtmlReporter());