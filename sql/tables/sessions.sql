drop table if exists oid_urls;
drop table if exists fb_ids;
drop table if exists sessions;
drop table if exists users;

create table users
       (userid varchar(255) primary key,
       userno  integer unsigned auto_increment not null, -- not to be used but mysql wants it
       created datetime not null,
       last_visit datetime not null,
       firstname varchar(255) not null,
       lastname varchar(255) not null,
       nick varchar(70) not null,
       email varchar(255) not null, 
       institution varchar(255) null,
       pays varchar(50) null,
       fonction varchar(50) null,
       index unumb (userno)
       )
ENGINE=InnoDB;
grant select on users to 'folkso'@'localhost';
grant select, insert, update, delete on users to 'folkso-rw'@'localhost';

create table fb_ids
       (userid varchar(255) primary key,
       fb_uid integer unsigned,
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
       as select fb_uid, u.userid as userid, last_visit, lastname, firstname, nick, email, institution, pays, fonction
          from users u 
          join fb_ids f on f.userid = u.userid;
grant select on fb_users to 'folkso'@'localhost';
grant select on fb_users to 'folkso-rw'@'localhost';


create or replace view oi_users
       as select oid_url, u.userid as userid, last_visit, lastname, firstname, nick, email, institution, pays, fonction
       from users u
       join oid_urls o on u.userid = o.userid;

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


create table users_rights
       (userid varchar(255) not null,
       rightid varchar(20) not null,
       foreign key (userid) references users (userid),
       foreign key (rightid) references rights (rightid),
       primary key (userid, rightid))
ENGINE=InnoDB;
grant select on sessions to 'folkso'@'localhost';
grant select, insert, update, delete on sessions to 'folkso-rw'@'localhost';


