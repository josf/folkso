

/* resource */
drop table if exists resource;
create table resource
       (id int unsigned primary key auto_increment,
        uri_normal varchar(255) not null,
        uri_raw varchar(255) not null,
        visited int default 0 not null);
