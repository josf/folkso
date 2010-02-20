<?php

require_once('folksoTags.php');

function test_db_init() {

  $tables = array('ean13', 'exclude', 'tagevent',
                  'memoize_tagnormal', 
                  'note', 'replace_characters', 'resource',
                  'tag',  'urltest',
                  'metatag', 'user_data', 'fb_ids', 'oid_urls', 'sessions', 'users_rights', 'users' );
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
      $i->sp_query(sprintf("call create_user('%s', '%s', %d, '%s', '%s', '%s', '', '', '')",
                           $k, $v[4], $v[3], $v[0], $v[1], $v[2]));
      print $i->error_info();
    }

  $reslist = array('http://example.com/1',
                   'http://example.com/2',
                   'http://example.com/3',
                   'http://example.com/4',
                   'http://example.com/5'
                   );

  $dynreslist = array('http://dynamic.example.com/1' => 
                      array('dyn1', 'dyn2', 'dyn3', 'dyn4'),
                      'http://dynamic.example.com/2' => array('dyn1', 'dyn8', 'dyn7'),
                      'http://dynamic.example.com/3' => array('dyn1', 'dyn2', 'dyn5'),
                      'http://dynamic.example.com/4' => 
                      array('dyn1', 'dyn4', 'dyn5', 'dyn6', 'dyn7'),
                      'http://dynamic.example.com/5' => 
                      array('dyn1', 'dyn2', 'dyn6', 'dyn7', 'dyn8', 'dyn3'),
                      'http://dynamic.example.com/6' => 
                      array('dyn1', 'dyn4', 'dyn6', 'dyn8'),
                      'http://dynamic.example.com/7' => array('dyn1', 'dyn8'),
                      'http://dynamic.example.com/8' => 
                      array('dyn1', 'dyn3', 'dyn5', 'dyn7', 'dyn8'),
                      'http://dynamic.example.com/9' => 
                      array('dyn1', 'dyn3', 'dyn5', 'dyn6', 'dyn8'),
                      'http://dynamic.example.com/10' => 
                      array('dyn1', 'dyn2', 'dyn5', 'dyn8'),
                      'http://dynamic.example.com/11' =>
                      array('dyn1', 'dyn3', 'dyn5', 'dyn6', 'dyn8'),
                      'http://dynamic.example.com/12' =>
                      array('dyn1', 'dyn3', 'dyn5', 'dyn6', 'dyn8'),
                      'http://dynamic.example.com/13' => array('dyn1'),
                      'http://dynamic.example.com/14' => 
                      array('dyn1', 'dyn2', 'dyn3', 'dyn4', 'dyn5', 'dyn6', 'dyn7', 'dyn8'),
                      'http://dynamic.example.com/15' => array('dyn1', 'dyn8'),
                      'http://dynamic.example.com/16' => array('dyn1'),
                      'http://dynamic.example.com/17' => array('dyn1'),
                      'http://dynamic.example.com/18' => array('dyn1'),
                      'http://dynamic.example.com/19' => array('dyn1'),
                      'http://dynamic.example.com/20' => array('dyn1'),
                      'http://dynamic.example.com/21' => array('dyn1'),
                      'http://dynamic.example.com/22' => array('dyn1'),
                      'http://dynamic.example.com/23' => array('dyn1'),
                      'http://dynamic.example.com/24' => array('dyn1'));

  $restitles = array('one', 'two', 'three', 'four', 'five');
  $tags = array('tagone', 'tagtwo', 'tagthree', 'tagfour', 'tagfive');

  $dyntags = array('dyn1', 'dyn2', 'dyn3', 'dyn4', 'dyn5', 'dyn6', 'dyn7', 'dyn8');
  $dynrestitles = array('A page, a page', 'Fun with resources', 'Something to look at', 'OMG!', 'WTF!', 'How bout that?', 'Can you imagine?', 'No I cannot imagine at all', 'That just bowls me over', 'What are you talking about?', 'This should not be wrong', 'But it is', 'Well that is not my problem', 'That is what you say', 'No, that is what you say', 'Well too bad', 'Sometimes there are just errors', 'I know', 'That is the way it goes', 'There you are', 'Well?', 'Harumph', 'Heck', 'Fudge', 'Enough already?', 'Not sure', 'Pretty sure', 'Getting there', 'Almost');

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

  $i->query("call bulk_visit('" . implode('&&&&&', array_keys($dynreslist)) . "', '"
            . implode('&&&&&', array_slice($dynrestitles, 0, count($dynreslist))) . "', 5)");

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

    foreach ($dyntags as $dt) {
      $i->sp_query("call new_tag('$dt')");
      if ($i->db_error()) {
        print "bad new tag $dt";
      }
    }

    foreach ($tags as $t) {
      $i->sp_query("call tag_resource('gustav-2010-001', 'http://example.com/1', '', '$t', '', '', 1 )");
      if ($i->db_error()) { 
        print "bad tag resource<br/>";
        print $i->error_info();
      }
    }

    foreach ($dynreslist as $dres => $dtags) {
      foreach ($dtags as $dyno) {
        $i->sp_query("call tag_resource('gustav-2010-001', '$dres', '', '$dyno', '', '', 1)");
        if ($i->db_error()) {
          print "bad dynamic tag resource<br/>";
          print $i->error_info();
        }
      }
    }

    $i->query("insert into users_rights (userid, rightid) values "
              ."('rambo-2010-001', 'tag'), "
              ."('rambo-2010-001', 'create'), "
              ."('marcelp-2010-001', 'tag'), "
              ."('marcelp-2010-001', 'create'), "
              ."('marcelp-2010-001', 'redac'), "
              ."('vicktr-2010-001', 'admin'), "
              ."('gustav-2010-001', 'tag')");



    //    $i->query("insert into fb_ids (userid, fb_uid) values "
    //              ." ('rambo-2010-001', 123456) ");
    /*    $i->query("insert into oid_urls (userid, oid_url) values "
              ." ('marcelp-2010-001', 'http://flickr.com/marcelp'), "
              ." ('gustav-2010-001', 'http://myspace.com/gustav')"); */

}

