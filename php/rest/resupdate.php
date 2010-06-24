<?php


  /**
   * Web interface for updating basic information about a resource by
   * repolling that resource.
   *
   * These functions are separated from resource.php because they use
   * the Curl libraries that do not usually need to be called.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

require_once('folksoUrl.php');
require_once('folksoQuery.php');

$srv = new folksoServer(array('methods' => array('POST'),
                              'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res')),
                                        'reload'));


$srv->Respond();
/**
 * Given a resource, this function fetches that resource and updates
 * its status in the database if anything has changed, in particular
 * the title field.
 *
 * If the resource is no longer available (returns 404), the resource
 * is removed. Is this too radical?
 *
 */
function reload (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  /** check initial url **/
  $url = '';
  if (is_numeric($q->res)){
    $url = $i->url_from_id($q->res);
    if ($url = 0){ // no corresponding url
      header('HTTP/1.1 404 Resource not found.');
      print 
        "The numeric id " . $q->res . " that was provided does not correspond "
        ." to an existing  resource. Perhaps the resource has been deleted.";
      return;
    }
  }
  else{
    $url = $q->res;
    if (! $i->resourcep($url)) {
      header('HTTP/1.1 404 Resource not found');
      print 
        "The url provided (" . $q->res . ") was not found in the database. "
        . "It must be added before it can be modified.";
      return;
    }
  }

  /** do request **/
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_USERAGENT, 'folksoClient');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  $result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  /** react to request results **/
  $rq = new folksoResupQuery();
  switch ($result_code){
  case '404':
    $i->query($rq->resremove($url));
    header('http/1.1 200 Deleted');
    print "Removed the resource $url from the system.";
    return;
    break;
  case '200':
    $i->query($rq->resmodtitle($url, $newtitle));

  }

}


/**
 * Given a page of html, returns the contents of the <title> element.
 *
 * Not using the DOM because we only will ever need the title (I think).  
 *
 * @param $html string Raw html from the reload request.
 * @return string The contents of the title element, or false if not found.
 */
function getTitle ($html) {
  if (preg_match('{<title>(.*)</title>}i',
                 $html,
                 $matches)) {
    return $matches[0];
  }
  else {
    return false;
  }
}

?>