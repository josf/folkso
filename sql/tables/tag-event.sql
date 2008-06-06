 DROP TABLE IF EXISTS tagevent;
CREATE TABLE tagevent
       (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
       tag_id INT UNSIGNED NOT NULL,
       resource_id INT UNSIGNED NOT NULL, 
       meta_id INT UNSIGNED DEFAULT 0 NOT NULL,
       user_id INT UNSIGNED NOT NULL,
       tagtime TIMESTAMP NOT NULL,
       INDEX tagdex (tag_id),
       INDEX resdex (resource_id),
       FOREIGN KEY (tag_id) REFERENCES tag(tagids),
       FOREIGN KEY (resource_id) REFERENCES resource(primary) )
    ENGINE=InnoDB;

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
     
     
     
     

               