<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoResponse.php');
require_once('folksoResponder.php');
require_once('dbinit.inc');



function nothingFunc ($a, $b, $c) {
  return new folksoResponse();
}

class testOffolksoResponder extends  UnitTestCase {

  public $rep;
  public $query;
  public $dbc; public $dbc2; public $dbc3;
  public $fks; public $fks2; public $fks3; public $fks4;

  public function setUp () {
    test_db_init();
    $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
    $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
    $this->dbc3 = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
    $this->fks = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
    $this->fks2 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
    $this->fks3 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
    $this->fks4 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
  }


  function testConstructor () {
      
      $query = new folksoQuery(array('REQUEST_METHOD' => 'GET',
                                     'REMOTE_ADDR' => '127.0.0.1',
                                     'REMOTE_HOST' => 'localhost'),
                                     array('folksouri' => 'example.com'),
                                     array());

      $rep = new folksoResponder('get', 
                                 array('required' => array('uri')),
                                 'nothingFunc');
      $this->assertIsA(nothingFunc($query, $this->dbc2, $this->fks2), 
                       folksoResponse,
                       'Testing the test: nothingFunc should return empty fkResponse');
      
      $this->assertIsA($rep, folksoResponder);
      $this->assertTrue($rep->activatep($query)); 
     }


  function testContentTypeFilter () {
    
    $rsp = new folksoResponder('get',
                               array('required' => array('hoo'),
                                     'accept' => array('xml')),
                               'nothingFunc');

    $this->assertTrue(is_array($rsp->activate_params['accept']),
                      'activate_params should be an array');
    $this->assertTrue(in_array('xml', $rsp->activate_params['accept']),
                      '"xml" not present in activate_params');
    $this->assertFalse(! in_array('xml', $rsp->activate_params['accept']),
                      '"xml" still not present');


    $q = new folksoQuery(array('REQUEST_METHOD' => 'get'),
                         array( 'folksohoo' => '1'),
                         array());


    $this->assertFalse($rsp->activatep($q),
                       'Should not activate without datatype xml here');


    $q2 = new folksoQuery(array('REQUEST_METHOD' => 'get'),
                          array('folksohoo' => '1',
                                'folksodatatype' => 'xml'),
                          array());
    $this->assertEqual($q2->content_type(), 'xml',
                       'Testing the test: incorrect content_type() in $q: ' 
                       . $q->content_type());

    $this->assertTrue($rsp->activatep($q2),
                      'should activate here with xml datatype');

  }
}//end class


$test = &new testOffolksoResponder();
$test->run(new HtmlReporter());