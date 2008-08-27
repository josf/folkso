DROP TABLE IF EXISTS note;
CREATE TABLE note
       (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
       resource_id INT UNSIGNED NOT NULL,
       note TEXT NOT NULL,
       user_id INT UNSIGNED NOT NULL DEFAULT 9999,
       INDEX resid (resource_id))
       ENGINE = InnoDB;
       