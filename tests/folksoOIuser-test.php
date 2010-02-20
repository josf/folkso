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
    $simple_u = $u->loadUser(array('urlbase' => 'slobbo',
                                   'firstname' => 'Bobness',
                                   'lastname' => 'Justaguy',
                                   'email' => 'sloink@zoink.com',
                                   'loginid' => 'http://i.am.me'));
    $this->assertTrue($simple_u[0],
                      'Basic user creation fails');
    $this->assertTrue($u->Writeable(),
                      'Writeable state incorrect: should be writeable now');
    $this->assertEqual($u->urlBase, 'slobbo',
                       'Not retreiving nick correctly');
    $this->assertEqual($u->firstName, 'Bobness',
                       'Not retreiving first name correctly');

    $this->assertTrue($u->validUrlbase('abcde'),
                      'Validation of "abcde" as urlbase fails');
    $this->assertFalse($u->validUrlbase('a'),
                       'Urlbase validation incorrect: single character should fail');
    $this->assertFalse($u->validUrlbase('abc_def'),
                       'Urlbase validation incorrect. Should not accept underscore');

    $this->assertFalse($u->validEmail("zork"), 
                       'Not detecting incomplete email (zork)');

    $this->assertTrue($u->validEmail("zork@zork.zork"),
                      'Email should be considered valid');
    $this->assertTrue($u->validEmail(),
                      'Email checking of object email value not working');
    /**
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
    **/
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

         $marcel = new folksoOIuser($this->dbc4);
         $marcel->userFromLogin('http://flickr.com/marcelp');
         $this->assertEqual($marcel->urlBase, 'marcelp',
                            'Not retreiving correct nick for marcelp');


         
         $gus = new folksoOIuser($this->dbc3);
         $zork = $gus->userFromLogin('http://myspace.com/gustav');

         $this->assertIsA($zork, folksoOIuser,
                          'userFromLogin should not return false');
         $this->assertTrue($gus->Writeable(),
                           'userFromLogin does not fetch a writeable user' );
         $this->assertEqual($gus->userid, 'gustav-2010-001',
                            'Not retreiving userid');
         $this->assertEqual($gus->urlBase, 'gustav',
                            'Not retreiving correct urlbase');
         $this->assertTrue(strlen($gus->urlBase) > 2,
                           'urlbase is too short or does not exist');
         $this->assertEqual($gus->firstName, 'Gustave',
                            'Not retrieving correct first name');
         $this->assertEqual($gus->email, 'gflaub@sentimental.edu',
                            'Not retrieving correct email address');
         $this->assertTrue($gus->Writeable(), 'retrieved user is not writeable');


   }

   function testCreation () {
         $claud = new folksoOIuser($this->dbc);
         $claud->loadUser(array('urlbase' => 'paulc',
                                'firstname' => 'Paul',
                                'lastname' => 'Claudel',
                                'email' => 'pclaudel@vatican.com',
                                'loginid' => 'http://pclaudel.openid.fr'));
         $this->assertTrue($claud->Writeable(),
                           'Claudel: failed to create writeable user');
         $claud->writeNewUser();
         $ex = new folksoOIuser($this->dbc2);
         $this->assertTrue($ex->exists('http://pclaudel.openid.fr'),
                           'User does not seem to have been created');

         $bad = new folksoOIuser($this->dbc3);
         $bad->loadUser(array('urlbase' => 'celine75',
                              'firstname' => 'Ferdy',
                              'lastname' => 'Céline',
                              'email' => 'f.celine@fn.fr'));


   }

   function testRights () {
     $u = new folksoOIuser($this->dbc);
     $u->userFromLogin('http://flickr.com/marcelp', 'folkso', 'tag');

     $this->assertIsA($u, folksoOIuser,
                      'Problem with object creation');
     $this->assertEqual($u->urlBase, 'marcelp',
                        'Incorrect urlBase with userFromLogin');
     $this->assertTrue($u->rights->hasRights(),
                       'RightStore is reporting empty');
     $this->assertTrue($u->rights->checkRight('folkso', 'tag'),
                       'checkRight via fkRightStore not working correctly');
     $this->assertTrue($u->checkUserRight('folkso', 'tag'),
                       'checkUserRight() not working');
   }

   function testAllRights () {
     $u = new folksoOIuser($this->dbc);
     $u->userFromLogin('http://flickr.com/marcelp');
     $this->assertEqual($u->userid, 'marcelp-2010-001',
                        'Did not load userid');
     $u->loadAllRights();
     $this->assertTrue($u->rights->hasRights(),
                       'Did not load any rights at all with loadAllrights');
     $this->assertTrue($u->checkUserRight('folkso', 'create'),
                       'marcelp should have folkso/create');
     $this->assertTrue($u->checkUserRight('folkso', 'tag'),
                       'marcelp should have folkso/tag');
     $this->assertTrue($u->checkUserRight('folkso', 'delete'),
                        'marcelp should have delete because he has redac');

   }
}//end class

$test = &new testOffolksoOIuser();
$test->run(new HtmlReporter());