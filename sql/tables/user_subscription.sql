drop table if exists user_subscription;
create table user_subscription 
       (userid varchar(255) primary key,
       tag_id int unsigned not null,
       subscribe_date timestamp default current_timestamp not null,
       foreign key (userid) references users (userid),
       foreign key (tag_id) references tag (id),
       index byuser (userid, tag_id),
       index bytag (tag_id, userid))
engine=InnoDB;