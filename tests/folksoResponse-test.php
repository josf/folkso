<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require_once('dbinit.inc');


class testOffolksoResponse extends  UnitTestCase {

  


  function testBasic () {
    $r = new folksoResponse();
    $this->assertIsA($r, folksoResponse);

    $this->assertIsA($r->loc, folksoLocal,
                     '$r->loc is not a folksoLocal object');
    $this->assertEqual($r->loc->xsl_dir, '/var/lib/php5/xsl/',
                       'xsl_dir var is incorrect: ' . $r->loc->xsl_dir);

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

  function testAdHocHeaders () {
    $r = new folksoResponse();
    $r->setType('xml');
    $r->setOk(200, 'Dandy');
    $r->addHeader('X-Test-Random: Bob');
    $r->prepareHeaders();
    $this->assertTrue(count($r->headers) == 3,
                      'Incorrect number of headers: ' . count($r->headers));
    $this->assertEqual($r->headers[2], 
                       'X-Test-Random: Bob',
                       'Not retrieveing correct ad-hoc header data');

  }


  function testExceptionHandling () {
    $r = new folksoResponse();
    $r->handleDBexception(new dbConnectionException('Something bad happened'));
    $r->prepareHeaders();
    $this->assertEqual($r->status, 500,
                       'dbConnectionException not handled correctly, no 500');

    $r2 = new folksoResponse();
    $r2->handleDBexception(new dbQueryException(234, 'select * from peeps',
                                                'Something horrible just happened'));
    $r2->prepareHeaders();
    $this->assertEqual($r->status, 500,
                       'dbQueryException not producing a 500');
    $this->assertPattern('/peeps/', $r2->body(),
                         'Not getting correct exception information in body for query exception');

    $r3 = new folksoResponse();
    $r3->handleUserException(new badUseridException('Who are you?'));
    $this->assertEqual($r3->status, 403,
                       'Bad userid not returning 403');

  }

  function testDB () {
    test_db_init();

  }
}

$test = &new testOffolksoResponse();
$test->run(new HtmlReporter());
