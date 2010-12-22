<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoUrlRewriteResource.php');
require_once('dbinit.inc');

class testOffolksoUrlRewriteResource extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testUrlRewriteResource () {
         $rw   = new folksoUrlRewriteResource();
         $this->assertIsA($rw, 'folksoUrlRewriteResource',
                               'object creation failed');
   }

   /*
    * Most of the functions are already tested in
    * folksoUrlRewriteTag-test.php. We are concentrating on the
    * correspondances here.
    */

   function testCorrespondances () {
     $rw = new folksoUrlRewriteResource();

     /** isHead **/
     $this->assertEqual($rw->transmute(333),
                        array('folksores' => 333),
                        'isHead conversion not working');

     $this->assertEqual($rw->transmute('333/clouduri'),
                        array('folksores' => 333,
                              'folksoclouduri' => '1'),
                        'tagCloudLocalPop conversion not working');

     print_r($rw->transmute('333/clouduri'));

     $this->assertEqual($rw->transmute('333/cloud'),
                        array('folksores' => '333',
                              'folksocloud' => '1'),
                        'tagCloudLocalPop conversion not working (cloud alias)');

     $this->assertEqual($rw->transmute('333/ean13list'),
                        array('folksores' => '333',
                              'folksoean13list' => '1'),
                        'resEans conversion not working');

     $this->assertEqual($rw->transmute('333/tag/tagone/delete'),
                        array('folksores' => '333',
                              'folksotag' => 'tagone',
                              'folksodelete' => '1'),
                        'unTag not converting properly');

     $this->assertEqual($rw->transmute('333/tag/tagone'),
                        array('folksores' => '333',
                              'folksotag' => 'tagone'),
                        'tagResource not converting properly');
     $this->assertEqual($rw->transmute('333/visit'),
                        array('folksores' => '333',
                              'folksovisit' => '1'),
                        'visitPage not converting properly');
     $this->assertEqual($rw->transmute('333/newtitle/Stuff'),
                        array('folksores' => '333',
                              'folksonewtitle' => 'Stuff'),
                        'addResource not converting properly');
     $this->assertEqual($rw->transmute('333/ean13/13'),
                        array('folksores' => '333',
                              'folksoean13' => '13'),
                        'assocEan13 not converting nicely');
     $this->assertEqual($rw->transmute('333/oldean13/13/newean13/12'),
                        array('folksores' => '333',
                              'folksooldean13' => '13',
                              'folksonewean13' => '12'),
                        'modifyEan13 not converting like it otter');
     $this->assertEqual($rw->transmute('333/ean13/13/delete'),
                        array('folksores' => '333',
                              'folksoean13' => '13',
                              'folksodelete' => '1'),
                        'deleteEan13 not converting nicely');
   }

}//end class

$test = &new testOffolksoUrlRewriteResource();
$test->run(new HtmlReporter());