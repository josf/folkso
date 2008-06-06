-- TAG MANAGEMENT

-- Create new tag

-- Normalize tag

-- (local-set-key [(control c) (b)] 'sql-snip) 
--(defun sql-snip () (interactive) (snippet-insert "set final_tag = replace(final_tag, '$${1}', '$${2}');
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

        select id 
               into existing_id 
               from tag 
               where tagnorm = normed;

        if (existing_id = 0) then 
           insert into tag
                  set tagnorm = normed,
                      tagdisplay = input_tag;
           set existing_id = last_insert_id();
        end if;

        SELECT id FROM tag WHERE id = existing_id;


END$$
DELIMITER ;


-- tag page We recommend calling url_visit _before_ calling this
-- procedure, to make sure that the resource already
-- exists. Especially true if this were to be used for external URIs.
DELIMITER $$
DROP PROCEDURE IF EXISTS tag_resource$$
CREATE PROCEDURE tag_resource(resource_uri      varchar(255),
                              tag_id            integer)
BEGIN

        declare existing_tag_id int unsigned;
        declare existing_uri varchar(255);      
        declare out_status varchar(255);
--        declare exit handler for 1048
--                set out_status='Tag does not exist';


        select id
               into existing_tag_id
               from tag
               where id = tag_id;

       select id
              into existing_uri
              from resource
              where uri_normal = url_whack(resource_uri);

       insert into tagevent
              set tag_id = existing_tag_id,
              resource_id = existing_uri,
              user_id = 9999;
end$$
DELIMITER ;
           



delimiter $$
drop procedure if exists update_tag_popularity$$
create procedure update_tag_popularity()

BEGIN
        DECLARE l_last_row_fetched INT;
        declare this_tag int;
        DECLARE tag_c CURSOR FOR
                SELECT id FROM tag;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

        DROP TABLE IF EXISTS tag_popularity;
        CREATE TABLE tag_popularity
               (tag_id INT UNSIGNED PRIMARY KEY,
               popularity INT UNSIGNED, INDEX pop (tag_id));

        SET l_last_row_fetched = 0;
        OPEN tag_c;
        read_tags: LOOP
                   FETCH tag_c INTO this_tag;
                   IF l_last_row_fetched=1 THEN
                      LEAVE read_tags;
                   END IF;

                   INSERT INTO tag_popularity
                          SET tag_id = this_tag,
                          popularity = (SELECT COUNT(id) 
                                                    FROM tagevent te
                                                    WHERE te.tag_id = this_tag);
        END LOOP read_tags;
        CLOSE tag_c;
        SET l_last_row_fetched=0;

END$$
DELIMITER ; 



DELIMITER $$
DROP PROCEDURE IF EXISTS cloudy$$
CREATE PROCEDURE cloudy(url varchar(255), 
                        localweight int,
                        globalweight int)

BEGIN

DECLARE url_norm VARCHAR(255);

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
               tpop.popularity AS pop
       FROM tag
            JOIN tagevent te ON te.tag_id = tag.id
            JOIN resource res ON res.id = te.resource_id
            JOIN tag_popularity tpop ON tpop.tag_id = tag.id
       WHERE res.uri_normal = url_whack(url)
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
               where output_temp_table3.globalpop >= ottglobalpop)) as weight
       from output_temp_table;
       

DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

-- because of a mysql bug
-- (http://dev.mysql.com/doc/refman/5.1/en/temporary-table-problems.html),
-- you cannot refer to a temporary table with an alias. Therefore, we
-- create two, no three! identical tables...

DROP TABLE IF EXISTS output_temp_table;
CREATE TABLE output_temp_table
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









