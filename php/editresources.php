<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Taggons des pages</title>

    <script type="text/javascript" src="js/jquery.js">
    </script>
    <script type="text/javascript" src="js/jquery.autocomplete.js">
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
      ul.editresources {
      background-color: white;
      }
      ul.editresources li {
      margin-top: 1em; margin-bottom: 1em;
      width: 80%;
      }

      iframe.preview {
      height: 400px;
      width: 100%;
      }

      a.restitle {padding-right: 2em; font-size: 14pt;}
      a.resurl { padding-right: 2em; font-size: 12pt;}
      ul.taglist {display: none;}

.tagid { padding-left: 0.5em; padding-right: 0.5em; }
    </style>

  </head>

  <body>
    <h1>Edition et taggage des resources</h1>
    
    <form action="editresources.php" method="get">
      <p>
        Saisir les premières lettres de l'url (après 'fabula.org') <!-- '-->
        <input type="text" size="5" maxlength="15" name="initial">
        </input>
      </p>
      <p>
        Saisir une séquence de caractères à chercher dans les
        url. (Peut être combiné avec les caractères initiaux).
        <input type="text" size="30" maxlength="200" name="sequence">
        </input>
      </p>
      <p>
        Afficher :<br/>
        <input type="radio" name="tagged" value="notags" checked="checked">
          </input>Ressources sans tags<br/>
          <input type="radio" name="tagged" value="tags">
            </input>Ressources déjà taggées<br/>
            <input type="radio" name="tagged" value="all">
              </input>Ressources taggées et non-taggées
      </p>
      <p>
        <input type="submit" name="submit">
        </input>
      </p>
    </form>

<?php

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

print $i->db_error();

if ($i->db_error()) {
  print "connection problem";
  die($i->error_info());
}

$i->query('set group_concat_max_len = 3072');

if ($i->db_error()) {
  print "connection problem";
  die($i->error_info());
}

$initial = $_GET['initial'];
$sequence = $_GET['sequence'];
$tagged = $_GET['tagged'];

/** 
 * Formatting the taglist (in the DB).
 *
 * $taglist_before is what will precede each tag link.
 * Putting this here to make it easier to modify.
 */
$taglist_before = 
  '<li><a href="/resourceview.php?tagthing=';
$taglist_between = 
  '">';
$taglist_between2 = 
  '</a> <span class="tagid">(';
$taglist_after =
  ')</span> <a class="removetag" href="#">Désassocier</a></li>';


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
  "                  SEPARATOR ' ')  \n". 
  "    FROM tagevent tee \n".
  "    JOIN tag ON tag.id = tee.tag_id \n".
  "    WHERE tee.resource_id = r.id \n".
  "    GROUP BY tee.resource_id) AS thesetags \n".

  "FROM resource r \n".
  "WHERE \n";

if ($initial) {
    $sql .= " (uri_normal LIKE 'fabula.org/" 
      . $i->dbescape($initial) . "%') \n";
}

if ($initial && $sequence) {
  $sql .= " AND \n";
}

if ($sequence) {
  $sql .= " (uri_normal LIKE '%" . $i->dbescape($sequence) . "%')\n";
}

if (($tagged <> 'all') && ($initial || $sequence)){
  $sql .= " AND \n";
}

if ($tagged == 'notags') {
  $sql .= 
    " ((SELECT COUNT(*) FROM tagevent teee ". 
    " WHERE teee.resource_id = r.id)  = 0) \n";
}
else if ($tagged == 'tags') {
  $sql .= 
    " ((SELECT COUNT(*) FROM tagevent teee ".
    " WHERE teee.resource_id = r.id) > 0) \n";
}

$sql .= " ORDER BY r.visited \n LIMIT 50 ";

print '<p>'. str_replace("\n", '<br/>', $sql).'</p>';

$i->query($sql);

if ($i->result_status == 'DBERR') {
  die( $i->error_info());
}

print '<ul class="editresources">';
while ($row = $i->result->fetch_object()) {
  print '<li id="res' . $row->id .'">'.
    '<p class="principal">'.
    '<a class="restitle" href="' . $row->url . '">' . $row->title . "</a>\n".
    '<a class="resurl" href="' . $row->url . '">' . $row->url . "</a>\n".
    '<span class="tagev_count">Taggé ' . $row->total_tagevs . " fois</span>\n".
    "</p>\n".
    '<p class="details">'.
    '<span class="infohead">Ajouté le</span><span class="added">'. $row->added . "</span>\n".
    '<br/><span class="currenttags">Tags : ' . $row->thesetags . "</span>\n".
    '<div class="iframeholder"></div> '.
    "<p><a class='openiframe' href='#'>Voir la page</a> <a class='closeiframe' href='#'>Fermer</a></p>".
    '<p><span class="infohead">Ajouter un tag</span> '.
    '<input type="text" size="30" class="tagbox" maxlength="100"></input>'.
    '<a class="tagbutton" href="#">Valider</a></p>' .
    '<p>Détails des tags existants. <a class="seetags" href="#">Voir</a> '.
    '<a class="hidetags" href="#">Cacher</a> </p> '.
    '<div class="emptytags"></div>'.
    '</li>';
}

?>
</ul>
  </body>
</html>