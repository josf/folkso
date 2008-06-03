/* resource */
drop table if exists resource;
create table resource
       (id int unsigned primary key auto_increment,
        uri_normal varchar(255) index resnorm (uri_normal) not null unique,
        uri_raw mediumtext not null,
        title mediumtext null,
        site_section varchar(255) null,
        visited int unsigned default 1 not null,
        added_timestamp timestamp 
        added_by int unsigned not null,
        status_flaq varchar(255) null)
        ENGINE = InnoDB;
