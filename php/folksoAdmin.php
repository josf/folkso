<?php
  /**
   * 
   * Functions for the administration of the tag system: creation,
   * modification, deletion of tags; administration tagging interface.
   *
   * @package Folkso
   * @subpackage webinterface
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

  /**
   * @package Folkso
   */
class folksoAdmin {

  /**
   * JS snippet: create document.folksonomie object if not already
   * present.
   */
  private $folksoCheck;

  function  __construct(){
    $this->folksoCheck = 
       'if (! "folksonomie" in Document) {' . "\n"
    . "\tdocument.folksonomie = new Object();\n"
    . "}\n";
  }


  /**
   * Takes $_SERVER['PHP_AUTH_USER'] and $_SERVER['PHP_AUTH_PW'] and,
   * if present, formats javascript that assigns these variables to
   * document.folksonomie.basicAuthUser and
   * document.folksonomie.basicAuthPasswd. This should be included in
   * a <script> element.
   *
   * This means sending username/password back and forth a few extra
   * times but doesn't seem much less secure than vanilla Basic Auth.
   *
   */

  public function BasicAuthJS(){
   if ((isset($_SERVER['PHP_AUTH_USER'])) &&
       (isset($_SERVER['PHP_AUTH_PW']))) {
     $return =  
     "if ('folksonomie' in Document) {\n".
     "\tdocument.folksonomie.basicAuthUser = '" 
     . $_SERVER['PHP_AUTH_USER'] . "';\n"
     ."\tDocument.folksonomie.basicAuthPasswd = '"
     .$_SERVER['PHP_AUTH_PW']."';\n}\n";

     $return .=  
     "else {\n"
     . "\tdocument.folksonomie = new Object();\n"
     ."\tdocument.folksonomie.basicAuthUser = '" 
     . $_SERVER['PHP_AUTH_USER'] . "';\n"
     ."\tdocument.folksonomie.basicAuthPasswd = '"
     .$_SERVER['PHP_AUTH_PW']."';\n}\n";
     
     return $return;
   }
  }

  /**
   * Wrapper that conditionally adds <script> element. 
   *
   * Convenience function to avoid empty <script> elements when
   * BasicAuthJS() is the only JS in such an element.
   */
  public function BasicAuthJSScript() {
    $auth = $this->BasicAuthJS();
    if (strlen($auth) > 1){
      return 
        "<script type='text/javascript'>\n".
        $auth.
        "</script>\n";

    }
  }

}
?>