<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoUserCreds.php');


class testOffolksoClient extends  UnitTestCase {
  public $c;

  function testBasic () {
    $this->c = new folksoUserCreds('xyz');
    $this->assertTrue($this->c instanceof folksoUserCreds);
  }

  function testAccesses () {
    $this->assertFalse($this->c->userid);  // returns nothing if check_digest() hasn't run yet
    $this->c->check_digest();
    $this->assertEqual($this->c->userid, 99999); // BOGUS
    $this->assertTrue($this->c->admin_access());

  }
}


$test = &new testOffolksoClient();
$test->run(new HtmlReporter());
