<?php
  //require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
  //require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoServer.php');

class testOffolksoServer extends  UnitTestCase {

  public $srv;

  function testConstructor () {
    $this->srv = new folksoServer(array( 'methods' => array('get'), 
                                   'access_mode' => 'LOCAL'));
    //    print var_dump( $this->srv);
    $this->assertTrue( ($this->srv instanceof folksoServer));
  }

  function testValidClientAddress () {
    $this->assertTrue( $this->srv->validClientAddress('localhost', '127.0.0.1'));
    $this->assertTrue( $this->srv->validClientAddress('', '127.0.0.1'));
    $this->assertTrue( $this->srv->validClientAddress('localhost', '::1'));
    $this->assertTrue( $this->srv->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']));
    $this->assertEqual(1, 1);
  }


  function message($message) {

  }

}//end class
