<?php
require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoResponse.php');

class testOffolksoResponse extends  UnitTestCase {

  public $rep;


    function testConstructor () {

    $test_f = create_function('', 'return true;');
    $act_f = create_function('', 'print "<p>Action!</p>";');
    $this->rep = new folksoResponse($test_f, $act_f);
    $this->assertTrue(($this->rep instanceof folksoResponse));


    }


  function message($message) {

  }

}//end class
