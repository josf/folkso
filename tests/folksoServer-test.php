<?php

require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoServer.php');


class testOffolksoServer extends  UnitTestCase {

  public $srv;

  function testConstructor () {
    $this->srv = new folksoServer(array('GET', 'POST'), 'LOCAL', '', '');      // send error message;
    $this->assertTrue( ($this->srv instanceof folksoServer));
  }

  function testValidClientAddress () {
    $q = new folksoQuery(array(), array(), array());
    $this->assertTrue( $this->srv->validClientAddress('localhost', '127.0.0.1'));
    $this->assertTrue( $this->srv->validClientAddress('', '127.0.0.1'));
    $this->assertTrue( $this->srv->validClientAddress('localhost', '::1'));
    $this->assertTrue( $this->srv->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']));
    $this->assertFalse($this->srv->is_auth_necessary($q));
  }


  function testSimpleResponses () {
      $test_f = create_function('', 'return true;');
      $act_f = create_function('', 'print "<p>Action!</p>"; return "ok";');
      $this->srv->addResponseObj(new folksoResponse('GET', 
                                                    array('required' => array()), 
                                                    $act_f));
      $this->assertTrue( is_array($this->srv->responseObjects));
      $this->assertEqual( count( $this->srv->responseObjects), 1);
      $this->srv->Respond();
  }

}//end class
  $test = new testOffolksoServer();
  $test->run(new HtmlReporter());