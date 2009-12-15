<?php

require_once('folksoTags.php');

function test_db_init() {

  $tables = array('ean13', 'exclude', 'tagevent',
                  'memoize_tagnormal', 
                  'note', 'replace_characters', 'resource',
                  'tag',  'urltest',
                  'metatag',  'fb_ids', 'oid_urls', 'sessions', 'users_rights', 'users' );
  $dbc = new folksoDBconnect('localhost', 'tester_dude', 'testy', 'testostonomie');
  $i = new folksoDBinteract($dbc);
  
  /** start over **/
  foreach ($tables as $tab) {
    $i->query("truncate table $tab");
  }

  print $i->error_info();
  $user = array( 'marcelp' => array('Marcel', 'Proust', 'marcelp@temps.eu', 
                                    0, 'http://flickr.com/marcelp'),
                 'gustav' => array('Gustave', 'Flaubert', 'gflaub@sentimental.edu',
                                   0, 'http://myspace.com/gustav'),
                 'rambo' => array('Arthur', 'Rimbaud', 'arthurrr@ivoire.biz',
                                  543210, ''),
                 'vicktr' => array('Victor', 'Hugo', 'vhugo@guernsey.co.uk',
                                   0, 'http://myspace.com/victorhugo'),
                 'michl' => array('Michel', 'de Montaigne', 'montaigne@mairie-bordeaux.fr', 0, 'http://michlm.blogspot.com')
                 );


    foreach($user as $k => $v){
      print $k;
      $i->sp_query(sprintf("call create_user('%s', '%s', '%s', '%s', '%s', %d, '', '', '')",
                           $k, $v[0], $v[1], $v[2], $v[4], $v[3]));
      print $i->error_info();
    }

  $reslist = array('http://example.com/1',
                   'http://example.com/2',
                   'http://example.com/3',
                   'http://example.com/4',
                   'http://example.com/5');

  $restitles = array('one', 'two', 'three', 'four', 'five');
  $tags = array('tagone', 'tagtwo', 'tagthree', 'tagfour', 'tagfive');

  $i->query(
            'insert into metatag (tagnorm, tagdisplay)'
            . ' values '
            . "('normal', 'normal'), "
            . "('auteur1', 'Auteur 1'), "
            . "('auteur2', 'Auteur 2')"
            );

  /** fill tables **/
  $i->query("call bulk_visit('" . implode('&&&&&', $reslist) . "', '"
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
    $i->sp_query("call new_tag('mysterio')"); // unknown tag
    if ($i->db_error()) { 
      print "bad new tag (mysterio)";
    }

    foreach ($tags as $t) {
      $i->sp_query("call tag_resource('gustav-2009-001', 'http://example.com/1', '', '$t', '', '', 1 )");
      if ($i->db_error()) { 
        print "bad tag resource<br/>";
        print $i->error_info();
      }
    }

    $i->query("insert into users_rights (userid, rightid) values "
              ."('rambo-2009-001', 'tag'), "
              ."('rambo-2009-001', 'create'), "
              ."('marcelp-2009-001', 'tag'), "
              ."('marcelp-2009-001', 'create'), "
              ."('marcelp-2009-001', 'redac'), "
              ."('vicktr-2009-001', 'admin'), "
              ."('gustav-2009-001', 'tag')");



    //    $i->query("insert into fb_ids (userid, fb_uid) values "
    //              ." ('rambo-2009-001', 123456) ");
    /*    $i->query("insert into oid_urls (userid, oid_url) values "
              ." ('marcelp-2009-001', 'http://flickr.com/marcelp'), "
              ." ('gustav-2009-001', 'http://myspace.com/gustav')"); */

}

