<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');


class testOffolksoResponder extends  UnitTestCase {

  public $rep;
  public $query;
  public $dbc;
  public $wsse;

    function testConstructor () {
      
      $this->query = new folksoQuery(array('REQUEST_METHOD' => 'GET',
                                           'REMOTE_ADDR' => '127.0.0.1',
                                           'REMOTE_HOST' => 'localhost'),
                                     array('folksouri' => 'example.com'),
                                     array());


      $this->dbc = new folksoDBconnect('yo', 'yo', 'yo', 'yo');
      $this->wsse = new folksoWsseCreds('yo');

      $test_f = create_function('', 'return true;');
      $act_f = create_function('', 'print "<p>Action!</p>"; return "ok";');
      $this->rep = new folksoResponder('get', 
                                      array('required' => array('uri')),
                                      $act_f);
      
      $this->assertTrue(($this->rep instanceof folksoResponder));
      $this->assertTrue($this->rep->activatep($this->query, $this->wsse)); 
      $this->assertEqual($this->rep->Respond($this->query, $this->wsse, $this->dbc), "ok");

    }
}//end class


$test = &new testOffolksoResponder();
$test->run(new HtmlReporter());