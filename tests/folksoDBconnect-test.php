<?php

require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoDBconnect.php');


class testOffolksoDBConnect extends  UnitTestCase {
  public $con;  

  function testConnection () {
    $this->con = new folksoDBconnect('localhost','root','hellyes','folksonomie');
    $this->assertTrue($this->con instanceof folksoDBconnect);
    $this->assertTrue($this->con->db_obj() instanceof mysqli);

  }

}



$test = &new testOffolksoDBConnect();
$test->run(new HtmlReporter());
