
drop table if exists sessions;
create table sessions  
       (token char(128) primary key,
       userid varchar(255) not null,
       started datetime not null default now(),
       foreign key (userid) references users (userid)
       )      
ENGINE=InnoDB;

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

drop table if exists fb_ids;
create table fb_ids
       (userid varchar(255) primary key,
       fb_uid integer unsigned,
       foreign key (userid) references users (userid),
       index fb (fb_uid))
ENGINE=InnoDB;


drop table if exists oid_urls;
create table oid_urls
       (userid varchar(255) primary key,
       oid_url text not null,
       foreign key (userid) references users (userid),
      index oid (oid_url(400)))
ENGINE=InnoDB;





