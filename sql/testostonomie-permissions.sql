-- Test suite permissions
grant select, delete, insert, update, drop on ean13 to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on exclude to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on tagevent to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on memoize_tagnormal to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on note to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on replace_characters to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on resource to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on tag to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on urltest to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on metatag to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on fb_ids to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on oid_urls to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on sessions to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on user_data to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on users_rights to 'tester_dude'@'localhost';
grant select, delete, insert, update, drop on users to 'tester_dude'@'localhost';

grant execute on procedure testostonomie.create_user to 'tester_dude'@'localhost';

grant execute on function normalize_tag to 'tester_dude'@'localhost';
grant execute on procedure new_tag to 'tester_dude'@'localhost';
grant execute on procedure tag_resource to 'tester_dude'@'localhost';
grant execute on procedure cloudy to 'tester_dude'@'localhost';
grant execute on procedure cloud_by_timestamp to 'tester_dude'@'localhost';
grant execute on procedure tagmerge to 'tester_dude'@'localhost';
grant execute on procedure metamod to 'tester_dude'@'localhost';
grant execute on procedure tagrank to 'tester_dude'@'localhost';
grant execute on procedure delete_user_with_tags to 'tester_dude'@'localhost';