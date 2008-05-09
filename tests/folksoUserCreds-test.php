<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');


class testOffolksoUserCreds extends  UnitTestCase {
  public $c;

  function testBasic () {
    $this->c = new folksoUserCreds( 'Digest username="guest", realm="Restricted area", nonce="481e29a45ed2a", uri="/phpexamp.php", response="ef83bc4581ca2ff2b7eefba5f87d4aaf", opaque="cdce8a5c95a1427d74df7acbf41c9ce0", qop=auth, nc=00000001, cnonce="b0c4985b224014dc"', 'GET', 'Restricted area');
    $this->assertTrue($this->c instanceof folksoUserCreds);
  }

  function testDigestParse () {
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
    
    $this->assertTrue($this->c->validateAuth($auth2));
    $this->assertTrue($this->c->checkUsername('folksy'));

  }

  function testDigestCalc () {
    print "A1: ";
    print $this->c->buildDigestA1($this->c->digest_data, 'Restricted area', 'guest');
    print "A2: ";
    print $this->c->buildDigestA2($this->c->digest_data, 'GET');
    print "Together "; 
    $alluh = $this->c->buildDigestResponse($this->c->digest_data, 
                                        $this->c->buildDigestA1(
                                             $this->c->digest_data, 
                                             'Restricted area', 
                                             'guest'),
                                        $this->c->buildDigestA2(
                                             $this->c->digest_data, 
                                             'GET'));
    print "<p>All unhashed: $alluh</p>";
    print "<p>Hashed ". md5($alluh) . "</p>";
    
    print "<h1>Flork</h1>";
    $this->assertFalse(preg_match('/^\d+$/', "123zd43"));
    $this->assertTrue(preg_match('/^\d+$/', "12343"));    
  }
  
}


$test = &new testOffolksoUserCreds();
$test->run(new HtmlReporter());
