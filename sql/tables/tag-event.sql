drop table if exists tag-event;
create table tag-event
       (id int unsigned primary key auto_increment,
       tag_id int unsigned,
       user_id int unsigned,
       tagtime timestamp);