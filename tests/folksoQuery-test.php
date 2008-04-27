<?php
require_once('/usr/local/www/apache22/lib/simpletest/autorun.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoQuery.php');

class testOffolksoResponse extends  UnitTestCase {

  public $qu;
  public $qu2;
  public $qubad;
  public $qumulti;

  function testConstructor () {

    $this->qu = new folksoQuery($_SERVER, $_GET, $_POST);
    $this->assertTrue($this->qu instanceof folksoQuery);
  }

  function testMethod () {
    $this->assertEqual(strtolower($this->qu->method()), 'get');
  }

  function testGetStuff () {
    $this->qu2 = new folksoQuery( $_SERVER, array('folksoCommand' => 'obey'), $_POST);
    $this->assertTrue($this->qu2 instanceof folksoQuery);
    $this->assertTrue(key_exists('folksoCommand', $this->qu2->params() ));
  }

  function testCheckingBadData () { 
    $this->qubad = new folksoQuery( $_SERVER,
                                    array('folksoCommand' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                                          'varboDeal'    => 'Hey guys'),
                                    array());
    $params = $this->qubad->params();
    $this->assertTrue(is_string($params['folksoCommand']));
    $this->assertTrue(strlen($params['folksoCommand'] < 301));
    $this->assertFalse(key_exists('varboDeal', $params));

  }

  function testMultiPartField () {
    $this->qumulti = new folksoQuery( $_SERVER,
                                      array('folksoArgs' => 'this+that+somethingelse'),
                                      array());
    $params = $this->qumulti->params();
    print var_dump($params);
    $this->assertTrue(is_array($params['folksoArgs']));

  }

  function message($message) {

  }

}//end class
