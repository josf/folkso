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
}//end class

$test = &new testOffolksoUserAdmin();
$test->run(new HtmlReporter());