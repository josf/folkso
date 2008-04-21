<?php
require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoResponse.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoQuery.php');


class testOffolksoResponse extends  UnitTestCase {

  public $rep;
  public $query;

    function testConstructor () {
      
      $this->query = new folksoQuery(array('REQUEST_METHOD' => 'GET',
                                           'REMOTE_ADDR' => '127.0.0.1',
                                           'REMOTE_HOST' => 'localhost'),
                                     array(),
                                     array());


      $test_f = create_function('', 'return true;');
      $act_f = create_function('', 'print "<p>Action!</p>"; return "ok";');
      $this->rep = new folksoResponse($test_f, $act_f);
      $this->assertTrue(($this->rep instanceof folksoResponse));
      $this->assertTrue($this->rep->activatep($this->query)); 
      $this->assertEqual($this->rep->Respond($this->query), "ok");

    }


  function message($message) {

  }

}//end class
