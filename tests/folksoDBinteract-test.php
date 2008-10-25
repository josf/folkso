<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoDBinteract.php');
require_once('folksoDBconnect.php');

class testOffolksoDBinteract extends  UnitTestCase {
  public $connection;
  public $i;
    
  
  public function testDBobjCreation () {
    $this->connection = new folksoDBconnect('localhost','root','hellyes','folksonomie');
    $this->i = new folksoDBinteract($this->connection);

    $this->assertIsA($this->connection, folksoDBconnect, 
                     "DBconnect object creation failed. Expect other problems. [%s]");
    $this->assertIsA($this->i, folksoDBinteract, "Object creation failed: [%s]");
  }

  public function testDBConnectError () {
    $baddb = new folksoDBconnect('localhost', 'bob', '123456', 'nonexistantdb');
  }
}



$test = &new testOffolksoDBinteract();
$test->run(new HtmlReporter());