<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');


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
