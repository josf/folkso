<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoUrlRewriteTag.php');
require('dbinit.inc');

class testOffolksoUrlRewriteTag extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testUrlRewriteTag () {
         $rw   = new folksoUrlRewriteTag();
         $this->assertIsA($rw, folksoUrlRewriteTag,
                               'object creation failed');

   }

   function testSplitting () {
     $req = 'blah/and/blaw';
     $rw = new folksoUrlRewriteTag();
     $this->assertEqual(array('tag','blah', 'and', 'blaw'),
                        $rw->splitReq($req));          
   }

   function testValidation() {
     $req = 'blah/and/blaw';
     $rw = new folksoUrlRewriteTag();
     $this->assertFalse($rw->validateArgs(array('blah', 'and', 'blah')),
                        'Request does not start with "tag", should return false here');

     $req2 = 'tag/and/blaw';
     $this->assertTrue($rw->validateArgs($rw->splitReq($req2)),
                       'Request starting with "tag" should return true');

   }

   function testBuildArray() {
     $rw = new folksoUrlRewriteTag();
     $arr = array();

     $rw->addPair($arr, 'brutal', 'kindness');
     $this->assertEqual($arr['folksobrutal'], 'kindness',
                        'Expecting "kindness" as param value, got: ' . $arr['folksobrutal']);

     $rw->addSingleton($arr, 'hopeless');
     $this->assertEqual($arr['folksohopeless'], 1,
                        'Incorrect value for singleton: ' . $arr['folksohopeless']);

     $qu = $rw->transmute('taggy');
     $this->assertEqual($qu, array('folksotag' => 'taggy'),
                        'Two arg transmute ("tag/taggy") should give "folksotag" => "taggy"');



   }

   function testSinglesAndDoubles () {
     $rw = new folksoUrlRewriteTag();
     $this->assertTrue($rw->isSingleVal('related'),
                       '"related" param should return true as single val');
     $this->assertFalse($rw->isSingleVal('feed'),
                        '"feed" param should return false as single val');
     $this->assertTrue($rw->isDoubleVal('feed'),
                       '"feed" should return true as double val');

     $this->assertTrue(in_array('feed', $rw->pairs),
                       '"feed" is not in $rw->pairs');
     $this->assertFalse($rw->isDoubleVal('related'),
                       '"related" should return false as double val');

     /** special **/
     $this->assertTrue($rw->isSpecialVal('merge'),
                       '"merge" should return true as special val');

   }

   function testArgParse () {
     $rw = new folksoUrlRewriteTag();
     $arr = array();
     $parsed = $rw->argParse($arr, 
                             array('tag', 'bogus', 'related', 'feed', 'atom',
                                   'merge', 'tagtwo'));
     $this->assertEqual($parsed,
                        array('folksotag' => 'bogus',
                              'folksorelated' => '1',
                              'folksofeed' => 'atom',
                              'folksomerge' => '1',
                              'folksotarget' => 'tagtwo'), 
                        'Output array does not match ');
     $arr2 = array();
     $par2 = $rw->argParse($arr2, array('tag', 'bogus'));
     $this->assertEqual($par2,
                        array('folksotag' => 'bogus'));


   }
}//end class

$test = &new testOffolksoUrlRewriteTag();
$test->run(new HtmlReporter());