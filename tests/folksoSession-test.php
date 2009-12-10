<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
require('folksoSession.php');
require('dbinit.inc');

class testOffolksoSession extends  UnitTestCase {

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
    
  }

   function testSession () {
         $s   = new folksoSession(
                                  new folksoDBconnect( 'localhost', 'tester_dude', 
                                                      'testy', 'testostonomie'));
         $this->assertIsA($s, folksoSession,
                               'object creation failed');
         $this->assertIsA($s->dbc, folksoDBconnect,
                          'DBconnection object is not there');


         $this->assertTrue(strlen($s->newSessionId()) == 64,
                           'session id not long enough (want 64 chars');
         $this->assertTrue($s->validateUid('zork-000-124'),
                           'zork-000-124 should validate as uid.');
         $this->assertFalse($s->validateUid('zorkisnottooshort'),
                            'this uid should not validate');
         $this->assertFalse($s->validateUid('zork-**--000'),
                            'this uid should not validate either');

         $this->assertTrue($s->validateSid('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'),
                           'this should be a valid session id');
         $this->assertFalse($s->validateSid('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee-e'),
                            'Non alphanumeric session id should fail');
         $this->assertTrue($s->validateSid($s->newSessionId()),
                           'a new session id should validate');
         $this->assertFalse($s->validateSid('tooshort'),
                            'a short session id should not validate');
                                      
         $this->expectException();
         $this->assertFalse($s->startSession('zork-ù**-volvp zZkr'),
                            'bad uid should prevent session from starting');
         $this->assertFalse($s->checkSession('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'),
                            'nonexistant session should not check true');

         $sess = $s->startSession('gustav-2009-001');
         $this->assertTrue($sess,
                           'This session should work');
         $this->assertTrue($s->validateSid($sess),
                           'startSession() should return a valid session id');
         $this->assertTrue($s->checkSession($sess),
                           'Session is there, we should see it with check');
         $s->killSession($sess);
         print $sess;
         $this->assertFalse($s->checkSession($sess),
                            'the session should be gone now');
         $sess2 = $s->startSession('gustav-2009-001');
         $this->assertTrue($sess2,
                           'session creation failed');
         $this->assertTrue($s->checkSession($sess2),
                           'session not valid (sess2)');
         $u = $s->userSession($sess2);
         $this->assertIsA($u, folksoUser,
                          'userSession not returning folksoUser object');

         $this->assertEqual($u->nick, 'gustav',
                            'User nick not correctly retrieved' . $u->nick);
         print_r( $u);
   }
}//end class

$test = &new testOffolksoSession();
$test->run(new HtmlReporter());