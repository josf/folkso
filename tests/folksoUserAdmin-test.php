<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoUserAdmin.php');
require('dbinit.inc');

class testOffolksoUserAdmin extends  UnitTestCase {
 
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
     $this->fks         = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks2        = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks3        = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks4        = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
  }

   function testBasic () {
     $this->fks->startSession('vicktr-2010-001', true);
     $r = getUsersByQuery(new folksoQuery(array(), array(), 
                                          array("folksosearch" => "default: Michel")),
                          $this->dbc, $this->fks);
     $this->assertIsA($r, folksoResponse,
                      "ob prob");
     $this->assertEqual($r->status, 200,
                        "Not getting 200 for getUsersByQuery: " . $r->status . " "
                        . $r->statusMessage . " " . $r->errorBody());

   }

   function testXml () {
     $this->fks->startSession('vicktr-2010-001', true);
     $r = getUsersByQuery(new folksoQuery(array(), array(), 
                                          array("folksosearch" => "default: Marcel")),
                          $this->dbc, $this->fks);
     $this->assertEqual($r->status, 200,
                        "Not getting 200 for 'default: Marcel'" . $r->status);

     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       "xml parsing failure");
     $this->assertPattern('/<userRights>/', $r->body(),
                          "Missing <userRights> tag in xml output");

   }


   function testMultiQuery () {
     $this->fks->startSession('vicktr-2010-001', true);
     $r = getUsersByQuery(new folksoQuery(array(), array(), 
                                          array("folksosearch" => "default: Marcel lname: Flaubert")),
                          $this->dbc, $this->fks);
     $this->assertEqual($r->status, 200,
                        "Not getting 200 for combined query (marcel or flaubert) " .
                        $r->status);
     print "<pre><code>" . htmlspecialchars($r->body()) . "</code></pre>";
     $this->assertPattern('/Gustave/', $r->body(),
                          "Not finding Gustave Flaubert");
   }

   function testEmptyResponse() {
     $this->fks->startSession('vicktr-2010-001', true);
     $r = getUsersByQuery(new folksoQuery(array(), array(), 
                                          array("folksosearch" 
                                                => "nothing")),
                          $this->dbc, $this->fks);

     $this->assertEqual($r->status, 204,
                        "Expecting 204 for empty result, got: " 
                        . $r->status);


   }

   function testTagCount () {
     $this->fks->startSession('vicktr-2010-001', true);
     $r = getUsersByQuery(new folksoQuery(array(), array(), 
                                          array('folksosearch' => 'flaubert')),
                          $this->dbc, $this->fks);

     $this->assertEqual($r->status, 200,
                        "Testing the test. Should get 200 here, not: " . $r->status);
     print $r->debug;

   }


   function testRightStuff () {
     $this->fks->startSession('vicktr-2010-001', true);
     
     // pretest
     $gus = new folksoUser($this->dbc);
     $gus->userFromUserId('gustav-2010-001');
     $this->assertFalse($gus->checkUserRight('folkso', 'admin'),
                        'gustav should not have admin rights yet. pb with test?');

     $r = newMaxRight(new folksoQuery(array(), array(),
                                      array("folksonewright" => "admin",
                                            "folksouser" => "gustav-2010-001")),
                      $this->dbc, $this->fks);
     $this->assertIsA($r, folksoResponse,
                      "Ob prob");
     $this->assertEqual($r->status, 200,
                        "Should have 200 status, not: " . $r->status . " " .
                        $r->statusMessage . " " . $r->error_body);

     print $r->statusMessage;
     $gus2 = new folksoUser($this->dbc2);
     $gus2->userFromUserId('gustav-2010-001');
     $gus2->loadAllRights();
     print_r($gus2->rights);

     $this->assertTrue($gus2->checkUserRight('folkso', 'admin'),
                        'gustav should have admin rights now');
     
     $r2 = newMaxRight(new folksoQuery(array(), array(),
                                      array("folksonewright" => "user",
                                            "folksouser" => "gustav-2010-001")),
                      $this->dbc, $this->fks);

     $this->assertEqual($r2->status, 200,
                        "Expecting 200 status, not: " . $r2->status . " " 
                        . $r2->statusMessage . " " . $r2->error_body);

     $gus3 = new folksoUser($this->dbc2);
     $gus3->userFromUserId('gustav-2010-001');
     $gus3->loadAllRights();
     $this->assertFalse($gus3->checkUserRight('folkso', 'redac'),
                        'gustav should not have redac rights, only user');
     print_r($gus3->rights);


   }

   function testRecentKeyword () {
     $this->fks->startSession('vicktr-2010-001', true);
     $r = getUsersByQuery(new folksoQuery(array(), array(), 
                                          array('folksosearch' => 'recent:')),
                          $this->dbc, $this->fks);

     $this->assertEqual($r->status, 200,
                        "Testing the test. Should get 200 here, not: " 
                        . $r->status . " " . $r->error_body);

   }
}//end class

$test = &new testOffolksoUserAdmin();
$test->run(new HtmlReporter());