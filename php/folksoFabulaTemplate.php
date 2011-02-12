<?php
  /**
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2011 Gnu Public Licence (GPL)
   * @subpackage utils
   */

/**
 * Templating utility for enclosing content in a standard Fabula container.
 */


class folksoFabulaTemplate {

  private $fablibDir;

  public function __construct () {
    $this->fablibDir =  '/var/www/dom/fabula/commun3/';
  }

  /**
   * @param $html String The html content to be wrapped
   * @param $css Mixed If not null, a filename + path to a css file
   */

  public function wrapContent ($html, $css = null) {
    ob_start();
    require($this->fablibDir . 'head_folkso.php');
    require($this->fablibDir . 'head_dtd.php');
    print "<html>\n<head>";
    require($this->fablibDir . 'head_meta.php');
    require($this->fablibDir . 'head_css.php');
    if ($css) {
      print '<link rel="stylesheet" type="text/css" href="' .  $css . '"></link>';
    }
    print "</head>\n<body>";
    require($this->fablibDir . 'html_start.php');
    print "<div id='colonnes_nouvelles'>";
    print "<div id='colonnes-un'>";
    print $html;
        print "</div>";
          print "</div>";
    include($this->fablibDir . 'foot.php');
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
  }

}