-- each newly indexed resource should be checked against this table
-- and rejected if found here.

drop table if exists exclude;
create table exclude
       (uri_normal varchar(255) primary key,
       exclusion_date timestamp default current_timestamp);