<?php

require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoUser.php');
require_once('dbinit.inc');

class testOffolksoUser extends  UnitTestCase {
  public $dbc;


  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }


  function testValidation() {
    $u = new folksoUser($this->dbc);
    $this->assertTrue($u->validUrlbase('marcelp'), "Basic urlbase should validate");
    $this->assertTrue($u->validUrlbase('marcelp123'), 
                      "Alphanumeric urlbase should validate");
    $this->assertTrue($u->validUrlbase('marcel.proust'),
                      "Period in urlbase is allowed");
    $this->assertFalse($u->validUrlbase(''),
                       "Empty string is not a valid urlbase");
    $this->assertFalse($u->validUrlbase('marcel!'),
                       "Non alphanumeric should be refused");
    $this->assertFalse($u->validUrlbase('abc'),
                       "Three letter urlbase should be rejected");
    $this->assertFalse($u->validUrlbase('Marcel.Proust'),
                       "Capital letters should be rejected");
    $this->assertFalse($u->validUrlbase('abcd'),
                       'Four letter urlbase should be rejected');
    $this->assertTrue($u->validUrlbase('abcde'),
                      'Five letter urlbase should be accepted');
                      

  }


  function testValidateUid () {
    $u = new folksoUser($this->dbc);
    $this->assertTrue($u->validateUid('herman-2010-004'),
                      "valid uid should validate");
    $this->assertFalse($u->validateUid("bobiskingoftheworld"),
                       "invalid uid should fail");
    $this->assertFalse($u->validateUid(""),
                       "empty uid should fail");

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
     $u->loadUser(array( 
                        'urlbase' => 'proust.marcel',
                        'firstname' => 'Marcel',
                        'lastname' => 'Proust',
                        'email' => 'marcelp@temps.eu',
                        'userid' => 'marcelp-2010-001'));
     $this->assertIsA($u, folksoUser,
                      'problem with object creation');
     $this->assertEqual($u->urlBase, 'proust.marcel',
                        'missing urlbase in user object');
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

     $u->loadUser(array('userid' => 'marcelp-2010-001',
                        'urlbase' => 'marcelp'));
     $u->loadAllRights();
     $this->assertTrue($u->checkUserRight("folkso", "tag"),
                       "user marcelp should have right 'tag'");
     $this->assertFalse($u->checkUserRight("folkso", "admin"),
                        "user marcelp should not have right 'admin'");
     $this->assertTrue($u->checkUserRight("folkso", "create"),
                       "user marcelp should have right 'create'");
                                           

   }

   function testExists() {
     $u = new folksoUser($this->dbc);
     $this->assertTrue($u->exists('marcelp-2010-001'));
     $this->assertFalse($u->exists('ffffffff-2010-001'),
                        "Unknown user should return false");

   }

   function testUserFromUserId () {
     $u = new folksoUser($this->dbc);
     $u->userFromUserId('marcelp-2010-001');

     $this->assertEqual($u->firstName, 'Marcel',
                        "First name is absent");


   }

   function testDeleteUserWithTags () {
     $u = new folksoUser($this->dbc);
     $this->assertTrue($u->userFromUserId('gustav-2010-001'),
                       "Failed to load gustav user ob");
     $u->deleteUserWithTags();
     $u2 = new folksoUser($this->dbc);
     $this->assertFalse($u->userFromUserId('gustav-2010-001'),
                        "Should no longer be able to load gustav here");
     


   }
   
}//end class

$test = &new testOffolksoUser();
$test->run(new HtmlReporter());