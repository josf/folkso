<?php

require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoUser.php');
require_once('dbinit.inc');

class testOffolksoUser extends  UnitTestCase {
  public $dbc;


  function setUp() {
    //    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }


  function testSetters () {
    $u = new folksoUser($this->dbc);
    $u->setUrlbase('stuff');
    $this->assertEqual($u->urlBase, 'stuff');


    $u->setFirstName('Bob');
    $this->assertEqual($u->firstName, 'Bob',
                       'setFirstName method: name not showing up in obj');

    $u->setLastName('TheSlob');
    $this->assertEqual($u->lastName, 'TheSlob',
                       'setLastName method: name not showing up in obj');
    
    $u->setCv('Wrote some books');
    $this->assertEqual($u->cv, 'Wrote some books',
                       'Failed to set cv');


    $u2 = new folksoUser($this->dbc);
    call_user_func(array($u2, 'setCv'), 'Wrote stuff');
    $this->assertEqual($u2->cv, 'Wrote stuff',
                       'Cv data failed with call_user_func');

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
    $this->assertTrue($u->validUrlbase('1221343243'),
                      "All number url base should be allowed");
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
    $this->assertTrue($u->validateUid('herman-2011-004'),
                      "valid uid should validate");
    $this->assertFalse($u->validateUid("bobiskingoftheworld"),
                       "invalid uid should fail");
    $this->assertFalse($u->validateUid(""),
                       "empty uid should fail");
    $this->assertTrue($u->validateUid('21312123112-2011-003'),
                      "User id with all number in first part should be valid");

  }

   function testUser () {
     $u = new folksoUser($this->dbc);
     $u->setUid('marcelp-2011-001');
     $this->assertEqual($u->userid, 'marcelp-2011-001',
                        'Bad or missing userid from setUid: ' . $u->userid);

     $u->setEmail('bob@warp.com');
     $this->assertEqual($u->email, 'bob@warp.com',
                        'Bad or missing email from setEmail: ' . $u->email);
     
   }


   function testWithDB (){
     test_db_init();
     $u = new folksoUser($this->dbc);
     $u->loadUser(array( 
                        'urlbase' => 'proust.marcel',
                        'firstname' => 'Marcel',
                        'lastname' => 'Proust',
                        'email' => 'marcelp@temps.eu',
                        'userid' => 'marcelp-2011-001'));
     $this->assertIsA($u, 'folksoUser',
                      'problem with object creation');
     $this->assertEqual($u->urlBase, 'proust.marcel',
                        'missing urlbase in user object');
     $this->assertEqual($u->email, 'marcelp@temps.eu',
                        'Email incorrect after loadUser');
     $this->assertEqual($u->userid, 'marcelp-2011-001',
                        'userid not present: ' . $u->userid);

     $this->assertFalse($u->checkUserRight('ploop', 'dooop'),
                        'inexistant right should not validate');
   }


   function testSetterWithDB () {
     test_db_init();
     $u = new folksoUser($this->dbc);
     $u->userFromUserId('marcelp-2011-001');

     $this->assertEqual($u->firstName, 'Marcel',
                        'Problem with test. firstName incorrect on load');
     $this->assertEqual($u->lastName, 'Proust',
                        'Problem with test. lastName incorrect on load');
     
     $u->setFirstName('Horace');
     $u->setLastName('Force');
     $u->setCv('Wrote a long book');

     $this->assertEqual($u->firstName, 'Horace',
                        'Incorrect firstname after attempt to change');
     $this->assertEqual($u->lastName, 'Force',
                        'Incorrect lastname after attempt to change');
     $this->assertEqual($u->cv, 'Wrote a long book',
                        'Cv failed to update on object');

     $u->storeUserData();
     $u2 = new folksoUser($this->dbc);
     $u2->userFromUserId('marcelp-2011-001');

     $this->assertEqual($u2->firstName, 'Horace',
                        'Incorrect firstname after DB retrieval');
     $this->assertEqual($u2->lastName, 'Force',
                        'Incorrect lastname after DB retrieval');
     $this->assertEqual($u2->cv, 'Wrote a long book',
                        'Cv failed to update after DB retrieval');
     
   }

   function testRights () {
     test_db_init();
     $u = new folksoUser($this->dbc);
     $this->assertIsA($u->rights, 
                      'folksoRightStore',
                      'No fkRightStore at $u->rights');

     $u->loadUser(array('userid' => 'marcelp-2011-001',
                        'urlbase' => 'marcelp'));
     $u->loadAllRights();
     $this->assertTrue($u->checkUserRight("folkso", "tag"),
                       "user marcelp should have right 'tag'");
     $this->assertFalse($u->checkUserRight("folkso", "admin"),
                        "user marcelp should not have right 'admin'");
     $this->assertTrue($u->checkUserRight("folkso", "create"),
                       "user marcelp should have right 'create'");

   }

   function testUserFromUserId () {
     test_db_init();
     $u = new folksoUser($this->dbc);
     $this->assertTrue($u->exists('marcelp-2011-001'));
     $this->assertFalse($u->exists('ffffffff-2011-001'),
                        "Unknown user should return false");

     $u->userFromUserId('marcelp-2011-001');

     $this->assertEqual($u->firstName, 'Marcel',
                        "First name is absent");
     $this->assertTrue($u->hasData(),
                       "hasData() should return true here");

   }

   function testDeleteUserWithTags () {
     test_db_init();
     $u = new folksoUser($this->dbc);
     $this->assertTrue($u->userFromUserId('gustav-2011-001'),
                       "Failed to load gustav user ob");
     $u->deleteUserWithTags();
     $u2 = new folksoUser($this->dbc);
     $ret = $u->userFromUserId('gustav-2011-001');

     $this->assertFalse($ret,
                        "Should no longer be able to load gustav here: userFromUserId should return false");
     $this->assertFalse($ret instanceof folksoUser,
                        '$ret should be false, not folksoUser object');
     $this->assertFalse($u2->exists('gustav-2011-001'),
                        'gustav should no longer exist here');
   }

   function testStoreUserData () {
     test_db_init();
     $u = new folksoUser($this->dbc);
     $u->userFromUserId('gustav-2011-001');
     $this->assertEqual($u->firstName, 'Gustave',
                        "Problem with initial retrieval of user");


     $u->setFonction("harpist");

     $this->assertEqual($u->fonction, 'harpist',
                        "Testing the test: 'fonction' should be set to 'harpist' "
                        . ' not: ' . $u->fonction);
     try {
       $u->storeUserData();
     }
     catch (dbQueryException $e) {
       print $e->sqlquery;
     }
     
     $u2 = new folksoUser($this->dbc);
     $u2->userFromUserId('gustav-2011-001');


     $this->assertEqual($u2->fonction, 'harpist',
                        "New fonction data was not retreived, got: " . $u2->fonction);

   }

   function testStoreCv () {
     test_db_init();
     $u = new folksoUser($this->dbc);
     $u->userFromUserId('gustav-2011-001');
     $this->assertEqual($u->firstName, 'Gustave',
                        "Problem with initial retrieval of user");

     $u->setCv("Wrote a book");
     $u->storeUserData();

     $u2 = new folksoUser($this->dbc);
     $u2->userFromUserId('gustav-2011-001');

     $this->assertEqual($u2->cv, 'Wrote a book',
                        'Did not retreive cv data');
   }

   
}//end class

$test = &new testOffolksoUser();
$test->run(new HtmlReporter());