-- each newly indexed resource should be checked against this table
-- and rejected if found here.

DROP TABLE IF EXISTS exclude;
CREATE TABLE exclude
       (uri_normal VARCHAR(255) PRIMARY KEY,
       exclusion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP);