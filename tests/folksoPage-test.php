<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoPage.php');
require_once "folksoSession.php";
require_once "folksoUser.php";
require_once "dbinit.inc";

class testOffolksoPage extends  UnitTestCase {

  function setUp () {
    test_db_init();
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude',
                                       'testy', 'testostonomie');
  }

  function testBasicTests () {
    $page = new folksoPage();
    $this->assertTrue($page instanceof folksoPage);
    $this->assertPattern('/folksoPage-test.php/', $page->url);
    $this->assertFalse($page->keyword_list());

  }


  function testKeyword_list () {
    $page = new folksoPage(26663);
    $this->assertIsA($page, folksoPage, "Page creation from number failed");
    $this->assertEqual($page->url, 26663, 
                       "Page url property  incorrect w/ number url");
    
    /*    Tests disabled because they depend on external data 
          $this->assertTrue(is_string($page->keyword_list()));
          $this->assertTrue(is_string($page->basic_cloud())); */
  }

  function testMetas () {
    $page = new folksoPage(26663);

    /**
       These should be tested in the PageDataMeta tests.

    $page->pdata->prepareMetaData();
    $this->assertIsA($page->pdata->mt, folksoPageDataMeta);
    $this->assertTrue((is_string($page->keyword_list()) &&
                       (strlen($page->keyword_list()) > 10)));
    $this->assertPattern('/<meta/', $page->meta_keywords());
    $this->assertTrue((is_string($page->meta_keywords()) &&
                       (strlen($page->meta_keywords()) > 10)));
    $this->assertTrue(is_string($page->DC_description_list()));
    $this->assertIsA($page->pdata->ptags, folksoPageTags);
    $this->assertIsA($page->pdata->ptags, folksoTagdata); // inheritance works!

    **/

    $p2 = new folksoPage('http://fabula.org/actu_meta_test.php');
    $this->assertIsA($p2, folksoPage, "Failed to create fKPage object from real url");
    $p3 = new folksoPage(38065);
    $this->assertIsA($p3, folksoPage, "Failed to create fkPage object from number");
    /*    $p2->pdata->prepareMetaData();
          $p3->pdata->prepareMetaData();
    $this->assertIsA($p2->pdata, folksoPageData, 
                     "Failed to create pageData object using prepareMetaData");
    $this->assertIsA($p2->pdata->mt, folksoPageDataMeta);
    $this->assertIsA($p2->pdata->ptags, folksoPageTags);
    $this->assertTrue($p2->pdata->ptags->is_valid(), 
                      "Valid request for page tags?");
    $this->assertIsA($p2->pdata->ptags->xml_DOM(), DOMDocument);
    $this->assertTrue(is_string($p2->pdata->ptags->xml));

    // url


    // id
    //    $this->assertTrue(is_string($p2->pdata->mt->meta_textlist()));
    $this->assertTrue($p3->pdata->ptags->is_valid(), 
                      "Valid request for page tags?");
    $this->assertEqual($p3->pdata->ptags->xml,
                       $p3->pdata->ptags->xml);

    $this->assertTrue(is_string($p2->keyword_list()), 
                                "keyword_list() returns string?");
    $this->assertEqual($p2->keyword_list(), $p3->keyword_list());
        $this->assertEqual($p2->pdata->mt->meta_keywords(),
                       $p3->pdata->mt->meta_keywords());
    $this->assertTrue(strlen($p2->keyword_list()) > 5);
    $this->assertPattern('/<meta/', $p2->meta_keywords());
    **/
  }

  function testTagRes () {
    $page = new folksoPage(5775);
    $this->assertIsA($page, folksoPage);
    $this->assertEqual($page->url, 5775);



           $html = $page->TagResources();
    $this->assertIsA($page->tr, folksoTagRes);

    /**     Should be tested in TagResources-test
    $this->assertTrue($page->tr->is_valid());
    $this->assertTrue(strlen($html) > 100);
    **/
  }


  function testClouds () {
    $page = new folksoPage('fabula.org/actualites/article20927.php');
    $this->assertIsA($page, folksoPage);

    /** tests disabled: depend on external data
    $cloud = $page->basic_cloud();
    $this->assertTrue(is_string($cloud));
    $this->assertTrue(strlen($cloud) > 200);

    
    $page->cloud_reset();
    $this->assertFalse($page->pdata->cloud);

    $cl2 = $page->popularity_cloud();
    $this->assertTrue(is_string($cl2));
    $this->assertTrue(strlen($cl2) > 200);
    $this->assertTrue($page->pdata->cloud->is_valid());

    $page->cloud_reset();
    $this->assertFalse($page->pdata->cloud);
    $this->assertNotA($page->pdata->cloud, folksoCloud);
    $this->assertNull($page->pdata->cloud);
    $this->assertNull($page->pdata->cloud->xml);

    $cl3 = $page->date_cloud();
    $this->assertTrue(is_string($cl3));
    $this->assertTrue(strlen($cl3) > 200);
    $this->assertTrue($page->pdata->cloud->is_valid());

    $this->assertNotEqual($cloud, $cl2);    
    $this->assertNotEqual($cl2, $cl3);
    */

  }

  function testEan13 () {
    $page = new folksoPage(38065);
    $this->assertIsA($page, folksoPage);
    $dc = $page->ean13_dc_identifier();
    /*    $this->assertTrue(is_string($dc));
          $this->assertPattern('/<meta\s+/', $dc);*/

    $page2 = new folksoPage('http://fabula.org/actu_meta_test.php');
    $dc2 = $page2->ean13_dc_identifier();
    /*    $this->assertTrue(is_string($dc2));
          $this->assertPattern('/<meta\s+/', $dc2);*/

  }

  function testRelatedTags () {
    $page = new folksoPage(38065);

    /** External data dependant
    $this->assertTrue(is_string($page->RelatedTags(8170)),
                      'RelatedTags method does not return string');
    $this->assertPattern('/cloudclass/',
                         $page->RelatedTags(8170),
                         'RelatedTags does not produce a tag cloud');

    **/
  }



  function testSessionInit () {
    $s = new folksoSession($this->dbc);
    $s->startSession('gustav-2010-001', true);
    $this->assertTrue($s->validateSid($s->sessionId),
                      'Just checking, but s->sessionId is not a valid Sid');
    $this->assertTrue($s->status(),
                      "Test session should be valid");

    $page = new folksoPage('http://bogus.example.com', $s->sessionId);

    $this->assertIsA($page, folksoPage,
                     "Object construction with session id failed");
    $this->assertIsA($page->session, folksoSession,
                     "No session object created on fkPage construction");
    $this->assertIsA($page->dbc, folksoDBconnect,
                     'Not initialzing fkDBconnect object on fkPage construction');

    $this->assertTrue($page->session->sessionId,
                      'Missing session id in fkPage  obj, should be: '
                      . $s->sessionId);
    $this->assertTrue($page->session->validateSid('2a81c4a49e11feb87bbcb6d2550d07173e4762d9427f117ac730b79e653d6bda'),
                      'Real sid fails when validated from within fkPage obj');

    $this->assertEqual($page->session->sessionId,
                       $s->sessionId,
                       'fkPage does not have the same session id that we gave it');

    $this->assertIsA($page->user, folksoUser,
                     'Not initializing fkUser obj at $page->user');
    $this->assertEqual($page->user->userid, 'gustav-2010-001',
                       "Not retrieving the correct userid");

    $this->assertTrue($page->loginInit(),
                      'Valid session should return true with loginCheck');

  }

  function testJsOutput() {
    $page = new folksoPage('http://bogus.yow');
    $this->assertFalse($page->jsHolder(""), "Empty content should return false");
    $this->assertTrue(is_string($page->jsHolder("stuff")), "Should return string");
    $this->assertTrue(is_string($page->jsHolder(array("stuff", "more stuff"))),
                      "even with array, should output string");
    /*    $this->assertPattern('/CDATA/', $page->jsHolder("stuff"),
          "Not finding CDATA in jsHolder output");*/
    $this->assertPattern('{^<script[^>]+>}', $page->jsHolder("stuff"),
                         'Not finding script tags in withjsHolder():' . $page->jsHolder("stuff"));
  }

  function testJsLoginStatus () {
    
    $page = new folksoPage('http://bogus.yow');

    $this->assertPattern('/false/', $page->fKjsLoginState(),
                         "Not getting 'false' as login state for uninitialized session");

    $s = new folksoSession($this->dbc);
    $s->startSession('gustav-2010-001', true);
    $page2 = new folksoPage('http://bogus.example.com', $s->sessionId);
    $this->assertTrue($page2->loginCheck(),
                      "Should be logged in here");
    
    $this->assertPattern('/true/', $page2->fKjsLoginState(),
                         "Not getting 'true' as login state for initialized session");

  }

  function testJsFBinfo () {
    $loc = new folksoFabula();
    $loc->snippets = array('facebookApiKey' => '1234567890',
                           'facebookXdmChannel' => '/xd_receiver.htm');
    $pg = new folksoPage('http://bogus.yow', null, $loc);

    $this->assertPattern('/1234567890/', $pg->fbJsVars(),
                         'javascript output should include api key');
    $this->assertPattern('/xd_receiver.htm/', $pg->fbJsVars(),
                         'javascript output should include facebook cross channel domain url');

  }

  /**
   * Stuff to determine the final state of the db.
   */

  function testEnd () {
    $s = new folksoSession($this->dbc);
    $s->startSession('gustav-2010-001', true);
  }

}//end class


