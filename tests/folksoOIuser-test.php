<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoOIuser.php');
require('dbinit.inc');

class testOffolksoOIuser extends  UnitTestCase {
  public $dbc;

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc3 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc4 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

  function testUser () { // tests that used to be in folksoUser-test
    $u   = new folksoOIuser($this->dbc);
    $this->assertIsA($u, folksoUser,
                     'object creation failed');

    $this->assertFalse($u->writeable,
                       'initial writable state should be false');
    $simple_u = $u->loadUser(array('nick' => 'bobert',
                                   'firstname' => 'Bobness',
                                   'lastname' => 'Justaguy',
                                   'email' => 'sloink@zoink.com',
                                   'loginid' => 'http://i.am.me'));
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


   function testfolksoOIuser () {
         $oi   = new folksoOIuser($this->dbc);
         $this->assertIsA($oi, folksoOIuser,
                               'object creation failed');
         $this->assertIsA($oi, folksoUser,
                          'object hierarchy problem. folksoOIusers should be folksoUser objs');
         $this->assertFalse($oi->exists('http://idont-exist.com'),
                            'bad oid url should return false');
         $this->assertFalse($oi->validateLoginId(''),
                            'empty login id should be false');
         $this->assertTrue($oi->validateLoginId('http://bob.com'),
                           'simple but valid url fails oid check');
         $this->assertFalse($oi->validateLoginId('bob.kom'),
                            'invalid oid url should fail');
         $this->assertTrue($oi->validateLoginId('http://myspace.com/gustav'),
                           'gustav url should validate, but is not');
         $this->assertTrue($oi->exists('http://myspace.com/gustav'),
                           'not existing gustav');

         $noone = new folksoOIuser($this->dbc2);
         $bzork = $noone->userFromLogin('http://myspace.com/ploof');
         $this->assertFalse($bzork,
                            'bad user id should return false in userFromLogin');

         
         $gus = new folksoOIuser($this->dbc3);
         $zork = $gus->userFromLogin('http://myspace.com/gustav');

         $this->assertIsA($zork, folksoOIuser,
                          'userFromLogin should not return false');
         $this->assertTrue($gus->Writeable(),
                           'userFromLogin does not fetch a writeable user'. print_r($gus));
         $this->assertEqual($gus->nick, 'gustav',
                            'Not retreiving correct nick');
         $this->assertTrue(strlen($gus->nick) > 2,
                           'nick is too short or does not exist');
         $this->assertEqual($gus->firstName, 'Gustave',
                            'Not retrieving correct first name');
         $this->assertEqual($gus->email, 'gflaub@sentimental.edu',
                            'Not retrieving correct email address');
         $this->assertTrue($gus->Writeable(), 'retrieved user is not writeable');


   }

   function testCreation () {
         $claud = new folksoOIuser($this->dbc);
         $claud->loadUser(array('nick' => 'paulc',
                                'firstname' => 'Paul',
                                'lastname' => 'Claudel',
                                'email' => 'pclaudel@vatican.com',
                                'loginid' => 'http://pclaudel.openid.fr'));
         $this->assertTrue($claud->Writeable(),
                           'Claudel: failed to create writeable user');

   }
}//end class

$test = &new testOffolksoOIuser();
$test->run(new HtmlReporter());