drop table if exists oid_urls;
drop table if exists fb_ids;
drop table if exists sessions;
drop table if exists user_data;
-- could be necessary to drop users but should be used w/ caution!
-- drop table if exists tagevent;
drop table if exists users_rights;
drop table if exists rights;
drop table if exists users;


create table user_data
       (userid varchar(255) primary key,
       firstname varchar(255) not null,
       lastname varchar(255) not null,
       firstname_norm varchar(255) not null,
       lastname_norm varchar(255) not null,
       nick varchar(70) null,
       email varchar(255) null,
       institution varchar(255) null,
       pays varchar(50) null,
       fonction varchar(50) null, 
       cv text default '' not null,
       foreign key (userid) references users (userid))
ENGINE=InnoDB;
grant select on user_data to 'folkso'@'localhost';
grant select, insert, update, delete on user_data to 'folkso-rw'@'localhost';

create table fb_ids
       (userid varchar(255) primary key,
       fb_uid bigint unsigned not null,
       foreign key (userid) references users (userid),
       index fb (fb_uid))
ENGINE=InnoDB;
grant select on fb_ids to 'folkso'@'localhost';
grant select, insert, update, delete on fb_ids to 'folkso-rw'@'localhost';

create table oid_urls
       (userid varchar(255) primary key,
       oid_url text not null,
       foreign key (userid) references users (userid),
      index oid (oid_url(400)))
ENGINE=InnoDB;
grant select  on oid_urls to 'folkso'@'localhost';
grant select, insert, update, delete on oid_urls to 'folkso-rw'@'localhost';

create or replace view fb_users
       as select 
          fb_uid, 
          u.userid as userid, u.urlbase as urlbase, last_visit, 
          ud.lastname as lastname, ud.firstname as firstname, ud.email as email, 
          ud.institution as institution, ud.pays as pays, ud.fonction as fonction,
          ud.cv as cv
          from users u 
          join fb_ids f on f.userid = u.userid
          left join user_data ud on u.userid = ud.userid;
grant select on fb_users to 'folkso'@'localhost';
grant select on fb_users to 'folkso-rw'@'localhost';


create or replace view oi_users
       as select 
       oid_url,
       u.userid as userid, u.urlbase as urlbase, last_visit,    
       ud.lastname as lastname, ud.firstname as firstname, ud.email as email, 
       ud.institution as institution, ud.pays as pays, ud.fonction as fonction,
       ud.cv as cv
       from users u
       join oid_urls o on u.userid = o.userid
       left join user_data ud on u.userid = ud.userid;
grant select on oi_users to 'folkso'@'localhost';
grant select on oi_users to 'folkso-rw'@'localhost';

create table sessions  
       (token char(64) primary key,
       userid varchar(255) not null,
       started timestamp not null default current_timestamp,
       foreign key (userid) references users (userid)
       )      
ENGINE=InnoDB;
grant select on sessions to 'folkso'@'localhost';
grant select, insert, update, delete on sessions to 'folkso-rw'@'localhost';

create table rights
       (rightid varchar(20) not null unique,
       service varchar(20) not null, 
       description text null)
ENGINE=InnoDB;
grant select on sessions to 'folkso'@'localhost';
grant select, insert, update, delete on sessions to 'folkso-rw'@'localhost';

insert into rights (rightid, service) values ('create', 'folkso');
insert into rights (rightid, service) values ('delete_othertaggage', 'folkso');
insert into rights (rightid, service) values ('supertag', 'folkso');
insert into rights (rightid, service) values ('delete_tags', 'folkso');
insert into rights (rightid, service) values ('delete', 'folkso_user');
insert into rights (rightid, service) values ('redac', 'folkso'), ('admin', 'folkso'), ('tag', 'folkso');



create table users_rights
       (userid varchar(255) not null,
       rightid varchar(20) not null,
       foreign key (userid) references users (userid),
       foreign key (rightid) references rights (rightid),
       primary key (userid, rightid))
ENGINE=InnoDB;
grant select on sessions to 'folkso'@'localhost';
grant select, insert, update, delete on sessions to 'folkso-rw'@'localhost';


