<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
//require_once('/var/www/user.php');
require_once('folksoUserServ.php');
require_once('dbinit.inc');

class testOfuser extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
    $lh = 'localhost';
    $td = 'tester_dude';
    $ty = 'testy';
    $tt = 'testostonomie';
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc3 =new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie'); 
     $this->fks = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks2 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks3 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks4 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
  }

   function testGetMyTags () {
     $this->fks->startSession('gustav-2010-001', true);
     $r = getMyTags(new folksoQuery(array(),
                                    array(),
                                    array()),
                    $this->dbc,
                    $this->fks);

     $this->assertIsA($r, folksoResponse,
                      'Not getting response object back');
     $this->assertEqual($r->status, 200,         
                        'Not getting 200: ' . $r->status);
     $this->assertNotEqual($r->status, 204,
                           'Getting no data back (204)');
     $this->assertTrue((strlen($r->body()) > 100),
                       'Response body is suspiciously short (less than 100 chars)');
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');

   }

   function testGetMyTagsJson () {
     $this->fks->startSession('gustav-2010-001', true);
     $r = getMyTags(new folksoQuery(array(),
                                    array('folksodatatype' => 'json'),
                                    array()),
                    $this->dbc,
                    $this->fks);

     $this->assertIsA($r, folksoResponse,
                      'Not getting response object back');
     $this->assertEqual($r->status, 200,         
                        'Not getting 200: ' . $r->status);
     $this->assertNotEqual($r->status, 204,
                           'Getting no data back (204)');
     $dec = json_decode($r->body());
     $this->assertNotNull($dec,
                          'failed to decode json, getting false');
     $this->assertTrue(is_array($dec),
                       'decoded json is not an array');
     $this->assertTrue(isset($dec[0]->resid),
                       'No resid in decoded json');
   }

   function testGetUserResByTag() {
     $this->fks->startSession('gustav-2010-001', true);
     $r = getUserResByTag(new folksoQuery(array(),
                                          array('folksotag' => 'tagone'),
                                          array()),
                          $this->dbc,
                          $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'Not getting fkResponse');
     $this->assertEqual($r->status, 200,
                        'Not a 200!: ' . $r->status);
     $this->assertNotEqual($r->status, 204,
                           'No data found for user resources by tag');
     $this->assertNotEqual($r->status, 500,
                           'Should not get this error: ' . $r->body());
     $this->assertPattern('/example\.com/', $r->body(),
                          'Not finding url in response');
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');

     /** not implemented yet **/
     /*     $this->assertPattern('/tagone/', $r->body(),
            'Not finding tag in response: ' . $r->body());*/
     $r2 = getUserResByTag(new folksoQuery(array(),
                                           array('folksotag' => 'emacs'),
                                           array()),
                           $this->dbc2,
                           $this->fks);
     $this->assertIsA($r2, folksoResponse,
                      'Not getting fkResponse this time');
     $this->assertEqual($r2->status, 204,
                        'Unknown tag should throw 204: ' . $r2->status);

     /** JSON output **/
     $this->fks2->startSession('gustav-2010-001', true);
     $r3 = getUserResByTag(new folksoQuery(array(),
                                           array('folksotag' => 'tagone',
                                                 'folksodatatype' => 'json'),
                                           array()),
                           $this->dbc3,
                           $this->fks2);
     $this->assertEqual($r3->status, 200,
                        "json query should return 200, not: " . $r3->status 
                        . " " . $r3->statusMessage);
     

   }

   function testCheckFBuserId () {
     /* good user */
     $r = checkFBuserId(new folksoQuery(array(),
                                        array('folksofbuid' => "543210",
                                              'folksocheck' => "1"),
                                        array()),
                        $this->dbc,
                        $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'checkFBuserId is not returning a response object');
     $this->assertEqual($r->status, 200,
                        'Did not find a valid FB user: ' . $r->statusMessage);

     /* unknown user */
     $r2 = checkFBuserId(new folksoQuery(array(),
                                         array('folksofbuid' => '1000000000000',
                                               'folksocheck' => '1'),
                                         array()),
                         $this->dbc2,
                         $this->fks2);
     $this->assertIsA($r2, folksoResponse,
                      'checkFBuserId not returning response object on unknown user');
     $this->assertEqual($r2->status, 404,
                        'Incorrect error status for unknown user: ' . $r2->status);

     /* invalid user string */
     $r3 = checkFBuserId(new folksoQuery(array(),
                                         array('folksofbuid' => 'wop a doo day',
                                               'folksocheck' => '1'),
                                         array()),
                         $this->dbc3,
                         $this->fks3);
     $this->assertIsA($r3, folksoResponse,
                      'checkFBuserId not returning response object on invalid uid');
     $this->assertEqual($r3->status, 406,
                        'Incorrect error status for unknown user'
                        .'. expecting 406, got ' . $r3->status);


   }

   function testLoginFBuser () {

     /*
      * Note: the key logic of the loginFBuser() function can't really
      * be tested here because they require Facebook cookies.
      */

     $this->fks->startSession('gustav-2010-001', true);
     $r = loginFBuser(new folksoQuery(array(), array(), array()),
                      $this->dbc,
                      $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'Not getting resp obj back from loginFBuser() using '
                      .' already open session');
     $this->assertEqual($r->status, 200,
                        'Already open session should return 200');

     $r2 = loginFBuser(new folksoQuery(array(), array(), array()),
                       $this->dbc, 
                       $this->fks2);
     $this->assertIsA($r, folksoResponse,
                      'Not getting resp obj back from loginFBuser(). '
                     .  'Expecting insufficient data warning (400)');
     $this->assertEqual($r2->status, 400,
                        'Expected 400 as status for insufficient fb data: '
                        . $r2->status);

   }


   function testGetSubscriptions () {
     $this->fks->startSession('gustav-2010-001', true);
     $r = userSubscriptions(new folksoQuery(array(), array(), array()),
                            $this->dbc,
                            $this->fks);
     $this->assertIsA($r, folksoResponse,
                      "No response obj from userSubscriptions");
     $this->assertEqual($r->status, 204,
                        'Expecting 204 status (no subscriptions for gustav): ' 
                        . $r->status);

     $this->fks2->startSession('rambo-2010-001', true);
     $r2 = userSubscriptions(new folksoQuery(array(), array(), array()),
                             $this->dbc,
                             $this->fks2);
     $this->assertIsA($r2, folksoResponse,
                      "No response ob from userSubscriptions with valid subscriptions");
     $this->assertEqual($r2->status, 200,
                         "Should return 200 for valid subscriptions: " . $r2->status);

   }


   function testAddSubscription () {
     $this->fks->startSession('rambo-2010-001', true);
     $r = addSubscription(new folksoQuery(array(), array('folksotag' => 'dyn3'), 
                                          array()),
                          $this->dbc,
                          $this->fks);
     $this->assertIsA($r, folksoResponse,
                      "No response object from addSubscription");
     $this->assertEqual($r->status, 200,
                        "Add subscription should be successful: " . $r->status . " "
                        . $r->statusMessage . " " . $r->errorBody());

                                          
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');


     $check = userSubscriptions(new folksoQuery(array(), array(), array()),
                                $this->dbc2,
                                $this->fks);
     $this->assertPattern('/dyn3/', $check->body(),
                          'Should find freshly subscribed tag here: ' . $check->body());



   }

   function testRemoveSubscription () {
     $this->fks->startSession('rambo-2010-001', true);
     $r = removeSubscription(new folksoQuery(array(), array('folksotag' => 1), array()),
                             $this->dbc,
                             $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'not a response obj');
     $this->assertEqual($r->status, 200,
                       "Expecting 200 on successful subscription removal: "
                       . $r->status . " " . $r->statusMessage . " " .
                        $r->errorBody());

     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');

     $check = userSubscriptions(new folksoQuery(array(), array(), array()),
                                $this->dbc2,
                                $this->fks);
     $check = userSubscriptions(new folksoQuery(array(), array(), array()),
                                $this->dbc2,
                                $this->fks);
     $this->assertNoPattern('/tagone/', $check->body(),
                            'tagone should be gone');

   }
   
}//end class

$test = &new testOfuser();
$test->run(new HtmlReporter());