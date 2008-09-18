-- TAG MANAGEMENT
-- Create new tag
-- Normalize tag
-- (local-set-key [(control c) (b)] 'sql-snip) 
-- (defun sql-snip () (interactive) (snippet-insert "set final_tag = replace(final_tag, '$${1}', '$${2}');
-- "))

delimiter $$
drop function if exists normalize_tag$$
create function normalize_tag(input_tag varchar(255))
       returns varchar(120)
       deterministic
begin
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


-- new_tag()
DELIMITER $$
DROP PROCEDURE if exists new_tag$$
CREATE PROCEDURE new_tag(input_tag varchar(255))
BEGIN
        DECLARE existing_id INTEGER DEFAULT 0;
        DECLARE normed VARCHAR(255) DEFAULT '';
        SET normed = normalize_tag(input_tag); 

        SELECT id 
               INTO existing_id 
               FROM tag 
               WHERE tagnorm = normed;

        IF (existing_id = 0) THEN 
           INSERT INTO tag
                  SET tagnorm = normed,
                      tagdisplay = input_tag;
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
CREATE PROCEDURE cloudy(residarg int,
                        urlarg varchar(255), 
                        localweight int,
                        globalweight int)

BEGIN

DECLARE url VARCHAR(255);
DECLARE url_norm VARCHAR(255);
DECLARE resid INT;

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

DECLARE finaldata CURSOR FOR
SELECT tagdisplay,
       tagnorm,
       tagid,
       globalpop AS ottglobalpop,
       localpop AS ottlocalpop,
       (localweight *
       (select count(distinct output_temp_table2.tagid)
               from
               output_temp_table2 
               where output_temp_table2.localpop <= ottlocalpop)) +
       (globalweight *
       (select count(distinct output_temp_table3.tagid)
               from
               output_temp_table3
               where output_temp_table3.globalpop <= ottglobalpop)) as weight
       from output_temp_table;
       

DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

-- because of a mysql bug
-- (http://dev.mysql.com/doc/refman/5.1/en/temporary-table-problems.html),
-- you cannot refer to a temporary table with an alias. Therefore, we
-- create two, no three! identical tables...

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

IF residarg > 0 THEN
   SET resid = residarg;
ELSE
   SELECT id 
   INTO resid
   FROM resource
   WHERE uri_normal = url_whack(urlarg);
END IF;


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
                    tagdisplay   = displayv,
                    tagnorm      = normv,
                    tagid     = tagidv,
                    localpop  = localpopv,
                    globalpop = globalpopv;
         
END LOOP cursing;
CLOSE ourdata;      
SET l_last_row_fetched=0;


DROP TABLE IF EXISTS final_output;
CREATE TEMPORARY TABLE final_output
       (tagdisplay VARCHAR(255) NOT NULL, 
       tagnorm VARCHAR(255) NOT NULL, 
       tagid INT UNSIGNED NOT NULL, 
       weight INT UNSIGNED NOT NULL);

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

select max(weight)
       into maxweight
       from final_output;

select min(weight)
       into minweight
       from final_output;


select r.title as tagdisplay, r.uri_raw as tagnorm, r.id as tagid, NULL as weight, NULL as cloudweight
       from resource r
       where r.id = resid
union
select tagdisplay, tagnorm, tagid, weight,
       case  
             when (weight - minweight) > 0.8 * (maxweight - minweight) then 5
             when (weight - minweight) > 0.6 * (maxweight - minweight) then 4
             when (weight - minweight) > 0.4 * (maxweight - minweight) then 3
             when (weight - minweight) > 0.2 * (maxweight - minweight) then 2
       else 1
       end as cloudweight
       from final_output;

END$$
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

