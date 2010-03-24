<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008-2010 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */


  /** Since DB connection etc. are used both in the <head> and <body>,
 all the initialization stuff goes here. **/

require_once "/var/www/dom/fabula/commun3/head_folkso.php"; 

require_once('folksoDBconnect.php');
require_once('folksoDBinteract.php');
require_once('folksoFabula.php');
require_once('folksoAdmin.php');
require_once('folksoSession.php');

$loc = new folksoFabula();

$dbc = new folksoDBconnect($loc->db_server,
                           $loc->db_user,
                           $loc->db_password,
                           $loc->db_database_name);
/*
 * We could just do a simple connect here, but it seems safer to
 * connect through our standard connection system.
 */
$i = new folksoDBinteract($dbc);
$fk = new folksoAdmin();


$login_page = 'http://www.fabula.org/tags/admin/adminlogin.php';
$sorry = 'http://www.fabula.org/tags/admin/sorry.php';

if (! $fks->status()) {
  header('Location: ' . $login_page);
  exit();
}

$user = $fks->userSession($sid);
if (! $user->checkUserRight('folkso', 'redac')) {
  header('Location: ' . $sorry);
  exit;
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="fr-FR"/>
    <title>Taggons des pages</title>


<link rel="stylesheet" 
  type="text/css" 
  href="/tags/css/jquery-ui-1.8.custom.css" media="screen">
</link>

  <script type="text/javascript" 
          src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>


<script 
  type="text/javascript"
  src="/tags/js/jquery-ui-1.8.custom.min.js">
</script>


    <script type="text/javascript" src="js/jquery.autocomplete.js">
    </script>

   <script type="text/javascript">
<?php


// basic auth info as js variables
print $fk->BasicAuthJS();

// paths to Document.folksonomie
print $loc->WebPathJS();
print 
  "\n if (! document.hasOwnProperty(\"folksonomie\")) { \n"
  . "\t document.folksonomie = new Object();\n"
  . "}\n\n";

print "document.folksonomie.metatag_autocomplete_list = ";
print fk_metatag_simple_list($i);
print ';';
?>
</script>
<script type="text/javascript" src="js/folkso.js">
</script>
<script type="text/javascript" src="js/resedit.js">
</script>

<link 
        rel="stylesheet" type="text/css" 
        href="http://www.fabula.org/commun3/template.css" 
        media="screen">
    </link>
    <link 
        rel="stylesheet" type="text/css"
        href="jquery.autocomplete.css"
        media="screen">
    </link>
  <link 
      rel="stylesheet" type="text/css"
      href="jquery.mcdropdown.css"
      media="screen">
  </link>
  <link
      rel="stylesheet" type="text/css" 
      href="editres.css"
      media="screen">
  </link>
  </head>

  <body>
  <div id="superscreen"> <!-- screen that appears with dialogue boxes -->
  <div id="superinfobox">
     <a id="closess" href="#">Fermer</a>
  </div>
  </div> <!-- end of superscreen -->
    <h1>Edition et taggage des ressources</h1>

    <h3>Sélection de l'URL</h3> <!-- ' -->
    <form action="editresources.php" method="get">
      <p>
        Saisir les premières lettres de l'url (après 'fabula.org') <!-- '-->
        <input type="text" size="5" maxlength="15" name="initial"
        <?php
        if ($_GET['initial']) { // automatically initialize with previous value
          print 'value="'. substr($_GET['initial'], 0, 15) . '"';
        }
        ?>
        >
        </input>
      </p>
      <p>
        Saisir une séquence de caractères à chercher dans les
        url. (Peut être combiné avec les caractères initiaux).
        <input type="text" size="30" maxlength="200" name="sequence"
        <?php
        if ($_GET['sequence']) { // automatically initialize with previous value
          print 'value="'. substr($_GET['sequence'], 0, 30) . '"';
        }
        ?>
        >
        </input>
      </p>
<div id="checklists">
<div id="tagstatus">
        <h3>Afficher :</h3>
      <p>
        <input type="radio" name="tagged" value="notags" checked="checked">
          </input>Ressources sans tags<br/>
        <input type="radio" name="tagged" value="tags">
        </input>Ressources déjà taggées<br/>
        <input type="radio" name="tagged" value="all">
        </input>Ressources taggées et non-taggées
      </p>
</div>
<div id="sortselect">
      <h3>Trier par :</h3>
      <p>
        <input type="radio" name="orderby" value="whenindexeddesc" 
        <?php
        print radioOrderbyDefault($_GET['orderby'], "whenindexeddesc", true);
       ?> >
        </input><em>Date de la première indexation à partir de la plus récente</em><br/> 

        <input type="radio" name="orderby" value="whenindexedasc"         
       <?php
          print radioOrderbyDefault($_GET['orderby'], "whenindexedasc", false);
       ?>>
        </input><em>Date de la première indexation à partir de la plus ancienne</em><br/> 

        <input type="radio" name="orderby" value="popularitydesc"<?php
          print radioOrderbyDefault($_GET['orderby'], "popularitydesc", false);
       ?>>
        </input><em>Popularité descendante</em> 
        (par nombre de visites, commençant par la resource la plus visitée)<br/>

        <input type="radio" name="orderby" value="popularityasc"<?php
          print radioOrderbyDefault($_GET['orderby'], "popularityasc", false);
       ?>>
        </input><em>Popularité croissante</em> 
        (par nombre de visites, commençant par la resource la moins visitée)<br/>
        
        <input type="radio" name="orderby" value="tagdate" <?php
          print radioOrderbyDefault($_GET['orderby'], "tagdate", false);
       ?>>
        </input><em>Date du dernier tag</em><br/>
      </p>
</div>
</div>
      <p>
        <input type="submit" name="submit">
        </input>
      </p>
    </form>

      <h3>Tagger les ressources sélectionnées</h3>
      <div id="grouptagging">
    <p>
      <span class="infohead">Tag</span>
      <input type="text" size="30" class="tagbox" id="grouptagbox" maxlength="100">
      </input> <span class="infohead"> Metatag </span>
      <select size="1" id="groupmetatagbox">  <?php
  $metatagOptions = metatagSelectBoxOptions($i);
  print $metatagOptions;
?>
      </select>
      <a href="#" id="grouptagvalidate" class="control">Valider</a> <span> </span> 
    </p>
    <p>

      <a href="#" id="groupchecksall" class="control">Cocher tous</a>
      <a href="#" id="cleargroupchecks" class="control">Décocher tous</a>
    </p>
      </div>
<?php

print $i->db_error();

if ($i->db_error()) {
  print "connection problem";
  die($i->error_info());
}

$i->query('SET group_concat_max_len = 3072');

$initial = substr($_GET['initial'], 0, 15);
$sequence = substr($_GET['sequence'], 0, 50);
$tagged = $_GET['tagged'];
$begin = $_GET['begin'];
$orderby = $_GET['orderby'];

/* tagdate makes no sense if 'tags' is not selected */
if ($orderby == 'tagdate') {
  $tagged = 'tags';
}

$rescount_sql = 
  'SELECT COUNT(*) AS rows '.
  ' FROM resource r ' .
  ' WHERE '.
  buildWhere($initial, $sequence, $tagged, $i);

$i->query($rescount_sql);

$total_results = $i->first_val('rows');

$fksql = "SELECT ".
  "r.id AS id, r.uri_raw AS url, \n".
  "r.title AS title, r.visited AS visits, \n".
  "r.added_timestamp AS added, \n".
  "date_format(r.added_timestamp, '%e %M %Y à %T') as display_date, \n".
  "(SELECT COUNT(resource_id) \n". 
  "        FROM tagevent te \n".
  "        WHERE te.resource_id = r.id) \n". 
  "AS total_tagevs, \n".

  "(SELECT \n".
  "     GROUP_CONCAT(DISTINCT tag.tagdisplay \n".
  "                  ORDER BY tag.tagdisplay \n". 
  "                  SEPARATOR '\" \"')  \n". 
  "    FROM tagevent tee \n".
  "    JOIN tag ON tag.id = tee.tag_id \n".
  "    WHERE tee.resource_id = r.id \n".
  "    GROUP BY tee.resource_id) AS thesetags, \n".

  "(select \n"
  ."    group_concat(distinct e.ean13 \n"
  ."                 separator ', ') \n"
  ."    from ean13 e \n"
  ."    where e.resource_id = r.id) as theseeans,  \n"

  ."(SELECT COUNT(*) FROM note n \n\t".
  " WHERE n.resource_id = r.id) AS notecount, \n".

  "(select \n".
  " max(tagtime) \n\t".
  " from tagevent te4 \n\t".
  " where te4.resource_id = r.id) as last_tagged \n" .

  "FROM resource r \n".
  "WHERE \n";

$fksql .= buildWhere( $initial, $sequence, $tagged, $i);

$fksql .= orderBySql($orderby);
$fksql .= " LIMIT 50\n";

if ((is_numeric($begin)) &&
    ($begin >= 50)) {
  $fksql .= " OFFSET $begin";
}

print 
  '<p><a href="#" id="showsql">Voir requête</a> (pour devel seulement)</p>' .
  '<div id="sql">' .
  '<p>'. str_replace("\n", '<br/>', $fksql).'</p>' . 
'<p>' . str_replace("\n", '<br/>', $rescount_sql) . '</p>'.
  '</div>';

$i->query($fksql);

if ($i->result_status == 'DBERR') {
  die( $i->error_info());
}

nextPrevious($begin, $i->result->num_rows, $total_results);

$begin_with_current_results = $begin + $i->result->num_rows;
$begin_display =  $begin ? $begin : 1;

print "<p>Reponses  $begin_display  à
    $begin_with_current_results  sur  $total_results. </p>";


print '<ul class="editresources">';
while ($row = $i->result->fetch_object()) {
  print 
  '<li id="res' . $row->id .'"';

if ($row->total_tagevs > 0) {
  print 'class="resitem tagged"';
}
else {
  print 'class="resitem nottagged"';
}


print '> ';

  print 
    '<p class="principal">'.
    '<a class="restitle" href="' . $row->url . '" target="fk" >' . $row->title . "</a>\n".
    '<span class="tagev_count">Taggé ' . $row->total_tagevs . " fois</span>\n".
    '<br/>'.
    '<a class="resurl" href="' . $row->url . '" target="fk" >' . $row->url . "</a>\n".

    "</p>\n" .
    '<p><span class="currenttags">Tags : ';

  if (strlen($row->thesetags) > 0) {
    print '"';
    print $row->thesetags;
    print '"';
  }

  print 
    "</span> \n"
    . "<span class='lasttaggage'>Date du dernier tag: " . $row->last_tagged . "</span></p>\n".

      "<p class='currentean13'>EAN13/ISBN: "
      . "<span class='currentean13'>". $row->theseeans . '</span>'
      . "</p>";


  print 
    '<p>'
    .'<span class="infohead">Ajouté le </span><span class="added">' . datesToFrench($row->display_date) ."</span>"
    ."</p>\n".
    '<p>'.
    '<input type="checkbox" class="groupmod"></input> '. 
    '<span class="explanation">Taggage groupé</span> '.
    "<a class='closeiframe' href='#'>Fermer</a>".
    '<div class="iframeholder"></div> '.
    "<a class='openiframe' href='#'>Voir la page</a> <a class='closeiframe' href='#'>Fermer</a> ".

    '<a href="#" class="resdeletebutton">Supprimer cette resource</a>'.
    '<span class="notecount">';

  /** notecount  stuff **/
  if ($row->notecount == 1) {
    print "<a href='#' class='existingnotes'>1 note</a>";
  }
  elseif ($row->notecount > 1) {
    print "<a href='#' class='existingnotes'>";
    print $row->notecount . " notes</a>";
  }
  print '</span>';
  print '<a href="#" class="addnote">Ajouter une note</a>';

  print "</p>"
//    '<a href="#" class="seedetails">Voir détails</a> '.
//    '<a href="#" class="hidedetails">Cacher les détails</a></p> '.
    .'<div class="details">'
    .'<div class="tagger">'
    .'<span class="infohead">Ajouter un tag</span> '
    .'<input type="text" size="25" class="tagbox" maxlength="100"></input>'
    .'<span class="infohead">Metatag (facultatif)</span>'
    .'<select size="1" class="metatagbox">' . $metatagOptions . '</select>'
    .'<a class="tagbutton" href="#">Valider</a>' 
    .'</div>' 
    /** ajouter un ean13 **/
    .'<div class="ean13add">'
    .'<span class="infohead">Ajouter un EAN13</span>'
    .'<input type="text" class="ean13addbox" size="17" maxlength="17"/>'
    .'<a href="#" class="ean13addbutton">Ean13 valider</a>'
    .'</div>'
    /** taglist **/
    .'<p>Détails des tags déjà associés à cette ressource. <a class="seetags" href="#">Voir</a> '
    .'<a class="hidetags" href="#">Cacher</a> </p> '
    .'<div class="emptytags"></div>'
    /** suggestions **/
    .'<div class="suggestions"><a href="#" class="getsuggestions">Suggérer</a>'
    .'<a href="#" class="closesuggest">Fermer</a></div>'
    .'</div>'
    .'</li>';
}

?>
</ul>

<?php

nextPrevious($begin, $i->result->num_rows, $total_results);

/**
 * Produces the "order by" part of the query based on the
 * $_GET['orderby'] parameter that it accepts as an argument.
 *
 * @param string 
 * @returns string where clause
 */

function orderBySql ($arg) {
  switch ($arg) {
  case 'whenindexeddesc':
    return " ORDER BY r.added_timestamp DESC\n";
    break;
  case 'whenindexedasc':
    return " ORDER BY r.added_timestamp ASC\n";
    break;
  case 'popularitydesc':
    return " ORDER BY r.visited DESC\n";
    break;
  case 'popularityasc':
    return " ORDER BY r.visited ASC\n";
    break;
  case 'tagdate':
    return " ORDER BY last_tagged DESC\n";
    break;
  }
  return "ORDER BY r.added_timestamp DESC\n"; // default
}

function buildWhere ($first, $inside, $tagp, folksoDBinteract $i) { 
  $where = '';
  if (strlen($first) > 0) {
    $where = 
      " (uri_normal LIKE 'fabula.org/".
      $i->dbescape($first) . "%') \n";

    if (strlen($inside) > 0) {
      $where .= " AND \n";
    }
  }

  if (strlen($inside) > 0) {
    $where .=
      " (uri_normal LIKE '%" . 
      $i->dbescape($inside) . "%') ";
  }

  // when there are no arguments, we list everything.
  if ((strlen($inside) == 0) &&
      (strlen($first) == 0)) {
    $where = " (1 = 1) ";
  }

  switch ($tagp) {
  case 'all':
    return $where; // we are done
    break;
  case 'notags':
    $where .=
      " AND ".
      " ((SELECT COUNT(*) FROM tagevent teee ". 
      " WHERE teee.resource_id = r.id)  = 0) \n";
    break;
  case 'tags':
    $where .=
      " AND ".
    " ((SELECT COUNT(*) FROM tagevent teee ".
    " WHERE teee.resource_id = r.id) > 0) \n";
    break;
  default:
    return $where;
  }
  return $where;
}

/**
 * $numrows should be $i->result->num_rows
 */
function nextPrevious ($begin, $numrows, $totalresults) {
  $thispage = $_SERVER['REQUEST_URI'];
  /** rebuilding the request **/

  /* remove begin parameter from url */
  $thispage = preg_replace('/\??(&?begin=\d+)/', '', $thispage);
  
  $base = round($begin / 500) * 500;

  print "<div class='paginations'>";
  if ($base > 499) {
    $base = $base - 250;

    // link to get back to first page
    print paginationElement($thispage,
                            0,
                            $current_begin,
                            $totalresults);
       print " ... ";
  }

  $last_end = 0;
  for ($it = 0; $it <= 14; $it++) {
    $start = $it * 50 + $base;

    print paginationElement($thispage,
                            $start, 
                            $begin,
                            $totalresults);
    if ($start >= $totalresults - 50) {
      break;
    }
    print " ";
  }

  if (($totalresults - $start) > 50) {
    print " ... ";
    print paginationElement($thispage,
                            floor($totalresults / 50) * 50,
                            $begin,
                            $totalresults);
  }
  print "</div>";
}

function paginationElement ($thispage, 
                            $start,  // this section begins
                            $current_begin, // to identify current section
                            $total_pages) {
  $end = $start + 50;
  $start++;
  if ( $end  > $total_pages) {
    $end = $total_pages;
  }

  $return = '<a class='; 

  //check if current page
  if ($current_begin == $start) {
    $return .= '"currentpagination" '; //add class
  }
  else { //just close the quotes
    $return .= '"pagination" ';
  }

  // "begin" might be the first parameter after '.php'
  if (substr($thispage, -4) == 'php?') {
    $begin_part = 'begin='.$start;
  }
  elseif (substr($thispage, -4) == '.php') {
    $begin_part = '?begin='.$start;
  }
  else {
    $begin_part = '&begin='.$start;
  }

  $return .=  " href='" . $thispage . $begin_part . "'";
  $return .=  ">$start - " . $end . "</a>";
  return $return;
}

/**
 * @param $thisradio The "value" of the <input> element.
 * 
 * When this function is called on the default checkbox, $defaultp should be true.
 */
function radioOrderbyDefault ($orderby, $thisbox, $defaultp = false) {
  $checked =  ' checked="checked" ';
  if (!$orderby && !$defaultp) {
    return '';
  }
  if (!$orderby && $defaultp) {
    return $checked;
  }
  if ($orderby == $thisbox) {
    return $checked;
  }
}          

function datesToFrench ($date) {

  $fr = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
         'juillet', 'août', 'septembre', 'octobre', 'novembre', 
         'décembre');

  $us = array('January', 'February', 'March', 'April', 'May', 'June',
         'July', 'August', 'September', 'October', 'November', 'December');

  return str_replace($us, $fr, $date);

}
function fk_metatag_simple_list (folksoDBinteract $i) {
  $i->query('SELECT tagdisplay FROM metatag WHERE id <> 1');
  if ($i->result_status == 'DBERR') {
    alert('Problem with metatag autocomplete');
    print "''";
  }
  else {
    $mtags = array();
    while ($row = $i->result->fetch_object()) {
      $mtags[] = '"'.$row->tagdisplay.'"'; 
    }
    return '['.implode(',', $mtags) . ']';
  }
}

function metatagSelectBoxOptions (folksoDBinteract $i) {
  $i->query('SELECT tagdisplay FROM metatag WHERE id <> 1');
  $return = '';
  if ($i->result_status == 'DBERR') {
    alert('Problem with metatag autocomplete');
    print "''";
  }
  else {
    $return .= "<option></option>";
    while ($row = $i->result->fetch_object()) {
      $return .= "<option>". $row->tagdisplay . "</option>\n";
    }
  }
  return $return;
}

?>

  </body>
</html>