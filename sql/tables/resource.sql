/* resource */
DROP TABLE IF EXISTS resource;
CREATE TABLE resource
       (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        uri_normal VARCHAR(255)  UNIQUE NOT NULL,
        uri_raw MEDIUMTEXT NOT NULL,
        title MEDIUMTEXT NULL,
        site_section VARCHAR(255) NULL,
        visited INT UNSIGNED DEFAULT 1 NOT NULL, -- number of total visits
        added_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_visited TIMESTAMP,
        added_by INT UNSIGNED NOT NULL,
        status_flag VARCHAR(255) NULL,
INDEX resnorm (uri_normal))
        ENGINE = InnoDB;
