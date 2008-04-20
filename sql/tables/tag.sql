drop table if exists tag;
create table tag
       (id int unsigned primary key auto_increment,
       tagnorm varchar(120) not null unique);
