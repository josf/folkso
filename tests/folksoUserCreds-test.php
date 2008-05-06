<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoUserCreds.php');


class testOffolksoUserCreds extends  UnitTestCase {
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

  function testDigest () {
    $str = 'Digest username="guest", realm="Restricted area", nonce="481e29a45ed2a", uri="/phpexamp.php", response="ef83bc4581ca2ff2b7eefba5f87d4aaf", opaque="cdce8a5c95a1427d74df7acbf41c9ce0", qop=auth, nc=00000001, cnonce="b0c4985b224014dc"';

    $this->assertTrue(is_array($this->c->http_digest_parse($str)));

    $auth = $this->c->http_digest_parse($str);
    print var_dump($auth);
    $this->assertTrue(count($auth) > 5);
    $this->assertEqual($auth['username'], 'guest');
    $this->assertEqual($auth['realm'], 'Restricted area');
    $this->assertEqual($auth['nonce'], '481e29a45ed2a');
    $this->assertEqual($auth['uri'], '/phpexamp.php');
    $this->assertEqual($auth['response'], 'ef83bc4581ca2ff2b7eefba5f87d4aaf');
    $this->assertEqual($auth['opaque'], 'cdce8a5c95a1427d74df7acbf41c9ce0');
    $this->assertEqual($auth['qop'], 'auth');
    $this->assertEqual($auth['nc'], '00000001');
    $this->assertEqual($auth['cnonce'], 'b0c4985b224014dc');


    // Same but with single quotes...
    $str2 = "'Digest username='guest', realm='Restricted area', nonce='481e29a45ed2a', uri='/phpexamp.php', response='ef83bc4581ca2ff2b7eefba5f87d4aaf', opaque='cdce8a5c95a1427d74df7acbf41c9ce0', qop=auth, nc=00000001, cnonce='b0c4985b224014dc'";

    $auth2 = $this->c->http_digest_parse($str2);
    $this->assertTrue(count($auth) > 5);
    $this->assertEqual($auth2['username'], 'guest');
    $this->assertEqual($auth2['realm'], 'Restricted area');
    $this->assertEqual($auth2['nonce'], '481e29a45ed2a');
    $this->assertEqual($auth2['uri'], '/phpexamp.php');
    $this->assertEqual($auth2['response'], 'ef83bc4581ca2ff2b7eefba5f87d4aaf');
    $this->assertEqual($auth2['opaque'], 'cdce8a5c95a1427d74df7acbf41c9ce0');
    $this->assertEqual($auth2['qop'], 'auth');
    $this->assertEqual($auth2['nc'], '00000001');
    $this->assertEqual($auth2['cnonce'], 'b0c4985b224014dc');

  }
}


$test = &new testOffolksoUserCreds();
$test->run(new HtmlReporter());
