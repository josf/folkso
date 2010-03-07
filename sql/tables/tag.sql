drop table if exists tag;
create table tag
       (id int unsigned primary key auto_increment,
       tagnorm varchar(120) not null unique,
       tagdisplay varchar(150) not null unique,
       popularity int unsigned not null default 0,
       created timestamp default current_timestamp,
       index tagids (id),
       index tagnorms (tagnorm))
    ENGINE=InnoDB;


-- (local-set-key [(control c) (b)] 'sql-snip)
-- (defun sql-snip () 
--     (interactive)(snippet-insert "insert into tag set tagnorm = '$${norm}',  tagdisplay = '$${raw}';
-- "))

-- alter table tagevent add index tag_res (tag_id, resource_id);
-- alter table tagevent add index res_tag (resource_id, tag_id);
