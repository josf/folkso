-- TAG MANAGEMENT
-- Create new tag
-- Normalize tag
-- (local-set-key [(control c) (b)] 'sql-snip) 
-- (defun sql-snip () (interactive) (snippet-insert "set final_tag = replace(final_tag, '$${1}', '$${2}');
-- "))

DELIMITER $$
DROP FUNCTION IF EXISTS normalize_tag$$
CREATE FUNCTION normalize_tag(input_tag VARCHAR(255))
       RETURNS VARCHAR(120)
       DETERMINISTIC
BEGIN
        DECLARE final_tag VARCHAR(255) DEFAULT '';

        SET final_tag = lower(input_tag);

        set final_tag = replace(final_tag, ' ', '');
        set final_tag = replace(final_tag, '.', '');
        set final_tag = replace(final_tag, ':', '');
        set final_tag = replace(final_tag, ';', '');
        set final_tag = replace(final_tag, ',', '');
        set final_tag = replace(final_tag, '!', '');
        set final_tag = replace(final_tag, '?', '');
        set final_tag = replace(final_tag, '/', '');
        set final_tag = replace(final_tag, '\\', '');
        set final_tag = replace(final_tag, '{', '');
        set final_tag = replace(final_tag, '}', '');
        set final_tag = replace(final_tag, '=', '');
        set final_tag = replace(final_tag, '$', '');
        set final_tag = replace(final_tag, '<', '');
        set final_tag = replace(final_tag, '>', '');
        set final_tag = replace(final_tag, '-', '');
        set final_tag = replace(final_tag, '"', '');
        set final_tag = replace(final_tag, '''', '');

        -- shorten
        if (length(final_tag) > 120) then
            set final_tag = substr(final_tag, 1, 120);
        end if;

        return(final_tag);
end$$
delimiter ;

delimiter $$
drop function if exists novel_normal$$
create function novel_normal(input_tag varchar(255))
       returns varchar(120)
       deterministic

begin
        declare output_tag varchar(255) default '';
        declare counter int default 1;
        declare current_char varchar(1) default '';
        declare prev_char varchar(1) default '';
        declare replaced_char varchar(1) default '';

        set input_tag  = lower(input_tag);

        while (counter <= char_length(input_tag)) do

                set current_char = substr(input_tag, counter, 1);
                set prev_char = substr(output_tag, -1);
                set counter = counter + 1;

                -- we just take the lower case ascii alphabet here
                if (((ord(current_char) < 123)  
                   and
                   (ord(current_char) > 96))
                   or
                   ((ord(current_char) > 47) and
                    (ord(current_char) < 58)) 
                    or
                    (current_char = '-')) then
                    -- if original tag has a hyphen, we leave it but
                    -- won't add any more later

                   set output_tag = concat(output_tag, current_char);
                 -- otherwise we look up the characters in the table
                 else
                        select replacewith
                        into replaced_char 
                        from replace_characters
                        where ord(current_char) = toreplace_code;
                                    
                 -- if previous character was a -, we don't add another one
                  if ((length(replaced_char) > 0) and
                     (((prev_char <> '-')
                        or
                        ((prev_char = '-') and (replaced_char <> '-'))))) then
                        set output_tag = concat(output_tag, replaced_char);
                  end if;
                  -- any other characters are simply discarded
              end if;
        end while;

        -- avoid tags ending with a hyphen
        if (substr(output_tag, -1) = '-') then
           return substr(output_tag, 1, char_length(output_tag) -1);
        end if;
return output_tag;

end$$
delimiter ;
        


-- new_tag()
DELIMITER $$
DROP PROCEDURE IF EXISTS new_tag$$
CREATE PROCEDURE new_tag(input_tag varchar(255))
BEGIN
        DECLARE existing_id INTEGER DEFAULT 0;
        DECLARE normed VARCHAR(255) DEFAULT '';
        DECLARE orig_tag VARCHAR(255) DEFAULT '';

        SET orig_tag = input_tag;

        IF  (SUBSTR(orig_tag, 1, 2) = '\\"') THEN
             SET orig_tag = SUBSTR(orig_tag, 3);
        ELSEIF  (SUBSTR(orig_tag, 1, 1) = '"') THEN
           SET orig_tag = SUBSTR(orig_tag, 2);
        END IF;

        IF (substr(orig_tag, -2, 2)  = '\\"') THEN
             SET orig_tag = SUBSTR(orig_tag, 1, CHAR_LENGTH(orig_tag) - 2);
        ELSEIF  (SUBSTR(orig_tag, -1, 1) = '"')  THEN
           SET orig_tag = SUBSTR(orig_tag, 1, CHAR_LENGTH(orig_tag));
        END IF;

        SET normed = normalize_tag(orig_tag); 

        SELECT id 
               INTO existing_id 
               FROM tag 
               WHERE tagnorm = normed;

        IF (existing_id = 0) THEN 
           INSERT INTO tag
                  SET tagnorm = normed,
                      tagdisplay = orig_tag;
           SET existing_id = LAST_INSERT_ID();
        END IF;

        SELECT id FROM tag WHERE id = existing_id;
END$$
DELIMITER ;

-- TAG A RESOURCE
-- tag page You might want to call url_visit before calling this
-- procedure, to make sure that the resource already
-- exists. Especially true if this were to be used for external URIs.
DELIMITER $$
DROP PROCEDURE IF EXISTS tag_resource$$
CREATE PROCEDURE tag_resource(resource_uri      VARCHAR(255),
                              resource_id       INT,
                              tag_name          VARCHAR(255),
                              tag_id            INT,
                              meta_name         VARCHAR(255),
                              meta_id           INT)

BEGIN
        DECLARE existing_tag_id INT UNSIGNED;
        DECLARE existing_uri VARCHAR(255);
        DECLARE existing_meta_id INT UNSIGNED;
        DECLARE out_status VARCHAR(255);
        DECLARE already_tagged INT UNSIGNED;

IF (tag_id) THEN
        SELECT id
               INTO existing_tag_id     
               FROM tag
               WHERE id = tag_id;
ELSE
        IF (SUBSTR(tag_name, 1, 1) = '"') THEN
            SET tag_name = SUBSTR(tag_name, 2);
        END IF;

        IF (SUBSTR(tag_name, -1, 1) = '"') THEN
           SET tag_name = SUBSTR(tag_name, 1, (CHAR_LENGTH(tag_name) - 1));
        END IF;
        
        SELECT id 
               INTO existing_tag_id
               FROM tag
               WHERE tagnorm = normalize_tag(tag_name);
END IF;

IF (resource_id) THEN
   SELECT id
          INTO existing_uri
          FROM resource 
          WHERE id = resource_id;
ELSE
       SELECT id
              INTO existing_uri
              FROM resource
              WHERE uri_normal = url_whack(resource_uri);
END IF;

IF (meta_id) THEN
   SELECT id
   INTO existing_meta_id
   FROM metatag
   WHERE id = meta_id;
ELSEIF (length(meta_name) > 1) THEN
   SELECT id
   INTO existing_meta_id
   FROM metatag
   WHERE tagnorm = normalize_tag(meta_name);
ELSE
   SET existing_meta_id = 1;
END IF;        

-- manually setting default value for meta_id 
IF ((existing_meta_id IS NULL) OR
   (existing_meta_id = 0)) THEN                  
   SET existing_meta_id = 1;
END IF;   

SELECT COUNT(*)
INTO already_tagged
FROM tagevent t
WHERE (t.resource_id = existing_uri)
AND (t.tag_id = existing_tag_id)
AND (user_id = 9999)
LIMIT 1;

IF (already_tagged > 0) THEN
   UPDATE tagevent t
   SET meta_id = existing_meta_id
   WHERE (t.resource_id = existing_uri)
   AND (t.tag_id = existing_tag_id)
   AND (user_id = 9999);
ELSE
    INSERT INTO tagevent
    SET tag_id = existing_tag_id,
        resource_id = existing_uri,
        meta_id = existing_meta_id,
        user_id = 9999;
END IF;

END$$
DELIMITER ;
           
-- this should no longer be a procedure
DELIMITER $$
DROP PROCEDURE IF EXISTS update_tag_popularity$$
CREATE PROCEDURE update_tag_popularity()

BEGIN

UPDATE tag SET popularity = 
         (SELECT COUNT(DISTINCT te.tag_id) 
         FROM tagevent te WHERE te.tag_id = tag.id);
END$$
DELIMITER ; 




DELIMITER $$
DROP PROCEDURE IF EXISTS cloudy$$
CREATE PROCEDURE cloudy(residarg INT,
                        urlarg VARCHAR(255), 
                        localweight INT,
                        globalweight INT,
                        taglimit INT) 
-- limit number of tags

BEGIN

DECLARE url VARCHAR(255);
DECLARE url_norm VARCHAR(255);
DECLARE resid INT;
DECLARE tagbis INT;

-- v is to indicate that these are variables, since later we have
-- identical column names
DECLARE displayv VARCHAR(255);
DECLARE normv VARCHAR(255);       
DECLARE tagidv INT;        
DECLARE localpopv INT;
DECLARE globalpopv INT;
DECLARE weightv INT;        

DECLARE maxlocal INT;
DECLARE maxglobal INT;
DECLARE maxweight INT;
DECLARE minweight INT;

DECLARE l_last_row_fetched INT default 0;

DECLARE ourdata CURSOR FOR
         SELECT  tag.tagdisplay,
               tag.tagnorm,
               tag.id,
               (SELECT COUNT(tag_id)
                       FROM tagevent tage
                       JOIN resource ON tage.resource_id = resource.id
                       WHERE (resource.uri_normal = url_whack(url)) AND 
                             (tage.tag_id = tag.id)) AS localcount,
               tag.popularity AS pop
       FROM tag
            JOIN tagevent te ON te.tag_id = tag.id
            JOIN resource res ON res.id = te.resource_id
       WHERE res.id = resid
       GROUP BY tag.id;


-- this is where we will actually calculate the relative weights of
-- the different tags
DECLARE finaldata CURSOR FOR
SELECT tagdisplay,
       tagnorm,
       tagid,
       globalpop AS ottglobalpop,
       localpop AS ottlocalpop,
       (localweight *
       (SELECT COUNT(DISTINCT output_temp_table2.tagid)
               FROM
               output_temp_table2 
               WHERE output_temp_table2.localpop <= ottlocalpop)) +
       (globalweight *
       (SELECT COUNT(DISTINCT output_temp_table3.tagid)
               FROM
               output_temp_table3
               WHERE output_temp_table3.globalpop <= ottglobalpop)) AS weight
       FROM output_temp_table;
       

DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

-- because of a mysql bug
-- (http://dev.mysql.com/doc/refman/5.1/en/temporary-table-problems.html),
-- you cannot refer to a temporary table with an alias. Therefore, we
-- create two, no three! identical tables...

SET tagbis = taglimit + 1;

DROP TABLE IF EXISTS output_temp_table;
CREATE TEMPORARY TABLE output_temp_table
       (tagid INT UNSIGNED PRIMARY KEY,
        tagnorm VARCHAR(255) NOT NULL,
        tagdisplay VARCHAR(255) NOT NULL,
        globalpop INT UNSIGNED,
        localpop INT UNSIGNED);


DROP TABLE IF EXISTS output_temp_table2;
CREATE TEMPORARY TABLE output_temp_table2
       (tagid INT UNSIGNED PRIMARY KEY,
        tagnorm VARCHAR(255) NOT NULL,
        tagdisplay VARCHAR(255) NOT NULL,
        globalpop INT UNSIGNED,
        localpop INT UNSIGNED);

DROP TABLE IF EXISTS output_temp_table3;
CREATE TEMPORARY TABLE output_temp_table3
       (tagid INT UNSIGNED PRIMARY KEY,
        tagnorm VARCHAR(255) NOT NULL,
        tagdisplay VARCHAR(255) NOT NULL,
        globalpop INT UNSIGNED,
        localpop INT UNSIGNED);


-- get numeric id if we do not have it already.
IF residarg > 0 THEN
   SET resid = residarg;
ELSE
   SELECT id 
   INTO resid
   FROM resource
   WHERE uri_normal = url_whack(urlarg);
END IF;

-- fill up our 3 identical temp tables
SET l_last_row_fetched = 0;
OPEN ourdata;
cursing: LOOP
         FETCH ourdata INTO displayv, normv, tagidv, localpopv, globalpopv;
         IF l_last_row_fetched=1 THEN
            LEAVE cursing;
         END IF;

         INSERT INTO output_temp_table 
                SET 
                    tagdisplay   = displayv,
                    tagnorm      = normv,
                    tagid     = tagidv,
                    localpop  = localpopv,
                    globalpop = globalpopv;

         INSERT INTO output_temp_table2 
                SET 
                    tagdisplay   = displayv,
                    tagnorm      = normv,
                    tagid     = tagidv,
                    localpop  = localpopv,
                    globalpop = globalpopv;

         INSERT INTO output_temp_table3
                SET 
                    tagdisplay   =      displayv,
                    tagnorm      =      normv,
                    tagid        =      tagidv,
                    localpop     =      localpopv,
                    globalpop    =      globalpopv;
         
END LOOP cursing;
CLOSE ourdata;      
SET l_last_row_fetched=0;

-- table for holding final results
DROP TABLE IF EXISTS final_output;
CREATE TEMPORARY TABLE final_output
       (tagdisplay VARCHAR(255) NOT NULL, 
       tagnorm VARCHAR(255) NOT NULL, 
       tagid INT UNSIGNED NOT NULL, 
       weight INT UNSIGNED NOT NULL);

-- calculate the respective weights of the tags (see cursor above)
SET l_last_row_fetched = 0;
OPEN finaldata;
cussing: LOOP
         FETCH finaldata INTO displayv, normv, tagidv, globalpopv, localpopv, weightv;
         IF l_last_row_fetched=1 THEN
            LEAVE cussing;
         END IF;

         INSERT INTO final_output
                SET
                tagdisplay = displayv,
                tagnorm    = normv,
                tagid      = tagidv,
                weight     = weightv;

END LOOP cussing;
CLOSE finaldata;      
SET l_last_row_fetched=0;

SELECT MAX(weight)
       INTO maxweight
       FROM final_output;

SELECT MIN(weight)
       INTO minweight
       FROM final_output;

-- and one last select against final_output to give the data back to the caller

-- the following is an ugly hack necessary because of mysql bug 11918
-- (http://bugs.mysql.com/bug.php?id=11918) which does not allow
-- variables in the LIMIT clause of a select. The bug was opened in
-- 2005...

IF (taglimit>0) THEN
 SET @minw=minweight;  
 SET @maxw=maxweight;
 SET @sql=concat('SELECT r.title AS tagdisplay, r.uri_raw AS tagnorm, r.id AS tagid, 5 AS weight, 5 AS cloudweight
       FROM resource r
       WHERE r.id =', resid,
  ' UNION
  SELECT tagdisplay, tagnorm, tagid, weight,
       CASE  
             WHEN (weight - ', @minw, ') > 0.8 * (', @maxw, ' -  ', @minw, ') THEN 5
             WHEN (weight -  ', @minw, ') > 0.6 * (', @maxw, ' -  ', @minw, ') THEN 4
             WHEN (weight -  ', @minw, ') > 0.4 * (', @maxw, ' -  ', @minw, ') THEN 3
             WHEN (weight -  ', @minw, ') > 0.2 * (', @maxw, ' -  ', @minw, ') THEN 2
       ELSE 1
       END AS cloudweight
  FROM final_output
  ORDER BY cloudweight DESC
  LIMIT ',  tagbis);
  PREPARE STMT FROM @sql; 
  EXECUTE STMT;

ELSE
        SELECT r.title AS tagdisplay, r.uri_raw AS tagnorm, r.id AS tagid, NULL AS weight, NULL AS cloudweight
               FROM resource r
               WHERE r.id = resid
        UNION
        SELECT tagdisplay, tagnorm, tagid, weight,
               CASE  
               WHEN (weight - minweight) > 0.8 * (maxweight - minweight) THEN 5
               WHEN (weight - minweight) > 0.6 * (maxweight - minweight) THEN 4
               WHEN (weight - minweight) > 0.4 * (maxweight - minweight) THEN 3
               WHEN (weight - minweight) > 0.2 * (maxweight - minweight) THEN 2
       ELSE 1
       END AS cloudweight
       FROM final_output;
END IF;

END$$
DELIMITER ;


/**
 *  With a resource (id or url) as argument, returns a
 *  dataset weighted by the last time each tag was added to the 
 *  resource. Higher numbers in the 'weight' field that is returned 
 *  indicate that the tag was added more recently.
 */

DELIMITER $$
DROP PROCEDURE IF EXISTS cloud_by_timestamp$$
CREATE PROCEDURE cloud_by_timestamp(    resid INT,
                                        resurl VARCHAR(255))
BEGIN

SELECT ta.id AS tagid,
       ta.tagdisplay AS tagdisplay,
       ta.tagnorm AS tagnorm,
       ta.popularity AS popularity,
       te.tagtime AS tagtime,
       CASE
              WHEN DATEDIFF(NOW(), te.tagtime) < 7 THEN 5
              WHEN DATEDIFF(NOW(), te.tagtime) < 30 THEN 4
              WHEN DATEDIFF(NOW(), te.tagtime) < 60 THEN 3
              WHEN DATEDIFF(NOW(), te.tagtime) < 180 THEN 2
              ELSE 1
       END AS weight
       FROM tag ta JOIN tagevent te ON ta.id = te.tag_id
       JOIN resource r ON te.resource_id = r.id
       WHERE (r.id = resid)
       OR (r.uri_normal = url_whack(resurl));

END $$
DELIMITER ;




-- 
-- tagmerge
-- 
-- Four arguments so that either tag_ids or strings can be used.
-- 
-- Do not forget to include all four.

DELIMITER $$
DROP PROCEDURE IF EXISTS tagmerge$$
CREATE PROCEDURE tagmerge(source_id_arg INT,
                          source_str_arg VARCHAR(255),
                          target_id_arg INT,
                          target_str_arg VARCHAR(255))
BEGIN

DECLARE source_id INT;
DECLARE target_id INT;
DECLARE return_statement VARCHAR(15);

IF (source_id_arg > 0) THEN
   SELECT id 
   INTO source_id
   FROM tag
   WHERE id = source_id_arg;
ELSE
   SELECT id
   INTO source_id
   FROM tag
   WHERE tagnorm = normalize_tag(source_str_arg);
END IF;

IF (target_id_arg > 0) THEN
   SELECT id
   INTO target_id
   FROM tag
   WHERE id = target_id_arg;
ELSE
   SELECT id
   INTO target_id
   FROM tag
   WHERE tagnorm = normalize_tag(target_str_arg);
END IF;

CASE
  WHEN (target_id is null) THEN
       SET return_statement = 'NOTARGET';
  WHEN (source_id is null) then
       SET return_statement = 'NOSOURCE';
  ELSE
       UPDATE tagevent
         SET tag_id = target_id
         WHERE tag_id = source_id;
       DELETE 
         FROM tag 
         WHERE id = source_id;

       UPDATE tag
          SET popularity = (SELECT COUNT(distinct resource_id)
                                   FROM tagevent te
                                   WHERE te.tag_id = target_id)
          WHERE id = target_id;
         SET return_statement = 'OK';
END CASE;

SELECT return_statement AS status, target_id AS newid;

END$$
DELIMITER ;


DELIMITER $$
DROP PROCEDURE IF EXISTS metamod$$
CREATE PROCEDURE metamod(       resource_id_arg INT,
                                resource_url_arg VARCHAR(255),
                                tag_id_arg INT,
                                tag_str_arg VARCHAR(255),
                                new_meta_id_arg INT,
                                new_meta_str_arg VARCHAR(255))

BEGIN

DECLARE resid INT;
DECLARE tagid INT;
DECLARE metaid INT;

IF (resource_id_arg > 0) THEN
   SET resid = resource_id_arg;
ELSE
   SELECT id
   INTO resid
   FROM resource
   WHERE uri_normal = url_whack(resource_url_arg);
END IF;

IF (tag_id_arg > 0) THEN
   SET tagid = tag_id_arg;
ELSE
   SELECT id 
   INTO tagid
   FROM tag
   WHERE tagnorm = normalize_tag(tag_str_arg);
END IF;

IF (new_meta_id_arg > 0) THEN
      SET metaid = new_meta_id_arg;
ELSE
      SELECT id
      INTO metaid
      FROM metatag 
      WHERE tagnorm = normalize_tag(new_meta_str_arg);
END IF;

UPDATE tagevent
       SET meta_id = metaid
       WHERE (resource_id = resid)
             AND
             (tag_id = tagid);

END$$
DELIMITER ; 

DELIMITER $$
DROP PROCEDURE IF EXISTS tagrank$$
CREATE PROCEDURE tagrank()

BEGIN

DECLARE atag_id INT DEFAULT 0;
DECLARE arank INT DEFAULT 0;
DECLARE dummyvar INT;

DECLARE l_last_row_fetched INT default 0;

DECLARE reading CURSOR FOR
SELECT t.id, t.popularity, COUNT(tt.popularity) AS rank
FROM tag t JOIN tag tt ON         
         t.popularity < tt.popularity 
         OR (t.popularity=tt.popularity AND t.id = tt.id)
GROUP BY t.id;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

SET l_last_row_fetched=0;
OPEN reading;
cursing: LOOP
         FETCH reading INTO atag_id, dummyvar, arank;
         IF l_last_row_fetched=1 THEN
            LEAVE cursing;
         END IF;

         UPDATE tag SET rank = arank WHERE id = atag_id; 
END LOOP cursing;
CLOSE reading;
SET l_last_row_fetched=0;

END$$
DELIMITER ;
