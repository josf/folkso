<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */


  /** Since DB connection etc. are used both in the <head> and <body>,
 all the initialization stuff goes here. **/

require_once('folksoDBconnect.php');
require_once('folksoDBinteract.php');
require_once('folksoFabula.php');

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

?>


<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Taggons des pages</title>

    <script type="text/javascript" src="js/jquery.js">
    </script>
    <script type="text/javascript" src="js/jquery.autocomplete.js">
    </script>

   <script type="text/javascript">
   var metatag_autocomplete_list = 
  <?php
  function fk_metatag_simple_list (folksoDBinteract $i) {
       $i->query('select tagdisplay from metatag');
      if ($i->result_status == 'DBERR') {
        alert('Problem with metatag autocomplete');
        print "''";
      }
      else {
        $mtags = array();
        while ($row = $i->result->fetch_object()) {
          $mtags[] = '"'.$row->tagdisplay.'"'; 
        }
        print '['.implode(',', $mtags) . ']';
      }
}
fk_metatag_simple_list($i);
?>;
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
      href="/editres.css"
      media="screen">
  </link>

  </head>

  <body>
  <div id="superscreen">
  <div id="superinfobox">
     <a id="closess" href="#">Fermer</a>
  </div>
  </div> <!-- end of superscreen -->
    <h1>Edition et taggage des resources</h1>

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

        <h3>Afficher :</h3>
      <p>
        <input type="radio" name="tagged" value="notags" checked="checked">
          </input>Ressources sans tags<br/>
        <input type="radio" name="tagged" value="tags">
        </input>Ressources déjà taggées<br/>
        <input type="radio" name="tagged" value="all">
        </input>Ressources taggées et non-taggées
      </p>
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
      <p>
        <input type="submit" name="submit">
        </input>
      </p>
    </form>

    <p>
      <strong>Tagger les ressources sélectionnées</strong>
      <input type="text" size="30" class="tagbox" id="grouptagbox" maxlength="100"></input>
      <a href="#" id="grouptagvalidate">Valider</a>
    </p>

<?php

print $i->db_error();

if ($i->db_error()) {
  print "connection problem";
  die($i->error_info());
}

$i->query('SET group_concat_max_len = 3072');

if ($i->db_error()) {
  print "connection problem";
  die($i->error_info());
}

$initial = $_GET['initial'];
$sequence = $_GET['sequence'];
$tagged = $_GET['tagged'];
$begin = $_GET['begin'];
$orderby = $_GET['orderby'];


/* tagdate makes no sense if 'tags' is not selected */
if ($orderby == 'tagdate') {
  $tagged = 'tags';
}

$rescount_sql = 
  'select count(*) as rows '.
  ' from resource r ' .
  ' where '.
  buildWhere($initial, $sequence, $tagged, $i);

$i->query($rescount_sql);

$total_results = $i->first_val('rows');

$fksql = "SELECT ".
  "r.id AS id, r.uri_raw AS url, \n".
  "r.title AS title, r.visited AS visits, \n".
  "r.added_timestamp AS added, \n".
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
  $offset = $begin + 50;
  $fksql .= " OFFSET $offset";
}

print 
  '<p><a href="#" id="showsql">Voir requête</a> (pour devel seulement)</p>' .
  '<div id="sql">' .
  '<p>'. str_replace("\n", '<br/>', $fksql).'</p>' . 
  '</div>';

$i->query($fksql);

if ($i->result_status == 'DBERR') {
  die( $i->error_info());
}

nextPrevious($begin, $i->result->num_rows, $total_results);

$begin_with_current_results = $begin + $i->result->num_rows;
$begin_display =  $begin ? $begin : 1;

print '<p>Reponses '. $begin_display . ' a ' . 
    $begin_with_current_results. " sur  $total_results. </p>";
//print '<p>'. $rescount_sql . '</p>';

print '<ul class="editresources">';
while ($row = $i->result->fetch_object()) {
  print 
  '<li id="res' . $row->id .'"';

if ($row->total_tagevs > 0) {
  print 'class="tagged"';
}
else {
  print 'class="nottagged"';
}


print '> ';

  print 
    '<p class="principal">'.
    '<a class="restitle" href="' . $row->url . '" target="fk" >' . $row->title . "</a>\n".
    '<a class="resurl" href="' . $row->url . '" target="fk" >' . $row->url . "</a>\n".
    '<span class="tagev_count">Taggé ' . $row->total_tagevs . " fois</span>\n".
    "</p>\n" .
    '<p><span class="currenttags">Tags : ';

  if (strlen($row->thesetags) > 0) {
    print '"';
  }

  print $row->thesetags;

  if (strlen($row->thesetags) > 0) {
    print '"';
  }

  print 
    "</span> <span class='lasttaggage'>Date du dernier tag: " . $row->last_tagged . "</span></p>\n".
    '<p><input type="checkbox" class="groupmod"></input> '. 
    '<span class="explanation">Taggage groupé</span></p> '.
    "<p><a class='closeiframe' href='#'>Fermer</a></p>".
    '<div class="iframeholder"></div> '.
    "<p><a class='openiframe' href='#'>Voir la page</a> <a class='closeiframe' href='#'>Fermer</a> ".
    '<a href="#" class="seedetails">Voir détails</a> '.
    '<a href="#" class="hidedetails">Cacher les détails</a></p> '.
    '<div class="details">'.
    '<p>'.
    '<span class="infohead">Ajouté le </span><span class="added">'. $row->added . "</span>\n".
    '<div class="tagger">'. 
    '<span class="infohead">Ajouter un tag</span> '.
    '<input type="text" size="25" class="tagbox" maxlength="100"></input>'.
    '<span class="infohead">Metatag (facultatif)</span>'.
    '<input type="text" size="20" class="metatagbox" maxlength="100"></input>'.
    '<a class="tagbutton" href="#">Valider</a>' .
    '</div>' .
    '<p>Détails des tags déjà associés à cette ressource. <a class="seetags" href="#">Voir</a> '.
    '<a class="hidetags" href="#">Cacher</a> </p> '.
    '<div class="emptytags"></div>'.
    '</div>'.
    '</li>';
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
    $last_end = $start + 50;
    if ($start >= $totalresults) {
      break;
    }
    print paginationElement($thispage,
                            $start, 
                            $begin,
                            $totalresults);
    print " ";
  }

  if (($totalresults - $last_end) > 50) {
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

?>

  </body>
</html>