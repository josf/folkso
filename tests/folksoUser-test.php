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
         $simple_u = $u->createUser(array('nick' => 'bobert',
                                          'firstname' => 'Bobness',
                                          'lastname' => 'Justaguy',
                                          'email' => 'sloink@zoink.com',
                                          'oid_url' => 'http://i.am.me'));
         $this->assertTrue($simple_u[0],
                           'Basic user creation fails');
         $this->assertTrue($u->Writeable(),
                           'Writeable state incorrect: should be writeable now');

         $this->assertEqual($u->nick, 'bobert',
                            'Not retreiving nick correctly');
         $this->assertEqual($u->firstName, 'Bobness',
                            'Not retreiving first name correctly');

         $this->assertTrue($u->validNick('abcde'),
                           'Nick validation of "abcde" fails');
         $this->assertFalse($u->validNick('a'),
                            'Nick validation incorrect: single character should fail');
         $this->assertTrue($u->validNick('abcdefghijklm'),
                           'Nick validation incorrect. Long password should pass.');
         $this->assertTrue($u->validNick('abc123'),
                           'Nick validation incorrect. Should accept numbers');
         $this->assertFalse($u->validNick('abc_def'),
                            'Nick validation incorrect. Should not accept underscore');

         $this->assertFalse($u->validEmail("zork"), 
                            'Not detecting incomplete email (zork)');

         $this->assertTrue($u->validEmail("zork@zork.zork"),
                           'Email should be considered valid');
         $this->assertTrue($u->validEmail(),
                           'Email checking of object email value not working');


         $this->assertTrue($u->checkFbId('abcdef'),
                           'ultra simple FB id should pass.');
         $this->assertTrue($u->checkOidUrl('http://bob.com'),
                           'simple but valid url fails oid check');
         $this->assertFalse($u->checkOidUrl('bob.kom'),
                            'invalid oid url should fail');
         $this->assertTrue($u->checkIds(),
                           'oid_url should be enough for getting a valid check here');

         $this->assertFalse($u->validateRight('i win'),
                            'spaces in right name should not validate');
         $this->assertFalse($u->validateRight('Iwin'),
                            'capitals in right name should not validate');
         $this->assertTrue($u->validateRight('tag'),
                           'right name "tag" should validate');
         
         $this->assertFalse($u->checkUserRight('99oooo zork'),
                            'bad right name should fail in checkUserRight');

         $this->assertFalse($u->checkUserRight('haroomph'),
                            'non existant right should return false');
   }

   function testWithDB (){
     $u = new folksoUser($this->dbc);
     $u->createUser(array( 'nick' => 'marcelp',
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