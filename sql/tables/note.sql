DROP TABLE IF EXISTS note;
CREATE TABLE note
       (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
       resource_id INT UNSIGNED NOT NULL,
       note TEXT NOT NULL,
       userid varchar(255) not null,
       created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (userid) REFERENCES users (userid),
       FOREIGN KEY (resource_id) REFERENCES resource (id),
       INDEX resid (resource_id),
       INDEX useres (userid, resource_id))
       ENGINE = InnoDB;
       