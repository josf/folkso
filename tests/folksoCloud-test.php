<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoCloud.php');

class testOffolksoCloud extends  UnitTestCase {

  function testCloud () {
    $loc = new folksoFabula();
    $cl  = new folksoCloud($loc, 20634);
    $this->assertIsA($cl, folksoCloud,
                     'Problem on object construction');
    $this->assertEqual($cl->url, 20634,
                       'url not being assigned correctly.');
    $cl->getData();
    $this->assertEqual($cl->status, 200, 
                       "Cloud request failed");
    $this->assertPattern('/cloud/',
                         $cl->xml,
                         'Cloud retrieved does not contain "cloud"');

    $cl->buildCloud();
    $this->assertIsA($cl->xml_dom,
                     DOMDocument,
                     "Did not successfully build DOMDocument");
    $this->assertPattern("/cloudclass/", $cl->html, 
                         "Cloud does not look like a cloud");

   }
}//end class

$test = &new testOffolksoCloud();
$test->run(new HtmlReporter());