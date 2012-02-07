

<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoAuth.php');
require('dbinit.inc');

class testOffolksoAuth extends  UnitTestCase {
  
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
    $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                     'testy', 'testostonomie');
  }
  
  function testAuth () {
    $fa   = new folksoAuth();
    $this->assertIsA($fa, folksoAuth, 'object creation failed');
  }
}//end class

$test = &new testOffolksoAuth();
$test->run(new HtmlReporter());