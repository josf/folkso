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
         $this->assertTrue($fb->validateLoginId('abcdef'),
                           'ultra simple FB id should pass.');
         $this->assertTrue($fb->validateLoginId('123456'),
                           '123456 not validating as FB id');
         $this->assertTrue($fb->exists('123456'),
                           'rambo-2009-001 at 123456 not showing up');
         $this->assertTrue($fb->userFromLogin('123456'),
                           'userFromLogin returns false');
         $this->assertEqual($fb->nick, 'rambo',
                            'Incorrect nick');

   }
}//end class

$test = &new testOffolksoFBuser();
$test->run(new HtmlReporter());