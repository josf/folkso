<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoResourceLink.php');

class testOffolksoResourceLink extends  UnitTestCase {

  function testBase () {
    $loc = new folksoFabula();
    $loc->setServerUrl('bobworld.com');

    $rl = new folksoResourceLink(234, $loc);
    $this->assertIsA($rl, 'folksoResourceLink', 
                     "Problem with folksoResourceLink object creation");
    $this->assertEqual($rl->identifier, 234, 'Problem with setting $identifier');

    $this->assertEqual(
                       $rl->getLink(),
                       'http://bobworld.com/resource.php/folksores=234&folksodatatype=html', 
                       'Incorrect url creation: '.$rl->getLink());
 
   }
}//end class

$test = &new testOffolksoResourceLink();
$test->run(new HtmlReporter());