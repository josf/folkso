<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoSession.php');
require('dbinit.inc');

class testOffolksoSession extends  UnitTestCase {
  public $dbc;

  function setUp() {
    test_db_init();
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

   function testSession () {
         $s   = new folksoSession(
                                  new folksoDBconnect( 'localhost', 'tester_dude', 
                                                      'testy', 'testostonomie'));

         $this->assertIsA($s, folksoSession,
                               'object creation failed');
         $this->assertIsA($s->dbc, folksoDBconnect,
                          'DBconnection object is not there');


         $this->assertTrue(strlen($s->newSessionId('zorkdork-2289-002')) == 64,
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
         $this->assertTrue($s->validateSid($s->newSessionId('zoopfest-1776-010')),
                           'a new session id should validate');

         $this->assertFalse($s->validateSid('tooshort'),
                            'a short session id should not validate');
                                      
         //         $this->expectException();

         /*         $this->assertFalse($s->startSession('zork-ù**-volvp zZkr'),
                    'bad uid should prevent session from starting');*/

         $this->assertFalse($s->checkSession('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'),
                            'nonexistant session should not check true');

         $sess = $s->startSession('gustav-2010-001', true);
         $this->assertTrue($sess,
                           'This session should work');
         $this->assertTrue($s->validateSid($sess),
                           'startSession() should return a valid session id');
         $this->assertTrue($s->checkSession($sess),
                           'Session is there, we should see it with check');

         $this->assertTrue($s->status(),
                           "status() method should return true for valid session");
         $this->assertEqual($s->getUserId(), 'gustav-2010-001',
                            'Not getting user id with getUserId: ' . $s->getUserId());

         
         $s->killSession($sess);

         $this->assertFalse($s->checkSession($sess),
                            'the session should be gone now');

   }

   function testSession2 () {
     $s = new folksoSession($this->dbc);
         $sess2 = $s->startSession('gustav-2010-001', true);
         $this->assertTrue($sess2,
                           'session creation failed');
         $this->assertTrue($s->checkSession($sess2),
                           'session not valid (sess2)');
         $u = $s->userSession($sess2);
         $this->assertIsA($u, folksoUser,
                          'userSession not returning folksoUser object');

         $this->assertEqual($u->firstName, 'Gustave',
                            'First name not correctly retrieved: ' . $u->firstName);

   }

   function testRights () {
     $s = new folksoSession($this->dbc);
     $this->assertIsA($s, folksoSession,
                      'No point in testing if we do not have a fkSession obj');
     $sid = $s->startSession('marcelp-2010-001', true);
     $u = $s->userSession($sid, 'folkso', 'tag');
     $this->assertTrue($u, 'userSession returns false');
     $this->assertIsA($u, folksoUser,
                      'userSession w/ args not returning a fkUser obj');
     $this->assertIsA($u->rights, folksoRightStore,
                      '$u->rights should be a folksoRightStore object');
     $this->assertTrue($u->rights->hasRights(),
                       'user right store is still empty');
     $this->assertTrue($u->checkUserRight('folkso', 'tag'),
                       'checkUserRight() not returning true');
   }

}//end class

$test = &new testOffolksoSession();
$test->run(new HtmlReporter());