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
    
    <style type="text/css">
    #superscreen {
        display: none; 
        z-index: 50;
        background-color: grey;
    }

    #superinfobox {
        background-color: white;
        height: 300px;
        width: 500px;
        position: fixed;
        top: 15%; left: 20%;
        border: 10px solid red;
        padding-top: 1em; padding-bottom: 1.2em;
        padding-left: 2em; padding-right: 2em;
    }
    #superinfobox h3 {
        color: red;
        font-size: 16pt;
        text-align: center;
        margin: 0.5em;
    }
    #superinfobox p {
        margin-top: 0.3em;
    }
    #superinfobox em {
        color: red;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .yesno {
        margin-left: 0.3em; margin-right: 0.3em;
    }
    #sql { display: none;}

      ul.editresources {
      background-color: white;
      }
      ul.editresources li {
      margin-top: 2em; margin-bottom: 1em;
      border-top: 2px solid grey;
      padding-left: 4em;
      width: 90%;
      }

      iframe.preview {
      height: 400px;
      width: 100%;
      }



      a.restitle {padding-right: 2em; font-size: 12pt; font-weight: bold;}
      a.resurl { padding-right: 2em; font-size: 10pt;}

      li.nottagged a.restitle {
          background-color: #e5966e;
      }

      li.nottagged span.tagev_count {
          color: #e66322;
      }

      ul.taglist {display: none;}

ul.tagmenu li {border: none;}

      .tagid { padding-left: 0.5em; padding-right: 0.5em; }

      .explanation {
      font-size: 9pt;
      font-style: italic;
      }
/*      a.hidedetails {display: none;} */
      a.seedetails {display: none; margin-left: 0.2em; margin-right: 0.2em;}
/*      div.details {display: none;} */
      span.tagid {font-size: 8pt;
                 font-style: italic;}
      a.remtag, a.tagbutton, a.metatagbutton { font-weight: bold;
                color: #e66322;
                margin-left: 0.5em; margin-right: 0.5em;
               }
      a.tagdisplay {font-weight: bold}
      .infohead {margin-left: 0.5em;}
    </style>

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

$rescount_sql = 
  'select count(*) as rows '.
  ' from resource r ' .
  ' where '.
  buildWhere($initial, $sequence, $tagged, $i);

$i->query($rescount_sql);

$total_results = $i->first_val('rows');

nextPrevious($begin);

$sql = "SELECT ".
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
  "    GROUP BY tee.resource_id) AS thesetags \n".

  "FROM resource r \n".
  "WHERE \n";

$sql .= buildWhere( $initial, $sequence, $tagged, $i);

$sql .= orderBySql($orderby);
$sql .= " LIMIT 50\n";

if ((is_numeric($begin)) &&
    ($begin >= 50)) {
  $offset = $begin + 50;
  $sql .= " OFFSET $offset";
}

print '<p><a href="#" id="showsql">Voir requête</a> (pour devel seulement)</p>';
print '<div id="sql">';
print '<p>'. str_replace("\n", '<br/>', $sql).'</p>';
print '</div>';

$i->query($sql);

if ($i->result_status == 'DBERR') {
  die( $i->error_info());
}

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
    '<a class="restitle" href="' . $row->url . '">' . $row->title . "</a>\n".
    '<a class="resurl" href="' . $row->url . '">' . $row->url . "</a>\n".
    '<span class="tagev_count">Taggé ' . $row->total_tagevs . " fois</span>\n".
    "</p>\n".
    '<p><span class="currenttags">Tags : "' . $row->thesetags . "\"</span><p>\n".
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
    '</div>'.
    '<p>Détails des tags déjà associés à cette ressource. <a class="seetags" href="#">Voir</a> '.
    '<a class="hidetags" href="#">Cacher</a> </p> '.
    '<div class="emptytags"></div>'.
    '</div>'.
    '</li>';
}

?>
</ul>

<?php

nextPrevious($begin);

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

function nextPrevious ($begin) {
  //$thispage = '/commun3/folksonomie/editresources.php?';
  $thispage = '/editresources.php?';
  $fields = array();
  if ($initial) {
    $fields[] = 'initial='.$initial;
  }
  if ($sequence) {
    $fields[] = 'sequence='.$sequence;
  }
  if ($tagged) {
    $fields[] = 'tagged='.$tagged;
  }

  /** previous **/
  if ($begin >= 50) {
    $newbegin = $begin - 50;
    if ($newbegin < 0) {
      $newbegin = 0;
    }
    $fields[] = 'begin=' . $newbegin;
    print 
      '<p><a href="'. 
      $thispage . 
      implode($fields, '&').
      '">Précédents</a></p>';
  }
  if ($i->result->num_rows > 49) {
    $newbegin = $begin + 50;
    $fields[] = 'begin='.$newbegin;
    print 
      '<p><a href="'.
      $thispage . implode($fields, '&') .
      '">Suivant</a></p>';
  }
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