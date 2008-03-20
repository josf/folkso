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
drop function if exists query_sort$$
create function query_sort(input_string varchar(255))
       RETURNS text
       DETERMINISTIC
begin
        declare orig varchar(255) default '';
        declare accum varchar(255) default '';
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


        set my_url=lower(input_url);

        IF (INSTR(my_url, '?')) THEN
           SET query_part=query_sort(
                                    substring(my_url, 
                                              instr(my_url, '?') + 1));
           SET my_url=substring(my_url, 1,
                                instr(my_url, '?'));
           set my_url = concat(my_url, query_part);
        END IF;                                                                
        

        if ( substring(my_url, 1, 7) = 'http://') THEN
                set my_url=substring(my_url, 8);
        end if;
        IF (substring(my_url, 1, 4) = 'www.') THEN
           SET my_url=substring(my_url, 5);
        END IF; 

        if (instr(my_url, ':80') and
         ( instr(my_url, ':80/') < instr(my_url, '/'))) then  -- port number before 1st slash
           set my_url = concat(
                substr(my_url, 1, instr(my_url, ':80/') - 1),
                substr(my_url, instr(my_url, ':80/') + 3));
        end if;

        if (instr(my_url, ':80?') and
            (instr(my_url, ':80?') < instr(my_url, '/'))) then
            set  my_url = concat(
                substr(my_url, 1, instr(my_url, ':80?') - 1),
                substr(my_url, instr(my_url, ':80?') + 3));
        end if;  


        set my_url=remove_end(my_url, 'index.php');
        set my_url=remove_end(my_url, 'index.html');
        set my_url=remove_end(my_url, 'index.htm');
        set my_url=remove_end(my_url, '/');         
        set my_url=remove_end(my_url, '?');

        RETURN(my_url);        
end$$
delimiter ;

delimiter $$
drop procedure if exists url_visit$$
create procedure url_visit(url varchar(255))

begin
declare found_url varchar(255) default '';
declare url_check varchar(255) default '';

        set url_check = url_whack(url);

        select uri_normal 
           into found_url
           from resource
           where uri_normal = url_check;

        if (length(found_url) > 0)  then
            update resource 
               set visited = visited + 1
               where uri_normal = url_check;
        else
            insert into resource
                    (uri_normal, uri_raw) 
                    values (url_check, url);
        end if;

        /* this is just a test */
        select visited from resource where uri_normal = url_check;
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
insert into urltest set url='http://example.com?user=bob';
insert into urltest set url='http://www.Example.com?page=44&aaa=ddd&bob';
insert into urltest set url='http://example.com:80/work?bob=slob&a=c';
insert into urltest set url='http://www.eXample.com:80?bob=theslob&action=quit';
insert into urltest set url='http://www.fabula.org/atelier.php?Biblioth%26egrave%3Bques%2C_Fables_et_M%26eacute%3Bmoires_en_acte%28s%29';