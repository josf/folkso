<?php

require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoUser.php');
include('dbinit.inc');

class testOffolksoUser extends  UnitTestCase {
  public $dbc;


  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testUser () {
         $u   = new folksoUser($this->dbc);
         $this->assertIsA($u, folksoUser,
                               'object creation failed');

         $this->assertFalse($u->writeable,
                            'initial writable state should be false');
         $simple_u = $u->createUser(array('nick' => 'Bobert',
                                          'firstname' => 'Bobness',
                                          'lastname' => 'Justaguy',
                                          'email' => 'sloink@zoink.com',
                                          'oid_url' => 'http://i.am.me'));
         $this->assertTrue($simple_u[0],
                           'Basic user creation fails');
         $this->assertTrue($u->writeable,
                           'Writeable state incorrect: should be writeable now');


   }
}//end class

$test = &new testOffolksoUser();
$test->run(new HtmlReporter());