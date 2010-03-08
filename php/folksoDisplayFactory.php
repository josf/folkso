<?php

  /**
   * Produce folksoDataDisplay objects conveniently.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */


require_once('folksoDataDisplay.php');
require_once('folksoDataJson.php');

  /**
   * @package Folkso
   */
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
                 'start' => 
                  "<?xml version=\"1.0\"?>\n<$listtype name=\"$listname\">",
                 'end' => "</$listtype>",
                  'lineformat' => $lineformat,
                 'argsperline' => 2);
    $obj->datastyles[] = $xml;
    return $xml;

  }

  /**
   * Returns a new JSON data display object. This is just a wrapper
   * function for the folksoDataJson constructor. 
   *
   * @param $args The keys that will be used by the line() method
   */
   public function json ($args) {
     if (is_array($args)) {
         return new folksoDataJson($args);
       }
       else {
         return new folksoDataJson(func_get_args());
       }
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
    $txt['lineformat'] = $line . "\n";
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
                                       'lineformat' => 
                                        '<li class="cloudXXX"><a href="XXX">XXX</a></li>',
                                        'titleformat' => '<h3>XXX</h3>'),
                                  array('type' => 'xml',
                                        'start' => 
                                        '<?xml version="1.0"?>',
                                        'titleformat' =>
                                        "\n<tagcloud resource=\"XXX\">\n",
                                        'lineformat' =>
                                        "<tag>\n"
                                        . "<numid>XXX</numid>\n"
                                        . "<display>XXX</display>\n"
                                        . '<link href="XXX" rel="alternate"/>'
                                        . "<weight>XXX</weight>\n"
                                        . "<tagnorm>XXX</tagnorm>"
                                        . "</tag>", 
                                        'end' => '</tagcloud>',
                                        'argsperline' => 5
                                        ));
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
                                       "\n<taglist>\n",
                                       'end' => '</taglist>',
                                       
                                       'titleformat' => "<tagtitle>XXX</tagtitle>\n",
                                       
                                       'lineformat' => 
                                       "<tag>\n\t<numid>XXX</numid>".
                                       "\n\t<tagnorm>XXX</tagnorm>".
                                       "\n\t<display>XXX</display>".
                                       "\n\t<popularity>XXX</popularity>".
                                       "\n\t<metatag>XXX</metatag>".
                                       "\n</tag>\n",
                                       'argsperline' => 5));
    return $obj;
  }


  /**
   * Simpler than TagList: norm, display, count
   */
   public function simpleTagList ($default_style = null) {
     $obj = new folksoDataDisplay(
                                  array('type' => 'xml',
                                        'start' => '<?xml version="1.0"?>' .
                                        "\n<taglist>\n",
                                        'end' => '</taglist>',
                                        'lineformat' => 
                                        "<tag>\n\t"
                                        ."<numid>>XXX</numid>\n\t"
                                        ."<tagnorm>XXX</tagnorm>\n\t"
                                        ."<link>XXX</link>\n\t"
                                        ."<display>XXX</display>\n\t"
                                        ."<count>XXX</count>\n"
                                        ."</tag>\n",
                                        'argsperline' => 5));
     if ($default_style) {
       $obj->activate_style($default_style);
     }
     return $obj;
   }
  
  //

  public function ResourceList ($default_style = null) {
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
    if ($default_style) {
      $obj->activate_style($default_style);
    }
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
                                       'start' => 
                                       "<?xml version=\"1.0\"?>\n".
                                       "<tag>\n",
                                       'titleformat' => 
                                       "<tagtitle>XXX</tagtitle>\n<resourcelist>\n",
                                       'end' => "\t</resourcelist>\n</tag>",
                                       'lineformat' =>
                                       "\t<resource>\n".
                                       "\t\t<numid>XXX</numid>\n" .
                                       "\t\t<url>XXX</url>\n" .
                                       "\t\t<title><![CDATA[XXX]]></title>\n" .
                                       "\t\t<tagdate>XXX</tagdate>\n" .
                                       "\t\t<tags>XXX</tags>\n" .
                                       "\t</resource>\n",
                                       'argsperline' => 5));
    return $obj;
  }

  public function NoteList () {
    $obj = 
      new folksoDataDisplay(array('type' => 'xml',
                                  'start' => '<?xml version="1.0"?>',
                                  'titleformat' => '<noteList resource="XXX">',
                                  'end' => '</noteList>',
                                  'lineformat' => 
                                  '<note userid="XXX" noteid="XXX">XXX</note>',
                                  'argsperline' => 3));
    return $obj;
  }
  public function MetatagList () {
    $obj = new folksoDataDisplay(array('type' => 'text',
                                       'start' => 'Metatags',
                                       'titleformat' => "XXX\n",
                                       'end' => "\n\n",
                                       'lineformat' => "XXX : XXX\n",
                                       'argsperline' => 2));
    $this->addXmlPart($obj, 
                      'metataglist',
                      'metatags',
                      'meta',
                      'numid');
    return $obj;
    /** $lineformat = "<meta numid=\"XXX\">XXX</meta>"; **/
  }


  public function associatedEan13resources () {

    return new folksoDataDisplay(
                                 array('type' => 'xml',
                                       'start' => 
                                       "<?xml version=\"1.0\"?>\n"
                                       ."<ean13>\n",
                                       'titleformat' => '',
                                       'end' => "</ean13>\n",
                                       'lineformat' =>
                                       "\t<resource>\n"
                                       ."\t\t<numid>XXX</numid>\n" 
                                       ."\t\t<url>XXX</url>\n" 
                                       . "\t\t<title><![CDATA[XXX]]></title>\n" 
                                       . "\t</resource>\n",
                                       'argsperline' => 3));
    

  }


/**
 * To be used on successful tagging of a resource, to send back the
 * new link.
 */
 public function tageventResponse () {
   return new folksoDataDisplay(
                                array('type' => 'xml',
                                      'start' =>
                                      "<?xml version=\"1.0\"?>\n"
                                      ."<tagevent>\n",
                                      'titleformat' => '',
                                      'end' => "</tagevent>\n",
                                      'lineformat' =>
                                      "\t<tag>XXX</tag>\n"
                                      ."\t<resource>XXX</resource>\n",
                                      'argsperline' => 2));
 }

}
?>