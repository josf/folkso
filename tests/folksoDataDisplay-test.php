<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoDataDisplay.php');
require_once('folksoDisplayFactory.php');

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
                                      'lineformat' => "-- XXX : XXX",
                                      'start' => "\n",
                                      'end' => "\n",
                                      'titleformat' => "XXX\n------\n"));
    $this->assertFalse($this->dd->type);
    $this->assertTrue($this->dd instanceof folksoDataDisplay);
    $this->assertTrue($this->dd->activate_style('xhtml'));
    $this->assertEqual($this->dd->type, 'xhtml');
  }

  function testOutput () {
    $this->assertEqual(
                       $this->dd->line("bob", "slob"),
                       '<a href="bob">slob</a>');
    $thing = array('xhtml' => 'Randy',
                   'default' => 'Slacker');
    $this->assertEqual($this->dd->line("bob", $thing), 
                       '<a href="bob">Randy</a>');
    $this->assertTrue($this->dd->activate_style('text'));
    print $this->dd->type;
    $this->assertEqual($this->dd->type, 'text');
    $this->assertEqual($this->dd->line("bob", $thing),
                      '-- bob : Slacker');
    $this->assertEqual($this->dd->line("bob", array("default" => "something")), 
                       '-- bob : something');
  }
}

$test = &new testOffolksoDataDisplay();
$test->run(new HtmlReporter());

?>