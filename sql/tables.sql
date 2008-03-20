

/* resource */
drop table if exists resource;
create table resource
       (id int unsigned primary key auto_increment,
        uri_normal varchar(255) not null unique,
        uri_raw smalltext not null,
        title null,
        visited int default 1 not null);

