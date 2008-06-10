<?php

  /**
   * Produce folksoDataDisplay objects conveniently.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */


require_once('/usr/local/www/apache22/lib/jf/fk/folksoDataDisplay.php');

class folksoDisplayFactory {

  public function basicLinkList () {
    $obj = new folksoDataDisplay(
                                 array('type' => 'xhtml',
                                        'start' => '<ul>',
                                        'end' => '</ul>',
                                       'titleformat' => '<h2>XXX</h2>',
                                        'lineformat' => '<li><a href="XXX">XXX</a></li>',
                                        'argsperline' => 2),
                                 $this->standardTextList(2));
                                 $this->addXmlPart($obj, 'list', 'standard list', 'element', 'id');
    return $obj;
  }

  /**
   * If $itemattr is not supplied, reverts to a 1-item list.
   */

  public function addXmlPart (folksoDataDisplay $obj, 
                              $listtype,  // element for list container
                              $listname,  // name attribute of entire list
                              $itemtype,  
                              $itemattr = '') { // the attribute: href, id, etc. 

    $argsperline = 2;
    $lineformat = '';
    if (strlen($itemattr) > 0) {
      $lineformat = "<$itemtype $itemattr=\"XXX\">XXX</$itemtype>";
    }
    else {
      $lineformat = "<$itemtype>XXX</$itemtype>";
      $argsperline = 1;
    }
    
    
    $xml =  array('type' => 'xml',
                 'start' => "<$listtype name=\"$listname\">",
                 'end' => "</$listtype>",
                  'lineformat' => $lineformat,
                 'argsperline' => 2);
    $obj->datastyles[] = $xml;
    return $xml;

  }

  public function standardTextList ($argnum) {
    $txt = array('type' => 'text',
                 'start' => "\n",
                 'end' => "\n",
                 'titleformat' => 'XXX',
                 'argsperline' => $argnum);

    $line = '';
    while ($argnum > 0) {
      $line = $line . ' XXX';
      --$argnum;
    }
    $line = $line . "\n";
    $txt['lineformat'] = $line;
    return $txt;
  }
  
  public function singleElementList () {
    $obj = new folksoDataDisplay(array('type' => 'xhtml',
                                       'start' => '<ul>',
                                       'end' => '</ul>',
                                       'argsperline' => 1,
                                       'titleformat' => '<h2>XXX</h2>',
                                       'lineformat' => '<li>XXX</li>'),
                                 $this->standardTextList(1));
    $this->addXmlPart($obj, 
                      'list',
                      'list',
                      'element');
    return $obj;
  }
  public function cloud () {
    $obj = new folksoDataDisplay( array('type' => 'xhtml',
                                       'start' => '<ul>',
                                       'end' => '</ul>',
                                       'argsperline' => 3,
                                       'lineformat' => '<li class="cloudXXX"><a href="XXX">XXX</a></li>',
                                        'titleformat' => '<h3>XXX</h3>'));
    return $obj;


  }

  public function TagList () {
    $obj = new folksoDataDisplay(
                                 array('type' => 'xhtml',
                                       'start' => '<ul class="taglist">',
                                       'end' => '</ul>',
                                       'titleformat' => '<h2>XXX</h2>',
                                       'lineformat' => '<li><a id="tagXXX" href="XXX">XXX</a></li>',
                                       'argsperline' => 3),
                                 $this->standardTextList(3),
                                 array('type' => 'xml',
                                       'start' => '<taglist>',
                                       'end' => '</taglist>',
                                       'lineformat' => 
                                       "<tag>\n\t<numid>XXX</numid>".
                                       "\n\t<tagnorm>XXX</tagnorm>".
                                       "\n\t<display>XXX</display>".
                                       "\n</tag>",
                                       'argsperline' => 3));
    return $obj;
  }
}
?>