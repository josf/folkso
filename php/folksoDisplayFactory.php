<?php

  /**
   * Produce folksoDataDisplay objects conveniently.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */


require_once('folksoDataDisplay.php');

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
                                 $this->addXmlPart($obj, 
                                                   'list', 
                                                   'standard list', 
                                                   'element', 
                                                   'id');
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
                                       'lineformat' => '<li><a id="tagXXX" href="XXX">XXX (XXX)</a></li>',
                                       'argsperline' => 4),
                                 $this->standardTextList(4),
                                 array('type' => 'xml',
                                       'start' => '<?xml version="1.0"?>'.
                                       "\n<taglist>",
                                       'end' => '</taglist>',
                                       'lineformat' => 
                                       "<tag>\n\t<numid>XXX</numid>".
                                       "\n\t<tagnorm>XXX</tagnorm>".
                                       "\n\t<display>XXX</display>".
                                       "\n\t<popularity>XXX</popularity>".
                                       "\n\t<metatag>XXX</metatag>".
                                       "\n</tag>",
                                       'argsperline' => 5));
    return $obj;
  }

  //

  public function ResourceList () {
    $obj = new folksoDataDisplay(
                                 array('type' => 'xhtml',
                                       'start' => '<ul class="resourcelist">',
                                       'end' => '<ul>',
                                       'titleformat' => '<h2 class="resourcelistTitle">XXX</h2>',
                                       'lineformat' => 
                                       "<li>\n" .
                                       "\t<a id=\"resXXX\" href=\"XXX\">XXX</a>\n" .
                                       "</li>",
                                       'argsperline' => 3),
                                 $this->standardTextList(3),
                                 array('type' => 'xml',
                                       'start' => 
                                       '<?xml version="1.0"?>'."\n".
                                       '<resourcelist>',
                                       'end' => '</resourcelist>',
                                       'lineformat' =>
                                       "<resource>\n".
                                       "\t<numid>XXX</numid>\n" .
                                       "\t<url>XXX</url>\n" .
                                       "\t<title>XXX</title>\n" .
                                       '</resource>',
                                       'argsperline' => 3));
    return $obj;
  }

  /**
   * Resource list with lots of data.
   *
   */
  public function FancyResourceList () {
    $obj = new folksoDataDisplay(
                                 array('type' => 'xhtml',
                                       'start' => '<ul class="resourcelist">',
                                       'end' => '<ul>',
                                       'titleformat' => '<h2 class="resourcelistTitle">XXX</h2>',
                                       'lineformat' => 
                                       "<li>\n" .
                                       "\t<a id=\"resXXX\" href=\"XXX\">XXX</a>\n" .
                                       "<span class=\"tags\">XXX</span>" .
                                       "</li>",
                                       'argsperline' => 4),
                                 $this->standardTextList(4),
                                 array('type' => 'xml',
                                       'start' => '<resourcelist>',
                                       'titleformat' => '<tagtitle>XXX</tagtitle>',
                                       'end' => '</resourcelist>',
                                       'lineformat' =>
                                       "<resource>\n".
                                       "\t<numid>XXX</numid>\n" .
                                       "\t<url>XXX</url>\n" .
                                       "\t<title><![CDATA[XXX]]></title>\n" .
                                       "\t<tags>XXX</tags>\n" .
                                       '</resource>',
                                       'argsperline' => 4));
    return $obj;
  }



  public function NoteList () {
    $obj = 
      new folksoDataDisplay(array('type' => 'xml',
                                  'start' => '<?xml version="1.0"?>',
                                  'titleformat' => '<noteList resource="XXX">',
                                  'end' => '</noteList>',
                                  'lineformat' => 
                                  '<note userid="XXX">XXX</note>',
                                  'argsperline' => 3));
    return $obj;
  }
}
?>