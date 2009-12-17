<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('/var/www/user.php');
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
     $this->fks->startSession('gustav-2009-001', true);
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

   function testGetUserResByTag() {
     $this->fks->startSession('gustav-2009-001', true);
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
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');
     $this->assertPattern('/example\.com/', $r->body(),
                          'Not finding url in response');
     /** not implemented yet **/
     $this->assertPattern('/tagone/', $r->body(),
                          'Not finding tag in response: ' . $r->body());
     $r2 = getUserResByTag(new folksoQuery(array(),
                                           array('folksotag' => 'emacs'),
                                           array()),
                           $this->dbc2,
                           $this->fks);
     $this->assertIsA($r2, folksoResponse,
                      'Not getting fkResponse this time');
     $this->assertEqual($r2->status, 404,
                        'Unknown tag should throw 404');

   }
}//end class

$test = &new testOfuser();
$test->run(new HtmlReporter());