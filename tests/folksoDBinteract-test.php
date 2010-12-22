<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoDBinteract.php');
require_once('folksoDBconnect.php');
require_once('dbinit.inc');

class testOffolksoDBinteract extends  UnitTestCase {
  public $dbc;
  public $dbc2;
  public $dbc3;
    
  public function setUp () {
    test_db_init();
    $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
    $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
    $this->dbc3 = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
  }

  
  public function testDBobjCreation () {

    $this->assertIsA($this->dbc, 'folksoDBconnect', 
                     'this->dbc is not a fkDBconnect object');
    $this->assertIsA($this->dbc2, 'folksoDBconnect', 
                     'this->dbc2 is not a fkDBconnect object');
    $this->assertIsA($this->dbc3, 'folksoDBconnect', 
                     'this->dbc3 is not a fkDBconnect object');

    $i = new folksoDBinteract($this->dbc);
    $this->assertIsA($i, 'folksoDBinteract', 
                     "Object creation failed:");
    $this->assertIsA($i->db, 'mysqli',
                     '$i->db is not a mysqli object');
  }

  public function testDBConnectError () {
    $baddb = new folksoDBconnect('localhost', 'bob', '123456', 'nonexistantdb');
    $this->expectException('dbConnectionException');
    $i = new folksoDBinteract($baddb); 
    $this->assertTrue($i->errorCode(), "errorCode() not returning anything");
  }

  public function testUrl_from_id () {
    $i = new folksoDBinteract(new folksoDBconnect('localhost', 'tester_dude', 
                                                  'testy', 'testostonomie'));
    $this->assertEqual($i->url_from_id(3), 
                       'http://example.com/3',
                       'url_from_id not retrieving correct url.');
  }

  public function testMultiQuery () {
    $db = new folksoDBinteract($this->dbc);
    /** actually a test of DBconnect...**/
    $this->assertIsA($this->dbc->db_obj(), 'mysqli', 
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
    $this->assertIsA($db->result, 'mysqli_result', 
                     'Not returning a mysqli result object');
    $this->assertTrue(count($db->result_array) == 1,
                      'Incorrect number of elements in $i->result_array');

    $this->assertEqual($db->result_array[0]->uri, 'example.com/1');


    $this->assertEqual(count($db->additional_results), 
                       1,
                       'Should have one additional result set');
    $this->assertIsA($db->additional_results[0], 
                     'mysqli_result',
                     '2nd result set is not a mysql_result object');

  }

  public function testLiveQueries () {
    $i = new folksoDBinteract($this->dbc);
    $this->assertIsA($i, 'folksoDBinteract', 'Problem with object creation');
    $this->assertFalse($i->db_error(),
                       'Database connection error'
                       );

    $sql = "select uri_normal as uri from resource where id = 1";
    $i->query($sql);

    $this->assertIsA($i->result, 
                     'mysqli_result', 
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

  function testExistence () {

    $i = new folksoDBinteract($this->dbc);
    $this->assertTrue($i->resourcep('http://example.com/1'),
                      'Not reporting existence of example.com/1 (resourcep)'); 
    $dbc2 = new folksoDBconnect('localhost','tester_dude',
                               'testy', 'testostonomie');
    $i2 = new folksoDBinteract($dbc2);
    $this->assertTrue($i2->tagp('tagone'),
                      'Not reporting existence of "tagone"');
    $this->assertFalse($i2->db_error(),
                       'tagp() is returning an DB error');
    $this->assertTrue($i2->tagp(1),
                      'numeric tagp not reporting existence of tag 1');
    $this->assertEqual($i2->db->real_escape_string('tagone'),
                       'tagone',
                       'Strangeness using real_escape_string');
    $this->assertFalse($i2->tagp('false tag'),
                       'Reporting existence of non-existant tag');
    $this->assertFalse($i2->tagp(199),
                       'Reporting existence of non-existant tag (numeric)');
                               
  }
}



$test = &new testOffolksoDBinteract();
$test->run(new HtmlReporter());