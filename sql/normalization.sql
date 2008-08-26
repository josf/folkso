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
        DECLARE current_seg VARCHAR(255) DEFAULT '';
        DECLARE seg_end TINYINT DEFAULT 0;
        DECLARE sorted TEXT DEFAULT '';
        DECLARE counter SMALLINT DEFAULT 1;
        DECLARE next_sorted_seg VARCHAR(255) DEFAULT '';
        DECLARE sorted_seg_end SMALLINT DEFAULT 0;
        DECLARE debug TEXT DEFAULT '';
        DECLARE orig_loop_counter INT DEFAULT 0;        
        DECLARE sorting_loop_counter INT DEFAULT 0;        

        SET orig = input_string;
        CASE
                /* no string */
             WHEN (LENGTH(input_string) = 0) THEN
                   RETURN '';
             
              /* single element */
             WHEN (INSTR(input_string, '&') = 0) THEN
                  SET sorted = input_string;

             /* multiple parameters */
             ELSE
                  IF (SUBSTR(orig, -1) <> '&') THEN
                      SET orig = CONCAT(orig, '&');
                  END IF;
                  orig_walk: while (  length(orig) > 0) do
                        SET orig_loop_counter = orig_loop_counter + 1;
                        if orig_loop_counter > 100 then
                           set orig_loop_counter = 0;
                           leave orig_walk;
                        end if;

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

                        /* this is the first segment we have to sort */
                        if length(sorted) = 0 then
                            set sorted = current_seg; -- remember to chop trailing & later
                            set current_seg = '';                            
                        else
                            set counter = 1;
                            set debug = concat(debug, '[[going into sorting loop with ', current_seg, ']]');

                            sorting: while (counter <= length(sorted)) do

                                /* infinite loop check */                            
                                set sorting_loop_counter = sorting_loop_counter + 1;
                                if sorting_loop_counter > 100 then
                                   set sorting_loop_counter = 0;
                                   leave sorting;
                                end if;

                                -- a segment always ends with ampersand, so we start looking on the next char (counter + 1)
                                -- sorted_seg_end is the position of the end of the next segment
                                -- next_sorted_seg is the string of the next (ie. after counter) segment already in 'sorted'
                                set sorted_seg_end = locate('&', sorted, counter + 1); 
                                set next_sorted_seg = substr(sorted, 
                                                             counter, 
                                                             sorted_seg_end - counter);
                                set debug = concat(debug, 
                                            '[[starting sorting loop iteration, next_sorted_seg is ', 
                                            next_sorted_seg, 'sorted is ', sorted,  ']]');

                                case 
                                      -- current_seg already present in sorted (duplicate parameters)
                                      -- some acrobatics to avoid the case where a parameter is a substring 
                                      -- of another.
                                      when (
                                      -- current_seg is there but is not first segment (no preceding &)
                                           (locate(concat('&', current_seg), sorted) > 0) or  
                                       -- current_seg is first segment
                                          (locate(current_seg, sorted) = 1)) then
                                          set debug = concat(debug, '[[ignoring repetition of ', current_seg, ' found in sorted: ', sorted, ']]');
                                          set current_seg = '';
                                          set sorted_seg_end = 0;
                                          set counter = 0;
                                          leave sorting;

                                       -- current_seg goes before other seg (and we are at beginning of sorted)
                                      when ((strcmp(current_seg, next_sorted_seg) = -1) and
                                            (counter < 2)) then          -- means: = 1, since counter starts at 1
                                           set sorted = concat( current_seg, sorted);
                                           set debug = concat(debug, '[[putting ', current_seg, ' at very front ', next_sorted_seg, ']]');
                                           set current_seg = '';
                                           set sorted_seg_end = 0;
                                           set counter = 0;
                                           leave sorting;
                                      
                                      -- current_seg goes before  next_sorted_seg but after
                                      -- others (meaning we are not at the beginning of sorted
                                      -- anymore)
                                      when ((strcmp(current_seg, next_sorted_seg) = -1) and
                                            (counter > 2)) then
                                            set sorted = concat(
                                                                substr(sorted, 1, counter -1),
                                                                current_seg,
                                                                substr(sorted, counter));
                                            set debug = concat(debug, '[[putting ', current_seg, ' between ', next_sorted_seg, ' and others ]]');
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

                                      -- current_seg goes after next_sorted_seg and before the following segment.
                                      when ((strcmp(current_seg, next_sorted_seg) = 1) and
                                            (strcmp(current_seg, 
                                                    substring(sorted,
                                                              sorted_seg_end + 1,
                                                              locate('&', sorted, sorted_seg_end + 1) 
                                                                   - sorted_seg_end + 1)) = -1)) then  -- this might have been the bug! (was "= 0")
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
                                      -- default: nowhere to insert, so we keep going.
                                            set counter = sorted_seg_end + 1;
                                            set debug = concat(debug, '[[nothing yet for ', current_seg, ', sorted is ', sorted , ']]');
                                      end case;
                                  end while;
                                  end if;
                                  
                                end while;
                              end case;
--                          return concat(sorted, '/////', debug);
                            if (substr(sorted, -1) = '&') then
                               set sorted = substr(sorted, 1, length(sorted) -1);
                            end if;

                            -- cleaning up. no idea if this is necessary.
                            set orig = '';
                            set accum = '';
                            set current_seg = '';
                            set seg_end = 0;
                            set counter = 0;
                            set next_sorted_seg = '';
                            set sorted_seg_end = 0;
                            set debug = '';
                            set loop_counter = 0;
                            
                            -- and finally...
--                            return sorted;
                            return concat(sorted, debug);

END$$
DELIMITER ;
                                           
                                                  

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