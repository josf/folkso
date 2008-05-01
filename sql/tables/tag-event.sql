drop table if exists tagevent;
create table tagevent
       (id int unsigned primary key auto_increment,
       tag_id int unsigned not null,
       resource_id int unsigned not null, 
       meta_id int unsigned 0,
       user_id int unsigned not null,
       tagtime timestamp not null);

-- (local-set-key [(control c) (b)] 'sql-snip)
-- (defun sql-snip () 
--      (interactive)(snippet-insert "insert into tag-event set tag_id = $${norm}, set resource_id = $${res}, user_id = 1;
--     "))

insert into tagevent set tag_id = 1, resource_id = 342, user_id = 1;
insert into tagevent set tag_id = 2, resource_id = 342, user_id = 1;
insert into tagevent set tag_id = 1, resource_id = 44, user_id = 1;
insert into tagevent set tag_id = 4, resource_id = 44, user_id = 1;
insert into tagevent set tag_id = 3, resource_id = 799, user_id = 1;
insert into tagevent set tag_id = 4, resource_id = 1000, user_id = 1;
insert into tagevent set tag_id = 1, resource_id = 1000, user_id = 1;
insert into tagevent set tag_id = 2, resource_id = 2000, user_id = 1;
     
     
     
     

               