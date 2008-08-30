<?php
  /**
   * 
   * Functions for the administration of the tag system: creation,
   * modification, deletion of tags; administration tagging interface.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */


class folksoAdmin {

  function  __construct(){

  }


  /**
   * Takes $_SERVER['PHP_AUTH_USER'] and $_SERVER['PHP_AUTH_PW'] and,
   * if present, formats a <script> element containing javascript that
   * assigns these variables to Document.folksonomie.basicAuthUser and
   * Document.folksonomie.basicAuthPasswd.
   *
   * This means sending username/password back and forth a few extra
   * times but doesn't seem much less secure than vanilla Basic Auth.
   *
   */

  public function BasicAuthJS(){
   if ((isset($_SERVER['PHP_AUTH_USER'])) &&
       (isset($_SERVER['PHP_AUTH_PW']))) {
     $return = "<script type='text/javascript'>\n";
     $return .=  
     "if ('folksonomie' in Document) {\n".
     "\tDocument.folksonomie.basicAuthUser = " 
     . $_SERVER['PHP_AUTH_USER'] . ";\n"
     ."\tDocuemnt.folksonomie.basicAuthPasswd = "
     .$_SERVER['PHP_AUTH_PW'].";\n}\n";

     $return .=  
     "else {\n"
     . "\tDocument.folksonomie = new Object();\n"
     ."\tDocument.folksonomie.basicAuthUser = " 
     . $_SERVER['PHP_AUTH_USER'] . ";\n"
     ."\tDocuemnt.folksonomie.basicAuthPasswd = "
     .$_SERVER['PHP_AUTH_PW'].";\n}\n";
     
     $return .=  "</script>\n";
     return $return;
   }
  }
?>