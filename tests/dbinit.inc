<?php

require_once('folksoTags.php');

function test_db_init() {

  $tables = array('ean13', 'exclude', 'tagevent',
                  'memoize_tagnormal', 
                  'note', 'replace_characters', 'resource',
                  'tag',  'urltest',
                  'metatag' );
  $dbc = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
  $i = new folksoDBinteract($dbc);
  
  /** start over **/
  foreach ($tables as $tab) {
    $i->query("truncate table $tab");
  }

  print $i->error_info();

  $reslist = array('http://example.com/1',
                   'http://example.com/2',
                   'http://example.com/3',
                   'http://example.com/4',
                   'http://example.com/5');

  $restitles = array('one', 'two', 'three', 'four', 'five');
  $tags = array('tagone', 'tagtwo', 'tagthree', 'tagfour', 'tagfive');

  $i->sp_query(
            'insert into metatag (tagnorm, tagdisplay)'
            . ' values '
            . "('normal', 'normal'), "
            . "('auteur1', 'Auteur 1'), "
            . "('auteur2', 'Auteur 2')"
            );
  
  /** fill tables **/
  $i->sp_query("call bulk_visit('" . implode('&&&&&', $reslist) . "', '"
            . implode('&&&&&', $restitles) . "', 5)");


  if ($i->db_error()) {
    print "Bulk " .  $i->error_info();
  }

    $i->sp_query("call new_tag('" . $tags[0] . "')");
    
    if ($i->db_error()) { 
      print "bad new tag";
    }

    $i->sp_query("call new_tag('" . $tags[1] . "')");

    if ($i->db_error()) { 
      print "bad new tag";
    }

    $i->sp_query("call new_tag('" . $tags[2] . "')");
    if ($i->db_error()) { 
      print "bad new tag";
    }

    $i->sp_query("call new_tag('" . $tags[3] . "')");
    if ($i->db_error()) { 
      print "bad new tag";
    }

    $i->sp_query("call new_tag('" . $tags[4] . "')");
    if ($i->db_error()) { 
      print "bad new tag";
    }

    foreach ($tags as $t) {

    $i->sp_query("call tag_resource('http://example.com/1', '', '$t', '', '', 1 )");
    if ($i->db_error()) { 
      print "bad tag resource<br/>";
      print $i->error_info();
    }
    }
}

