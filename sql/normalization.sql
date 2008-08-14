DELIMITER $$
DROP FUNCTION IF EXISTS remove_end$$
CREATE FUNCTION remove_end(input_string VARCHAR(250),
                           remove_target VARCHAR(250))
                RETURNS VARCHAR(250)
                DETERMINISTIC
BEGIN
        DECLARE target_length INT;
        DECLARE output_string VARCHAR(250);
        SET target_length=LENGTH(remove_target);

        IF (target_length=0) THEN
           SET output_string=input_string;
        ELSEIF ( substring(input_string, 0 - target_length, target_length) = remove_target ) THEN
             SET output_string=substring(input_string, 1, length(input_string) - target_length);
        ELSE 
             SET output_string=input_string;
        END IF;
        RETURN(output_string);

END$$

DELIMITER ;
        


         
DELIMITER $$
DROP FUNCTION IF EXISTS query_sort$$
CREATE FUNCTION query_sort(input_string VARCHAR(255))
       RETURNS text
       DETERMINISTIC
BEGIN
        DECLARE orig VARCHAR(255) DEFAULT '';
        DECLARE accum VARCHAR(255) DEFAULT '';
        declare current_seg varchar(255) default '';
        declare seg_end tinyint default 0;
        declare sorted text default '';
        declare counter smallint default 1;
        declare next_sorted_seg varchar(255) default '';
        declare sorted_seg_end smallint default 0;
        declare debug text default '';
        
        set orig = input_string;
        case
                /* no string */
             when (length(input_string) = 0) then
                   return '';
             
              /* single element */
             when (instr(input_string, '&') = 0) then
                  set sorted = input_string;

             /* multiple parameters */
             else
                  if (substr(orig, -1) <> '&') then
                      set orig = concat(orig, '&');
                  end if;
                  orig_walk: while (  length(orig) > 0) do
                        set seg_end = instr(orig, '&');

                        /* only one parameter - we are done*/
                        if (seg_end = 0) then
                           set seg_end = length(orig);
                           set sorted = orig;
                           leave orig_walk;
                           
                        /* cut first segment off of orig */
                        else
                           set current_seg = substr(orig, 1, seg_end);
                           set orig = substr(orig, seg_end + 1);
                        end if;

                        if length(sorted) = 0 then
                            set sorted = current_seg; -- remember to chop trailing & later
                            
                        else
                            set counter = 1;
                            set debug = concat(debug, '[[going into sorting loop with ', current_seg, ']]');
                            sorting: while (counter <= length(sorted)) do
                                set sorted_seg_end = locate('&', sorted, counter + 1); -- alwas ends with ampersand
                                set next_sorted_seg = substr(sorted, counter, sorted_seg_end - counter);
                                set debug = concat(debug, '[[starting sorting loop iteration, next_sorted_seg is ', next_sorted_seg, 'sorted is ', sorted,  ']]');
                                case 

                                       -- current_seg goes before other seg (and we are at beginning of sorted)
                                      when ((strcmp(current_seg, next_sorted_seg) = -1) and
                                            (counter < 2)) then
                                           set sorted = concat( current_seg, sorted);
                                           set debug = concat(debug, '[[putting ', current_seg, ' at very front ', next_sorted_seg, ']]');
                                           set current_seg = '';
                                           set sorted_seg_end = 0;
                                           set counter = 0;
                                           leave sorting;
                                      
                                      -- current_seg goes before next_sorted_seg but after others
                                      when ((strcmp(current_seg, next_sorted_seg) = -1) and
                                            (counter > 2)) then
                                            set sorted = concat(
                                                                substr(sorted, 1, counter -1),
                                                                current_seg,
                                                                substr(sorted, counter));
                                            set current_seg = '';
                                            set sorted_seg_end = 0;
                                            set counter = 0;
                                            leave sorting;
                                                                       
                                       -- current_seg goes after last seg in sorted
                                      when ((strcmp(current_seg, next_sorted_seg) = 1) and
                                            (sorted_seg_end = length(sorted))) then
                                           set sorted = concat( sorted,  current_seg);
                                           set debug = concat( debug, '[[putting ', current_seg, ' after (at end) ', next_sorted_seg, ']]');
                                           set current_seg = '';
                                           set sorted_seg_end = 0;
                                           set counter = 0;
                                           leave sorting;

                                      when ((strcmp(current_seg, next_sorted_seg) = 1) and
                                            (strcmp(current_seg, 
                                                    substring(sorted,
                                                              sorted_seg_end + 1,
                                                              locate('&', sorted, sorted_seg_end + 1) 
                                                                   - sorted_seg_end + 1)) = 0)) then
                                            set sorted = concat(
                                                           substr(sorted, 1, sorted_seg_end),
                                                           current_seg,
                                                           substr(sorted, sorted_seg_end + 1));
                                            set debug = concat(debug, '[[putting ', current_seg, ' after ', next_sorted_seg, ']]');
                                            set current_seg = '';
                                            set sorted_seg_end = 0;
                                            set counter = 0;
                                            leave sorting;
                                      else
                                            set counter = sorted_seg_end + 1;
                                            set debug = concat(debug, '[[nothing yet for ', current_seg, ', counter is ', counter, ']]');
                                      end case;
                                  end while;
                                  end if;
                                end while;
                              end case;
