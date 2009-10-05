<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoRelatedTags.php');

class testOffolksoRelatedTags extends  UnitTestCase {

   function testBase () {
     $rt = new folksoRelatedTags(new folksoFabula(),
                                 'tagone');
     $this->assertIsA($rt, folksoRelatedTags,
                      'Object creation failing');

     $this->assertIsA($rt, folksoTagdata,
                      'Problem with inheritance');

     $r2 = new folksoRelatedTags(new folksoFabula(),
                                 8170);

     $this->assertIsA($r2, folksoRelatedTags,
                      'Object creation failing with numeric tag');

     $r2->getData();
     $this->assertTrue(is_string($r2->xml),
                       'No string in $r2->xml after request');
     $this->assertPattern('/cloudclass/', 
                          $r2->xml,
                          'Not finding "cloudclass" in $r2->xml');
     $r2->buildCloud();
     $this->assertEqual($r2->xml, $r2->html,
                        '$r2->html does not contain the same thing as ->xml. (This might be a bad test for future dev');
   }
}//end class

$test = &new testOffolksoRelatedTags();
$test->run(new HtmlReporter());