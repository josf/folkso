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

   function testMax () {
     $st = new folksoRightStore();
     $st->addRight(new folksoRight('folkso', 'redac'));
     $st->addRight(new folksoRight('folkso', 'admin'));

     $this->assertEqual($st->maxRight(), 'admin',
                        'Should get "admin" as max right here, not: ' . $st->maxRight());

     $st2 = new folksoRightStore();
     $this->assertEqual($st2->maxRight(), 'user',
                        "Empty right store should have 'user' as max right, not: "
                        . $st2->maxRight());
   }


   function testRightExists () {
     $st = new folksoRightStore();
     $this->assertTrue($st->rightExists('folkso', 'redac'),
                       "redac exists, this should return true here");
     $this->assertFalse($st->rightExists('folkso', 'florkdork'),
                        "fake right should return false here");
                                         


   }


   function testRightsAsArray() {
     $st = new folksoRightStore();
     $st->addRight(new folksoRight('folkso', 'admin'));
     $arr = $st->rightsAsArray();
     $this->assertTrue(is_array($arr), 'rightsAsArray not returning array');
     $this->assertEqual($arr[0], 'admin', 
                        'Not getting single right in $arr[0]. '
                        . 'expecting "admin", got ' . $arr[0]);
     

   }


   function testRemoveRightsAbove() {
     $st = new folksoRightStore();
     $st->addRight(new folksoRight('folkso', 'admin'));
     print_r($st);
     $this->assertTrue($st->checkRight('folkso', 'admin'));
     $st->removeRightsAbove(1);
     $this->assertFalse($st->checkRight('folkso', 'admin'),
                        "Admin right should be gone now");


     $st->addRight(new folksoRight('folkso', 'redac'));
     $st->removeRightsAbove(0);
     $this->assertFalse($st->checkRight('folkso', 'redac',
                                        "redac should be gone now"));

     $this->assertEqual($st->rightValues['folkso/redac'], 1,
                        "expecting 1 as right value for redac, not: " . 
                        $st->rightValues['folkso/redac']);
     $this->assertEqual($st->rightValues['folkso/admin'], 2,
                        "expecting 2 as right val for admin, not: " . 
                        $st->rightValues['folkso/redac']);

     $st2 = new folksoRightStore();
     $st2->addRight(new folksoRight('folkso', 'admin'));
     $st2->removeRightsAbove('redac');
     $this->assertFalse($st2->checkRight('folkso', 'admin'),
                        "removeRightsAbove failed with string arg");
   }

   function testRemoveRightsAboveDeux () {
     $st = new folksoRightStore();
     $st->addRight(new folksoRight('folkso', 'admin'));
     
     $this->assertEqual(count($st->store), 1,
                        "Store should have one item now, not: " . count($st->store));

   }


   function testSynchDB () {
     $u = new folksoUser($this->dbc);
     $u->userFromUserId('vicktr-2010-001');
     $u->loadAllRights();

     $this->assertTrue($u->checkUserRight('folkso', 'admin'),
                       "Victor does not have admin right to start with");

     $u->rights->removeRight(new folksoRight('folkso', 'admin'));

     try {
       $u->rights->synchDB($u);
     }
     catch (dbException $e) {
       print '<p>' . $e->sqlquery . '</p>';
     }

     $u2 = new folksoUser($this->dbc);
     $u2->userFromUserId('vicktr-2010-001');
     $u2->loadAllRights();
     $this->assertFalse($u2->checkUserRight('folkso', 'admin'),
                        "Expecting false on right check for removed right");

     $u->rights->addRight(new folksoRight('folkso', 'redac'));
     $u->rights->synchDB($u);
     $u3 = new folksoUser($this->dbc);
     $u3->userFromUserId('vicktr-2010-001');
     $u3->loadAllRights();
     $this->assertTrue($u3->checkUserRight('folkso', 'redac'),
                       "expecting true on right check for added right (redac)");

   }

}//end class

$test = &new testOffolksoRights();
$test->run(new HtmlReporter());