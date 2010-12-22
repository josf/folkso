<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include_once('folksoUrl.php');

class testOffolksoUrl extends  UnitTestCase {

   function testBase () {
     $u = new folksoUrl("http://example.org", "Bogus");
     $this->assertIsA($u, 'folksoUrl',
                      "new folksoUrl does not return a folksoUrl object");
     $this->assertEqual(
                        $u->get_url(),
                        'http://example.org',
                        "get_url() returning incorrect data"
                        );
   }

   function testFolksoSpecific () {
     $loc = new folksoFabula();
     
   }
}//end class

$test = &new testOffolksoUrl();
$test->run(new HtmlReporter());