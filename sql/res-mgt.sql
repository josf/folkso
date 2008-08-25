
-- REMOVE RESOURCE
DELIMITER $$
DROP PROCEDURE IF EXISTS rmres$$
CREATE PROCEDURE rmres (res_norm VARCHAR(250),
                        res_id INT)

BEGIN

DECLARE real_id INT;

IF (res_id > 0) THEN
   SET real_id = res_id;
ELSE
   SELECT id
   INTO real_id
   FROM resource
   WHERE uri_normal = res_norm;
END IF;

INSERT INTO exclude SET uri_normal = res_norm;

DELETE FROM te, r 
USING resource r JOIN tagevent te ON r.id = te.resource_id
WHERE r.id = real_id;

end$$
delimiter ;


