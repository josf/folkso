<?php

require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoDBconnect.php');


class testOffolksoDBConnect extends  UnitTestCase {

  function testConnection () {
    $con = new folksoDBconnect('localhost','root','hellyes','folksonomie');
    $this->assertTrue($con instanceof folksoDBconnect);
    $this->assertTrue($con->db_obj() instanceof mysqli);

    $con2 = new folksoDBconnect('localhost', 'tester_dude',
                                'testy', 'testostonomie');

    $this->assertIsA($con2, folksoDBconnect,
                     'problem with object creation');
    $this->assertIsA($con2->db_obj(),
                     mysqli,
                     'Not creating mysqli object with db_obj()');

  }

}



$test = &new testOffolksoDBConnect();
$test->run(new HtmlReporter());
