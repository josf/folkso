<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoTagLink.php');

class testOffolksoTagLink extends  UnitTestCase {

  function testBase () {
    $loc = new folksoFabula();
    $loc->setServerUrl('bobworld.com');

    $tl = new folksoTagLink('poesie', $loc);
    $this->assertIsA($tl, 
                     folksoTagLink,
                     'Problem with folksoTagLink object creation');
    $this->assertEqual($tl->identifier, 'poesie',
                       'Problem with setting $identifier');
    $this->assertEqual(
                       $tl->getLink(),
                       'http://bobworld.com/tag/poesie',
                       'Incorrect url creation: ' . $tl->getLink());

   }
}//end class

$test = &new testOffolksoTagLink();
$test->run(new HtmlReporter());