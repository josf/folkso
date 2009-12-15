DROP TABLE IF EXISTS tagevent;
CREATE TABLE tagevent
       (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
       tag_id INT UNSIGNED NOT NULL,
       resource_id INT UNSIGNED NOT NULL, 
       meta_id INT UNSIGNED DEFAULT 1 NOT NULL,
       userid varchar(255) NOT NULL,
       tagtime TIMESTAMP NOT NULL,
       INDEX tagdex (tag_id, resource_id),
       INDEX resdex (resource_id, tag_id),
       INDEX useres (userid, resource_id),
       INDEX usetag (userid, tag_id),
       FOREIGN KEY (tag_id) REFERENCES tag (id),
       FOREIGN KEY (resource_id) REFERENCES resource (id),
       FOREIGN KEY (meta_id) REFERENCES metatag (id),
       FOREIGN KEY (userid) REFERENCES users (userid))
    ENGINE=InnoDB;

-- (local-set-key [(control c) (b)] 'sql-snip)
-- (defun sql-snip () 
--      (interactive)(snippet-insert "insert into tag-event set tag_id = $${norm}, set resource_id = $${res}, user_id = 1;
--     "))

               