$test = new testOffolksoPage();
$test->run(new HtmlReporter());


/**

assertTrue($x) 	Fail if $x is false
assertFalse($x) 	Fail if $x is true
assertNull($x) 	Fail if $x is set
assertNotNull($x) 	Fail if $x not set
assertIsA($x, $t) 	Fail if $x is not the class or type $t
assertNotA($x, $t) 	Fail if $x is of the class or type $t
assertEqual($x, $y) 	Fail if $x == $y is false
assertNotEqual($x, $y) 	Fail if $x == $y is true
assertWithinMargin($x, $y, $m) 	Fail if abs($x - $y) < $m is false
assertOutsideMargin($x, $y, $m) 	Fail if abs($x - $y) < $m is true
assertIdentical($x, $y) 	Fail if $x == $y is false or a type mismatch
assertNotIdentical($x, $y) 	Fail if $x == $y is true and types match
assertReference($x, $y) 	Fail unless $x and $y are the same variable
assertClone($x, $y) 	Fail unless $x and $y are identical copies
assertPattern($p, $x) 	Fail unless the regex $p matches $x
assertNoPattern($p, $x) 	Fail if the regex $p matches $x
expectError($x) 	Swallows any upcoming matching error
assert($e) 	Fail on failed expectation.html object $e

 **/
?>