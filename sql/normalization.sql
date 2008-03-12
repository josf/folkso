delimiter $$
drop function if exists remove_end$$
create function remove_end(input_string VARCHAR(250),
                           remove_target VARCHAR(250))
                RETURNS VARCHAR(250)
                DETERMINISTIC
BEGIN
        declare target_length INT;
        declare output_string VARCHAR(250);
        set target_length=length(remove_target);

        IF (target_length=0) THEN
           SET output_string=input_string;
        ELSEIF ( substring(input_string, 0 - target_length, target_length) = remove_target ) THEN
             SET output_string=substring(input_string, 1, length(input_string) - target_length);
        ELSE 
             SET output_string=input_string;
        END IF;
        RETURN(output_string);

END$$

delimiter ;

delimiter $$
drop function if exists alphabet_score$$
create function alphabet_score (word varchar(255))
       returns bigint
       deterministic
begin
        declare depth tinyint default 12;
        declare counter tinyint default 1;
        declare best_score bigint default 0;
        declare current_score bigint default 0;
        declare current_character char;
        declare char_value tinyint;

        while (length(word) >= counter) do
              set current_character = substring(word, counter, 1);
              select rank 
                     into char_value
                     from alphabet
                     where letter = current_character;
              set score = score + char_value * power(10, depth);
              set depth = depth - 1;
              set counter = counter + 1;
        end while;
        return score;               
end$$
delimiter ;
        


delimiter $$
drop function if exists param_sort$$
create function param_sort( input_string VARCHAR(255))
       returns varchar(255)
       deterministic

begin
        declare accum varchar(255) default '';
        declare are_here smallint default 1;
        declare current_param varchar(255) default '';
        declare current_param_score bigint default 0;
        declare current_best_score bigint default 0;
        declare current_word varchar(255) default '';
        declare next_word_end tinyint default 0;
        declare next_amp tinyint default 0;
        declare next_equ tinyint default 0;
        declare next_segment_end tinyint default 0;
        declare remaining varchar(255) default '';


        set remaining = input_string;
        while (length(remaining) > 0) do
              set next_amp = locate('&', input_string, are_here);
              set next_equ = locate('=', input_string, are_here);

              case
                when (next_amp  = 0) and
                     (next_equ  = 0) then
                      set next_word_end = length(input_string);
                      set next_segment_end = length(input_string) + 1;

                when (next_amp > 0) and
                     (next_equ = 0) then
                      set next_word_end = next_amp;
                      set next_segment_end = next_amp + 1;

                when (next_amp  = 0) and
                     (next_equ > 0) then
                     set next_word_end = next_equ;
                     set next_segment_end = length(input_string) + 1;

                when (next_amp > 0) and
                     (next_equ > 0) and
                     (next_amp > next_equ) then
                     set next_word_end = next_equ;
                     set next_segment_end = next_amp + 1;

                when (next_amp > 0) and
                     (next_equ > 0) and
                     (next_equ > next_amp) then
                     set next_word_end = next_amp;
                     set next_segment_end = next_amp + 1;
                else
                     set next_word_end = length(input_string);
                     set next_segment_end = length(input_string) + 1;
                     /* this should probably be an error... */
             end case;
             
             set current_word = substr(input_string, are_here, next_word_end - are_here);
             set current_param_score = alphabet_score(current_word);
             set accum = concat(accum, '##', cast(current_param_score as char), '##', current_word, '##');
             set are_here = next_segment_end;
        end while;
        return accum;
end$$
delimiter ;

         
delimiter $$
drop function if exists find_best_sequence$$
create function find_best_sequence(input_string varchar(255))
       RETURNS VARCHAR(255)
       DETERMINISTIC
begin
        declare orig varchar(255) default '';
        declare accum varchar(255) default '';
        declare current_seg varchar(255) default '';
        declare seg_end tinyint default 0;
        declare sorted varchar(255) default '';
        declare counter smallint default 1;
        declare next_sorted_seg varchar(255) default '';
        declare sorted_seg_end smallint default 0;
        
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
                  while (  length(orig) > 0) do
                        set seg_end = instr(orig, '&');
                        if (seg_end = 0) then
                           set seg_end = length(orig);
                           set orig = '';
                        else
                           set current_seg = substr(orig, 1, seg_end - 1);
                           set orig = substr(orig, seg_end + 1);
                        end if;

                        if length(sorted) = 0 then
                            set sorted = concat(current_seg, '&'); -- remember to chop trailing & later
                            
                        else
                            set counter = 1;
                            sorting: while (counter < length(sorted)) do
                                set sorted_seg_end = locate('&', sorted, counter + 1); -- alwas ends with ampersand
                                set next_sorted_seg = substr(sorted, counter + 1, sorted_seg_end - counter);
                                case 
                                       -- current_seg goes before other seg
                                      when (strcmp(current_seg, next_sorted_seg) = -1) then
                                           set sorted = concat( current_seg, '&', sorted);
                                           set current_seg = '';
                                           set sorted_seg_end = 0;
                                           leave sorting;
                                      
                                       -- current_seg goes after last seg in sorted
                                      when ((strcmp(current_seg, next_sorted_seg) = 1) and
                                            (sorted_seg_end = length(sorted))) then
                                           set sorted = concat( sorted, '&', current_seg);
                                           set current_seg = '';
                                           set sorted_seg_end = 0;
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
                                            set current_seg = '';
                                            set sorted_seg_end = 0;
                                            leave sorting;
                                      else
                                            set counter = sorted_seg_end + 1;
                                      end case;
                                  end while;
                                  end if;
                                end while;
                              end case;
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
drop function if exists query_sort$$
create function query_sort(input_string VARCHAR(255))
       RETURNS VARCHAR(255)
       deterministic

