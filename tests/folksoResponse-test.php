<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');


class testOffolksoResponse extends  UnitTestCase {




  function testBasic () {
    $r = new folksoResponse();
    $this->assertIsA($r, folksoResponse);


  }
  function testBodyFuncs () {
    $r = new folksoResponse();
    $r->t('Hello');
    $this->assertTrue(is_string($r->body()));
    $this->assertEqual($r->body(), 'Hello', 
                       "Not returning body() correctly");
    $r->t('there');
    $this->assertEqual($r->body(), "Hello\nthere", 
                       "Newline insertion fail");

  }

  function testErrorFuncs () {
    $r = new folksoResponse();
    $r->setError(404);
    $this->assertEqual($r->status, 404, "Error status problem.");
    $this->assertEqual($r->statusMessage,
                       'Not Found',
                       "Default error status message not working.");

    $r2 = new folksoResponse();
    $r2->setError(404, "something is wrong");
    $this->assertEqual($r2->statusMessage, "something is wrong",
                       "Problem setting error status message");
    $r3 = new folksoResponse();
    $r3->setError(404);
    $this->assertEqual($r3->statusMessage, "Not Found",
                       "Problem setting default 404 error status message:". $r3->statusMessage.":");

  }

  function testContentType () {
    $r = new folksoResponse();
    $r->setType('xml');
    $this->assertEqual($r->contentType(), 'Content-Type: text/xml', 
                       'XML content type not correctly determined');
    $r2 = new folksoResponse();
    $this->assertEqual($r2->contentType(),
                       'Content-Type: text/text',
                       'Default content type not correctly selected');
    $r3 = new folksoResponse();
    $r3->setType('html');
    $this->assertEqual($r3->contentType(),
                       'Content-Type: text/html',
                       'HTML content type not correctly determined');
  }


  function testHeader () {
    $r = new folksoResponse();
    $r->setType('xml');
    $r->setOk(200, 'Fine');
    $r->prepareHeaders();
    $this->assertTrue(is_array($r->headers),
                      '$this->headers is not an array');

    $this->assertPattern('/HTTP/',
                         $r->headers[0], 
                         'HTTP not in first line of headers');

  }
}

$test = &new testOffolksoResponse();
$test->run(new HtmlReporter());
