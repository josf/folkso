/* resource */
drop table if exists resource;
create table resource
       (id int unsigned primary key auto_increment,
        uri_normal varchar(255) not null unique,
        uri_raw mediumtext not null,
        title mediumtext null,
        visited int unsigned default 1 not null);
