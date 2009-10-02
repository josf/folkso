<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');

class testOffolksoLocal extends  UnitTestCase {

  function testStatic () {
    $loc = new folksoFabula();
    $this->assertEqual(
                       $loc->setServerUrl('example.com'),
                       'http://example.com',
                       'Not adding http:// to url with setServerUrl');

    $this->assertEqual($loc->web_url,
                       'http://example.com',
                       '$web_url is not getting set correctly by setServerUrl');

    $this->assertEqual($loc->setServerUrl('http://bobworld.com'),
                       'http://bobworld.com',
                       'setServerUrl() is mangling url that already contained http://'
                       );
    $this->assertEqual($loc->setServerUrl('bobworld3/'),
                       'http://bobworld3',
                       'setServerUrl() is not removing trailing slash');

   }
}//end class

$test = &new testOffolksoLocal();
$test->run(new HtmlReporter());