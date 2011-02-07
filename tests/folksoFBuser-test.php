<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoFBuser.php');
require_once('dbinit.inc');

class testOffolksoFBuser extends  UnitTestCase {
  public $dbc;

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude',
                                       'testy', 'testostonomie');
  }

  function testNaming () {
    $fb = new folksoFBuser($this->dbc);
    $url = $fb->urlbaseFromFBname("Dennis Menace");
    $this->assertEqual($fb->firstName, "Dennis",
                       "urlbaseFromFBname not setting firstName");
    $this->assertEqual($fb->lastName, "Menace",
                       "urlbaseFromFBname not setting lastName");
    $this->assertTrue(is_string($url),
                      "urlbaseFromFBname not returning string");
    $this->assertEqual($url, "dennis.menace",
                       "urlbaseFromFBname returning incorrect url: " . $url);
    $this->assertEqual($url, $fb->urlBase,
                       "urlbaseFromFBname not setting urlBase property");

    /* Spaces in name */
    $url2 = $fb->urlbaseFromFBname("Dennis the Menace");
    $this->assertEqual($url2, "dennisthe.menace",
                       "urlbaseFromFBname not removing spaces from lastname: " 
                       . $url2);
    $this->assertEqual($fb->lastName, "Menace",
                       "Incorrect last name when more than 1 word" . $fb->lastName);
    $this->assertEqual($fb->firstName, "Dennis the",
                       "Incorrect first name when more than 1 word: " . $fb->firstName);

    /* Accented characters */
    $url3 = $fb->urlbaseFromFBname("Hervé François");
    $this->assertEqual($url3, "herve.francois",
                       "urlbaseFromFBname not switching out accented characters"
                       . $url3);


  }


   function testFBuser () {
         $fb   = new folksoFBuser($this->dbc);
         $this->assertIsA($fb, 'folksoFBuser',
                               'object creation failed');

         $this->assertIsA($fb, 'folksoUser',
                          'object hierarchy problem');
         $this->assertFalse($fb->exists('999999'),
                            'bad fb id should return false');
         $this->assertFalse($fb->validateLoginId('abcdef'),
                            'non-numeric facebook uid should fail');

         $this->assertFalse($fb->validateLoginId('A doo day'),
                            'Bad FB id with letters and spaces should return false');
         $this->assertTrue($fb->validateLoginId('123456'),
                           '123456 not validating as FB id');
         $this->assertTrue($fb->exists('543210'),
                           'rambo-2010-001 at 543210 not showing up');
         $this->assertTrue($fb->userFromLogin('543210'),
                           'userFromLogin returns false');

         $this->assertEqual($fb->urlBase, 'rambo',
                            'Incorrect urlbase: ' . $fb->urlBase);

         $fb->setFirstName('Frank');
         $this->assertEqual('Frank', $fb->firstName,
                            'Problem setting first name');

         $fb->useFBname('Bob Henderson', TRUE);
         $this->assertEqual($fb->firstName, 'Bob',
                            'userFBname does not produce correct first name' . $fb->firstName);
         $this->assertEqual($fb->lastName, 'Henderson',
                            'userFBname does nto produce correct last name' 
                            . $fb->lastName);

         $fb->useFBname('Pocahontas', true);
         $this->assertEqual($fb->lastName, 'Pocahontas',
                            'Single name not showing up as last name');

         $fb->useFBname('Ronald M. Stevenson', true);
         $this->assertEqual($fb->firstName, 'Ronald M.',
                            'useFBname not treating multiple space names correctly');
         $this->assertEqual($fb->lastName, 'Stevenson',
                            'useFBname not treating multiple space names correctly');

         $fb->useFBname('Ollie Olson');
         $this->assertNotEqual('Ollie',
                               $fb->firstName,
                               'Conditional overwriting not working: should not have written here.');
         $fb2   = new folksoFBuser($this->dbc2);                        
         $fb2->useFBname('Randall Walker');
         $this->assertEqual($fb2->firstName, 
                            'Randall',
                            'useFBname not working on empty user ob');

   }



   function testMinimalUserCreation () {
     $u = new folksoFBuser($this->dbc);
     $u->setLoginId(123123112);
     $u->urlBaseFromLoginId();
     $this->assertTrue($u->urlBase, 123123112, "urlBaseFromLoginId() did not set urlBase var");
     $this->assertTrue($u->Writeable(),
                       "Minimal user should already be writeable, with only login id");

   }


   function testGenerateUrlbase () {
     $u = new folksoFBuser($this->dbc);
     $u->loadUser(array('firstname' => 'Francis',
                        'lastname' => 'Ponge',
                        'email' => 'fponge@parti-choses.fr',
                        'loginid' => 1234567890));
     $u->writeNewUser();
     
     $u2 = new folksoFBuser($this->dbc2);
     $u2->userFromLogin(1234567890);
     $this->assertEqual($u2->firstName, 'Francis',
                        'Did not get first name back after writing');
     $this->assertEqual($u2->urlBase, 'francis.ponge',
                        'Did not get urlbase back after writing: ' . $u2->urlBase);

   }


   function testCreateUser() {
     $u = new folksoFBuser($this->dbc);
     $fbid = 10101010101;
     $u->loadUser(array('urlbase' => 'margotduras',
                        'firstname' => 'Margie',
                        'lastname' => 'Duras',
                        'email' => 'md@nicolas.fr',
                        'userid' => 'duras-2011-001',
                        'loginid' => $fbid));
     $this->assertIsA($u, 'folksoFBuser',
                      'problem with object creation');

     $this->assertTrue($u->Writeable(),
                       'Duras object is not writeable');
     $u->writeNewUser();
     $ex = new folksoFBuser($this->dbc2);
     $this->assertTrue($ex->exists($fbid),
                       'User does not seem to have been created');
     

     $u2 = new folksoFBuser($this->dbc);
     $this->assertTrue($u2->userFromLogin($fbid),
                       "Should return true here, user loading should have succeeded");
     $this->assertEqual($u2->firstName_norm, 'margie',
                        'Incorrect normalized first name, should be margie, not: '
                        . $u2->firstName_norm);

     $this->assertEqual($u2->lastName_norm, 'duras',
                        'Incorrect normalized last name, should be duras, not: '
                        . $u2->lastName_norm);

     $this->assertEqual($u2->nameUrl(), 'margie.duras',
                        'Incorrect or missing nameUrl, expecting margie.duras, got: '
                        . $u2->nameUrl());

   }

}//end class



$test = &new testOffolksoFBuser();
$test->run(new HtmlReporter());