<?php

require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
require_once('folksoUser.php');
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
     $u = new folksoUser($this->dbc);
     $u->setUid('marcelp-2010-001');
     $this->assertEqual($u->userid, 'marcelp-2010-001',
                        'Bad or missing userid from setUid: ' . $u->userid);

     $u->setEmail('bob@warp.com');
     $this->assertEqual($u->email, 'bob@warp.com',
                        'Bad or missing email from setEmail: ' . $u->email);

   }

   function testWithDB (){
     $u = new folksoUser($this->dbc);
     $u->loadUser(array( 'nick' => 'marcelp',
                         'firstname' => 'Marcel',
                         'lastname' => 'Proust',
                         'email' => 'marcelp@temps.eu',
                         'userid' => 'marcelp-2010-001'));
     $this->assertIsA($u, folksoUser,
                      'problem with object creation');
     $this->assertEqual($u->nick, 'marcelp',
                        'missing data in user object');
     $this->assertEqual($u->email, 'marcelp@temps.eu',
                        'Email incorrect after loadUser');
     $this->assertEqual($u->userid, 'marcelp-2010-001',
                        'userid not present: ' . $u->userid);
     $this->assertTrue($u->checkUserRight('folkso', 'tag'),
                       'user right fails incorrectly');
     $this->assertFalse($u->checkUserRight('ploop', 'dooop'),
                        'inexistant right should not validate');
   }

   function testRights () {
     $u = new folksoUser($this->dbc);
     $this->assertIsA($u->rights, 
                      folksoRightStore,
                      'No fkRightStore at $u->rights');

   }

   function testExists() {
     $u = new folksoUser($this->dbc);
     $this->assertTrue($u->exists('marcelp-2010-001'));
     $this->assertEqual($u->userid, 'marcelp-2010-001');

   }
}//end class

$test = &new testOffolksoUser();
$test->run(new HtmlReporter());