BEGIN
        DECLARE accum VARCHAR(250) DEFAULT '';
        DECLARE the_position INT DEFAULT 1;
        DECLARE no_more_rows INT DEFAULT 0;
        DECLARE inform VARCHAR(255) DEFAULT '';
        DECLARE parm VARCHAR(255) DEFAULT '';
        DECLARE local_accum VARCHAR(255) DEFAULT '';
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows=1; -- no_more_rows initialized at 0           
        
        IF (instr(input_string, '&') > 0) THEN
           
           CREATE TEMPORARY TABLE tmpUrlAccum(
                  param VARCHAR(255) primary key,
                  info  VARCHAR(255) default null);


           WHILE (locate('&', input_string, the_position) > 0) DO
                INSERT INTO tmpUrlAccum SET
                   param = substring(input_string, the_position, locate('=', input_string, the_position) -1),
                   info  = substring(input_string, locate('=', input_string, the_position) + 1,
                                                   locate('&', input_string, the_position) -1);

                   --  is there another '&'? 
                   IF ( locate('&', input_string, the_position) > 0) THEN
                     SET the_position = locate('&', input_string, the_position);
                   ELSE -- no more '&', so we grab the rest of the string
                     INSERT INTO tmpUrlAccum SET
                                param = substring(input_string, 
                                                  the_position + 1, 
                                                  locate('=', input_string, the_position) - 1),
                                info = substring(input_string,
                                                  locate('=', input_string, the_position) + 1);
                     SET the_position = length(input_string);
                   END IF;
           END WHILE;


           DECLARE our_cursor CURSOR FOR SELECT param, info
                                                 FROM tmpUrlAccum
                                                 ORDER BY param;

           OPEN our_cursor;
           REPEAT
                FETCH our_cursor INTO parm, inform;
                IF (length(accum) > 0) THEN
                   SET accum = concat(accum, '&', parm, '=', inform);
                ELSE
                   SET accum = concat(parm, '=', inform);
                END IF;
           UNTIL no_more_rows;
           END REPEAT;
           CLOSE our_cursor;
           SET no_more_rows = 0;
           DROP TABLE tmpUrlAccum;
      END IF;
           RETURN accum;
END$$      
delimiter ; 


                          
delimiter$$
drop function if exists url_whack$$
create function url_whack(input_url VARCHAR(250))
       RETURNS VARCHAR(250)
       DETERMINISTIC
begin
        DECLARE my_url VARCHAR(250) DEFAULT input_url ;
        DECLARE query_part VARCHAR(250) DEFAULT '';
        DECLARE query_start INT DEFAULT 0;

        IF (INSTR(input_url, '&')) THEN
           SET query_part=substring(input_url, 
                                    instr(input_url, '&') + 1);
           SET my_url=substring(input_url,
                                instr(input_url, '&') - 1);
        END IF;                                                                
        
        set my_url=lower(input_url);
        if ( substring(my_url, 1, 7) = 'http://') THEN
                set my_url=substring(my_url, 8);
        end if;

        set my_url=remove_end(my_url, 'index.php');
        set my_url=remove_end(my_url, 'index.html');
        set my_url=remove_end(my_url, 'index.htm');
        set my_url=remove_end(my_url, '/');         

        IF (substring(my_url, 1, 4) = 'www.') THEN
           SET my_url=substring(my_url, 5);
        END IF; 
        
        IF ( length(query_part) > 0) THEN
           SET my_url= concat( my_url, '&', query_sort(query_part));
        END IF;

        RETURN(my_url);        
end$$
delimiter ;





drop table if exists urltest;
create table urltest
       (id bigint(20) unsigned primary key auto_increment,
       url varchar(250) not null);

insert into urltest set url='http://example.com';
insert into urltest set url='example.com';
insert into urltest set url='http://example.com/';
insert into urltest set url='http://EXAMPLE.com/';
insert into urltest set url='http://www.example.com/index.htm';
insert into urltest set url='http://www.example.com/index.html';
insert into urltest set url='http://www.example.com/index.php';
insert into urltest set url='http://www.example.com/';
insert into urltest set url='http://www.example.com';
insert into urltest set url='http://www.example.com?user=bob&page=4';