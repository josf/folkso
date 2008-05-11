<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoDataDisplay.php');


class testOffolksoDataDisplay extends  UnitTestCase {

  public $dd;

  function testConstruct () {
    $this->dd = new folksoDataDisplay(
                                array('type' => 'xhtml',
                                      'argsperline' => 2,
                                      'lineformat' => '<a href="XXX">XXX</a>',
                                      'start' => '<ul>',
                                      'end' => '</ul>',
                                      'titleformat' => '<h1>XXX</h1>'),
                                array('type' => 'text',
                                      'argsperline' => 2,
                                      'lineformat' => "XXX :\t XXX",
                                      'start' => "\n",
                                      'end' => "\n",
                                      'titleformat' => "XXX\n------\n"));

    $this->assertTrue($this->dd instanceof folksoDataDisplay);
    $this->assertTrue($this->dd->activate_style('xhtml'));
  }

  function testOutput () {
    $this->assertEqual(
                       $this->dd->line("bob", "slob"),
                       '<a href="bob">slob</a>');

  }

}

$test = &new testOffolksoDataDisplay();
$test->run(new HtmlReporter());

?>