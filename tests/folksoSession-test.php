<?php

require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoSession.php');
require_once('dbinit.inc');

class testOffolksoSession extends  UnitTestCase {
  public $dbc;

  function setUp() {

    /** not using teardown because this function does a truncate
        before starting. **/
    $this->dbc = new folksoDBconnect( 'localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
    $this->dbc2 = new folksoDBconnect( 'localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
    $this->dbc3 = new folksoDBconnect( 'localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
    $this->dbc4 = new folksoDBconnect( 'localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

  function testSessionValidation () { // no DB
         $s   = new folksoSession(
                                  new folksoDBconnect( 'localhost', 'tester_dude', 
                                                      'testy', 'testostonomie'));

         $this->assertIsA($s, 'folksoSession',
                               'object creation failed');
         $this->assertIsA($s->dbc, 'folksoDBconnect',
                          'DBconnection object is not there');

         $this->assertTrue(strlen($s->newSessionId('zorkdork-2289-002')) == 64,
                           'session id not long enough (want 64 chars');
         $this->assertTrue($s->validateSid('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'),
                           'this should be a valid session id');
         $this->assertFalse($s->validateSid('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee-e'),
                            'Non alphanumeric session id should fail');

         $this->assertTrue($s->validateSid('948e1f8fabdfcac218a234ce2f154d6b4865deb03fbf54421fe28a60fd528543'),
                           'Real sid does not pass validateSid()');
                                         
         $this->assertTrue($s->validateSid($s->newSessionId('zoopfest-1776-010')),
                           'a new session id should validate');

         $this->assertFalse($s->validateSid('tooshort'),
                            'a short session id should not validate');
                                      
         //         $this->expectException();

         /*         $this->assertFalse($s->startSession('zork-ù**-volvp zZkr'),
                    'bad uid should prevent session from starting');*/

         $this->assertFalse($s->checkSession('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'),
                            'nonexistant session should not check true');
   }

  function testSessionIdCreation () {
    $s = new folksoSession(
                           new folksoDBconnect( 'localhost', 'tester_dude', 
                                                'testy', 'testostonomie'));
    $this->assertTrue(strlen($s->newSessionId("bogusid")) > 0,
                      "newSessionId should return string even with invalid uid");

    $first = $s->newSessionId("zoopfest-1776-010");
    sleep(3);
    $second = $s->newSessionId("zoopfest-1776-010");
    $this->assertNotEqual($first, $second, 
                          "Successive calls to newSessionId should return different hashes");
  }

  function testSesssion () {
    test_db_init();
    $s   = new folksoSession(
                             new folksoDBconnect( 'localhost', 'tester_dude', 
                                                  'testy', 'testostonomie'));

    $sess = $s->startSession('gustav-2011-001', true);
    $this->assertTrue($sess, 'This session should work');
    $this->assertTrue($s->validateSid($sess),
                      'startSession() should return a valid session id');
    $this->assertTrue($s->checkSession($sess),
                      'Session is there, we should see it with check');
    $this->assertTrue($s->status(),
                      "status() method should return true for valid session");
    $this->assertEqual($s->getUserId(), 'gustav-2011-001',
                       'Not getting user id with getUserId: ' . $s->getUserId());

         
    $s->killSession($sess);

    $this->assertFalse($s->checkSession($sess),
                       'the session should be gone now');

    $u = new folksoUser($this->dbc);
    $u->userFromUserId('gustav-2011-001');
    $cesse = new folksoSession($this->dbc);
    $cesse->startSession($u, true);
    $this->assertTrue($cesse->status(),
                      "Session from user object. Should be valid");

  }

   function testSession2 () {
     test_db_init();
     $s = new folksoSession($this->dbc);
     $sess2 = $s->startSession('gustav-2011-001', true);
     $this->assertTrue($sess2, 'session creation failed');
     $this->assertTrue($s->checkSession($sess2), 'session not valid (sess2)');

     $u = $s->userSession($sess2);
     $this->assertIsA($u, 'folksoUser',
                      'userSession not returning folksoUser object');
     $this->assertEqual($u->firstName, 'Gustave',
                        'First name not correctly retrieved: ' . $u->firstName);
     $this->assertEqual($u->userId, 'gustav-2011-001',
                        'userid not correctly retrieved: ' . $u->userid);

   }

   function testSessionCleanup () {
     test_db_init();
     $s = new folksoSession(
                            new folksoDBconnect( 'localhost', 'tester_dude', 
                                                  'testy', 'testostonomie'));

     $thisSess = $s->newSessionId('gustav-2011-001');
     $i = new folksoDBinteract($this->dbc);
     try {
       $i->query(sprintf('insert into sessions (token, userid, started) '
                         . ' values '
                         . " ('%s', '%s', date_sub(now(), interval 3 week))",
                         $i->dbescape($thisSess),
                         $i->dbescape('gustav-2011-001')));

       $i->query("select * from sessions where started < date_sub(now(), interval 2 week)");
     }
     catch (dbQueryException $e) {
       print($e->sqlquery);
     }

     $this->assertTrue($i->rowCount > 0,
                       "Old session should be present, none found. Testing the test.");

     $s->startSession('gustav-2011-001', // this should kill the expired session
                      true); 

     try {
       $i->query("select * from sessions where started < date_sub(now(), interval 2 week)");
     }
     catch (dbQueryException $e) {
       print("Second time around:  " .  $e->sqlquery);
     }

     $this->assertTrue($i->rowCount === 0,
                       "Old session should now be gone");

   }

   function testRights () {
     $s = new folksoSession($this->dbc);
     $this->assertIsA($s, 'folksoSession',
                      'No point in testing if we do not have a fkSession obj');
     $sid = $s->startSession('marcelp-2011-001', true);
     $u = $s->userSession($sid, 'folkso', 'tag');
     $this->assertTrue($u, 'userSession returns false');
     $this->assertIsA($u, 'folksoUser',
                      'userSession w/ args not returning a fkUser obj');
     $this->assertIsA($u->rights, 'folksoRightStore',
                      '$u->rights should be a folksoRightStore object');
     $this->assertTrue($u->rights->hasRights(),
                       'user right store is still empty');
     $this->assertTrue($u->checkUserRight('folkso', 'tag'),
                       'checkUserRight() not returning true');
   }

   function testDestUrl () {
     $s = new folksoSession($this->dbc);
     $sid = $s->startSession('marcelp-2011-001', true);
     $this->assertTrue($s->sessionId, "session id should exist");
     
     $this->assertFalse($s->getDestUrl(),
                        "Dest url not set yet, should return false");
     $this->assertTrue($s->status(), "status should be true");
     $s->setDestUrl('http://example.com');
     $this->assertEqual($s->destUrl, 'http://example.com',
                        "destUrl should be set in object");

     /**
      * This only tests whether we can get the url back from the
      * object, not whether the url is stored in the DB. See below.
      */
     $this->assertEqual($s->getDestUrl(), "http://example.com",
                        "Should retreive url (http://example.com), not: " .
                        $s->getDestUrl());
   }

   function testDestUrlDB () {
     $s = new folksoSession($this->dbc);
     $sid = $s->startSession('chuckyb-2011-001', true);
     $s->setDestUrl('http://example.com');

     $s->destUrl = false; // kill destUrl in the object

     $this->assertEqual($s->getDestUrl(), "http://example.com",
                        "Should still get destUrl from DB, even "
                        ." when not present in obj.");


   }


}//end class

$test = &new testOffolksoSession();
$test->run(new HtmlReporter());