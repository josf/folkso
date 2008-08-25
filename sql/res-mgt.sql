
-- Bulk visit
DELIMITER $$
DROP PROCEDURE IF EXISTS bulk_visit$$
CREATE PROCEDURE bulk_visit(urls_arg TEXT,
                            titles_arg TEXT,
                            userid INT)

BEGIN

DECLARE titles TEXT DEFAULT '';
DECLARE urls TEXT DEFAULT '';

DECLARE remaining_titles TEXT DEFAULT '';
DECLARE remaining_urls TEXT DEFAULT '';

DECLARE current_url TEXT DEFAULT '';
DECLARE current_title TEXT DEFAULT '';

-- whether or not the resource is already present
DECLARE existence VARCHAR(255) DEFAULT '';
-- whether or not the resource is in the exclude table
DECLARE excludep VARCHAR(10) DEFAULT '';

SET @useridentifier = userid;
SET urls = urls_arg;
SET titles = titles_arg;

PREPARE new_url FROM  'INSERT INTO resource
                      SET 
                         uri_raw = ?, 
                         uri_normal = url_whack(?), 
                         title = ?,
                         added_by = ?,
                         last_visited = NOW()';

PREPARE old_url FROM 'UPDATE resource 
                             SET visited = visited + 1, 
                             last_visited = NOW() 
                             where uri_normal = url_whack(?)';

walking: WHILE LENGTH(urls) > 0 DO
         IF (INSTR(urls, '&&&&&')) THEN
            SET current_url = 
                SUBSTRING(urls, 1, INSTR(urls, '&&&&&') - 1);

            SET remaining_urls = SUBSTRING(urls, INSTR(urls, '&&&&&') + 5);
            SET current_title = SUBSTRING(titles, 1, INSTR(titles, '&&&&&') -1);
            SET remaining_titles = SUBSTRING(titles, INSTR(titles, '&&&&&') + 5);
            SET urls = remaining_urls;
            SET titles = remaining_titles;

         ELSE -- last or single url

            SET current_url = urls;
            SET current_title = titles;

            -- avoid infinite loop
            SET urls = '';
            SET titles = '';

         END IF;

         SET existence = '';

         SELECT uri_normal 
             INTO existence
             FROM resource 
             WHERE uri_normal = url_whack(current_url);

         IF (LENGTH(current_url) > 0) THEN

                  SET @cururl = current_url;
                  SET @curtit = current_title;

                  select substr(uri_normal, 1, 9)
                  into excludep
                  from exclude
                  where uri_normal = url_whack(current_url);

                  IF (LENGTH(excludep) = 0) THEN
                  IF (LENGTH(existence) > 0) THEN
                        EXECUTE old_url USING @cururl;
                  ELSE
                        EXECUTE new_url USING @cururl, @cururl, @curtit, @useridentifier;
                  END IF;        
         END IF;
         END IF; -- do nothing if url found in exclude
    END WHILE walking;

END$$
DELIMITER ;



-- REMOVE RESOURCE
DELIMITER $$
DROP PROCEDURE IF EXISTS rmres$$
CREATE PROCEDURE rmres (res VARCHAR(255),
                        res_id INT)

BEGIN

DECLARE real_id INT;
DECLARE res_norm VARCHAR(255);

IF (res_id > 0) THEN
   SET real_id = res_id;

   SELECT uri_normal
   INTO res_norm
   FROM resource
   WHERE id = res_id;
ELSE
   SET res_norm = url_whack(res);
   SELECT id
   INTO real_id
   FROM resource
   WHERE uri_normal = res_norm;
END IF;

INSERT INTO exclude SET uri_normal = res_norm;

DELETE FROM te, r 
USING resource r JOIN tagevent te ON r.id = te.resource_id
WHERE r.id = real_id;

END$$
DELIMITER ;


