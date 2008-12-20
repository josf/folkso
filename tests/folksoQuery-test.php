<?php

require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoQuery.php');

class testOffolksoQuery extends  UnitTestCase {

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
    $this->assertTrue($this->qu2->is_param('folksoCommand'));
    $this->assertEqual($this->qu2->get_param('folksoCommand'), 'obey');
    $this->assertEqual($this->qu2->get_param('Command'), 'obey');
    $this->assertTrue($this->qu2->is_single_param('folksoCommand'));
  }

  function testCheckingBadData () { 
    $this->qubad = new folksoQuery( $_SERVER,
                                    array('folksoCommand' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                                          'varboDeal'    => 'Hey guys'),
                                    array());

    $this->assertTrue(is_string($this->qubad->get_param('folksoCommand')));
    $this->assertTrue(strlen($this->qubad->get_param('folksoCommand') < 301));
    $this->assertFalse($this->qubad->is_param('varboDeal'));

  }

  function testMultiPartField () {
    $this->qumulti = new folksoQuery( $_SERVER,
                                      array('folksoArgs001' => 'this',
                                            'folksoArgs002' => 'that',
                                            'folksoArgs003' => 'somethingelse'),
                                      array());

    $this->assertTrue($this->qumulti->is_multiple_param('folksoArgs'));
    $this->assertFalse($this->qumulti->is_single_param('folksoArgs'));

  }

  function testDataFuncs () {
    $data = new folksoQuery( $_SERVER,
                             array('folksoNumber' => '12343',
                                   'folksoNotNumber' => '123edf3',
                                   'folksoNothing' => ''),
                             array());
    $this->assertTrue($data->is_number('Number'));
    $this->assertFalse($data->is_number('NotNumber'));
    $this->assertFalse($data->is_number('Nothing'));

  }


  function message($message) {

  }

}//end class
$test = new testOffolksoQuery();
$test->run(new HtmlReporter());