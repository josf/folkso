<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoTagLink.php');

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
                       'http://bobworld.com/tag.php/folksotag=poesie&folksodatatype=html',
                       'Incorrect url creation: ' . $tl->getLink());

   }
}//end class

$test = &new testOffolksoTagLink();
$test->run(new HtmlReporter());