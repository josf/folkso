<?php
require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoResponse.php');

class testOffolksoResponse extends  UnitTestCase {

  public $rep;


    function testConstructor () {
      
      print "<p>METH: " . $_SERVER['REQUEST_METHOD'] . "</p>";

      $test_f = create_function('', 'return true;');
      $act_f = create_function('', 'print "<p>Action!</p>"; return "ok";');
      $this->rep = new folksoResponse($test_f, $act_f);
      $this->assertTrue(($this->rep instanceof folksoResponse));
      $this->assertTrue($this->rep->activatep()); 
      $this->assertEqual($this->rep->Respond(), "ok");

    }


  function message($message) {

  }

}//end class
