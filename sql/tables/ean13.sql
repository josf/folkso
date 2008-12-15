/* ean13 */
DROP TABLE IF EXISTS ean13;
CREATE TABLE ean13
       (resource_id INT UNSIGNED NOT NULL,
       ean13 BIGINT UNSIGNED NOT NULL,
       UNIQUE INDEX resean (resource_id, ean13),
       UNIQUE INDEX eanres (ean13, resource_id),
       FOREIGN KEY (resource_id) REFERENCES resource (id))
ENGINE=InnoDB;