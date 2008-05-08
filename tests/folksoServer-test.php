<?php
  require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
  require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
  //require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');


class testOffolksoServer extends  UnitTestCase {

  public $srv;

  function testConstructor () {
    $this->srv = new folksoServer(array('GET', 'POST'), 'LOCAL', '', '');      // send error message;
    //    print var_dump( $this->srv);
    $this->assertTrue( ($this->srv instanceof folksoServer));
  }

  function testValidClientAddress () {
    $this->assertTrue( $this->srv->validClientAddress('localhost', '127.0.0.1'));
    $this->assertTrue( $this->srv->validClientAddress('', '127.0.0.1'));
    $this->assertTrue( $this->srv->validClientAddress('localhost', '::1'));
    $this->assertTrue( $this->srv->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']));
    $this->assertFalse($this->srv->is_auth_necessary());
  }


  function testSimpleResponses () {
      $test_f = create_function('', 'return true;');
      $act_f = create_function('', 'print "<p>Action!</p>"; return "ok";');
      $this->srv->addResponseObj(new folksoResponse($test_f, $act_f));
      $this->assertTrue( is_array($this->srv->responseObjects));
      $this->assertEqual( count( $this->srv->responseObjects), 1);
      $this->srv->Respond();
  }

}//end class
  $test = new testOffolksoServer();
  $test->run(new HtmlReporter());