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

function reload (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

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

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_USERAGENT, 'folksoClient');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  $result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

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


?>