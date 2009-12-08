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



   }

   function testWithDB (){
     $u = new folksoUser($this->dbc);
     $u->loadUser(array( 'nick' => 'marcelp',
                           'firstname' => 'Marcel',
                           'lastname' => 'Proust',
                           'email' => 'marcep@temps.eu',
                           'userid' => 'marcelp-2009-001'));
     $this->assertIsA($u, folksoUser,
                      'problem with object creation');
     $this->assertEqual($u->nick, 'marcelp',
                        'missing data in user object');
     $this->assertEqual($u->uid, 'marcelp-2009-001',
                        'userid not present');
     $this->assertTrue($u->checkUserRight('tag'),
                       'user right fails incorrectly');
     $this->assertFalse($u->checkUserRight('ploop'),
                        'inexistant right should not validate');
   }
}//end class

$test = &new testOffolksoUser();
$test->run(new HtmlReporter());