--                          return concat(sorted, '/////', debug);
                            if (substr(sorted, -1) = '&') then
                               set sorted = substr(sorted, 1, length(sorted) -1);
                            end if;
                            return sorted;
end$$
delimiter ;
                                           
                                                  

delimiter $$
drop procedure if exists tmp_test$$
create procedure tmp_test(inpu VARCHAR(255))
begin
        declare a_number int default 0;
       create temporary table tmpStuff(
              id int primary key auto_increment,
              thing varchar(255) ) engine=memory;
       insert  into tmpStuff set thing=inpu;
       select id into a_number from tmpStuff where thing=inpu;

       select * from tmpStuff;
       drop table tmpStuff;
end$$
delimiter ;


                          
delimiter $$
drop function if exists url_whack$$
create function url_whack(input_url VARCHAR(255))
       RETURNS VARCHAR(255)
       DETERMINISTIC
begin
        DECLARE my_url VARCHAR(255) DEFAULT '' ;
        DECLARE query_part VARCHAR(255) DEFAULT '';
        DECLARE query_start INT DEFAULT 0;

        SET my_url=LOWER(input_url);

        IF (INSTR(my_url, '#')) THEN 
          SET my_url = SUBSTRING(my_url, 1, INSTR(my_url, '#') - 1);
        END IF;

        IF (INSTR(my_url, '?')) THEN
           SET query_part=query_sort(
                                    SUBSTRING(my_url, 
                                              INSTR(my_url, '?') + 1));
           SET my_url=SUBSTRING(my_url, 1,
                                INSTR(my_url, '?'));
           SET my_url = CONCAT(my_url, query_part);
        END IF;                                                                
        

        IF ( SUBSTRING(my_url, 1, 7) = 'http://') THEN
                SET my_url=substring(my_url, 8);
        END IF;
        IF (SUBSTRING(my_url, 1, 4) = 'www.') THEN
           SET my_url=SUBSTRING(my_url, 5);
        END IF; 

        IF (INSTR(my_url, ':80') AND
         ( INSTR(my_url, ':80/') < INSTR(my_url, '/'))) then  -- port number before 1st slash
           SET my_url = CONCAT(
                SUBSTR(my_url, 1, INSTR(my_url, ':80/') - 1),
                SUBSTR(my_url, INSTR(my_url, ':80/') + 3));
        END IF;

        IF (INSTR(my_url, ':80?') AND
            (INSTR(my_url, ':80?') < INSTR(my_url, '/'))) THEN
            SET  my_url = CONCAT(
                SUBSTR(my_url, 1, INSTR(my_url, ':80?') - 1),
                SUBSTR(my_url, INSTR(my_url, ':80?') + 3));
        END IF;  

        
        SET my_url=remove_end(my_url, 'index.php');
        SET my_url=remove_end(my_url, 'index.html');
        SET my_url=remove_end(my_url, 'index.htm');
        SET my_url=remove_end(my_url, '/');         
        SET my_url=remove_end(my_url, '?');

        RETURN(my_url);        
END$$
DELIMITER ;

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
DECLARE existence VARCHAR(255) DEFAULT '';

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

                  IF (LENGTH(existence) > 0) THEN
                        EXECUTE old_url USING @cururl;
                  ELSE
                        EXECUTE new_url USING @cururl, @cururl, @curtit, @useridentifier;
                  END IF;        
         END IF;
    END WHILE walking;

END$$
DELIMITER ;




DELIMITER $$
DROP PROCEDURE IF EXISTS url_visit$$
CREATE PROCEDURE url_visit(url VARCHAR(255),
                           title VARCHAR(255),
                           userid INT)

BEGIN
DECLARE found_url VARCHAR(255) DEFAULT '';
DECLARE url_check VARCHAR(255) DEFAULT '';

        SET url_check = url_whack(url);

        SELECT uri_normal 
           INTO found_url
           FROM resource
           WHERE uri_normal = url_check;

        IF (LENGTH(found_url))  THEN
            UPDATE resource 
               SET visited = visited + 1
               WHERE uri_normal = url_check;
        ELSE
            INSERT INTO resource
                    (uri_normal, uri_raw, title, added_by) 
                    VALUES (url_check, url, title, userid);
        END IF;

END$$
DELIMITER ;


-- call bulk_visit(
--      'http://www.example.com&&&&&http://fabula.org&&&&&http://ditl.info&&&&&http://www.ditl.info&&&&&http://www.selfxx.org',
--     'Example&&&&&Fab1&&&&&Ditl1&&&&&Ditl2&&&&&Self', 5);