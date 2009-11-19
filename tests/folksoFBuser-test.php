<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoFBuser.php');
require('dbinit.inc');

class testOffolksoFBuser extends  UnitTestCase {
  public $dbc;

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }


   function testFBuser () {
         $fb   = new folksoFBuser($this->dbc);
         $this->assertIsA($fb, folksoFBuser,
                               'object creation failed');

         $this->assertIsA($fb, folksoUser,
                          'object hierarchy problem');
         $this->assertFalse($fb->exists('999999'),
                            'bad fb id should return false');
         

   }
}//end class

$test = &new testOffolksoFBuser();
$test->run(new HtmlReporter());