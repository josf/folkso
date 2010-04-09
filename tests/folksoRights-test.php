<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoRights.php');
require_once('dbinit.inc');

class testOffolksoRights extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testRights () {
     $dr   = new folksoRight('folkso', 'delete');
     $this->assertIsA($dr, folksoRight,
                      'object creation failed');
    
     $this->assertEqual($dr->getRight(),
                        'delete',
                        'Not retreiving right name correctly');
     $this->assertEqual($dr->getService(),
                        'folkso',
                        'Not retreiving service name correctly');
     $this->assertPattern('/<service>/', $dr->asXmlFrag(),
                          "asXmlFrag does not contain service tag");

   }

   function testStore () {
     $st = new folksoRightStore();
     $this->assertIsA($st, folksoRightStore,
                      'object creation failed for folksoRightStore');
    $dr   = new folksoRight('folkso', 'delete');
     $this->assertIsA($dr, folksoRight,
                      'object creation failed');
     $this->assertFalse($st->hasRights(),
                        'hasRights not reporting empty store');

     $st->addRight($dr);
     $this->assertTrue($st->hasRights(),
                       'hasRights not acknowledging new right');

     $this->assertTrue($st->checkRight('folkso', 'delete'),
                     'checkRight not reporting new right');
     $this->assertIsA($st->getRight('folkso', 'delete'),
                      folksoRight,
                      'getRight does not return fkRight object');
   }

   function testAliases () {
     $st = new folksoRightStore();
     $dr = new folksoRight('folkso', 'redac');
     $st->addRight($dr);
     $this->assertTrue($st->checkRight('folkso', 'create'),
                       'redac to create alias does not work');
     $this->assertTrue(is_array($st->aliases['folkso/create']),
                       'folkso/create not found in alias table');


   }
   function testXml () {
     $st = new folksoRightStore();
     $st->addRight(new folksoRight('folkso', 'redac'));
     $st->addRight(new folksoRight('folkso', 'admin'));
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($st->xmlRights()),
                       "Failed to load as XML");
     $this->assertPattern('/<userRights>/', $st->xmlRights(),
                          "Did not find userRights tag");

   }

}//end class

$test = &new testOffolksoRights();
$test->run(new HtmlReporter());