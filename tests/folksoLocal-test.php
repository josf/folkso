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

    $this->assertIsA($loc->locDBC(), folksoDBconnect,
                     'Not able to build a dbc object');
    $loc->loginPage = 'loggezmoi.php';
    $loc->setServerUrl('example.com');
    $loc->set_server_web_path('hooha');
    $this->assertEqual($loc->loginPage(),
                       'http://example.com/hooha/loggezmoi.php',
                       'Incorrect loginPage: ' . $loc->loginPage());
    $this->assertEqual($loc->web_url, 'http://example.com',
                       'Incorrect web_url is set: ' . $loc->web_url);

    $loc->loginPage = 'tagorama/loggezmoi.php';
    $this->assertEqual($loc->loginPage(),
                       'http://example.com/tagorama/loggezmoi.php',
                       'Incorrect login url with path in $loc->loginPage');
    $loc->loginPage = '/tagallday/loggezmoi.php';
    $this->assertEqual($loc->loginPage(),
                       'http://example.com/tagallday/loggezmoi.php',
                       'Incorrect login url with absolute path in $loc->loginPage' .
                       $loc->loginPage());

   }
}//end class

$test = &new testOffolksoLocal();
$test->run(new HtmlReporter());