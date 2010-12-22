<?php

require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoQuery.php');


class testOffolksoQuery extends  UnitTestCase {

  public $qu;
  public $qu2;
  public $qubad;
  public $qumulti;


  function testConstructor () {

    $this->qu = new folksoQuery($_SERVER, $_GET, $_POST);
    $this->assertTrue($this->qu instanceof folksoQuery);
  }

  function testMethod () {
    $this->assertEqual(strtolower($this->qu->method()), 'get');
  }

  function testTagStripping () {
    $q = new folksoQuery(array(), array(), array('bob' => 'stuff'));
    $this->assertFalse($q->is_param('bob'),
                       "invalid parameter 'bob' should not appear here");
    $q2 = new folksoQuery(array(), array(), array('folksotags' => 'hey <p>there</p> dude'));
    $this->assertEqual($q2->get_param('tags'), 'hey there dude',
                       'tags should be removed here, getting: ' . $q2->get_param('tags'));
    $q3 = new folksoQuery(array(), array(), array('folksocv' => 'hey <p>there</p> dude'));
    $this->assertEqual($q3->get_param('cv'), 'hey <p>there</p> dude',
                       'tags should not be removed here: ' . $q3->get_param('cv'));

    $q4 = new folksoQuery(array(), array(), 
                          array('folksocv' => '<ol><li>Illegal</li></ol>'));
    $this->assertEqual($q4->get_param('cv'), '<li>Illegal</li>',
                       'Illegal OL tag should be removed. Expecting "<li>Illegal</li>", 
got: ' . $q4->get_param('cv'));
   

    $valid_html = '<ul><li><em>What!</em></li><li>Huh</li></ul>';
    $q5 = new folksoQuery(array(), array(),
                          array('folksocv' => $valid_html));
    $this->assertEqual($q5->get_param('cv'), $valid_html,
                       'Complicated tags failing, getting: ' . $q5->get_param('cv'));
                       
  }


  function testGetStuff () {
    $this->qu2 = new folksoQuery( $_SERVER, array('folksoCommand' => 'obey'), $_POST);
    $this->assertTrue($this->qu2 instanceof folksoQuery);
    $this->assertTrue($this->qu2->is_param('folksoCommand'));
    $this->assertEqual($this->qu2->get_param('folksoCommand'), 'obey');
    $this->assertEqual($this->qu2->get_param('Command'), 'obey');
    $this->assertTrue($this->qu2->is_single_param('folksoCommand'));
  }

  function testCheckingBadData () { 
    $this->qubad = new folksoQuery( $_SERVER,
                                    array('folksoCommand' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                                          'varboDeal'    => 'Hey guys'),
                                    array());

    $this->assertTrue(is_string($this->qubad->get_param('folksoCommand')));
    $this->assertTrue(strlen($this->qubad->get_param('folksoCommand') < 301));
    $this->assertFalse($this->qubad->is_param('varboDeal'));

  }

  function testMultiPartField () {
    $this->qumulti = new folksoQuery( $_SERVER,
                                      array('folksoArgs001' => 'this',
                                            'folksoArgs002' => 'that',
                                            'folksoArgs003' => 'somethingelse'),
                                      array());

    $this->assertTrue($this->qumulti->is_multiple_param('folksoArgs'));
    $this->assertFalse($this->qumulti->is_single_param('folksoArgs'));

  }

  function testDataFuncs () {
    $data = new folksoQuery( $_SERVER,
                             array('folksoNumber' => '12343',
                                   'folksoNotNumber' => '123edf3',
                                   'folksoNothing' => ''),
                             array());
    $this->assertTrue($data->is_number('Number'));
    $this->assertFalse($data->is_number('NotNumber'));
    $this->assertFalse($data->is_number('Nothing'));

  }

  function testBasic () {
    $url='http://example.com/1';
    $cuki = new folksoQuery(array(), array('folksores' => $url), array());
    $this->assertEqual($url, $cuki->res, "Not getting correct res out of object");

  }

  function testMethodStuff () {
    $q = new folksoQuery(array('REQUEST_METHOD' => 'GET'),
                         array('folksores' => 1234),
                         array());
    $this->assertIsA($q, 'folksoQuery',
                     'Problem with object creation');
    $this->assertEqual($q->method(), 'get',
                       'Reporting incorrect method');
    $this->assertFalse($q->is_write_method,
                       'is_write_method should report false on GET');
    $qq = new folksoQuery(array('REQUEST_METHOD' => 'POST'),
                          array('folksostuff' => 'hoohoa'),
                          array());
    $this->assertEqual($qq->method(), 'post',
                       'Reporting incorrect method, should be post');
    $this->assertTrue($qq->is_write_method(),
                      'Is write method should say true on POST');


  }


  function testXsltOutput () {
    $q = new folksoQuery(array('HTTP_ACCEPT' => 'application/atom+xml'),
                         array('folksotag' => 'tagone'),
                         array());
    $this->assertIsA($q, 'folksoQuery',
                     'Problem with object creation using atom HTTP_ACCEPT');
    $this->assertEqual($q->content_type(), 'xml',
                       'application/atom+xml not giving "xml" as $q->content_type()'); 
    $this->assertEqual($q->subType(), 'atom',
                       '$q->subType not getting set to "atom": ' . $q->subType());
  }


  function testParseContentType () {
    $q = new folksoQuery(array(),
                         array(),
                         array());
    $this->assertIsA($q, 'folksoQuery', 'Object creation failed');
    
    $this->assertEqual($q->parse_content_type('text/xml'),
                       'xml',
                       'text/xml not parsing to "xml"');
    $this->assertEqual($q->parse_content_type('application/atom+xml'),
                       'xml',
                       'application/atom+xml not parsing to "xml"');
    $this->assertEqual($q->subType(), 'atom',
                       'parse_content_not setting $q->subType to "atom"');
  }

  function testContentTypeFromParams () {
    $q = new folksoQuery(array(),
                         array('folksofeed' => 'atom'),
                         array());
    $this->assertIsA($q, 'folksoQuery', 'Object creation failed');
    $this->assertEqual($q->fk_content_type, 'xml',
                       'fk_content_type not getting set with atom query');
    $this->assertEqual($q->subType(), 'atom',
                       'subType not set with atom query');

    $q2 = new folksoQuery(array('HTTP_ACCEPT' => 
                                'application/xml, application/xhtml+xml, text/html'),
                          array('folksores' => 'bogus.com'),
                          array());
    $q2->parse_content_type($q2->req_content_type);
    $this->assertEqual($q2->content_type(), 'html',
                       'multiple accept header should parse to html when html present: '
                       . $q2->content_type());

    $q3 = new folksoQuery(array('HTTP_ACCEPT' =>
                                'application/xml;q=0.7, application/xhtml+xml;q=0.9'),
                          array('folksores' => 'bogus.com'),
                          array());
    $q3->parse_content_type($q3->req_content_type);
    $this->assertEqual($q3->chosenContentType, 
                       'application/xhtml+xml',
                       'chosenContentType should be "application/xhtml+xml here, not: '
                       . $q3->chosenContentType);

    $quark = $q->buildAcceptArray('application/xml, application/xhtml+xml, text/html');
    $this->assertEqual(count($quark), 2,
                       'There should be 2 content type arrays here, not: ' .
                       count($quark));
    $this->assertEqual(count($quark['xml']), 1,
                       'We should have a content type in the xml array not:' . 
                       count($quark['xml']));
    $this->assertEqual(count($quark['html']), 2,
                       'We should have 2 content types in the html array, not: ' .
                       count($quark['html']));

    $quirk = $q->buildAcceptArray('application/xml;q=0.6, application/xhtml+xml;q=0.9,'
                                  . 'text/html;q=0.2');
    $this->assertEqual(count($quirk), 2,
                       'Using q params: should have 2 datatypes here, not: ' .
                       count($quirk));

    $this->assertEqual(count($quirk['html']), 2,
                       'Should have two html content types here, not: ' .
                       count($quirk['html']));
    $this->assertEqual($quirk['xml'][0]->weight(), 0.6,
                       'Weight for xml should be 0.6 here');
    $this->assertEqual($quirk['html'][0]->weight(), 0.9,
                       'Weight for html should be 0.9 here');
    $this->assertEqual($quirk['html'][1]->weight(), 0.2,
                       'Weight for 2nd html should be 0.2 here');

    $quatom = $q->buildAcceptArray('application/atom+xml');
    $this->assertEqual($quatom['xml'][0]->subType, 'atom');
    $this->assertEqual($quatom['xml'][0]->type_part, 'atom+xml');


  }


  function testUtil () {
    $q = new folksoQuery(array(), array('folksores' => 'bogus.xml'), array());
    $arr = array(array('name' => 'hee',
                       'weight' => 5),
                 array('name' => 'hoo', 
                       'weight' => 10),
                 array('name' => 'haa'));
    usort($arr, array($q, 'contentTypeComp'));
    $this->assertEqual($arr[0]['name'], 'hoo',
                       'Hoo should be first');
    $this->assertEqual($arr[1]['name'], 'hee',
                       'Hee should be 2nd');
    $this->assertEqual($arr[2]['name'], 'haa',
                       'Haa should still be last');


  }

  function testSelectTypeFromArray() {
    $q = new folksoQuery(array(), array(), array());
    $xml = new folksoQueryAcceptType('application/xml;q=0.9', 0);
    $atom = new folksoQueryAcceptType('application/atom+xml;q=0.5', 1);
    $rss = new folksoQueryAcceptType('application/rss+xml', 2);

    $best = $q->selectTypeFromArray(array($xml, $atom, $rss));
    $this->assertEqual($best->accept(), 'application/rss+xml',
                       'Should have sorted to rss feed in this example: ' 
                       . $best->accept());

    $this->assertEqual($atom->weight(), 0.5,
                       'incorrect wieght for atom, expecting 0.5, got: ' . 
                       $atom->weight());
  }

  function testChooseContentType () {
    $q = new folksoQuery(array(), array(), array());
    $xml = new folksoQueryAcceptType('application/xml;q=0.5', 1);
    $atom = new folksoQueryAcceptType('application/atom+xml', 2);
    $rss = new folksoQueryAcceptType('application/rss+xml;q=0.9', 3);
    $html = new folksoQueryAcceptType('text/html', 4);

    $best1 = $q->chooseContentType(array('xml' => array($xml, $atom),
                                         'html' => array($html)));
    $this->assertIsA($best1, 'folksoQueryAcceptType',
                     'chooseContentType should return a folksoQueryAcceptType object');
    $this->assertEqual($best1->accept(), 'application/atom+xml',
                       'Should prefer atom here, not: ' . $best1->accept());



    $text = new folksoQueryAcceptType('text/text', 0);
    $html2 = new folksoQueryAcceptType('text/html', 1);

    $best2 = $q->chooseContentType(array('text' => array($text),
                                         'html' => array($html2)));
    $this->assertIsA($best2, 'folksoQueryAcceptType',
                     'chooseContentType does not return a fkQaT obj');
    /*    $this->assertEqual($best2->accept(), 'text/text',
          'Should prefer text/text here');*/

    $xml2 = new folksoQueryAcceptType('application/xml', 0);
    $apph = new folksoQueryAcceptType('application/xhtml+xml', 1);
    $html3 = new folksoQueryAcceptType('text/html', 2);
    $text2 = new folksoQueryAcceptType('text/text', 3);

    $best3 = $q->chooseContentType(array('xml' => array($xml2),
                                         'html' => array($apph, $html3),
                                         'text' => array($text2)));
    $this->assertEqual($best3->accept(), 'application/xhtml+xml',
                       'Expecting application/xhtml+xml, not: ' . $best3->accept());

    $html4 = new folksoQueryAcceptType('text/html;q=0.9', 5);

    $best4 = $q->chooseContentType(array('xml' => array($xml2),
                                         'html' => array($apph, $html3, $html4),
                                         'text' => array($text2)));
    $this->assertEqual($best4->accept(), 'application/xhtml+xml',
                       'Expecting application/xhtml+xml, not: ' . $best4->accept());

    $atom = new folksoQueryAcceptType('application/atom+xml', 0);
    $bestAtom = $q->chooseContentType(array('xml' => array($atom)));
    $this->assertEqual($bestAtom->accept(), 'application/atom+xml',
                       'Should get atom here: ' . $bestAtom->accept());
    $fkt = $bestAtom->fkType();
    $this->assertEqual($bestAtom->subType, 'atom',
                       'subtype not set: ' . $bestAtom->subType);


    $q5 = new folksoQuery(array(), array(), array());
    $firef = $q5->buildAcceptArray('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
    $fireChoose = $q5->chooseContentType($firef);
    $this->assertIsA($fireChoose, 'folksoQueryAcceptType',
                     'Firefox accept: chooseContentType not returning fkQaT obj');
    $this->assertEqual($fireChoose->accept(), 'text/html',
                       'Real firefox 3.6 accept header should evaluate to text/html, not: ' 
                       . $fireChoose->accept());
  }

  function testAcceptTypeClass () {
    $ac = new folksoQueryAcceptType('application/xml', 0);
    $this->assertEqual($ac->raw, 'application/xml', 'constructor args not showing up');
    $this->assertEqual($ac->type_part(), 'xml',
                       'type_part not parsing correctly: ' . $ac->type_part());
    $this->assertEqual($ac->accept(), 'application/xml',
                       '$ac->accept incorrect on simple accept: ' . $ac->accept());
    $this->assertEqual($ac->weight(), 1,
                       'Weight not defaulting to 1');

    $this->assertEqual($ac->fkType(), 'xml',
                       'Should get "xml" as fkType here, not: ' . $ac->fkType());

    $bc = new folksoQueryAcceptType('application/xml;q=0.8', 0);
    $this->assertEqual($bc->accept(), 'application/xml',
                       'Incorrect ->accept with param: ' . $bc->accept());
    $this->assertEqual($bc->type_part(), 'xml',
                       'type_part incorrect with q param: ' . $bc->type_part());
    $this->assertEqual($bc->weight(), 0.8,
                       'Weight incorrect: ' . $bc->weight);
    $this->assertEqual($bc->fkType(), 'xml',
                       'Should get xml as fkType here, not: ' . $bc->fkType());

    $nothing = new folksoQueryAcceptType('image/jpeg', 0);
    $this->assertFalse($nothing->fkType(),
                       'Unsupported content type should give undefined fkType: ' .
                       $nothing->fkType());

    $at = new folksoQueryAcceptType('application/atom+xml', 0);
    $this->assertEqual($at->accept(), 'application/atom+xml',
                       '$at->accept incorrect for atom content type: ' . $at->accept());
      
    $this->assertEqual($at->fkType(), 'xml',
                      'Should get xml as fkType for atom: ' . $at->fkType());
    $this->assertEqual($at->subType, 'atom',
                       'Should get atom as subtype here: ' . $at->subType);

    $rs = new folksoQueryAcceptType('application/rss+xml', 0);
    $this->assertEqual($rs->fkType(), 'xml',
                       'Should get xml as fkType for rss: ' . $rs->fkType());
    $this->assertEqual($rs->subType, 'rss',
                       'Should get rss as subtype here: '. $rs->subType);

    $xh = new folksoQueryAcceptType('application/xhtml+xml', 0);
    $this->assertEqual($xh->fkType(), 'html',
                       'application/xhtml+xml should return html');

    $tr = new folksoQueryAcceptType('text/rss', 0);
    $this->assertEqual($tr->fkType(), 'xml',
                       'text/rss should invoke xml fkType: ' . $tr->fkType());
    $this->assertEqual($tr->subType, 'rss',
                       'text/rss should give rss subtype: ' . $tr->subType);

  }

  



  function message($message) {

  }

}//end class
$test = new testOffolksoQuery();
$test->run(new HtmlReporter());