<?php
require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoQuery.php');

class testOffolksoResponse extends  UnitTestCase {

  public $qu;

  function testConstructor () {
    $this->qu = new folksoQuery();
    $this->assertTrue($this->qu instanceof folksoQuery);
  }

  function testMethod () {
    $this->assertEqual(strtolower($this->qu->method()), 'get');
  }


  function message($message) {

  }

}//end class
